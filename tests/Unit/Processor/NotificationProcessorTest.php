<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Tests\Unit\Processor;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Setono\SyliusAbandonedCartPlugin\DataProvider\PendingNotificationDataProviderInterface;
use Setono\SyliusAbandonedCartPlugin\EligibilityChecker\EligibilityCheck;
use Setono\SyliusAbandonedCartPlugin\EligibilityChecker\NotificationEligibilityCheckerInterface;
use Setono\SyliusAbandonedCartPlugin\Mailer\EmailManagerInterface;
use Setono\SyliusAbandonedCartPlugin\Model\NotificationInterface;
use Setono\SyliusAbandonedCartPlugin\Processor\NotificationProcessor;
use Setono\SyliusAbandonedCartPlugin\Workflow\NotificationWorkflow;
use Symfony\Component\Workflow\WorkflowInterface;

final class NotificationProcessorTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @test
     */
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

    /**
     * @test
     */
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

    /**
     * @test
     */
    public function it_fails_notification_when_email_sending_throws(): void
    {
        $notification = $this->prophesize(NotificationInterface::class);
        $notification->getId()->willReturn(1);
        $notification->getState()->willReturn(NotificationWorkflow::STATE_PROCESSING);
        $notification->addProcessingError(Argument::containingString('An unexpected error occurred'))->shouldBeCalled();

        $pendingNotificationDataProvider = $this->prophesize(PendingNotificationDataProviderInterface::class);
        $pendingNotificationDataProvider->getNotifications()->willReturn([$notification->reveal()]);

        $emailManager = $this->prophesize(EmailManagerInterface::class);
        $emailManager->sendNotification($notification->reveal())->willThrow(new \RuntimeException('SMTP connection failed'));

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

    /**
     * @test
     */
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
}
