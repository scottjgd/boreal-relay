# Boreal Relay — Changelog

All notable changes are documented here.
Format follows [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).
Versioning follows [Semantic Versioning](https://semver.org/).

---

## [2.1.0] — 2026-09-05

### Changed

- WordPress.org now receives a complete free edition with no commercial licence or updater code.
- Boreal Relay Pro is a separate add-on that owns licence verification, custom knowledge editing, and feedback-review drafts.
- The free edition permanently retains chat, approved starter knowledge, conversation history, feedback, escalation, and widget settings.
- Added reproducible Free and Pro packaging checks plus external-service, privacy, and uninstall disclosures.

## [2.0.0] — 2026-08-19

### Breaking

This is a clean independent release. It shares no identifiers with any prior product.

### Added

- New `BR_` PHP class prefix, `br_` function prefix, `boreal_relay_` option/transient/action/nonce identifiers throughout
- New DB tables: `{prefix}boreal_relay_conversations`, `{prefix}boreal_relay_knowledge`, `{prefix}boreal_relay_escalations`
- Public-facing JS global `BorealRelay`; all widget HTML IDs use `boreal-relay-` prefix
- Admin menu slugs: `boreal-relay-dashboard`, `boreal-relay-conversations`, `boreal-relay-knowledge`, `boreal-relay-escalations`, `boreal-relay-settings`
- Bootstrap file: `boreal-relay.php`; plugin folder: `boreal-relay/`
- Fresh seed knowledge base: 25 entries covering overview, location, products, ordering, quotes, billing, payments, delivery, fulfilment, returns/cancellations, accounts, privacy, accessibility, technical help, contact, and hours
- Cautious generic seed wording — site owners are explicitly prompted to customize business-specific facts; no industry assumptions baked in
- Uses WordPress's native plugin update path; no external updater is included
- Asset handles: `boreal-relay-widget` (CSS + JS), `boreal-relay-admin` (CSS + JS)

### Preserved from prior feature set

- Free chat: OpenAI API key configuration, chat widget, conversation history
- Free feedback: thumbs-up / thumbs-down on AI responses, ownership validation
- Free escalation: trigger, contact form, email notification, admin queue management
- Widget settings: bot name, greeting, theme color, enabled toggle, business name, support email, tone, model
- The separately distributed Pro add-on owns licence activation and all knowledge-base write operations.
- All security hardening: input validation and escaping, strict status/category allowlists, rate limiting (30 chat/min, 20 feedback/min per IP), feedback ownership check, and no secrets exposed in front-end output
