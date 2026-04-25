---
name: Full Lan* suite i18n audit 2026-04-25
description: Read-only audit results for all 5 apps; key file locations, string counts, and critical findings
type: project
---

## Audit scope
All 5 apps: LanCore, LanHelp, LanShout, LanBrackets, LanEntrance.
Vue files only (resources/js/**/*.vue). Report at /home/mawiguko/git/lan-software/i18n-audit-2026-04-25.md

## String counts
- LanCore: ~90 hardcoded strings across ~25 files (dominant offender)
- LanHelp: ~4 strings (low severity)
- LanShout: 0 strings (fully translated)
- LanBrackets: 1 string (AppLogo.vue not using common.appName)
- LanEntrance: 1 string (AppLogo.vue, same pattern)

## Confirmed "upcoming / past events" location
IN: LanCore/resources/js/components/PublicTopbar.vue (lines 106, 113, 184, 191)
IN: LanCore/resources/js/pages/Welcome.vue (lines 223, 230, 300, 307 — dead v-if="false" block)
NOT IN: LanBrackets/Landing.vue (already fully translated)

## Highest priority LanCore strings
1. Hardcoded German "Erst lesen dann klicken" in cart/Checkout.vue (3 occurrences) — REGRESSION
2. Script-side string assignment in events/Public.vue ("Past Events"/"Upcoming Events" computed from literals)
3. NotificationBell.vue — all 8 notification type labels in notificationLabel() function
4. PushNotificationPrompt.vue — 13 strings, entire component untranslated
5. announcements/Public.vue — 5 strings including page heading
6. Welcome.vue — 14 strings in main content (program/venue/sponsors/seat map section headings)

## Cross-app pattern
DemoBanner.vue line 30: "Open Mailpit inbox" — same in all 5 apps. Key: demo.openMailpit

## Key naming convention (all 5 apps)
camelCase, nested JSON, feature-namespaced. Example: competitions.statusDraft, entrance.decision.validTicket
Interpolations use {paramName} syntax (vue-i18n v9 named params).
