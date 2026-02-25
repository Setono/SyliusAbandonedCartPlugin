## Context

The admin notification grid is configured programmatically in `SetonoSyliusAbandonedCartExtension::prepend()`. Each column uses a Twig template via `type: twig`. The existing state column uses `path: '.'` to pass the full Notification object to the template. Sylius provides `sylius_format_money` Twig filter and an admin money macro at `@SyliusAdmin/Common/Macro/money.html.twig` for currency formatting.

## Goals / Non-Goals

**Goals:**
- Show attributed revenue in the admin notification grid
- Use click-through attribution (customer clicked the recovery link AND completed the order)
- Format money using Sylius's built-in money formatting tools

**Non-Goals:**
- Aggregate revenue statistics or dashboard summaries
- Time-windowed attribution (e.g., only count if ordered within 24h)
- Revenue tracking beyond what the existing order total provides

## Decisions

### 1. New grid column with dedicated Twig template

Add a `revenue` field to the grid config using `type: twig` with `path: '.'` (passes the full Notification to the template). The template handles all display logic.

**Alternative considered:** Enhancing the existing cart or state column — rejected because it clutters those columns and makes the revenue data harder to scan at a glance.

### 2. Use Sylius admin money macro for formatting

Import `@SyliusAdmin/Common/Macro/money.html.twig` and call `money.format(total, currencyCode)`. This ensures consistent formatting with the rest of the Sylius admin.

**Alternative considered:** Raw `sylius_format_money` filter — works but the macro is the established pattern in admin templates.

### 3. Attribution requires lastClickedAt to be set

Only show revenue when `notification.lastClickedAt` is not null, meaning the customer actually clicked the recovery link. Combined with checking `notification.state == 'sent'` and `cart.state in ['new', 'fulfilled']`.

**Alternative considered:** Any sent notification whose order completed — too broad, no proof the email drove the conversion.

## Risks / Trade-offs

**[N+1 query on cart relationship]** → The grid already joins the cart for the existing cart/channel columns, so accessing `cart.state`, `cart.total`, and `cart.currencyCode` should not cause additional queries.

**[No locale parameter for money formatting]** → The admin money macro doesn't pass a locale to `sylius_format_money`. This matches how all other admin pages format money (uses the default locale).
