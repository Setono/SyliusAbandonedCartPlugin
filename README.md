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

### Add cronjob

The following command should run on a regular basis:

```bash
bin/console setono:sylius-abandoned-cart:process
```

also, if you want to prune the notifications table you can run:

```bash
bin/console setono:sylius-abandoned-cart:prune
```

[ico-version]: https://poser.pugx.org/setono/sylius-abandoned-cart-plugin/v/stable
[ico-license]: https://poser.pugx.org/setono/sylius-abandoned-cart-plugin/license
[ico-github-actions]: https://github.com/Setono/SyliusAbandonedCartPlugin/workflows/build/badge.svg
[ico-code-coverage]: https://codecov.io/gh/Setono/SyliusAbandonedCartPlugin/branch/master/graph/badge.svg

[link-packagist]: https://packagist.org/packages/setono/sylius-abandoned-cart-plugin
[link-github-actions]: https://github.com/Setono/SyliusAbandonedCartPlugin/actions
[link-code-coverage]: https://codecov.io/gh/Setono/SyliusAbandonedCartPlugin
