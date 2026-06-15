<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Tests\Unit\Processor;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Setono\SyliusAbandonedCartPlugin\DataProvider\PendingNotificationDataProviderInterface;
use Setono\SyliusAbandonedCartPlugin\EligibilityChecker\EligibilityCheck;
use Setono\SyliusAbandonedCartPlugin\EligibilityChecker\NotificationEligibilityCheckerInterface;
use Setono\SyliusAbandonedCartPlugin\Mailer\EmailManagerInterface;
use Setono\SyliusAbandonedCartPlugin\Model\NotificationInterface;
use Setono\SyliusAbandonedCartPlugin\Processor\NotificationProcessor;
use Setono\SyliusAbandonedCartPlugin\Workflow\NotificationWorkflow;
use Symfony\Component\Workflow\WorkflowInterface;
use Twig\Error\Error;
use Twig\Source;

final class NotificationProcessorTest extends TestCase
{
    use ProphecyTrait;

    #[Test]
    public function it_sends_eligible_notification(): void
    {
        $notification = $this->prophesize(NotificationInterface::class);
        $notification->getId()->willReturn(1);
        $notification->getState()->willReturn(NotificationWorkflow::STATE_PENDING);

        $pendingNotificationDataProvider = $this->prophesize(PendingNotificationDataProviderInterface::class);
        $pendingNotificationDataProvider->getNotifications()->willReturn([$notification->reveal()]);

        $emailManager = $this->prophesize(EmailManagerInterface::class);
        $emailManager->sendNotification($notification->reveal())->shouldBeCalled();

        $workflow = $this->prophesize(WorkflowInterface::class);
        $workflow->can($notification->reveal(), NotificationWorkflow::TRANSITION_SEND)->willReturn(true);
        $workflow->apply($notification->reveal(), NotificationWorkflow::TRANSITION_PROCESS)->shouldBeCalled();
        $workflow->apply($notification->reveal(), NotificationWorkflow::TRANSITION_SEND)->shouldBeCalled();

        $eligibilityChecker = $this->prophesize(NotificationEligibilityCheckerInterface::class);
        $eligibilityChecker->check($notification->reveal())->willReturn(new EligibilityCheck(true));

        $entityManager = $this->prophesize(EntityManagerInterface::class);
        $entityManager->flush()->shouldBeCalled();

        $managerRegistry = $this->prophesize(ManagerRegistry::class);
        $managerRegistry->getManagerForClass(Argument::any())->willReturn($entityManager->reveal());

        $processor = new NotificationProcessor(
            $managerRegistry->reveal(),
            $pendingNotificationDataProvider->reveal(),
            $emailManager->reveal(),
            $workflow->reveal(),
            $eligibilityChecker->reveal(),
        );

        $processor->process();
    }

    #[Test]
    public function it_marks_ineligible_notification(): void
    {
        $notification = $this->prophesize(NotificationInterface::class);
        $notification->getId()->willReturn(1);
        $notification->getState()->willReturn(NotificationWorkflow::STATE_PENDING);
        $notification->addProcessingErrors(['Customer unsubscribed'])->shouldBeCalled();

        $pendingNotificationDataProvider = $this->prophesize(PendingNotificationDataProviderInterface::class);
        $pendingNotificationDataProvider->getNotifications()->willReturn([$notification->reveal()]);

        $emailManager = $this->prophesize(EmailManagerInterface::class);
        $emailManager->sendNotification(Argument::any())->shouldNotBeCalled();

        $workflow = $this->prophesize(WorkflowInterface::class);
        $workflow->apply($notification->reveal(), NotificationWorkflow::TRANSITION_PROCESS)->shouldBeCalled();
        $workflow->apply($notification->reveal(), NotificationWorkflow::TRANSITION_FAIL_ELIGIBILITY_CHECK)->shouldBeCalled();

        $eligibilityChecker = $this->prophesize(NotificationEligibilityCheckerInterface::class);
        $eligibilityChecker->check($notification->reveal())->willReturn(new EligibilityCheck(false, ['Customer unsubscribed']));

        $entityManager = $this->prophesize(EntityManagerInterface::class);
        $entityManager->flush()->shouldBeCalled();

        $managerRegistry = $this->prophesize(ManagerRegistry::class);
        $managerRegistry->getManagerForClass(Argument::any())->willReturn($entityManager->reveal());

        $processor = new NotificationProcessor(
            $managerRegistry->reveal(),
            $pendingNotificationDataProvider->reveal(),
            $emailManager->reveal(),
            $workflow->reveal(),
            $eligibilityChecker->reveal(),
        );

        $processor->process();
    }

    #[Test]
    public function it_fails_notification_when_email_sending_throws(): void
    {
        $notification = $this->prophesize(NotificationInterface::class);
        $notification->getId()->willReturn(1);
        $notification->getState()->willReturn(NotificationWorkflow::STATE_PROCESSING);
        $notification->addProcessingError(Argument::containingString('An unexpected error occurred'))->shouldBeCalled();

        $pendingNotificationDataProvider = $this->prophesize(PendingNotificationDataProviderInterface::class);
        $pendingNotificationDataProvider->getNotifications()->willReturn([$notification->reveal()]);

        $emailManager = $this->prophesize(EmailManagerInterface::class);
        $emailManager->sendNotification($notification->reveal())->willThrow(new RuntimeException('SMTP connection failed'));

        $workflow = $this->prophesize(WorkflowInterface::class);
        $workflow->apply($notification->reveal(), NotificationWorkflow::TRANSITION_PROCESS)->shouldBeCalled();
        $workflow->can($notification->reveal(), NotificationWorkflow::TRANSITION_SEND)->willReturn(true);
        $workflow->apply($notification->reveal(), NotificationWorkflow::TRANSITION_SEND)->shouldNotBeCalled();
        $workflow->apply($notification->reveal(), NotificationWorkflow::TRANSITION_FAIL)->shouldBeCalled();

        $eligibilityChecker = $this->prophesize(NotificationEligibilityCheckerInterface::class);
        $eligibilityChecker->check($notification->reveal())->willReturn(new EligibilityCheck(true));

        $entityManager = $this->prophesize(EntityManagerInterface::class);
        $entityManager->flush()->shouldBeCalled();

        $managerRegistry = $this->prophesize(ManagerRegistry::class);
        $managerRegistry->getManagerForClass(Argument::any())->willReturn($entityManager->reveal());

        $processor = new NotificationProcessor(
            $managerRegistry->reveal(),
            $pendingNotificationDataProvider->reveal(),
            $emailManager->reveal(),
            $workflow->reveal(),
            $eligibilityChecker->reveal(),
        );

        // The exception is caught in the outer process() loop and logged
        $processor->process();
    }

    #[Test]
    public function it_completes_without_errors_when_no_pending_notifications(): void
    {
        $pendingNotificationDataProvider = $this->prophesize(PendingNotificationDataProviderInterface::class);
        $pendingNotificationDataProvider->getNotifications()->willReturn([]);

        $emailManager = $this->prophesize(EmailManagerInterface::class);
        $workflow = $this->prophesize(WorkflowInterface::class);
        $eligibilityChecker = $this->prophesize(NotificationEligibilityCheckerInterface::class);
        $managerRegistry = $this->prophesize(ManagerRegistry::class);

        $processor = new NotificationProcessor(
            $managerRegistry->reveal(),
            $pendingNotificationDataProvider->reveal(),
            $emailManager->reveal(),
            $workflow->reveal(),
            $eligibilityChecker->reveal(),
        );

        $processor->process();

        $emailManager->sendNotification(Argument::any())->shouldNotHaveBeenCalled();
    }

    #[Test]
    public function it_records_a_descriptive_error_when_rendering_the_email_throws_a_twig_error(): void
    {
        $notification = $this->prophesize(NotificationInterface::class);
        $notification->getId()->willReturn(7);
        $notification->getState()->willReturn(NotificationWorkflow::STATE_PROCESSING);
        $notification->addProcessingError(Argument::containingString('A Twig error occurred'))->shouldBeCalled();

        $pendingNotificationDataProvider = $this->prophesize(PendingNotificationDataProviderInterface::class);
        $pendingNotificationDataProvider->getNotifications()->willReturn([$notification->reveal()]);

        $twigError = new Error('Boom');
        $twigError->setSourceContext(new Source('', 'notification.html.twig'));

        $emailManager = $this->prophesize(EmailManagerInterface::class);
        $emailManager->sendNotification($notification->reveal())->willThrow($twigError);

        $workflow = $this->prophesize(WorkflowInterface::class);
        $workflow->apply($notification->reveal(), NotificationWorkflow::TRANSITION_PROCESS)->shouldBeCalled();
        $workflow->can($notification->reveal(), NotificationWorkflow::TRANSITION_SEND)->willReturn(true);
        $workflow->apply($notification->reveal(), NotificationWorkflow::TRANSITION_FAIL)->shouldBeCalled();

        $eligibilityChecker = $this->prophesize(NotificationEligibilityCheckerInterface::class);
        $eligibilityChecker->check($notification->reveal())->willReturn(new EligibilityCheck(true));

        $entityManager = $this->prophesize(EntityManagerInterface::class);
        $entityManager->flush()->shouldBeCalled();

        $managerRegistry = $this->prophesize(ManagerRegistry::class);
        $managerRegistry->getManagerForClass(Argument::any())->willReturn($entityManager->reveal());

        $processor = new NotificationProcessor(
            $managerRegistry->reveal(),
            $pendingNotificationDataProvider->reveal(),
            $emailManager->reveal(),
            $workflow->reveal(),
            $eligibilityChecker->reveal(),
        );

        // The Twig error is caught and logged by the outer process() loop
        $processor->process();
    }

    #[Test]
    public function it_fails_the_notification_when_the_send_transition_is_not_allowed(): void
    {
        $notification = $this->prophesize(NotificationInterface::class);
        $notification->getId()->willReturn(3);
        $notification->getState()->willReturn(NotificationWorkflow::STATE_PROCESSING);
        $notification->addProcessingError(Argument::containingString('Could not take transition'))->shouldBeCalled();

        $pendingNotificationDataProvider = $this->prophesize(PendingNotificationDataProviderInterface::class);
        $pendingNotificationDataProvider->getNotifications()->willReturn([$notification->reveal()]);

        $emailManager = $this->prophesize(EmailManagerInterface::class);
        $emailManager->sendNotification(Argument::any())->shouldNotBeCalled();

        $workflow = $this->prophesize(WorkflowInterface::class);
        $workflow->apply($notification->reveal(), NotificationWorkflow::TRANSITION_PROCESS)->shouldBeCalled();
        $workflow->can($notification->reveal(), NotificationWorkflow::TRANSITION_SEND)->willReturn(false);
        $workflow->apply($notification->reveal(), NotificationWorkflow::TRANSITION_FAIL)->shouldBeCalled();

        $eligibilityChecker = $this->prophesize(NotificationEligibilityCheckerInterface::class);
        $eligibilityChecker->check($notification->reveal())->willReturn(new EligibilityCheck(true));

        $entityManager = $this->prophesize(EntityManagerInterface::class);
        $entityManager->flush()->shouldBeCalled();

        $managerRegistry = $this->prophesize(ManagerRegistry::class);
        $managerRegistry->getManagerForClass(Argument::any())->willReturn($entityManager->reveal());

        $processor = new NotificationProcessor(
            $managerRegistry->reveal(),
            $pendingNotificationDataProvider->reveal(),
            $emailManager->reveal(),
            $workflow->reveal(),
            $eligibilityChecker->reveal(),
        );

        $processor->process();
    }

    #[Test]
    public function it_logs_processing_errors_through_the_injected_logger(): void
    {
        $notification = $this->prophesize(NotificationInterface::class);
        $notification->getId()->willReturn(9);
        $notification->getState()->willReturn(NotificationWorkflow::STATE_PROCESSING);
        $notification->addProcessingError(Argument::any())->shouldBeCalled();

        $pendingNotificationDataProvider = $this->prophesize(PendingNotificationDataProviderInterface::class);
        $pendingNotificationDataProvider->getNotifications()->willReturn([$notification->reveal()]);

        $emailManager = $this->prophesize(EmailManagerInterface::class);
        $emailManager->sendNotification($notification->reveal())->willThrow(new RuntimeException('SMTP down'));

        $workflow = $this->prophesize(WorkflowInterface::class);
        $workflow->apply($notification->reveal(), NotificationWorkflow::TRANSITION_PROCESS)->shouldBeCalled();
        $workflow->can($notification->reveal(), NotificationWorkflow::TRANSITION_SEND)->willReturn(true);
        $workflow->apply($notification->reveal(), NotificationWorkflow::TRANSITION_FAIL)->shouldBeCalled();

        $eligibilityChecker = $this->prophesize(NotificationEligibilityCheckerInterface::class);
        $eligibilityChecker->check($notification->reveal())->willReturn(new EligibilityCheck(true));

        $entityManager = $this->prophesize(EntityManagerInterface::class);
        $entityManager->flush()->shouldBeCalled();

        $managerRegistry = $this->prophesize(ManagerRegistry::class);
        $managerRegistry->getManagerForClass(Argument::any())->willReturn($entityManager->reveal());

        $logger = $this->prophesize(LoggerInterface::class);

        $processor = new NotificationProcessor(
            $managerRegistry->reveal(),
            $pendingNotificationDataProvider->reveal(),
            $emailManager->reveal(),
            $workflow->reveal(),
            $eligibilityChecker->reveal(),
        );
        $processor->setLogger($logger->reveal());

        $processor->process();

        $logger->error(Argument::containingString('Error processing notification 9'))->shouldHaveBeenCalled();
    }
}
