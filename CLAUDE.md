# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

A Sylius e-commerce plugin for abandoned cart recovery. It detects idle shopping carts and sends re-engagement emails with cart recovery URLs and unsubscribe options.

## Code Standards

Follow clean code principles and SOLID design patterns:
- Write clean, readable, and maintainable code
- Apply SOLID principles (Single Responsibility, Open/Closed, Liskov Substitution, Interface Segregation, Dependency Inversion)
- Use meaningful variable and method names
- Keep methods and classes focused on a single responsibility
- Favor composition over inheritance

## Common Commands

```bash
# Run tests
composer phpunit

# Run a single test file
vendor/bin/phpunit tests/Path/To/TestFile.php

# Run a single test method
vendor/bin/phpunit --filter testMethodName

# Static analysis (PHPStan at max level)
composer analyse

# Check coding standards (ECS)
composer check-style

# Fix coding standards
composer fix-style
```

## Architecture

### Notification State Machine

The plugin uses Symfony Workflow (state machine type) to manage notification lifecycle (`src/Workflow/NotificationWorkflow.php`):

```
pending → processing → sent
                    ↘ ineligible
(any state) → failed
```

Initial marking: `pending`

Transitions:
- `process`: `pending` → `processing`
- `send`: `processing` → `sent`
- `fail_eligibility_check`: `processing` → `ineligible`
- `fail`: any state → `failed`

### Core Processing Pipeline

1. **Creator** (`src/Creator/NotificationCreator`) - Uses `IdleCartDataProvider` to find idle carts and creates `Notification` entities for each
2. **Processor** (`src/Processor/NotificationProcessor`) - Iterates pending notifications via `PendingNotificationDataProvider`, runs eligibility checks, sends email, and transitions the state machine
3. **EligibilityChecker** (`src/EligibilityChecker/`) - Composite checker pattern; validates if notification should be sent
4. **Mailer** (`src/Mailer/EmailManager`) - Sends the actual email with recovery/unsubscribe URLs

### Data Providers

- `IdleCartDataProvider` (`src/DataProvider/`) - Queries idle carts using configurable `idle_threshold` and `lookback_window`, returns a batch iterator. Dispatches `QueryBuilderForIdleCartsCreated` event for query customization.
- `PendingNotificationDataProvider` (`src/DataProvider/`) - Queries pending notifications whose carts are still in the cart state

### Key Models

- `Notification` (`src/Model/`) - Tracks abandoned cart notification state (linked to Order). Has `state`, `processingErrors`, `sentAt`, `lastClickedAt`. Uses optimistic locking (`version`).
- `UnsubscribedCustomer` (`src/Model/`) - Records customers who opted out, keyed by unique email

### URL Generation

- `CartRecoveryUrlGenerator` - Generates links to restore customer's cart (with UTM tracking params)
- `UnsubscribeUrlGenerator` - Generates secure unsubscribe links using configurable salt + SHA256 hash via `EmailHasher`

### Controllers

- `RecoverCartAction` (`src/Controller/Action/`) - Recovers a cart by token value, sets `lastClickedAt` on the notification for engagement tracking, redirects to cart summary
- `UnsubscribeCustomerAction` (`src/Controller/Action/`) - Validates email + hash, creates `UnsubscribedCustomer` entity

### Factory Decorators

- `NotificationFactory` - Creates notifications linked to an order
- `UnsubscribedCustomerFactory` - Creates unsubscribed customer records from email
- `OrderFactory` - Decorates Sylius order factory to assign tokens on creation

### Other Components

- `TokenValueBasedCartContext` (`src/Context/`) - Cart context using token value from request (priority 100)
- `AdminMenuListener` (`src/Menu/`) - Adds "Abandoned Cart" under Marketing in admin menu
- `Pruner` (`src/Pruner/`) - Deletes notifications older than `prune_older_than` minutes
- `EmailHasher` (`src/Hasher/`) - SHA256 email hashing for secure unsubscribe links
- `SetSentAtSubscriber` / `ResetProcessingErrorsSubscriber` (`src/EventSubscriber/Workflow/`) - Workflow event subscribers

### Console Commands

- `setono:sylius-abandoned-cart:create-notifications` - Finds idle carts and creates notification entities (supports `--dry-run`)
- `setono:sylius-abandoned-cart:process-notifications` - Processes pending notifications (eligibility check + send email)
- `setono:sylius-abandoned-cart:prune-notifications` - Cleanup old notifications

### Service Configuration

Services are defined in XML under `src/Resources/config/services/`. The DI extension conditionally loads eligibility checkers from `services/conditional/` based on plugin config. Grids, workflow, and mailer config are prepended in the extension's `prepend()` method.

## Plugin Configuration

The plugin is configured via `setono_sylius_abandoned_cart` in Symfony config:

```yaml
setono_sylius_abandoned_cart:
    salt: 's3cr3t'              # Secret for unsubscribe URL hashing (required, change in production)
    idle_threshold: 60          # Minutes before a cart is considered idle (default: 60)
    lookback_window: 15         # Minutes lookback window for notification creation (default: 15)
    prune_older_than: 43200     # Prune notifications older than N minutes (default: 43200 = 30 days)
    eligibility_checkers:
        unsubscribed_customer: true    # Skip customers who unsubscribed (default: true)
        subscribed_to_newsletter: false # Only notify newsletter subscribers (default: false)
```

## Testing

Tests are in `tests/` with a full Sylius test application in `tests/Application/`. The test app bootstraps via `tests/Application/config/bootstrap.php`.

### Testing Conventions

- **BDD-style naming**: Use `it_` prefix for test methods (e.g., `it_returns_eligible_when_email_is_null`)
- **Use `@test` annotation**: Methods use `@test` docblock annotation
- **Prophecy for mocking**: Use `ProphecyTrait` and `$this->prophesize()` for all mocks, NOT PHPUnit's `createMock()`

Example:
```php
use Prophecy\PhpUnit\ProphecyTrait;

final class MyTest extends TestCase
{
    use ProphecyTrait;

    /** @test */
    public function it_does_something(): void
    {
        $dependency = $this->prophesize(DependencyInterface::class);
        $dependency->method()->willReturn('value');

        $sut = new MyClass($dependency->reveal());
    }
}
```

## Code Quality

- PHP 8.1+ required
- PHPStan at `max` level with Symfony and Doctrine integrations
- ECS follows `sylius-labs/coding-standard`
- Strict typing enforced (`declare(strict_types=1)`)
- Rector configured for PHP 8.1 level
- Infection mutation testing with min MSI of 37.33 and 100% covered MSI

### Translations

Translation files are in `src/Resources/translations/` (domain: `messages`):

- **Available languages**: English (en), Danish (da), French (fr)
- **Key prefixes**:
    - `setono_sylius_abandoned_cart.emails.*` - Email template strings
    - `setono_sylius_abandoned_cart.form.*` - Form field labels
    - `setono_sylius_abandoned_cart.ui.*` - Admin UI labels and messages

## Bash Tools

Use the right tool for the job when executing bash commands:

- **Finding files** → Use `fd` (fast file finder)
- **Finding text/strings** → Use `rg` (ripgrep for text search)
- **Finding code structure** → Use `ast-grep` (syntax-aware code search)
- **Selecting from multiple results** → Pipe to `fzf` (interactive fuzzy finder)
- **Interacting with JSON** → Use `jq` (JSON processor)
- **Interacting with YAML or XML** → Use `yq` (YAML/XML processor)

Examples:
- `fd "*.php" | fzf` - Find PHP files and interactively select one
- `rg "function.*validate" | fzf` - Search for validation functions and select
- `ast-grep --lang php -p 'class $name extends $parent'` - Find class inheritance patterns
