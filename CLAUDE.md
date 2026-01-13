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

The plugin uses Symfony Workflow to manage notification lifecycle (`src/Workflow/NotificationWorkflow.php`):

```
initial → pending → processing → sent
                              ↘ ineligible
                              ↘ failed
```

Transitions: `start` → `process` → `send` / `fail_eligibility_check` / `fail`

### Core Processing Pipeline

1. **Dispatcher** (`src/Dispatcher/`) - Finds idle carts via `NotificationRepository::findIdle()` and creates `Notification` entities
2. **Messenger** (`src/Message/`) - `ProcessNotification` command is dispatched to the command bus
3. **Processor** (`src/Processor/`) - Handles the message, runs eligibility checks, transitions state machine
4. **EligibilityChecker** (`src/EligibilityChecker/`) - Composite checker pattern; validates if notification should be sent
5. **Mailer** (`src/Mailer/`) - Sends the actual email with recovery/unsubscribe URLs

### Key Models

- `Notification` - Tracks abandoned cart notification state, linked to `Order`
- `UnsubscribedCustomer` - Records customers who opted out

### URL Generation

- `CartRecoveryUrlGeneratorInterface` - Generates links to restore customer's cart
- `UnsubscribeUrlGeneratorInterface` - Generates secure unsubscribe links using configurable salt + hash

### Console Commands

- `setono:sylius-abandoned-cart:process-notifications` - Main worker command (run via cron)
- `setono:sylius-abandoned-cart:prune` - Cleanup old notifications

## Plugin Configuration

The plugin is configured via `setono_sylius_abandoned_cart` in Symfony config. Key options:
- `salt` - Secret for unsubscribe URL hashing
- `idle_threshold` - Time before cart is considered abandoned
- `eligibility_checkers` - Enable/disable specific checkers

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

### Translations
The plugin provides multilingual support through translation files in `src/Resources/translations/`:

- **Translation Files**: Available in 10 languages (en, da, de, es, fr, it, nl, no, pl, sv)
- **Translation Domains**:
    - `messages.*` - General UI translations
    - `flashes.*` - Flash message translations (success/error messages)

Key translation keys:
- `setono_sylius_abandoned_cart.ui.*` - UI labels
- `setono_sylius_abandoned_cart.form.*` - Form field labels
- `setono_sylius_abandoned_cart.single_message` - A flash message

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
