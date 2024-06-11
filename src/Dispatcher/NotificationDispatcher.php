<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Dispatcher;

use DateInterval;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Setono\DoctrineObjectManagerTrait\ORM\ORMManagerTrait;
use Setono\SyliusAbandonedCartPlugin\Message\Command\ProcessNotification;
use Setono\SyliusAbandonedCartPlugin\Model\NotificationInterface;
use Setono\SyliusAbandonedCartPlugin\Repository\NotificationRepositoryInterface;
use Setono\SyliusAbandonedCartPlugin\Workflow\NotificationWorkflow;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Workflow\Registry;
use Symfony\Component\Workflow\WorkflowInterface;

final class NotificationDispatcher implements NotificationDispatcherInterface, LoggerAwareInterface
{
    use ORMManagerTrait;

    private LoggerInterface $logger;

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
        int $idleThresholdInMinutes,
    ) {
        $this->managerRegistry = $managerRegistry;
        $this->logger = new NullLogger();
        $this->commandBus = $commandBus;
        $this->notificationRepository = $notificationRepository;
        $this->workflowRegistry = $workflowRegistry;
        $this->idleThresholdInMinutes = $idleThresholdInMinutes;
    }

    public function dispatch(): void
    {
        $notifications = $this->notificationRepository->findNew(
            new DateInterval(sprintf('PT%dM', $this->idleThresholdInMinutes)),
        );

        $this->logger->debug(sprintf(
            'Notifications with associated orders that have not been updated for %d minutes will be notified',
            $this->idleThresholdInMinutes,
        ));

        $dispatchCount = 0;

        foreach ($notifications as $notification) {
            $workflow = $this->getWorkflow($notification);
            if (!$workflow->can($notification, NotificationWorkflow::TRANSITION_START)) {
                $this->logger->warning(sprintf(
                    'The notification with id %d could not take the transition "%s"',
                    (int) $notification->getId(),
                    NotificationWorkflow::TRANSITION_START,
                ));

                continue;
            }

            $workflow->apply($notification, NotificationWorkflow::TRANSITION_START);

            $this->getManager($notification)->flush();

            $this->commandBus->dispatch(new ProcessNotification($notification));

            ++$dispatchCount;
        }

        $this->logger->debug(sprintf('%d notifications dispatched for processing', $dispatchCount));
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    private function getWorkflow(NotificationInterface $notification): WorkflowInterface
    {
        if (null === $this->workflow) {
            $this->workflow = $this->workflowRegistry->get($notification, NotificationWorkflow::NAME);
        }

        return $this->workflow;
    }
}
