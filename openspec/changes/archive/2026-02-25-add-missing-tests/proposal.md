## Why

The plugin has ~53% test coverage with critical business logic components lacking dedicated tests. The NotificationProcessor, EmailManager, UnsubscribeCustomerAction, and workflow event subscribers have no unit or functional tests, making refactoring risky and regressions hard to catch.

## What Changes

- Add unit tests for core business logic: NotificationProcessor, EmailManager, CompositeNotificationEligibilityChecker
- Add unit test for UnsubscribeCustomerAction controller
- Add unit tests for workflow event subscribers: SetSentAtSubscriber, ResetProcessingErrorsSubscriber
- Add unit tests for Notification model business logic methods
- Add functional test for ProcessNotificationsCommand

## Capabilities

### New Capabilities
- `unit-tests`: Unit tests for untested classes covering business logic, controllers, event subscribers, and model methods
- `functional-tests`: Functional test for ProcessNotificationsCommand to complete command test coverage

### Modified Capabilities

(none)

## Impact

- New test files in `tests/Unit/` and `tests/Functional/`
- No changes to production code
- Improved confidence for future refactoring and feature development
