<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Processor;

use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Setono\Doctrine\ORMTrait;
use Setono\SyliusAbandonedCartPlugin\DataProvider\PendingNotificationDataProviderInterface;
use Setono\SyliusAbandonedCartPlugin\EligibilityChecker\NotificationEligibilityCheckerInterface;
use Setono\SyliusAbandonedCartPlugin\Mailer\EmailManagerInterface;
use Setono\SyliusAbandonedCartPlugin\Model\NotificationInterface;
use Setono\SyliusAbandonedCartPlugin\Workflow\NotificationWorkflow;
use Symfony\Component\Workflow\WorkflowInterface;
use Throwable;
use Twig\Error\Error;

final class NotificationProcessor implements NotificationProcessorInterface, LoggerAwareInterface
{
    use ORMTrait;

    private LoggerInterface $logger;

    public function __construct(
        ManagerRegistry $managerRegistry,
        private readonly PendingNotificationDataProviderInterface $pendingNotificationDataProvider,
        private readonly EmailManagerInterface $emailManager,
        private readonly WorkflowInterface $workflow,
        private readonly NotificationEligibilityCheckerInterface $notificationEligibilityChecker,
    ) {
        $this->managerRegistry = $managerRegistry;
        $this->logger = new NullLogger();
    }

    public function process(): void
    {
        $processedCount = 0;

        foreach ($this->pendingNotificationDataProvider->getNotifications() as $notification) {
            try {
                $this->processNotification($notification);
                ++$processedCount;
            } catch (Throwable $e) {
                $this->logger->error(sprintf(
                    'Error processing notification %d: %s',
                    (int) $notification->getId(),
                    $e->getMessage(),
                ));
            }
        }

        $this->logger->debug(sprintf('%d notifications processed', $processedCount));
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    private function processNotification(NotificationInterface $notification): void
    {
        try {
            $this->tryTransition($notification, NotificationWorkflow::TRANSITION_PROCESS);

            $eligibilityCheck = $this->notificationEligibilityChecker->check($notification);
            if (!$eligibilityCheck->eligible) {
                $notification->addProcessingErrors($eligibilityCheck->reasons);

                $this->tryTransition($notification, NotificationWorkflow::TRANSITION_FAIL_ELIGIBILITY_CHECK);

                return;
            }

            $this->tryTransition(
                $notification,
                NotificationWorkflow::TRANSITION_SEND,
                function (NotificationInterface $notification) {
                    $this->emailManager->sendNotification($notification);
                },
            );
        } catch (Throwable $e) {
            $message = sprintf(
                'An unexpected error occurred when processing notification %d: %s',
                (int) $notification->getId(),
                $e->getMessage(),
            );

            if ($e instanceof Error) {
                $message = sprintf(
                    "A Twig error occurred when processing notification %d.\nTemplate: %s\nError: %s\nLine: %d",
                    (int) $notification->getId(),
                    (string) ($e->getSourceContext()?->getName() ?? 'Unknown'), /** @phpstan-ignore cast.useless,cast.string */
                    $e->getRawMessage(),
                    $e->getTemplateLine(),
                );
            }

            $notification->addProcessingError($message);

            $this->tryTransition($notification, NotificationWorkflow::TRANSITION_FAIL);

            throw $e;
        }
    }

    private function tryTransition(NotificationInterface $notification, string $transition, callable $callable = null): void
    {
        $manager = $this->getManager($notification);

        if (null !== $callable) {
            if (!$this->workflow->can($notification, $transition)) {
                $notification->addProcessingError(sprintf(
                    'Could not take transition "%s". The state when trying to take the transition was: "%s"',
                    $transition,
                    $notification->getState(),
                ));

                $this->tryTransition($notification, NotificationWorkflow::TRANSITION_FAIL);

                return;
            }

            $callable($notification);
        }

        $this->workflow->apply($notification, $transition);

        $manager->flush();
    }
}
