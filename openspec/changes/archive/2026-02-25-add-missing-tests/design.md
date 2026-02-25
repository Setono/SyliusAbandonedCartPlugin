## Context

The plugin has established testing patterns: unit tests use Prophecy for mocking with `@test` annotation and `it_` prefix naming, and functional tests use Symfony's `WebTestCase`/`KernelTestCase`. The existing tests cover ~53% of classes. The untested classes contain critical business logic (notification processing pipeline, email sending, unsubscribe flow) and supporting components (workflow subscribers, composite checker, model logic).

## Goals / Non-Goals

**Goals:**
- Achieve dedicated test coverage for all classes containing meaningful business logic
- Follow existing test conventions (Prophecy, `@test`, `it_` prefix, `self::assert*()`)
- Test error paths and edge cases, not just happy paths

**Non-Goals:**
- 100% line coverage — skip trivial getters/setters with no logic
- Testing framework/Symfony internals (e.g., workflow engine itself)
- Integration/end-to-end tests spanning the full create→process→send pipeline
- Modifying production code to improve testability

## Decisions

### 1. Unit tests for all business logic classes, functional test only for the command

**Rationale:** NotificationProcessor, EmailManager, CompositeNotificationEligibilityChecker, workflow subscribers, and the Notification model all have injectable dependencies or isolated logic suitable for unit testing with Prophecy mocks. The ProcessNotificationsCommand is a thin wrapper that delegates to NotificationProcessor, so a functional test verifies the wiring while the processor's logic is covered by unit tests.

**Alternative considered:** Functional tests for everything — rejected because unit tests are faster, more precise in failure messages, and easier to cover edge cases.

### 2. UnsubscribeCustomerAction as unit test

**Rationale:** The controller has four injected dependencies that are straightforward to mock. A unit test can verify all validation branches (missing email, missing hash, invalid hash, already unsubscribed) and the happy path without needing a running Sylius application. The existing functional test for RecoverCartAction uses WebTestCase, but UnsubscribeCustomerAction's validation logic is better served by isolated unit tests that can exercise each branch precisely.

**Alternative considered:** Functional test with WebTestCase — viable but requires fixtures and HTTP setup for what is essentially input validation logic.

### 3. Notification model tested as a plain unit test

**Rationale:** The model has logic methods (`isFailed()`, `isDeletable()`, `getEmail()`, `getRecipientFirstName()`, processing error management) that derive values from state and associated entities. These can be tested with simple instantiation and Prophecy mocks for the associated Order/Customer/Address objects.

### 4. Mirror the source directory structure in tests/Unit/

**Rationale:** Follow existing convention — `src/Processor/` → `tests/Unit/Processor/`, `src/Mailer/` → `tests/Unit/Mailer/`, etc. This is already the pattern used for existing tests.

## Risks / Trade-offs

**[Prophecy limitations with final classes]** → If any dependency is a final class, Prophecy cannot mock it. Mitigation: mock interfaces instead (all dependencies use interfaces in this codebase).

**[NotificationProcessor has private methods]** → The core logic is in private `processNotification()` and `tryTransition()`. Mitigation: test through the public `process()` method, setting up the data provider to return specific notifications that exercise each path.

**[Workflow mock complexity]** → The processor interacts with WorkflowInterface which has `can()` and `apply()` methods that need careful mock setup for each transition scenario. Mitigation: set up `can()` to return true/false and verify `apply()` is called with correct transition names.
