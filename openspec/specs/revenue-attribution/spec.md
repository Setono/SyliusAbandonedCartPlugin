## ADDED Requirements

### Requirement: Revenue column in admin notification grid
The admin notification grid SHALL display a "Revenue" column that shows the attributed order revenue when a sent notification led to a completed order via click-through.

#### Scenario: Notification drove a conversion
- **WHEN** the notification state is "sent" AND `lastClickedAt` is not null AND the cart state is "new" or "fulfilled"
- **THEN** the column SHALL display the order total formatted using Sylius money formatting with the order's currency code

#### Scenario: Notification sent but not clicked
- **WHEN** the notification state is "sent" AND `lastClickedAt` is null
- **THEN** the column SHALL display a dash ("—")

#### Scenario: Notification sent and clicked but order not completed
- **WHEN** the notification state is "sent" AND `lastClickedAt` is not null AND the cart state is "cart"
- **THEN** the column SHALL display a dash ("—")

#### Scenario: Notification sent and clicked but order cancelled
- **WHEN** the notification state is "sent" AND `lastClickedAt` is not null AND the cart state is "cancelled"
- **THEN** the column SHALL display a dash ("—")

#### Scenario: Notification not yet sent
- **WHEN** the notification state is "pending", "processing", "ineligible", or "failed"
- **THEN** the column SHALL display a dash ("—")

### Requirement: Revenue column translation keys
The column header label SHALL be translatable in all supported languages (en, da, fr).

#### Scenario: English label
- **WHEN** the locale is English
- **THEN** the column header SHALL display "Revenue"

#### Scenario: Danish label
- **WHEN** the locale is Danish
- **THEN** the column header SHALL display "Omsætning"

#### Scenario: French label
- **WHEN** the locale is French
- **THEN** the column header SHALL display "Revenu"
