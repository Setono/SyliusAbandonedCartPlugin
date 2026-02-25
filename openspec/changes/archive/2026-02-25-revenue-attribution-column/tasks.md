## 1. Grid Template

- [x] 1.1 Create `src/Resources/views/admin/grid/label/revenue.html.twig` — display formatted order total when notification state is "sent", lastClickedAt is set, and cart state is "new" or "fulfilled"; show "—" otherwise. Use the Sylius admin money macro for formatting.

## 2. Grid Configuration

- [x] 2.1 Add `revenue` field to the notification grid config in `SetonoSyliusAbandonedCartExtension::prepend()` — use `type: twig`, `path: '.'`, pointing to the new template. Place after the `lastClickedAt` column.

## 3. Translations

- [x] 3.1 Add `setono_sylius_abandoned_cart.ui.revenue` translation key to `messages.en.yaml`, `messages.da.yaml`, and `messages.fr.yaml`

## 4. Verification

- [x] 4.1 Run static analysis (`composer analyse`) and verify no errors
- [x] 4.2 Run coding standards check (`composer check-style`) and fix any violations
