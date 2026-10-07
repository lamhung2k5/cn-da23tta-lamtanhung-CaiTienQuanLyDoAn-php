# Target MVC Architecture

Phase 3 design revised 2026-10-08 for the current repository state. This revision changes documentation only. Existing Phase 4 infrastructure and the Phase 5 notification API pilot are retained; no additional module is migrated here.

## 1. Basis and decisions

The design follows [AGENTS.md](../AGENTS.md), the [migration plan](../.agent/mvc-refactor-plan.md), [current architecture](current-architecture.md) and [behavior map](behavior-map.md). Source checks include [bootstrap](../bootstrap.php), [BaseModel](../models/BaseModel.php), [Apache configuration](../.htaccess), [shared header](../includes/header.php), [profile page](../profile.php) and [notification API](../api/notifications.php). The behavior map takes precedence over general README feature descriptions when they conflict with executed code.

Use native PHP with the existing PDO connection, models, configuration and helpers. Reuse the existing internal front controller, exact-path router, route table, view renderer and notification controller. Add ordinary controller actions and PHP templates only as individual modules migrate. Add a service only when an actual migrated workflow needs coordination beyond request handling. Keep the current deployment directory and browser URLs.

This is sufficient because persistence already has a shared `Database`/`BaseModel` foundation. Remaining legacy boundaries are request coordination, SQL ownership in pages, and presentation separated from data loading. A framework, ORM, dependency-injection container, repository interface hierarchy, generic middleware pipeline, Request/Response object hierarchy or new template language is unnecessary for this application. Do not introduce Laravel. No new Composer dependency or runtime upgrade is part of this design; retain PHP 7.4-compatible syntax. The plan records earlier local checks on PHP 8.2/Apache 2.4, not proof of the production runtime.

### Current implementation to reuse

| Existing file | Current behavior / design decision |
| --- | --- |
| [app/front_controller.php](../app/front_controller.php) | Wrapper-supplied exact key; unknown/missing key returns 404 before bootstrap. Supports only ordinary bootstrap startup and rejects nonstandard startup/pre_session declarations. Reuse this dispatcher. |
| [app/Router.php](../app/Router.php) | Associative lookup; trusted file/class/action validation, require_once and controller invocation. No method enforcement or legacy fallback. Reuse it. |
| [app/View.php](../app/View.php) | realpath confines templates to app/views; isolated include scope with EXTR_SKIP. Reuse it; app/views does not exist yet because the API needs no template. |
| [config/routes.php](../config/routes.php) | Currently contains exactly `/api/notifications.php` → `NotificationsController::api()`. Extend the same registry only with verified migrations. |
| [app/.htaccess](../app/.htaccess) | Require all denied protects the internal app directory from direct HTTP. It does not prevent PHP includes from public wrappers. |
| [api/notifications.php](../api/notifications.php), [NotificationsController](../app/controllers/NotificationsController.php) | Existing pilot: JSON login failure or empty notifications/count zero, standard bootstrap, no DB operations or view. Keep this action and URL intact; do not implement notifications. |

The [Phase 1 architecture](current-architecture.md) describes the earlier legacy snapshot; its statement that no dispatcher exists is historical. The revised [behavior map](behavior-map.md) describes current source and the user-supplied schema. The SQL reference is the pasted 13-table DDL, not an executable migration. No physical qldt-schema.sql was found during the Phase 2 revision; do not create or import a replacement. M1-M3 and U1-U13 remain checkpoints rather than permission to repair behavior.

### Target request flow

```text
Browser -> existing physical public PHP URL
  migrated endpoint:
    thin wrapper with fixed route key
    -> existing front_controller.php -> existing config/routes.php / Router lookup
    -> verified startup (currently standard bootstrap only)
    -> endpoint controller action: exact guard, input branches, session/flash handling
    -> existing concrete model methods (optional workflow service if needed)
    -> controller emits JSON/redirect/download, or View::render(template, data)
    -> pure page/partial templates emit the existing HTML/CSS/JavaScript
  unmigrated endpoint:
    original PHP script -> its original startup, guards, SQL/models and rendering
```

SQL stays inside models. Services call models, not SQL or views. Controller guard/input/DB/flash/header operations retain their original order; moving an operation earlier merely to fit a layout is a behavior change. Legacy and migrated requests share the existing configuration/model contracts, not two application stacks.

## 2. Directory structure

Existing and future paths are distinguished below. Create future paths only when used by a verified module migration.

```text
WebsiteQuanLyDeTai/
  app/
    .htaccess                  existing internal HTTP access denial
    front_controller.php       existing shared dispatch entry
    Router.php                 existing exact route lookup/invocation
    View.php                   existing trusted-template renderer
    controllers/
      ProfileController.php    example module, created when it migrates
      NotificationsController.php existing api() action; personal actions future
      page_data.php            shared header-data preparation, when first needed
    views/                     future; no HTML module migrated yet
      account/                 migrated profile/password templates
      auth/                    migrated authentication templates
      sinh_vien/               migrated student templates
      giang_vien/              migrated lecturer templates
      lanh_dao/                migrated leadership templates
      public/                  migrated public content templates
      reports/                 future extraction of legacy HTML/print markup
      partials/                pure header/footer/sidebar/modal templates
    services/                  future optional; actual complex workflows only
  config/
    routes.php                 existing centralized migrated-route table
    config.php                 existing constants/session/environment behavior
    database.php               existing Database/PDO configuration
    *_oauth.php                existing provider configuration
    otp_config.php             existing configuration
    faculty_members.php        existing faculty fallback data
    links_config.json          existing writable menu-link data
  models/                      existing concrete models and BaseModel
  helpers/                     existing utilities and email integration
  bootstrap.php                existing initialization, retained initially
  includes/                    existing includes for unmigrated modules
  index.php                    legacy home until migrated, then thin wrapper
  profile.php                  legacy page until migrated, then thin wrapper
  auth/, sinh_vien/,           existing URL files retained module by module
  giang_vien/, lanh_dao/, api/
  api/notifications.php        existing thin compatibility wrapper
  assets/, img/, vendor/       existing locations retained
  docs/                        migration documentation
```

Do not relocate existing models/helpers/configuration to `app/` merely for uniform naming. Keeping them in place lets old and migrated modules use the same implementation. New views may be grouped by role for discoverability; access policy still belongs to the endpoint, not its view directory.

The four PHP foundation files and scoped app access rule already exist. Do not create a second router, public/index.php entry tree, bootstrap, connection manager or view engine. Controllers/templates are module work, not infrastructure placeholders. `page_data.php` and services are added only when used. No base controller is required.

| Layer | Responsibility | Boundary |
| --- | --- | --- |
| Public wrapper | Preserve public entry identity; supply trusted route key and include dispatcher | No guard, SQL or presentation |
| Dispatcher/router/route configuration | Select known endpoint and verified startup; instantiate action | No business policy, model construction, method-wide rules or fallback |
| Controller | HTTP coordination, existing input validation/guards/flash/headers, data preparation | No raw SQL/PDO or HTML report construction |
| Existing models | Queries, writes, current model business rules and transaction ownership | Preserve schema, shared callers and named method contracts |
| Optional service | Complex confirmed orchestration among models and external helpers | No superglobals, SQL or rendering; no new transaction policy |
| Views/partials | Existing markup, display loops/conditionals, escaping/formatting, inline assets | No DB, authorization, session writes/flash consumption, redirects or workflows |
| Config/helpers/assets | Existing startup/session/URL/provider and utility contracts; public resources | Preserve locations and endpoint-specific use |

## 3. Front controller and unchanged URLs

Use `app/front_controller.php` as the single internal entry for migrated requests. Keep every existing public PHP path as a thin compatibility wrapper when that endpoint migrates. An unmigrated file continues executing its original code directly.

Illustrative future contents of `profile.php`:

```php
<?php
$mvcRoutePath = '/profile.php';
require __DIR__ . '/app/front_controller.php';
```

A nested wrapper would include the same front controller through its filesystem-relative parent path and supply its own full application-relative route key. It must not redirect the browser to a new URL. The wrapper contains no SQL, validation, authentication or presentation.

The front controller:

1. Accepts the wrapper's hardcoded route key and looks it up in the centralized table.
2. Performs the startup specified for that route; ordinary routes use the current `bootstrap.php`.
3. Loads the selected controller file from the trusted route entry and invokes its action.
4. Lets that action produce the existing HTML, JSON, redirect, plain text or download and terminate as appropriate.

Do not select a controller, include path, bootstrap file or template from GET/POST values. At the PHP dispatcher level, absent/unknown wrapper keys return the existing infrastructure 404 before startup. Direct HTTP access to app files is instead denied by app/.htaccess (earlier local checks observed 403). Preserve both boundaries. Do not introduce arbitrary legacy-file inclusion as a router fallback. Unmigrated public endpoints never need that fallback.

Existing URLs such as `/sinh_vien/dang_ky_de_tai.php?id=...` and `/lanh_dao/xu_ly_phan_cong.php` remain the browser-visible addresses. A migrated root `index.php` wrapper continues to serve both the directory index URL and explicit `index.php`, with their original request URI retained. No catch-all Apache rewrite or new document root is required. Existing files/assets continue to be served directly; introducing friendly URLs is outside this refactor.

All migrated endpoints eventually pass through the front controller, while URL wrappers remain compatibility entry points. This satisfies centralized coordination without rerouting the whole legacy application at once.

## 4. Router and route declarations

`config/routes.php` returns a plain associative array. Keys are exact application-relative paths including `.php`; values identify a controller file, class and action. `Router` performs lookup, trusted file loading and invocation only. Explicit `require_once` loading is adequate and matches existing model loading; an application autoloader is not required.

Illustrative declaration, not an implemented or preselected migration:

```php
return [
    '/profile.php' => [
        'file' => __DIR__ . '/../app/controllers/ProfileController.php',
        'controller' => 'ProfileController',
        'action' => 'show',
    ],
];
```

Route keys come from wrappers, so routing does not depend on stripping `/WebsiteQuanLyDeTai/` from a request or on InfinityFree's document-root layout. Query strings stay in `$_GET` and do not change the endpoint lookup. Path case/encoding behavior remains with the existing web-server/file path; do not introduce normalization redirects.

Use one action per existing endpoint initially. That action preserves its method and query/body-action branches. For example, `giang_vien/xu_ly_duyet.php` can still accept GET approval, POST rejection and its existing fallback redirect within one controller action. Do not globally enforce POST for writes, force GET-only for display pages, add automatic HEAD behavior or return 405 where the old endpoint did something else. Central routes select endpoints; the endpoint's existing HTTP contract determines allowed methods.

No role auto-selection based on URL prefix, reflection-based controller discovery, regex route parameters or route names replacing legacy redirect strings are needed. Add a route only in the same module change that replaces its original script with a wrapper. Never register an endpoint pointing at a missing or speculative controller action.

## 5. Startup, configuration and dependency loading

Keep `config/config.php`, `config/database.php`, provider files, faculty fallback data, menu JSON and `bootstrap.php` at their existing paths. Initially ordinary migrated routes reuse the same include order, constants, environment detection, timezone, HTTPS enforcement and PHPMailer dependency. Do not eagerly connect to PDO or instantiate models in the dispatcher; do so when the original action needed data.

The code has meaningful startup exceptions: Google and alternate-reset scripts can start a session before bootstrap; CAPTCHA/faculty pages start their own session; lecturer details include only database configuration; diagnostics can output before normal initialization. Uniformly running standard bootstrap on these routes would change behavior.

The existing front controller deliberately supports `standard` startup only and rejects other startup/pre_session declarations. Keep it as is until an exception module is verified. If an affected migration needs it, extend this same dispatcher with a small explicit choice: `database` (existing database configuration only), `session` (existing standalone session start), or `none` (no common startup). A standard route that formerly started its session first can then declare `pre_session=guarded` or `pre_session=unguarded`, preserving its original check or unconditional call. These are future options, not currently supported route declarations or an extensible middleware system. Nonstandard controllers must load only verified dependencies and must not pull ordinary bootstrap indirectly through a view.

Before migrating an exception, verify its actual ordering and cookie/header effects. If a diagnostic script's pre-bootstrap output cannot be moved without changing behavior, leave it legacy until that behavior is determined. Do not guess a lifecycle simply to put every endpoint through identical startup.

This architecture does not consolidate credentials into a new environment loader, alter database fallback/reconnect behavior, enable unused timeout helpers, load extra OAuth configuration globally, or change session state keys. Those are separate behavior/configuration changes, especially where the behavior map records missing dependencies or conflicting configuration. Keep credential values out of migration documentation.

## 6. Controller responsibilities

Controllers are small concrete PHP classes. They may use existing superglobals and helpers; wrapping every HTTP input in a new object is unnecessary. Actions:

- Apply the exact endpoint-specific login/role checks, at the same point in request handling.
- Read GET/POST/files/header values and preserve coercion, defaults, sanitization and HTTP-specific validation.
- Resolve current user/profile, call named model methods or a workflow service, and preserve result handling.
- Set/consume session and flash fields, select redirect arguments/status/headers, and provide arrays to views.
- Render a trusted view, emit existing JSON/plain text or stream the established download format.

Controllers must not hold raw SQL, use PDO, query through `BaseModel::query($sql)` with inline SQL, or contain HTML report tables. Move page-authored SQL into the existing owning model and expose a named method with scalar/array arguments. Calling a generic query method from a controller would keep the old boundary problem.

Dependencies can be constructed explicitly within an action when first needed, or passed into a simple constructor if testing requires it. Preserve lazy construction where it affects failures/order. There is no service locator or container. Use fetched associative arrays rather than adding entity/DTO classes. Do not share business actions by calling another controller or requiring another endpoint.

## 7. Models and database boundaries

Reuse `BaseModel`, `NguoiDungModel`, `VaiTroModel`, `GiangVienModel`, `SinhVienModel`, `LanhDaoModel`, `DeTaiModel`, `DangKyDeTaiModel`, `OTPModel`, `ThongBaoModel`, `ThongBaoDoAnModel` and `CaiDatModel`. Use models as the project's persistence boundary; do not add a repository counterpart for every model.

| Existing responsibility | Target owner |
| --- | --- |
| Account/role/profile queries, password hashes/reset-token persistence | Existing user/role/profile models |
| Topic queries, duplicate/count checks, state/history writes and counters | `DeTaiModel` |
| Registration lists, eligibility queries, approval/removal and examiner writes | `DangKyDeTaiModel`, with current student-specific queries retained in `SinhVienModel` |
| Personal/public notices and recipient lookups | `ThongBaoModel` |
| Page content, timeline and notice persistence/visibility calculations | `ThongBaoDoAnModel` |
| Setting rows and current lecturer capacity fields | `CaiDatModel` and `GiangVienModel` respectively |
| Joined report rows/examiner lookups/statistics | Existing registration/topic/profile models, named for the report query |

Extract existing queries faithfully, including filters, joins, sort order, LIMIT choices, selected fields and update conditions. Do not replace a title-based lookup with an ID lookup, combine queries or change statement-success semantics merely because it is cleaner; data-dependent behavior is explicitly uncertain. Generic CRUD can remain inside models for compatibility with legacy callers.

Preserve database/table/column names and existing PDO settings. Keep the singleton connection shared across related model operations. Retain transactions currently inside models, existing per-row/per-topic commit boundaries, and notification work after commits. A service wrapping the workflow must not add an outer transaction or move the notification inside an existing one. Keep existing methods available to unmigrated callers; add named extraction methods rather than removing/changing shared contracts during a single-module migration.

Treat the supplied DDL as a read-only structural reference, never execute or modify it. Retain its primary keys, unique email/profile-code/user-link/registration-pair constraints, nullable fields, enums and defaults. Code explicitly inserting registration `da_duyet` must keep overriding the declared `cho_duyet` default. User IDs, role-profile IDs, supervisor IDs and examiner IDs remain distinct; notification helpers retain profile-to-user recipient translation. Topic deletion's declared child cascades and examiner FK's SET NULL behavior must remain database behavior, not replacement application cleanup or reconciliation. The DDL has no per-track registration constraint or automatic counter-maintenance definition; do not add either.

Use associative arrays already returned by the models. Add named methods to the owning models for page-authored SQL, rather than generic repositories or SQL passed through controllers. Preserve existing SELECT aliases, LIMIT/ordering, parameter values, affected-row versus execute-success handling and lookup absence semantics. For report/assignment render-time queries, fetch results through those methods into prepared view data without changing which row is selected. If moving queries ahead of output would change failure/header timing that cannot be verified, defer that endpoint.

Preserve the transaction rows in all 22 behavior-map sections. In particular, handler eligibility/capacity reads stay before registration's model transaction; topic/registration model beginTransaction calls remain outside their current try boundaries; per-topic review and per-row import commits remain independent; OTP/account/profile, edits, settings and publication writes must not gain enclosing transactions. Keep post-commit notification/mail failures distinct from the preceding committed operations. No retry, locking, idempotence, counter repair or generalized exception translation is introduced by extraction.

## 8. Services only where necessary

Most list/profile/notification API pages can call models directly from a controller. Simple creation or a query does not justify another layer.

A concrete `RegistrationService` becomes useful when migrating the student registration workflow: it can coordinate student eligibility, lecturer capacity checks, the existing registration model transaction and the subsequent lecturer notification. The controller still handles HTTP parsing, guard, session flashes and redirects. The service receives IDs/data rather than reading superglobals, has no SQL or rendering, and returns the same outcome data/error information required by the existing endpoint.

Other possible extractions are account/OTP completion, import parsing plus per-row account creation, or topic submission with leadership notifications. Create each service only if that migrated module has enough coordination to warrant it. Avoid a generic CRUD service, an event bus for notifications, a provider abstraction that merges the three OAuth policies, or a universal workflow/state machine. Retain existing model-local business rules while legacy callers depend on them; extract only the confirmed orchestration required by the module.

If rules or authorization are unclear, the service is not a place to invent them. Leave the affected endpoint legacy and record the blocking uncertainty.

## 9. Views, shared header and assets

`View` includes a template selected by the controller from a fixed application path and supplies an explicit data array. A small isolated include scope, using `extract(..., EXTR_SKIP)` or equivalent explicit locals, is enough. Templates must not be selected by request-supplied filenames. No automatic output buffer or enclosing layout is needed; pages already have different markup structures and exports need their own headers.

Views contain markup, existing inline CSS/JavaScript, escaped output, loops, conditional display and pure formatting helpers. They must not instantiate models, call database methods, perform session writes, consume flashes, send redirects, validate authorization, or execute workflow actions. Existing response bodies and their escaping/sanitization behavior must be preserved; do not add double escaping or change stored values while extracting templates.

The [current header](../includes/header.php) queries notifications and can bootstrap the application. It cannot be reused as a migrated view unchanged. When the first HTML module migrates:

1. Create a presentation-only copy at `app/views/partials/header.php`, retaining the actual markup/styles/scripts.
2. Prepare `currentUser`, `isLoggedIn`, unread count, recent notification rows, page title and current page/directory in controller coordination, via a small shared `app/controllers/page_data.php` function when useful. It calls `ThongBaoModel`, contains no SQL and performs no rendering.
3. Provide those values at the original header-preparation point, after that endpoint's actions/redirects, and include the pure partial from its view.
4. Leave legacy `includes/header.php` unchanged for legacy callers. Migrate footer/sidebar/modal copies only when needed, first inspecting any data-loading behavior.

Do not automatically call header preparation for JSON, CSV, plain-text or standalone public pages. That would introduce extra DB reads/failures. Do not globally consume all flash keys; preserve which keys each endpoint reads and when.

Retain assets at their current public locations and preserve DOM, classes, inline scripts, forms, field names, action URLs, fetch URLs, client-side search and print behavior. Filesystem includes in moved templates become explicit absolute includes, while browser-relative URLs still resolve against the retained public wrapper address. Shared menu highlighting currently uses `PHP_SELF`; wrappers retain the visible entry identity, and controllers should pass explicit original-page context rather than rewrite server globals. Do not add an HTML `<base>` tag or normalize redirect strings as a shortcut.

Prepare report/examiner queries outside templates, but preserve the current selection/order semantics and output format: CSV with its BOM, HTML tables downloaded as `.xls`, and printable HTML for the `pdf` action. Report template extraction is not a format conversion.

## 10. Authentication, authorization and middleware

No generic middleware layer is needed initially. Existing helper calls or copied endpoint-specific checks belong at the start of each corresponding controller action, preserving exact rejection handling. A small helper may remove genuinely identical guard duplication after verification; it must not apply inferred policies by directory or broaden ownership checks.

| Existing contract | Target handling |
| --- | --- |
| Student-only and leadership-only checks | Retain their `requireRole` behavior and redirects |
| Lecturer pages allowing G or L, sometimes requiring lecturer profile | Retain each action's dual-role/profile checks separately |
| Notification API login failure | Retain its JSON response and status, not a generic HTML-login redirect |
| Redirect-before-JSON guards on other endpoints | Keep their ordering rather than imposing API-wide errors |
| Object ownership checks | Move confirmed predicates with their queries/rules; do not infer missing checks |
| Imports/quick-date editor without guards | Intended policy is unresolved; keep affected paths legacy until clarified, rather than silently adding guards |
| HTML vs AJAX login and provider session differences | Preserve separate actions and session writes/rotation/time initialization |
| Reset page variants | Keep separate actions/lookups; do not replace token-only lookup with expiring lookup implicitly |

Continue using the current identity/role/profile session fields, CAPTCHA/OTP/recovery/provider state and flash keys. CSRF validation remains endpoint-specific. Do not globally enable timeout validation, CSRF enforcement, session regeneration, active-account rechecks or a uniform provider login routine as part of the refactor. Such changes would alter the mapped behavior.

Retain guarded standard session startup and its current lifetime-zero, path `/`, HTTPS-dependent Secure, HttpOnly and SameSite=Lax cookie settings through the existing configuration. Do not flatten early/standalone starts into standard startup. HTML login keeps regeneration/login_time/CSRF rotation; AJAX login and individual provider callbacks keep their different policies. `getCurrentUser()` remains a session-only identity read, with no new account-status database refresh. Password changes retain their lack of automatic session rotation/logout. Preserve each endpoint's flash names (including success_message, warning and provider/registration fields), write/consume timing, and redirect-before-JSON guard order.

## 11. Mapping modules into the target

The names below are placement guidance, not an instruction to create all classes now. Create controllers for one confirmed module at a time; combine small related pages when reasonable and split only when actual size/responsibilities justify it.

| Current endpoint group | Controller responsibility | Existing model/service boundary |
| --- | --- | --- |
| Root profile/edit/password/settings | `ProfileController`: separate actions preserving each form and redirect contract | User and role-profile models; no service needed for read-only profile |
| `thong_bao.php`, `api/notifications.php` | `NotificationsController`: personal actions and separate API action | `ThongBaoModel`; API remains placeholder, with no new DB access |
| `sinh_vien/` lists/detail/dashboard/own topics | `StudentTopicController`: page preparation and own-page side-effect coordination | Topic/student/registration models; move own-page updates out of the view |
| Student register/delete | `StudentRegistrationController`: HTTP contract and results | Existing models; registration service only for confirmed orchestration |
| `giang_vien/` topic CRUD/submission/detail | `LecturerTopicController`: owner/track/form coordination | Topic/profile/notice models; submission service only if useful |
| Lecturer registration review/actions/removal/CSV | `LecturerRegistrationController`: preserve differing single/bulk/removal/export contracts | Registration/topic/student models; unresolved bulk methods remain legacy |
| Leadership topic dashboard/review/approved lists/actions | `LeadershipTopicController`: department review and page data | Topic/history/leadership/notification models |
| Leadership pending registrations and examiner assignments | `AssignmentController`: distinct list and handler actions, existing access policy | Registration/lecturer models; no unified assignment-update policy |
| Leadership accounts and import endpoints | `AccountController` for list/create; import controller/service only when independently verified | User/role/profile models and actual parser; auth uncertainty gates imports |
| Leadership parameters/menu | `SettingsController`: preserve action-specific JSON/redirects and storage | Settings/lecturer models; existing JSON file helper/path for menu |
| Notices/project content | `NoticeController` and `ProjectContentController` as needed | Existing notice/content models; public and admin actions remain distinct |
| Leadership reports | `ReportController`: filters, format choice, headers; report views render rows | Named queries in existing models; no report engine |
| Local authentication/OTP/recovery/providers | Small auth controllers grouped by actual workflow, separate provider/reset actions | Existing user/role/OTP/profile models and mail/cURL helpers; services only where justified |
| Home/public topics/lecturer details/static pages | Public controller actions added as each endpoint is verified | Existing topic/profile models and faculty fallback data; honor startup exceptions |

Controller grouping does not authorize simultaneous migration of high-risk modules. Shared model methods used by several groups must remain backward-compatible until their callers migrate.

`NotificationsController::api()` is already implemented; `thong_bao.php` remains legacy. Other controller names above are placement guidance and should not become empty files. Keep one action per existing endpoint and split workflows only when actual size warrants it. Registration creation, own-page auto-rejection, deletion variants, assignments and provider callbacks retain their distinct actions, not one generic CRUD/state action. Import endpoints remain independent from guarded leadership account creation even if they share persistence models.

## 12. Incremental migration order, dependencies and risk

Phase 4 and Phase 5 are already complete according to the plan and current files. Their dispatcher and API pilot are the baseline; neither is restarted. [Pilot verification](phase5-verification.md) records earlier checks, including its limits. This Phase 3 revision schedules later work only and does not mark Phase 6 started.

Risk below is an architectural assessment based on writes, coupling, startup differences and unresolved behavior, not proof of production safety. A row groups related candidates; migrate one endpoint or coherent small module at a time, verify and commit it before another. Existing legacy models/workflows can remain dependencies; an earlier row need not convert every provider/producer first. Shared header extraction is part of the first HTML migration, not a new application-wide rollout.

| Order | Candidate endpoints / module | Dependencies and prerequisite | Risk / preservation gate |
| --- | --- | --- | --- |
| 0: existing baseline | api/notifications.php | Existing wrapper, standard startup, NotificationsController::api; no model/views | Low, already migrated; retain placeholder contract and previous verification |
| 1 | profile.php read-only profile | Existing user/role/profile models; first pure header/footer partials and controller preparation at original point | Low-medium: shared-header DB reads and absent profiles (U1/U13); verify original HTML/navigation/flash timing |
| 2, separately | sinh_vien/dashboard.php, danh_sach_de_tai.php, chi_tiet_de_tai.php; then ordinary lecturer read pages | Verified header pattern, existing topic/registration/profile methods; extract any page SQL into owning models | Medium: detail/list filtering, role/profile/ownership differences (U1/U5); do not include own-page auto-rejection or writes |
| 3, separately | index.php; danh_sach_de_tai_cong_khai.php | Ordinary startup/header, public notice/topic models; preserve pre-redirect home read | Medium: U2/U10; retain unfiltered notices and original role redirects; do not include database-only lecturer detail |
| 4 | thong_bao.php personal page | Extend existing NotificationsController with separate personal action; ThongBaoModel named query methods, header pattern | Medium: U2/U13, recipient check/GET writes/stored-link redirects; API remains unchanged |
| 5, separately | lanh_dao/quan_ly_thong_bao.php; project editor/public page | Existing notice/content models and pure views; page-specific auth and same-page/PRG contracts | Medium-high: U1/U10/U13, publication ambiguity, nontransactional writes; quick date editor remains deferred for U5 |
| 6, separately | Lecturer topic create, edit, submit, delete | Lecturer/profile/topic models, leadership recipient notification helper; known list/view context | High: U3/U5/U13; preserve 100-topic model rule, separate unused 10-limit method, state/owner checks, per-item notification timing and delete cascades; destructive verification gate |
| 7 | Leadership topic review and single/bulk handler paths | Topic/history methods, lecturer notices, established topic displays | High: U1/U5/U13; keep hardcoded prior state, per-topic transactions and post-commit notices; no inferred pending-state enforcement |
| 8, separately | Student register; own-topics; rejected-row deletion | Existing eligibility/topic/capacity/registration models, personal notifications and verified read views | High: U4/U5/U13; keep approved insert, counter effects and read-triggered auto-rejection; deletion requires safe verification; no whole-group migration |
| 9, separately | Lecturer single approval/rejection and each deletion variant | Registration/topic/user identity, notification helpers, existing review UI | High: U4/U5/U13; lack of owner check/prior-state guard and differing deletions require determined preservation contract; bulk methods remain deferred U6 |
| 10, separately | Assignment page, simple handler, dedicated handler; reports/CSV | Registration/lecturer statistics, query extraction, existing examiner fields and report templates | High: U12/U11/U5/U13; ambiguous title lookup and different examiner/date/JSON behavior; read-only reports may proceed earlier if their dependencies are verified |
| 11, separately | Leadership account list/create; settings/menu; edit/password pages | Reused profile/setting models, verified header, named persistence operations, current JSON menu file | Medium-high: U1/U3/U13; preserve nontransactional partial writes, capacity storage differences, silent/JSON/flash branches; menu is a file write |
| 12, separately | Local HTML/AJAX login/logout; ordinary password recovery/reset | Existing helpers/user/role/profile models, mail doubles and request/session comparisons | High: U2/U9/U13; keep login variants and reset policies distinct; early-session/diagnostic variants require verified startup extension |
| Deferred individually | Registration/OTP; Google new-account flow; GitHub flow; remaining OAuth callbacks | M1 definition evidence; M2/M3 result/schema contract evidence; U7/U8 provider/initialization behavior | High/blocking: leave affected paths legacy until behavior can be determined; no missing table/column/join/helper implementation as an architectural fix |
| Deferred individually | Bulk/automatic lecturer review, imports, quick-date editor, lecturer detail, CAPTCHA/faculty and diagnostics | U6 missing methods; U5 auth determination; U11 parsers; verified nonstandard startup/header lifecycle | High/blocking or special-startup: no invented algorithms/guards or automatic standard bootstrap; process only after relevant checkpoint evidence |

A later module change must capture the applicable behavior-map contract, add named persistence methods without changing existing callers, extract controller/template coordination faithfully, and register its wrapper together with the route. Verify allow/deny roles, methods/inputs, SQL arguments/results/writes, transaction sequence, session/flash/cookie effects, redirects/status/body and existing UI at the old URL. Fix extraction regressions before marking that module complete and committing it separately. Changes to business rules require a separate task, not migration acceptance.

Keep legacy includes/helpers and URL files available to unmigrated callers. Legacy cleanup belongs to Phase 7; do not remove duplicate reset/delete paths, repair aliases or implement missing methods here. Compatibility wrappers may remain indefinitely. If behavior/auth is undetermined, a schema change seems necessary, destructive database operations are required, or a critical comparison fails, stop that module as AGENTS.md requires. Restore its legacy entry and remove its route/module-specific extraction together if already changed; retain unrelated working infrastructure. Do not add a duplicate legacy tree or permanent feature-flag framework for rollback.

## 13. Preservation and verification of M1-M3 and U1-U13

The [schema mismatch report](behavior-map.md#separate-mismatch-report) and [uncertainty register](behavior-map.md#explicit-uncertainties-and-preservation-checkpoints) are authoritative. Each affected future migration needs an explicit before/after preservation record. An inconsistency can be source-established even when its intent is unknown: retain the established effect; never substitute a preferred rule. Where the effect or authorization cannot be determined, defer the affected endpoint rather than claim successful migration. No checkpoint is resolved by this architecture document.

Use source inspection, request-level comparisons with isolated sessions, scripted persistence/model doubles, and controlled provider/mail doubles where needed. They can establish call order, arguments, response/session contracts and simulated failure handling without live data. They do not establish deployed SQL mode, concurrency, provider availability, actual cascades or data-dependent row selection. No production/sample data is required or requested. Do not execute or modify the supplied schema, alter the database, or claim database integration coverage from a mock. Leave any dependent migration blocked when adequate verification is unavailable within its authorized scope.

| Checkpoint | Required preservation | Verification evidence / unresolved gate |
| --- | --- | --- |
| M1: OTP definition absent | Keep OTPModel and legacy registration/verification; do not create otp_verification or replace it with session storage | Read-only definition/contract evidence is needed to determine constraints/defaults; call-order doubles can show delete/insert/consume timing but cannot resolve missing schema |
| M2: oauth_provider absent | Do not remove the INSERT field or add a column to make new Google accounts work | Compare existing-user/new-user branches and caught failure contract against the declared schema; new-account migration stays deferred while incompatibility is unresolved |
| M3: GitHub result fields absent | Do not add joins/aliases/columns or infer role/profile in the callback | Trace findByEmail base result and accessed keys, missing-field/failure paths; no successful callback claimed without a determined result contract |
| U1: live schema/record integrity | Retain declared keys/defaults/FKs and exact missing-row assumptions; no schema/data repair | Inspect SQL/payloads against read-only DDL; doubles cover missing rows/constraint failures. Deployed integrity/cascades/triggers remain unverified |
| U2: redirects | Keep BASE_URL helper prefixing, literal direct Location headers, ../ and bare-name arguments | Capture exact emitted Location/status/exit and visible request identity for root/subfolder assumptions; do not normalize intended destinations |
| U3: limits | Keep current model 100-per-track check, separate unused 10-limit method, settings keys/lecturer columns and current coercion | Boundary/call-argument comparisons for each actual code path; do not connect unused limits or reconcile topic-versus-student wording |
| U4: lifecycle/counters | Preserve approved registration insert/increment, repeated approval increment, rejection/deletion/auto-rejection with no decrement | Record exact statement/model-call sequence, statuses/counter deltas and commit boundaries using doubles; no reconciliation or locking added |
| U5: authorization | Preserve endpoint-specific guards/absence/ownership predicates and G/L versus L distinctions; no folder-derived policy | Anonymous/each-role/wrong-owner/profile-absent comparisons. Unclear authorization blocks affected migration; no guard repair in refactor |
| U6: missing methods | Do not invent bulk/automatic approval algorithms or fabricate success counts | Verify call sites and absent definitions; leave affected routes legacy until actual behavior is established |
| U7: GitHub wiring | Keep dependency ordering; do not load missing helpers/config globally or repair M3 | Inspect include chain/constants/helper availability and startup/error behavior; unresolved flow stays legacy |
| U8: providers | Keep separate creation/active checks/state cleanup/session regeneration/time/CSRF policies | Deterministic provider-response/error doubles and session comparisons; deployed credentials/network and oauth_selected_role writer remain unknown |
| U9: recovery/OTP failures | Keep token-only versus expiry/active lookups, password-then-clear writes, OTP consumption before account creation and diagnostic output order | Compare query predicates, calls, sessions, redirects/header timing and mail failure paths; no policy unification or added encompassing transaction |
| U10: publication/timing | Home retains unfiltered public notices; project visibility retains date/state checks; no new registration-window enforcement | Query selection/predicates and date-boundary display comparisons; absence of permission-key read must remain absence |
| U11: imports/reports | Keep ZIP/XML parser for accepted extensions, per-row commit/error behavior, CSV BOM, HTML .xls and browser-print pdf action | Parser/error fixtures and exact output/header/client checks without production data; doubles cannot prove binary .xls or browser compatibility; defer destructive/import verification if required |
| U12: assignment selection | Retain student-code/topic-title subqueries, LIMIT 1 selection semantics and distinct handler date effects | Compare extracted SQL/parameters/order and prepared view rows; ambiguous real selection stays uncertain, no replacement ID lookup |
| U13: failures/partial commits | Preserve lazy dependency construction, catches/uncaught boundaries, no-row success semantics and notifications after commits | Inject failures at each existing boundary and compare calls/commits/output/status/session; no global catch/response/transaction that converts existing behavior |

Keep inconsistencies visible in module review notes and verification results. DDL constraints are structural evidence, not proof of business intent. Resolving a blocking checkpoint requires evidence sufficient to preserve behavior; authorizing a rule/schema fix is a separate decision outside this architecture migration.

## 14. Acceptance criteria and Phase 3 validation

The target is ready for incremental implementation when its dispatcher and registry are small, old URLs stay stable, migrated controllers contain no SQL, views have no persistence/actions, and existing models/data contracts remain reusable by legacy callers. Services are optional workflow-specific coordination; configuration/auth/session behavior remains established per endpoint.

For later validation, use focused infrastructure checks for trusted route lookup/unknown paths/view isolation, then each module's recorded behavior comparisons. Test both local subfolder and hosting-root URL assumptions when deployment access permits. Check relative assets/forms/fetch calls, original page context, HTML/JSON/download output and header ordering. Database verification must use an appropriate isolated environment; this document authorizes no live destructive operation.

Phase 3 validation is documentation-only: all ten requested topics are covered by sections 1-13 (request flow, directories, responsibilities, module mapping, URL compatibility, authentication/session/authorization, database access, views/layouts, migration order/dependencies/risk, and checkpoint preservation/verification). Check current infrastructure descriptions against the actual files, all M1-M3/U1-U13 matrix rows, source/document links, whitespace and final edit scope. No new PHP files, routes, dependencies, schema changes or business migrations are part of this revision. Existing uncommitted behavior-map, plan and architecture-analysis edits must remain intact; only this document and the Phase 3 section of the plan are changed by this task. No runtime or database verification is claimed for Phase 3.
