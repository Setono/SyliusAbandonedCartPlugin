# Upgrade Guide

## From `2.x` to `3.x` (Sylius 1 → Sylius 2)

Version `3.x` of this plugin targets **Sylius 2** only. Support for Sylius 1 has been dropped.

### Requirements

| Requirement | `2.x` (Sylius 1) | `3.x` (Sylius 2) |
|-------------|------------------|------------------|
| PHP         | `>=8.1`          | `>=8.2`          |
| Symfony     | `^6.4`           | `^6.4 \|\| ^7.4` |
| Sylius      | `^1.0`           | `^2.0`           |
| Doctrine ORM| `^2.0 \|\| ^3.0` | `^2.0 \|\| ^3.0` |

Upgrade your application to Sylius 2 first (see the official
[Sylius upgrade guide](https://docs.sylius.com/the-book/upgrades)), then bump this plugin:

```bash
composer require setono/sylius-abandoned-cart-plugin:^3.0
```

### Breaking changes you need to act on

#### 1. Route import path changed

The plugin's resources moved from `src/Resources/` to the repository root, so the route import path
changed. Update `config/routes/setono_sylius_abandoned_cart.yaml` in your application:

```diff
 setono_sylius_abandoned_cart:
-    resource: "@SetonoSyliusAbandonedCartPlugin/Resources/config/routes.yaml"
+    resource: "@SetonoSyliusAbandonedCartPlugin/config/routes.yaml"
```

#### 2. Admin UI rewritten for Bootstrap 5 / Twig Hooks

The Sylius 2 admin uses Bootstrap 5 and Twig Hooks instead of Semantic UI and `sylius_ui` events.
The plugin's admin grid templates (notification state badge, cart link, revenue, the
"unsubscribed customers" grid action) were rewritten accordingly. **If you overrode any of these
templates**, update your overrides to the Bootstrap 5 markup.

The notification "state" column previously showed a Semantic UI popup with the processing errors on
hover. This is now a native tooltip (the `title` attribute) — the `sylius_ui` event block and the
`popup.js` asset have been removed.

#### 3. Shop unsubscribe page rewritten for Bootstrap

`templates/shop/unsubscribe.html.twig` now extends `@SyliusShop/shared/layout/base.html.twig` (the
Sylius 2 shop layout) and uses Bootstrap markup. If you overrode it, update your override.

### Internal changes (no action required)

These are relevant only if you extend the plugin internals:

- Plugin layout moved from `src/Resources/{config,translations,views}/` to repository-root
  `config/`, `translations/` and `templates/`.
- Service definitions were converted from XML to the PHP-DSL `ContainerConfigurator` format under
  `config/services/`. Service IDs are unchanged (FQCN-based).
- Doctrine mappings remain XML mapped-superclasses, now under `config/doctrine/model/`.
- The `OrderFactory` decorator now depends on
  `Sylius\Component\Core\TokenAssigner\OrderTokenAssignerInterface` (was the removed
  `sylius.unique_id_based_order_token_assigner` service id).
- The admin menu and notification state icons now use the Tabler icon set (e.g. `tabler:mail`).
