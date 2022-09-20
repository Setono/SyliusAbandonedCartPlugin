<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\EligibilityChecker;

use Setono\SyliusAbandonedCartPlugin\Model\NotificationInterface;
use Webmozart\Assert\Assert;

final class SubscribedToNewsletterNotificationEligibilityChecker implements NotificationEligibilityCheckerInterface
{
    public function check(NotificationInterface $notification): EligibilityCheck
    {
        $cart = $notification->getCart();
        Assert::notNull($cart);

        $customer = $cart->getCustomer();
        if (null === $customer) {
            // it is not the responsibility of this class to check this
            return new EligibilityCheck(true);
        }

        return $customer->isSubscribedToNewsletter() ? new EligibilityCheck(true) : new EligibilityCheck(false, ['The customer is not subscribed to your newsletter']);
    }
}
