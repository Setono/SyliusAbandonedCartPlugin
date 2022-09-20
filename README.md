# Setono SyliusAbandonedCartPlugin

[![Latest Version][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE)
[![Build Status][ico-github-actions]][link-github-actions]
[![Code Coverage][ico-code-coverage]][link-code-coverage]

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
