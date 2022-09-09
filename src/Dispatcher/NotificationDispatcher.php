<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Dispatcher;

use DateInterval;
use Doctrine\Persistence\ManagerRegistry;
use Setono\DoctrineObjectManagerTrait\ORM\ORMManagerTrait;
use Setono\SyliusAbandonedCartPlugin\Message\Command\ProcessNotification;
use Setono\SyliusAbandonedCartPlugin\Model\NotificationInterface;
use Setono\SyliusAbandonedCartPlugin\Repository\NotificationRepositoryInterface;
use Setono\SyliusAbandonedCartPlugin\Workflow\NotificationWorkflow;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Workflow\Registry;
use Symfony\Component\Workflow\WorkflowInterface;

final class NotificationDispatcher implements NotificationDispatcherInterface
{
    use ORMManagerTrait;

    private ?WorkflowInterface $workflow = null;

    private MessageBusInterface $commandBus;

    private NotificationRepositoryInterface $notificationRepository;

    private Registry $workflowRegistry;

    private int $idleThresholdInMinutes;

    public function __construct(
        ManagerRegistry $managerRegistry,
        MessageBusInterface $commandBus,
        NotificationRepositoryInterface $notificationRepository,
        Registry $workflowRegistry,
        int $idleThresholdInMinutes
    ) {
        $this->managerRegistry = $managerRegistry;
        $this->commandBus = $commandBus;
        $this->notificationRepository = $notificationRepository;
        $this->workflowRegistry = $workflowRegistry;
        $this->idleThresholdInMinutes = $idleThresholdInMinutes;
    }

    public function dispatch(): void
    {
        $notifications = $this->notificationRepository->findNew(
            new DateInterval(sprintf('PT%dM', $this->idleThresholdInMinutes))
        );

        foreach ($notifications as $notification) {
            $workflow = $this->getWorkflow($notification);
            if (!$workflow->can($notification, NotificationWorkflow::TRANSITION_START)) {
                continue;
            }

            $workflow->apply($notification, NotificationWorkflow::TRANSITION_START);

            $this->getManager($notification)->flush();

            $this->commandBus->dispatch(new ProcessNotification($notification));
        }
    }

    private function getWorkflow(NotificationInterface $notification): WorkflowInterface
    {
        if (null === $this->workflow) {
            $this->workflow = $this->workflowRegistry->get($notification, NotificationWorkflow::NAME);
        }

        return $this->workflow;
    }
}
