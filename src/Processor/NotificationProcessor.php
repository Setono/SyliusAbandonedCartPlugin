<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Processor;

use Doctrine\Persistence\ManagerRegistry;
use Setono\Doctrine\ORMTrait;
use Setono\SyliusAbandonedCartPlugin\EligibilityChecker\NotificationEligibilityCheckerInterface;
use Setono\SyliusAbandonedCartPlugin\Mailer\EmailManagerInterface;
use Setono\SyliusAbandonedCartPlugin\Model\NotificationInterface;
use Setono\SyliusAbandonedCartPlugin\Workflow\NotificationWorkflow;
use Symfony\Component\Workflow\Registry;
use Symfony\Component\Workflow\WorkflowInterface;
use Throwable;
use Twig\Error\Error;
use Webmozart\Assert\Assert;

final class NotificationProcessor implements NotificationProcessorInterface
{
    use ORMTrait;

    private ?WorkflowInterface $workflow = null;

    public function __construct(
        ManagerRegistry $managerRegistry,
        private readonly EmailManagerInterface $emailManager,
        private readonly Registry $workflowRegistry,
        private readonly NotificationEligibilityCheckerInterface $notificationEligibilityChecker,
    ) {
        $this->managerRegistry = $managerRegistry;
    }

    public function process(NotificationInterface $notification): void
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
                    $order = $notification->getCart();
                    Assert::notNull($order);

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
                    (string) ($e->getSourceContext()?->getName() ?? 'Unknown'), /** @phpstan-ignore cast.useless */
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
        $workflow = $this->getWorkflow($notification);

        if (null !== $callable) {
            if (!$workflow->can($notification, $transition)) {
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

        $workflow->apply($notification, $transition);

        $manager->flush();
    }

    private function getWorkflow(NotificationInterface $notification): WorkflowInterface
    {
        if (null === $this->workflow) {
            $this->workflow = $this->workflowRegistry->get($notification, NotificationWorkflow::NAME);
        }

        return $this->workflow;
    }
}
