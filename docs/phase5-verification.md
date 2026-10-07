# Phase 5 pilot verification

Completed 2026-10-08. Only `api/notifications.php` was migrated.

## Selection and implementation

The current architecture's dependency map (section 8) separates the JSON API placeholder from database-backed personal/public notifications. The target architecture (section 12) recommends this endpoint as a possible pilot. Inspection confirmed no SQL, model instances, writes, HTML layout, or external requests in its action. Its authorization is explicitly defined by the existing `isLoggedIn()` helper, so no unknown business rules needed resolution.

The existing URL is a thin wrapper dispatching through the standard front controller and one centralized route to `NotificationsController::api()`. The response block was extracted without changing its operations, strings, ordering, exception handling, or exit behavior. Standard bootstrap is unchanged. A JSON-only action needs no view, persistence extraction, service, or shared header changes. `thong_bao.php` and every other endpoint remain legacy.

## Comparison with the behavior map

Reference: [behavior map, section 18](behavior-map.md#18-personal-notifications-and-json-api) and its shared bootstrap/session/identity contracts.

| Contract | Verification |
| --- | --- |
| Original URL and query/body input behavior | Wrapper fixes the route to `/api/notifications.php`; submitted actions, IDs, and fake route keys do not change dispatch or response. |
| Custom login check; no role restriction | Anonymous and zero-valued user IDs return the original login-failure JSON; student, lecturer, leadership, and unexpected role values with a nonempty user ID return success. |
| No method/action dispatch | GET, POST, PUT, DELETE, OPTIONS, and HEAD execute the same action in isolated comparisons; no 405 handling added. |
| Placeholder response | Signed-in response remains `success: true`, `notifications: []`, `unread_count: 0`; anonymous response retains the exact original message and HTTP 200. |
| No database operations | Database singleton remains uninitialized in every comparison; controller contains no SQL, PDO access, or model construction. Schema/models untouched. |
| Session/authentication | Real bootstrap and real identity helpers used for comparisons; session fields and existing flash values remain unchanged. Apache responses retain the path `/`, HttpOnly, and SameSite=Lax cookie policy. |
| JSON output, redirects, exception handling | Exact response bytes/status match the legacy action. Apache confirms JSON UTF-8 content type and no redirect for anonymous requests. Injected `Exception` from the identity helper yields byte-identical failure JSON. |
| UI | This API has no HTML UI; no markup, assets, shared header, or browser caller changed. |

## Checks and limits

- PHP 8.2 syntax checks passed for the wrapper, route table, new controller, front controller, and router.
- 36 fresh-process legacy/MVC comparisons passed: six identity states across six methods. A temporary copy of the original endpoint retained its original bootstrap dependency. Both variants used the real bootstrap/helpers with isolated temporary session storage and identical inputs. Compared exact body, status, session contents, and an uninitialized database singleton.
- One isolated fault-injection comparison verified the retained `Exception` catch. This check substituted identity helpers; it does not claim a production exception was observed.
- Real local Apache requests at the original URL passed for anonymous GET, POST, PUT, DELETE, and OPTIONS, compared with the pre-migration GET baseline. Cookie IDs naturally differ; cookie policy was checked.
- Extraction comparison confirmed the response block differs only in whitespace. Scoped Git whitespace and changed-file checks passed before commit.

Signed-in cases used seeded sessions in isolated PHP processes, not browser login or live account records. HEAD was compared at the PHP action level; web-server body suppression is not asserted. No live database, OAuth, SMTP, production hosting, or other module flows were exercised. Those dependencies are unchanged and unnecessary for this placeholder's mapped contract. Temporary test fixtures were not added to the application.
