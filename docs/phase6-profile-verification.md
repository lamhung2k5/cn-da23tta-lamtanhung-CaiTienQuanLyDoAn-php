# Phase 6: profile page verification

Completed 2026-10-08. Only `/profile.php` was migrated; Phase 6 remains in progress for future separately authorized modules.

## Implementation and preservation

The original URL now supplies a fixed `/profile.php` route key to the existing dispatcher. `ProfileController::show()` retains standard bootstrap, `requireLogin()`, session-only identity lookup and `NguoiDungModel::getUserFullInfo()` in their original order. No model SQL, schema, bootstrap, shared helper or existing legacy include changed. The user/role join and role-specific profile lookup remain in the existing user model.

`page_data.php` prepares header identity/context and calls the same notification model methods in order: unread count, then newest five notifications. The presentation-only header and flash partials copy the original markup, inline CSS and JavaScript. They are separate so flash consumption remains after navigation output, including on rendering failures. The controller preserves the original header's emitted UTF-8 BOM before header data reads; removing it would change output/failure timing.

The trusted profile template retains the original markup/styles and presentation role selection: lecturer referer or mode takes precedence, followed by leadership, student, then session/database-role fallback. These affect navigation and existing edit/password links, not authorization. Request/session values are supplied explicitly to the template. Database role still selects detailed profile fields. No escaping, missing-row fallback, role check or bug fix was added.

The original page does not include a footer; the migrated page preserves that output. `includes/header.php` and `includes/footer.php` remain unchanged for legacy callers. `edit-profile.php`, `settings.php`, `change_password.php`, the notification API and all other endpoints remain unchanged.

## Behavior-map comparison

Reference: [shared profile behavior](behavior-map.md#6-shared-profile-and-password-settings), shared bootstrap/session/identity/rendering contracts, and U1/U2/U5/U13. No M1-M3-dependent workflow is involved.

| Contract | Result |
| --- | --- |
| Original URL | Exact centralized route and fixed wrapper; signed-in isolated HTTP responses remain at `/WebsiteQuanLyDeTai/profile.php`, with no redirect or rewritten address. |
| Authentication | Original helper permits any nonempty session user ID; anonymous/zero ID rejects before model reads and sets the same error flash/302 login redirect. No role/status/profile guard added. |
| Database reads/results | Real existing models exercised against a recording connection double; exact SQL, parameters, SELECT 1 health checks, fetch calls, query order and absent-row semantics match. No database writes or transactions introduced. |
| Notifications/navigation | Same unread count and five-recent query, original empty/read/unread/link display, wrapper-derived PHP_SELF page/directory context, current identity and role/mode/referer precedence. |
| Session/flash | Session ID unchanged; success/error/warning/info consumed in the same order after navigation; unrelated success_message/CSRF/identity/time fields unchanged. Pre-header failures leave flashes intact, matching legacy. |
| Redirects/status | Anonymous Apache response has identical 302, Location and body; signed-in ordinary requests keep HTTP 200 and no redirect. Existing failure/partial-output behavior preserved in comparisons. |
| HTML/CSS/JavaScript | Rendered output matches byte-for-byte, including BOM, whitespace, inline styles/scripts, profile sections and links; only the existing time-based style.css cache query value is normalized in comparison. No visual redesign or footer added. |

## Verification evidence and limits

- PHP 8.2 syntax checks passed for all seven changed/new PHP files: wrapper, routes, controller, page-data helper, profile template and two header partials.
- 69 isolated legacy/MVC comparisons passed: 23 scenarios across GET, POST and DELETE. Scenarios cover anonymous/zero IDs, all three roles, unknown role, missing user/profile, differing/absent session role, full/empty/absent flashes, full/empty notifications, absent phone, mode/referer conflicts, malformed mode/header name, and failures at user/count/recent-notice reads.
- Comparisons use actual bootstrap, authentication/flash helpers, user/notification models and their SQL; only the database connection is replaced in memory with a recording double. The original endpoint is a temporary unmodified copy resolved against the workspace. Response body/status, SQL/calls, session state, unchanged session ID, warnings and caught failure class/message all match. Failure catching belongs to the test harness, not application code.
- 14 signed-in HTTP comparisons passed on an isolated PHP development server at the original path, using seeded sessions, real bootstrap/helpers/models and the same connection double. GET/POST cases cover roles, missing profile, no notifications, navigation conflict and header-render failure. Headers, cookie policy, response content/status and visible URL match. Temporary session storage is used by this test server; application session configuration is unchanged.
- Real local Apache anonymous `/profile.php?mode=lanh_dao` response matches its pre-change baseline: 302 to `http://localhost/WebsiteQuanLyDeTai/auth/login.php`, same body, path `/`/HttpOnly/SameSite=Lax cookie policy.
- Hash comparisons confirm excluded endpoints, legacy header/footer, bootstrap, models and existing migration documentation were not changed. Scoped whitespace and changed-file checks passed; inherited presentation trailing whitespace and the original header's final blank line were deliberately retained for output equivalence.

No SQL schema was executed or modified, and no live database operations or production/sample data were used. Signed-in comparisons do not assert live account integrity or production database/host behavior. CSS/JavaScript source and rendered HTML equivalence were checked; no independent browser screenshot or visual-layout comparison is claimed. Missing-row warnings and partial rendering remain observable rather than being repaired. No second endpoint was migrated.
