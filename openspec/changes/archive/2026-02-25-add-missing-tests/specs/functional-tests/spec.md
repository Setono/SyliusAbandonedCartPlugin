## ADDED Requirements

### Requirement: ProcessNotificationsCommand functional test
The test SHALL verify that the process-notifications command executes correctly within the Symfony application context.

#### Scenario: Command executes successfully with no pending notifications
- **WHEN** the command is run with no pending notifications in the database
- **THEN** the command SHALL exit with a success status code

#### Scenario: Command is registered and findable
- **WHEN** the Symfony application is booted
- **THEN** the command `setono:sylius-abandoned-cart:process-notifications` SHALL be registered and findable
