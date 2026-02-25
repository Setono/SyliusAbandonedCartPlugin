## ADDED Requirements

### Requirement: NotificationProcessor unit test
The test SHALL verify that NotificationProcessor correctly orchestrates the notification processing pipeline: transitioning state, checking eligibility, sending email, and handling errors.

#### Scenario: Eligible notification is sent
- **WHEN** a pending notification is eligible
- **THEN** the processor SHALL transition it through processing → sent and call the email manager

#### Scenario: Ineligible notification is marked
- **WHEN** a pending notification fails eligibility checks
- **THEN** the processor SHALL transition it to ineligible and record the eligibility check reasons as processing errors

#### Scenario: Email sending failure
- **WHEN** the email manager throws an exception during sending
- **THEN** the processor SHALL transition the notification to failed and record the error message

#### Scenario: No pending notifications
- **WHEN** the data provider returns no pending notifications
- **THEN** the processor SHALL complete without errors

### Requirement: EmailManager unit test
The test SHALL verify that EmailManager sends emails with the correct parameters including recovery and unsubscribe URLs.

#### Scenario: Sends email with correct data
- **WHEN** a notification with a valid cart, channel, and email is provided
- **THEN** the email manager SHALL call the sender with the notification email, locale code, and a data array containing the notification, cart recovery URL, and unsubscribe URL

#### Scenario: Notification without a cart
- **WHEN** a notification has no associated cart
- **THEN** the email manager SHALL throw an assertion error

#### Scenario: Cart without a channel
- **WHEN** the associated cart has no channel
- **THEN** the email manager SHALL throw an assertion error

#### Scenario: Notification without an email
- **WHEN** the notification has no email
- **THEN** the email manager SHALL throw an assertion error

### Requirement: CompositeNotificationEligibilityChecker unit test
The test SHALL verify that the composite checker correctly aggregates results from multiple child checkers.

#### Scenario: All checkers pass
- **WHEN** all registered checkers return eligible
- **THEN** the composite checker SHALL return an eligible result with no reasons

#### Scenario: One checker fails
- **WHEN** one registered checker returns ineligible with reasons
- **THEN** the composite checker SHALL return an ineligible result containing those reasons

#### Scenario: Multiple checkers fail
- **WHEN** multiple registered checkers return ineligible
- **THEN** the composite checker SHALL return an ineligible result with all reasons merged

#### Scenario: No checkers registered
- **WHEN** no checkers are registered
- **THEN** the composite checker SHALL return an eligible result

### Requirement: UnsubscribeCustomerAction unit test
The test SHALL verify that the controller validates input, verifies the email hash, and creates unsubscribed customer records.

#### Scenario: Successful unsubscribe
- **WHEN** a request with a valid email and matching hash is received
- **THEN** the action SHALL create an UnsubscribedCustomer entity and render the success template

#### Scenario: Missing email
- **WHEN** the request has no email parameter
- **THEN** the action SHALL render the template with an error message

#### Scenario: Missing hash
- **WHEN** the request has an email but no hash parameter
- **THEN** the action SHALL render the template with an error message

#### Scenario: Invalid hash
- **WHEN** the hash does not match the computed hash for the email
- **THEN** the action SHALL render the template with an error message

#### Scenario: Already unsubscribed
- **WHEN** the email is already in the unsubscribed customer list
- **THEN** the action SHALL render the template with an error message

#### Scenario: Email normalization
- **WHEN** the email contains uppercase characters
- **THEN** the action SHALL normalize the email to lowercase before processing

### Requirement: SetSentAtSubscriber unit test
The test SHALL verify that the subscriber sets the sentAt timestamp when the send transition completes.

#### Scenario: Sets sentAt on send completion
- **WHEN** the workflow send transition completes
- **THEN** the subscriber SHALL set the notification's sentAt to the current datetime

#### Scenario: Subscribes to correct event
- **WHEN** checking subscribed events
- **THEN** the subscriber SHALL be subscribed to the send transition completed event

### Requirement: ResetProcessingErrorsSubscriber unit test
The test SHALL verify that the subscriber resets processing errors when the process transition occurs.

#### Scenario: Resets errors on process transition
- **WHEN** the workflow process transition occurs
- **THEN** the subscriber SHALL call resetProcessingErrors on the notification

#### Scenario: Subscribes to correct event
- **WHEN** checking subscribed events
- **THEN** the subscriber SHALL be subscribed to the process transition event

### Requirement: Notification model unit test
The test SHALL verify the business logic methods on the Notification model.

#### Scenario: isFailed returns true for failed state
- **WHEN** the notification state is "failed"
- **THEN** isFailed() SHALL return true

#### Scenario: isFailed returns false for other states
- **WHEN** the notification state is not "failed"
- **THEN** isFailed() SHALL return false

#### Scenario: isIneligible returns true for ineligible state
- **WHEN** the notification state is "ineligible"
- **THEN** isIneligible() SHALL return true

#### Scenario: isDeletable returns true for non-sent states
- **WHEN** the notification state is not "sent"
- **THEN** isDeletable() SHALL return true

#### Scenario: isDeletable returns false for sent state
- **WHEN** the notification state is "sent"
- **THEN** isDeletable() SHALL return false

#### Scenario: Processing errors management
- **WHEN** errors are added via addProcessingError and addProcessingErrors
- **THEN** getProcessingErrors() SHALL return all accumulated errors, and resetProcessingErrors() SHALL clear them

#### Scenario: getEmail derives from cart customer
- **WHEN** the notification has a cart with a customer
- **THEN** getEmail() SHALL return the customer's emailCanonical

#### Scenario: getEmail returns null without customer
- **WHEN** the notification has no cart or the cart has no customer
- **THEN** getEmail() SHALL return null

#### Scenario: getRecipientFirstName from customer
- **WHEN** the notification has a cart with a customer that has a first name
- **THEN** getRecipientFirstName() SHALL return the customer's first name

#### Scenario: getRecipientFirstName from billing address
- **WHEN** the cart's customer has no first name but the cart has a billing address
- **THEN** getRecipientFirstName() SHALL return the billing address first name
