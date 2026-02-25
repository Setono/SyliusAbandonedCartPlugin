## 1. Unit Tests — Core Business Logic

- [x] 1.1 Create `tests/Unit/Processor/NotificationProcessorTest.php` — test eligible notification flow (processing → sent), ineligible flow (processing → ineligible with reasons), email sending failure (→ failed), and no pending notifications
- [x] 1.2 Create `tests/Unit/Mailer/EmailManagerTest.php` — test sending with correct parameters (email, locale, URLs), and assertion errors for missing cart, missing channel, missing email
- [x] 1.3 Create `tests/Unit/EligibilityChecker/CompositeNotificationEligibilityCheckerTest.php` — test all pass, one fails, multiple fail, and no checkers registered

## 2. Unit Tests — Controller

- [x] 2.1 Create `tests/Unit/Controller/Action/UnsubscribeCustomerActionTest.php` — test successful unsubscribe, missing email, missing hash, invalid hash, already unsubscribed, and email normalization

## 3. Unit Tests — Workflow Event Subscribers

- [x] 3.1 Create `tests/Unit/EventSubscriber/Workflow/SetSentAtSubscriberTest.php` — test sentAt is set on send completion and subscribes to correct event
- [x] 3.2 Create `tests/Unit/EventSubscriber/Workflow/ResetProcessingErrorsSubscriberTest.php` — test errors are reset on process transition and subscribes to correct event

## 4. Unit Tests — Model

- [x] 4.1 Create `tests/Unit/Model/NotificationTest.php` — test isFailed, isIneligible, isDeletable, processing errors management, getEmail derivation, and getRecipientFirstName from customer/billing address

## 5. Functional Tests — Command

- [x] 5.1 Create `tests/Functional/Command/ProcessNotificationsCommandTest.php` — test command executes successfully with no pending notifications and is registered

## 6. Verification

- [x] 6.1 Run full test suite (`composer phpunit`) and verify all new tests pass
- [x] 6.2 Run static analysis (`composer analyse`) and verify no new errors
- [x] 6.3 Run coding standards check (`composer check-style`) and fix any violations
