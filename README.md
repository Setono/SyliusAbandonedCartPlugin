# Abandoned cart plugin for Sylius

[![Latest Stable Version](http://poser.pugx.org/setono/sylius-abandoned-cart-plugin/v)](https://packagist.org/packages/setono/sylius-abandoned-cart-plugin)
[![Total Downloads](http://poser.pugx.org/setono/sylius-abandoned-cart-plugin/downloads)](https://packagist.org/packages/setono/sylius-abandoned-cart-plugin)
[![License](http://poser.pugx.org/setono/sylius-abandoned-cart-plugin/license)](https://packagist.org/packages/setono/sylius-abandoned-cart-plugin)
[![PHP Version Require](http://poser.pugx.org/setono/sylius-abandoned-cart-plugin/require/php)](https://packagist.org/packages/setono/sylius-abandoned-cart-plugin)
[![build](https://github.com/Setono/SyliusAbandonedCartPlugin/actions/workflows/build.yaml/badge.svg)](https://github.com/Setono/SyliusAbandonedCartPlugin/actions/workflows/build.yaml)
[![codecov](https://codecov.io/gh/Setono/SyliusAbandonedCartPlugin/graph/badge.svg?token=r7Bhm7aYCl)](https://codecov.io/gh/Setono/SyliusAbandonedCartPlugin)
[![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2FSetono%2FSyliusAbandonedCartPlugin%2F3.x)](https://dashboard.stryker-mutator.io/reports/github.com/Setono/SyliusAbandonedCartPlugin/3.x)

Recover lost sales on autopilot. This plugin spots shopping carts your customers leave behind,
then emails them a friendly reminder with a **one-click link back to their cart** — and a
compliant **unsubscribe link**. Everything is tracked in the admin panel so you can see exactly
which carts were notified, recovered, or opted out.

## Features

- 🛒 **Automatic idle-cart detection** — finds carts that have been sitting untouched for a configurable amount of time.
- ✉️ **Re-engagement emails** — a clean, localized email with a recovery call-to-action and an unsubscribe link.
- 🔁 **One-click cart recovery** — the recovery link restores the customer's cart and records the click for engagement tracking.
- 🚫 **Built-in opt-out & eligibility rules** — never email customers who unsubscribed, and optionally target newsletter subscribers only.
- 📊 **Admin overview** — a grid under *Marketing → Abandoned cart* listing every notification with its state, channel, customer and revenue.
- 🧩 **Extensible by design** — add your own eligibility rules, tweak the idle-cart query, or override the email template.
- 🌍 **15 locales out of the box** — `en`, `da`, `de`, `es`, `fi`, `fr`, `hu`, `it`, `nl`, `no`, `pl`, `pt`, `ro`, `sv`, `uk`.

## How it works

The plugin runs as a small pipeline, driven by two commands you schedule on a cron:

1. **Detect** — `create-notifications` finds carts that have been idle for `idle_threshold` minutes
   and creates a `Notification` for each (initial state: `pending`).
2. **Process** — `process-notifications` runs every pending notification through the eligibility
   checks, sends the email, and transitions it to `sent` (or `ineligible` if a check fails).
3. **Recover** — the email contains a **recovery link** (restores the cart and tracks the click)
   and an **unsubscribe link** (records the opt-out so the customer is never emailed again).

Each notification moves through a state machine:

```
pending ──► processing ──► sent
                       └──► ineligible
(any state) ──► failed
```

## Requirements

| Package | Version          |
|---------|------------------|
| PHP     | 8.2+             |
| Sylius  | ^2.0             |
| Symfony | 6.4 or 7.4       |

> **Using Sylius 1.x?** This branch (`3.x`) targets Sylius 2. For Sylius 1.x support, use the [`2.x`](https://github.com/Setono/SyliusAbandonedCartPlugin/tree/2.x) branch.

## Installation

### 1. Require the plugin

```bash
composer require setono/sylius-abandoned-cart-plugin
```

### 2. Register the bundle

Add the plugin to `config/bundles.php` **before** `SyliusGridBundle` — otherwise you'll get a
`non-existent parameter "setono_sylius_abandoned_cart.model.notification.class"` error, because the
grid configuration references parameters the plugin registers.

```php
<?php
// config/bundles.php

return [
    // ...
    Setono\SyliusAbandonedCartPlugin\SetonoSyliusAbandonedCartPlugin::class => ['all' => true],
    Sylius\Bundle\GridBundle\SyliusGridBundle::class => ['all' => true],
    // ...
];
```

### 3. Import the routes

```yaml
# config/routes/setono_sylius_abandoned_cart.yaml
setono_sylius_abandoned_cart:
    resource: "@SetonoSyliusAbandonedCartPlugin/config/routes.yaml"
```

This registers the admin grid routes (under `/admin/abandoned-cart`) and the shop routes for cart
recovery and unsubscribe.

### 4. Configure the salt

The unsubscribe links are signed with a SHA-256 hash. Set a secret salt and **change it in
production** — anyone who knows it could forge unsubscribe links.

```yaml
# config/packages/setono_sylius_abandoned_cart.yaml
setono_sylius_abandoned_cart:
    salt: '%env(ABANDONED_CART_SALT)%' # or any secret string
```

### 5. Install assets

```bash
bin/console assets:install
```

### 6. Update your database schema

```bash
bin/console doctrine:migrations:diff    # generate a migration
bin/console doctrine:migrations:migrate # apply it
```

### 7. Schedule the commands

The plugin needs `create-notifications` and `process-notifications` to run regularly. A typical
crontab:

```cron
# Detect newly-idle carts (run more often than `lookback_window`, e.g. every 5 minutes)
*/5 * * * * cd /path/to/your/app && bin/console setono:sylius-abandoned-cart:create-notifications

# Send emails for pending notifications
*/5 * * * * cd /path/to/your/app && bin/console setono:sylius-abandoned-cart:process-notifications

# Clean up old notifications once a day
0 3 * * *   cd /path/to/your/app && bin/console setono:sylius-abandoned-cart:prune-notifications
```

## Commands

| Command                                              | What it does                                                                 |
|------------------------------------------------------|------------------------------------------------------------------------------|
| `setono:sylius-abandoned-cart:create-notifications`  | Finds idle carts and creates notifications. Supports `--dry-run` to preview. |
| `setono:sylius-abandoned-cart:process-notifications` | Runs eligibility checks and sends the emails.                                |
| `setono:sylius-abandoned-cart:prune-notifications`   | Deletes notifications older than `prune_older_than`.                         |

Preview which carts would be picked up, without persisting anything:

```bash
bin/console setono:sylius-abandoned-cart:create-notifications --dry-run
```

## Configuration reference

```yaml
# config/packages/setono_sylius_abandoned_cart.yaml
setono_sylius_abandoned_cart:
    # Secret salt used to sign unsubscribe URLs (SHA-256). Required — change it in production.
    salt: '%env(ABANDONED_CART_SALT)%'

    # Minutes a cart must be idle before it's eligible for a notification (default: 60)
    idle_threshold: 60

    # Only carts that became idle within this many minutes are picked up, which caps how many
    # notifications are created per run. Run create-notifications more often than this. (default: 15)
    lookback_window: 15

    # Prune notifications older than this many minutes (default: 43200 = 30 days)
    prune_older_than: 43200

    eligibility_checkers:
        # Skip customers who actively unsubscribed (default: true)
        unsubscribed_customer: true

        # Only notify customers subscribed to the newsletter (default: false)
        subscribed_to_newsletter: false
```

## Customization

### Add a custom eligibility rule

Implement `NotificationEligibilityCheckerInterface` and return an `EligibilityCheck`. With Symfony
autoconfiguration enabled (the default), the plugin discovers and registers your checker
automatically — no service config needed.

```php
<?php

declare(strict_types=1);

namespace App\EligibilityChecker;

use Setono\SyliusAbandonedCartPlugin\EligibilityChecker\EligibilityCheck;
use Setono\SyliusAbandonedCartPlugin\EligibilityChecker\NotificationEligibilityCheckerInterface;
use Setono\SyliusAbandonedCartPlugin\Model\NotificationInterface;

final class MinimumCartTotalEligibilityChecker implements NotificationEligibilityCheckerInterface
{
    public function check(NotificationInterface $notification): EligibilityCheck
    {
        $cart = $notification->getCart();

        if (null === $cart || $cart->getTotal() < 5000) {
            // The reason is stored on the notification for debugging.
            return new EligibilityCheck(false, 'Cart total is below the minimum');
        }

        return new EligibilityCheck(true);
    }
}
```

> If you've disabled autoconfiguration, tag the service manually with
> `setono_sylius_abandoned_cart.notification_eligibility_checker`.

### Customize which carts are considered "idle"

The idle-cart query is dispatched as a `QueryBuilderForIdleCartsCreated` event before it runs, so you
can add your own constraints. The cart root alias is `o`:

```php
<?php

declare(strict_types=1);

namespace App\EventListener;

use Setono\SyliusAbandonedCartPlugin\Event\QueryBuilderForIdleCartsCreated;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
final class OnlyNotifyMainChannelListener
{
    public function __invoke(QueryBuilderForIdleCartsCreated $event): void
    {
        $event->queryBuilder
            ->andWhere('o.channel = :channel')
            ->setParameter('channel', 1);
    }
}
```

### Override the email template

Copy the template into your app and edit it — Symfony's template overriding does the rest:

```
templates/bundles/SetonoSyliusAbandonedCartPlugin/email/notification.html.twig
```

### Override translations

Override any string by redefining its key (under the `setono_sylius_abandoned_cart.*` prefix) in your
app's `translations/messages.<locale>.yml`.

## Contributing

```bash
composer install

composer phpunit       # run the test suite
composer analyse       # PHPStan (max level)
composer check-style   # coding standards (ECS)
composer fix-style     # auto-fix coding standards
```

A full Sylius test application lives in `tests/Application` for functional tests and manual
testing. See [`CLAUDE.md`](CLAUDE.md) for architecture notes and developer conventions.

## License

This plugin is released under the [MIT License](LICENSE).
