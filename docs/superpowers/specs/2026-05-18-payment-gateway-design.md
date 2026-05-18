# Payment Gateway: Stripe + PayPal — design

**Status:** draft, awaiting review
**Date:** 2026-05-18
**Owner:** Shobjiwala team
**Scope:** Add WooCommerce payment processing for cards plus Apple Pay and Google Pay, in region C (UK / EU / US, currencies GBP / EUR / USD). One-off purchases only; subscriptions are out of scope.

## 1. Decision

Use two free, first-party WooCommerce gateways in parallel:

| Role | Plugin | Composer slug | Vendor |
| --- | --- | --- | --- |
| Primary — wallets + cards + alt methods | Stripe Payment Gateway for WooCommerce | `wpackagist-plugin/woocommerce-gateway-stripe` | Stripe |
| Secondary — PayPal Wallet + Pay Later | PayPal Payments | `wpackagist-plugin/woocommerce-paypal-payments` | PayPal |

Stripe carries the Apple Pay and Google Pay flow through its Payment Request Button / Payment Element. PayPal exists alongside it solely to capture buyers who default to the PayPal button at checkout (a meaningful share of UK/EU traffic).

### Why this pair, not one of the others

- **WooPayments** (Automattic): also Stripe under the hood, slightly tighter Woo admin UX, but ties fees and KYC to Automattic and removes direct Stripe dashboard control. Rejected to keep the merchant relationship with Stripe direct.
- **Square**: solid US/UK product, weaker continental EU coverage and weaker BNPL story. Rejected for region-C breadth.
- **Mollie**: best when traffic is concentrated in NL/BE (iDEAL/Bancontact). Weaker UK and US. Rejected as the primary; can be revisited as a regional add-on.
- **PayPal alone**: PayPal's ACDC product covers cards plus Apple/Google Pay, but the wallet button UX is heavier and Apple Pay availability is patchy by country. Rejected as the sole gateway.

## 2. Capabilities covered

| Capability | Provided by | Notes |
| --- | --- | --- |
| Visa / Mastercard / Amex / Discover | Stripe (Payment Element) | Hosted fields, no PAN touches our server. |
| 3DS / SCA | Stripe | Automatic per PSD2 in EU/UK. |
| Apple Pay | Stripe Payment Request Button | Safari on iOS and macOS. Requires HTTPS and a domain-association file (see §6). |
| Google Pay | Stripe Payment Request Button | Chrome on Android and desktop. Requires HTTPS. |
| Link (Stripe's saved-payment service) | Stripe | Speeds repeat checkout for returning buyers. |
| PayPal wallet button | PayPal Payments | Smart Buttons on cart / checkout / product. |
| Pay Later (UK/DE/FR/ES/IT/US) | PayPal Payments | Surfaced automatically by buyer region. |
| Refunds from Woo admin | both | Partial and full. |

Explicitly **deferred** (see §10):

- Subscriptions / recurring billing.
- BNPL via Stripe (Klarna, Afterpay/Clearpay, Affirm). Toggleable later from the Stripe dashboard; Payment Element renders them automatically once enabled.
- SEPA, iDEAL, Bancontact — same deferral path as BNPL.
- Custom fraud rules beyond Stripe Radar defaults.

## 3. Dependencies

### Composer additions

Add to `composer.json` under `require`:

```json
"wpackagist-plugin/woocommerce-gateway-stripe": "*",
"wpackagist-plugin/woocommerce-paypal-payments": "*"
```

Both resolve from `wpackagist.org`, which is already configured as a Composer repository in this project. `composer update --lock` to refresh the lock file; `composer install` to materialise the plugins into `wp-content/plugins/`.

### Runtime prerequisites

- WordPress >= 6.4, WooCommerce >= 8.0 (both met by current install).
- PHP 8.3 (matches `composer.json` platform pin and the docker image).
- HTTPS in production. Apple Pay refuses to surface without it and Google Pay falls back to a degraded flow.
- Outbound HTTPS to `api.stripe.com` and `api-m.paypal.com` from the WP host.

## 4. Account setup checklist

Done out-of-band by the merchant before plugin configuration.

### Stripe

1. Create a Stripe account at `stripe.com`, complete KYC for the operating entity.
2. Verify the bank account that will receive payouts.
3. In the Stripe dashboard, enable the payment methods that should appear on checkout: Cards, Apple Pay, Google Pay, Link. Leave BNPL and bank-redirect methods off for now.
4. Capture the **publishable key** and **secret key** for both test mode and live mode. Store as WordPress options via the plugin UI, not in source.
5. Register the live domain under **Settings → Payments → Payment methods → Apple Pay → Add a new domain** (the plugin automates this if it can drop the verification file; otherwise manual — see §6).

### PayPal

1. Create a PayPal Business account or upgrade an existing personal account.
2. Complete business verification.
3. In the PayPal Payments plugin, run the **Connect to PayPal** onboarding flow. This issues the merchant credentials directly — no manual client ID / secret juggling required.
4. Confirm that Pay Later eligibility shows for the merchant's primary country.

## 5. WordPress / WooCommerce configuration

| Setting | Value |
| --- | --- |
| WC currency | Per region: GBP / EUR / USD. Decide one primary; multi-currency is out of scope (would need a separate plugin like Aelia or WooCommerce Multi-Currency). |
| WC shop country | Merchant's country of establishment. |
| WC checkout pages | Cart, Checkout, My Account auto-created by WC; ensure they exist after the empty-DB install completes. |
| Stripe plugin → mode | **Test** until §8 passes, then **Live**. |
| Stripe plugin → payment methods | Cards, Apple Pay, Google Pay, Link enabled. Webhook URL configured (the plugin generates this; copy it into the Stripe dashboard). |
| Stripe plugin → statement descriptor | Set to a recognisable merchant name (≤ 22 chars). |
| PayPal Payments → onboarding | Connected. Smart Buttons enabled on Cart and Checkout. Hide the PayPal-rendered card form (we use Stripe for cards to avoid two card UIs). |

## 6. Apple Pay domain verification

Apple Pay requires a file served at:

```
https://<production-domain>/.well-known/apple-developer-merchantid-domain-association
```

The Stripe plugin attempts to drop this file automatically through the WP filesystem on first activation. It succeeds when `wp-content/uploads/.well-known/` (or equivalent) is writable and the web server is configured to serve `.well-known/` directly.

Deployment notes for our stack:

- The runtime is `wordpress:php8.3-apache` (see `docker/wordpress/Dockerfile`). Apache serves `.well-known/` by default; no extra config needed.
- If the host migrates to Nginx in production, add an explicit `location ^~ /.well-known/ { try_files $uri =404; }` block — Nginx commonly blocks unprefixed dotfile paths.
- If the plugin cannot write the file, fetch it manually from the Stripe dashboard (Apple Pay settings → "Download verification file") and commit it to the web root via deploy, then re-register in Stripe.

Google Pay needs no equivalent file — it activates as soon as the Payment Request Button detects HTTPS and a verified Stripe account.

## 7. PCI / compliance posture

- Card data is collected by Stripe's hosted Payment Element and tokenised before submission. The PAN never touches our PHP runtime, qualifying the site for **SAQ A** rather than SAQ A-EP.
- PayPal Smart Buttons follow the same hosted model. PayPal's optional ACDC card form is intentionally not enabled, so SAQ scope stays small.
- Webhook endpoints validate the Stripe signature header server-side before mutating order state.

## 8. Test plan

Performed in `WP_DEBUG=true` mode against the Stripe test environment and the PayPal sandbox before flipping either gateway to live.

| # | Scenario | Expected |
| --- | --- | --- |
| 1 | Checkout with Stripe test card `4242 4242 4242 4242`, any future date, any CVC. | Order completes, marked `processing`, Stripe dashboard shows payment in test mode. |
| 2 | Checkout with 3DS test card `4000 0027 6000 3184`. | 3DS challenge modal appears, completes, order goes to `processing`. |
| 3 | Checkout with decline card `4000 0000 0000 0002`. | Friendly decline message, order stays `pending`, no charge. |
| 4 | Apple Pay button on Safari iOS (TestFlight device or real device on a verified domain). | Sheet opens, completes with test Stripe card linked to Wallet, order completes. |
| 5 | Google Pay button on Chrome Android. | Sheet opens, completes with a Google Pay test card, order completes. |
| 6 | PayPal Smart Button in sandbox. | PayPal popup, sandbox buyer logs in, returns to site, order completes. |
| 7 | Refund a captured Stripe order from Woo admin (partial). | Stripe dashboard shows the refund, Woo order note records it. |
| 8 | Refund a captured PayPal order from Woo admin (full). | PayPal sandbox shows the refund. |
| 9 | Webhook delivery: send `payment_intent.succeeded` from Stripe CLI to local. | Plugin logs receipt, order state updates if not already. |
| 10 | Disable Stripe plugin, reload checkout. | Only PayPal renders; no PHP fatals. Re-enable. |

All ten must pass on staging before the merchant signs off and live keys are entered.

## 9. Rollback plan

- **Soft rollback** (recommended first step): toggle the offending gateway off in `WooCommerce → Settings → Payments`. Checkout continues via the other gateway. No code change.
- **Plugin rollback**: remove the Composer line, run `composer update --lock`, redeploy. Plugin files vanish from `wp-content/plugins/`. Existing orders retain their payment metadata; no DB destructive action.
- **Full revert**: `git revert` the commit that added the gateways. Same effect as the plugin rollback plus the doc change.
- Already-captured payments are not affected by any rollback; refunds for them must be processed in the Stripe / PayPal dashboards directly.

## 10. Out of scope / follow-ups

Captured here so they are not lost; each becomes its own spec when prioritised.

- **Subscriptions.** Would need WooCommerce Subscriptions (paid) or "Subscriptions for WooCommerce" by WebToffee (free, less polished). Both work with Stripe.
- **BNPL.** Enable Klarna / Afterpay / Affirm from the Stripe dashboard. No code change beyond toggling them in the Stripe plugin.
- **Bank-redirect methods.** SEPA, iDEAL, Bancontact via the same Stripe path.
- **Multi-currency.** Aelia Currency Switcher or WooCommerce Multi-Currency, paired with Stripe's per-currency presentment.
- **Fraud rules.** Stripe Radar defaults are fine for launch; revisit if dispute rate exceeds 0.5%.
- **Express checkout buttons on PDP / cart.** Off by default; turn on after wallets prove out at checkout.

## 11. Open questions

None at design time. Standing assumptions to confirm before implementation:

- Primary currency is one of GBP / EUR / USD (single currency at launch).
- The merchant entity exists and can pass Stripe / PayPal KYC.
- Production is HTTPS-served (with a valid cert, not self-signed).
