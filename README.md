# Abandoned cart plugin for Sylius

[![Latest Stable Version](http://poser.pugx.org/setono/sylius-abandoned-cart-plugin/v)](https://packagist.org/packages/setono/sylius-abandoned-cart-plugin)
[![Total Downloads](http://poser.pugx.org/setono/sylius-abandoned-cart-plugin/downloads)](https://packagist.org/packages/setono/sylius-abandoned-cart-plugin)
[![License](http://poser.pugx.org/setono/sylius-abandoned-cart-plugin/license)](https://packagist.org/packages/setono/sylius-abandoned-cart-plugin)
[![PHP Version Require](http://poser.pugx.org/setono/sylius-abandoned-cart-plugin/require/php)](https://packagist.org/packages/setono/sylius-abandoned-cart-plugin)
[![build](https://github.com/Setono/SyliusAbandonedCartPlugin/actions/workflows/build.yaml/badge.svg)](https://github.com/Setono/SyliusAbandonedCartPlugin/actions/workflows/build.yaml)
[![codecov](https://codecov.io/gh/Setono/SyliusAbandonedCartPlugin/graph/badge.svg?token=r7Bhm7aYCl)](https://codecov.io/gh/Setono/SyliusAbandonedCartPlugin)
[![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2FSetono%2FSyliusAbandonedCartPlugin%2F1.x)](https://dashboard.stryker-mutator.io/reports/github.com/Setono/SyliusAbandonedCartPlugin/1.x)

## Installation

```bash
composer require setono/sylius-abandoned-cart-plugin
```

### Add bundle to `config/bundles.php`:

Make sure you add it before `SyliusGridBundle`, otherwise you'll get
`You have requested a non-existent parameter "setono_sylius_abandoned_cart.model.notification.class".` exception.

```php
<?php
// config/bundles.php

return [
    // ...
    Setono\SyliusAbandonedCartPlugin\SetonoSyliusAbandonedCartPlugin::class => ['all' => true],
];
```

### Configure plugin

```yaml
# config/packages/setono_sylius_abandoned_cart.yaml
imports:
    - { resource: "@SetonoSyliusAbandonedCartPlugin/Resources/config/app/config.yaml" }

setono_sylius_abandoned_cart:
    # When unsubscribing a customer a hash is used to prevent false unsubscribes. This hash is generated using this salt.
    salt: your_secret_salt
```

### Configure routes

```yaml
# config/routes/setono_sylius_abandoned_cart.yaml
setono_sylius_abandoned_cart:
    resource: "@SetonoSyliusAbandonedCartPlugin/Resources/config/routes.yaml"
```

### Install assets

```bash
bin/console assets:install
```

### Update your schema

```bash
# Generate and edit migration
bin/console doctrine:migrations:diff

# Then apply migration
bin/console doctrine:migrations:migrate
```

### Add cronjobs

The plugin requires two commands to run on a regular basis:

```bash
# Create notifications for idle carts (should run frequently, e.g., every 5 minutes)
bin/console setono:sylius-abandoned-cart:create-notifications

# Process pending notifications and send emails
bin/console setono:sylius-abandoned-cart:process
```

The `create-notifications` command finds carts that have been idle for the configured threshold and creates notification records for them. The `process` command then sends the actual emails.

You can test the `create-notifications` command without persisting anything:

```bash
bin/console setono:sylius-abandoned-cart:create-notifications --dry-run
```

To clean up old notifications, run the prune command (e.g., daily):

```bash
bin/console setono:sylius-abandoned-cart:prune
```

### Configuration options

```yaml
# config/packages/setono_sylius_abandoned_cart.yaml
setono_sylius_abandoned_cart:
    salt: your_secret_salt

    # Minutes before a cart is considered idle (default: 60)
    idle_threshold: 60

    # Lookback window in minutes - only carts that became idle within this window
    # will be selected for notification creation. This limits the number of
    # notifications created per command run. (default: 15)
    # Important: Run the create-notifications command more frequently than this value
    lookback_window: 15

    # Prune notifications older than this many minutes (default: 43200 = 30 days)
    prune_older_than: 43200
```

[ico-version]: https://poser.pugx.org/setono/sylius-abandoned-cart-plugin/v/stable
[ico-license]: https://poser.pugx.org/setono/sylius-abandoned-cart-plugin/license
[ico-github-actions]: https://github.com/Setono/SyliusAbandonedCartPlugin/workflows/build/badge.svg
[ico-code-coverage]: https://codecov.io/gh/Setono/SyliusAbandonedCartPlugin/branch/master/graph/badge.svg

[link-packagist]: https://packagist.org/packages/setono/sylius-abandoned-cart-plugin
[link-github-actions]: https://github.com/Setono/SyliusAbandonedCartPlugin/actions
[link-code-coverage]: https://codecov.io/gh/Setono/SyliusAbandonedCartPlugin
