# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

A Sylius e-commerce plugin for abandoned cart recovery. It detects idle shopping carts and sends re-engagement emails with cart recovery URLs and unsubscribe options.

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

- `setono:sylius-abandoned-cart:process` - Main worker command (run via cron)
- `setono:sylius-abandoned-cart:prune` - Cleanup old notifications

## Plugin Configuration

The plugin is configured via `setono_sylius_abandoned_cart` in Symfony config. Key options:
- `salt` - Secret for unsubscribe URL hashing
- `idle_threshold` - Time before cart is considered abandoned
- `eligibility_checkers` - Enable/disable specific checkers

## Testing

Tests are in `tests/` with a full Sylius test application in `tests/Application/`. The test app bootstraps via `tests/Application/config/bootstrap.php`.

## Code Quality

- PHP 8.1+ required
- PHPStan at `max` level
- ECS follows `sylius-labs/coding-standard`
- Strict typing enforced (`declare(strict_types=1)`)
