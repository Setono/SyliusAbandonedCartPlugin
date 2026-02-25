## Why

Shop owners have no visibility into which abandoned cart emails actually drove revenue. When a customer clicks a recovery email and completes their order, that's a direct win — but the admin grid doesn't surface this. Adding revenue attribution helps shop owners understand the ROI of their abandoned cart recovery emails. Relates to GitHub issue #5.

## What Changes

- Add a new "Revenue" column to the admin notification grid
- Display the order total (formatted with Sylius money tools) when all three conditions are met: notification was sent, customer clicked the recovery link (`lastClickedAt` is set), and the order state is `new` or `fulfilled`
- Show a dash ("—") when the notification hasn't driven a conversion
- Add translation keys for the column header in en, da, and fr

## Capabilities

### New Capabilities
- `revenue-attribution`: Revenue column in the admin notification grid showing attributed revenue for clicked-through notifications whose orders were completed

### Modified Capabilities

(none)

## Impact

- New Twig template for the revenue grid column
- Grid configuration change in the DI extension (add field)
- New translation keys in all three language files
- No model changes, no migrations, no new services
