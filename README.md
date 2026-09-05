# Boreal Relay

Boreal Relay is the free WordPress.org edition of Borealform's BYOK AI support
assistant. It includes chat, approved starter knowledge, conversation history,
feedback, human escalation, and widget settings without a licence.

Public source: https://github.com/scottjgd/boreal-relay

The commercial `boreal-relay-pro` add-on is maintained separately and must never
be copied into this plugin or its WordPress.org ZIP.

## Build the WordPress.org package

```bash
node scripts/package-wordpressorg.mjs
```

The packager creates `.wordpressorg-dist/boreal-relay.zip` and rejects
unexpected files, licence code, Pro source, external updater hooks, Borealform
licence endpoints, and nested development artifacts.

## Release checks

1. Run PHP syntax checks across the free and Pro source.
2. Build both ZIPs with their dedicated scripts.
3. Run WordPress Plugin Check in strict mode against the extracted free ZIP.
4. Test Free alone on a clean WordPress install.
5. Test Pro inactive, valid, invalid, revoked, expired, and offline.
6. Capture real WordPress screenshots for the directory listing.
7. Upload only the free ZIP/source to WordPress.org.

## External service boundary

Free contacts OpenAI only after the site administrator saves their own API key
and a visitor uses chat. It does not contact Borealform, load remote code,
perform licence checks, or use a commercial updater.