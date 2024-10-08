<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\EligibilityChecker;

use Setono\SyliusAbandonedCartPlugin\Model\NotificationInterface;
use Setono\SyliusAbandonedCartPlugin\Repository\UnsubscribedCustomerRepositoryInterface;

final class UnsubscribedCustomerNotificationEligibilityChecker implements NotificationEligibilityCheckerInterface
{
    public function __construct(private readonly UnsubscribedCustomerRepositoryInterface $unsubscribedCustomerRepository)
    {
    }

    public function check(NotificationInterface $notification): EligibilityCheck
    {
        $email = $notification->getEmail();
        if (null === $email) {
            // it's not the responsibility of this checker to check that the email is set
            return new EligibilityCheck(true);
        }

        if (!$this->unsubscribedCustomerRepository->isUnsubscribed($email)) {
            return new EligibilityCheck(true);
        }

        return new EligibilityCheck(false, [sprintf('The email %s is unsubscribed', $email)]);
    }
}
