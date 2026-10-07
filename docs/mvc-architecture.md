# Target MVC Architecture

Phase 3 design, 2026-10-08. This document specifies a target structure; none of the proposed application files or routing changes are implemented in this phase.

## 1. Basis and decisions

The design follows [AGENTS.md](../AGENTS.md), the [migration plan](../.agent/mvc-refactor-plan.md), [current architecture](current-architecture.md) and [behavior map](behavior-map.md). Source checks include [bootstrap](../bootstrap.php), [BaseModel](../models/BaseModel.php), [Apache configuration](../.htaccess), [shared header](../includes/header.php), [profile page](../profile.php) and [notification API](../api/notifications.php). The behavior map takes precedence over general README feature descriptions when they conflict with executed code.

Use native PHP with the existing PDO connection, models, configuration and helpers. Add one internal front controller, one exact-path router, one route table, ordinary controller classes and PHP templates. Add a service only when an actual migrated workflow needs coordination beyond request handling. Keep the current deployment directory and browser URLs.

This is sufficient because persistence already has a shared `Database`/`BaseModel` foundation. The missing boundaries are request coordination, SQL ownership in pages, and presentation separated from data loading. A framework, ORM, dependency-injection container, repository interface hierarchy, generic middleware pipeline, Request/Response object hierarchy or new template language is unnecessary for this application. No new Composer dependency or runtime upgrade is part of this design; new infrastructure should remain compatible with the documented PHP 7.4 baseline, subject to confirming the installed runtime in Phase 4.

## 2. Directory structure

Proposed paths are shown below; only create them as needed in later phases.

```text
WebsiteQuanLyDeTai/
  app/
    .htaccess                  deny direct HTTP access to internal app files
    front_controller.php       shared dispatch entry, included by URL wrappers
    Router.php                 exact route lookup and controller invocation
    View.php                   render a trusted PHP template with supplied data
    controllers/
      ProfileController.php    example module, created when it migrates
      NotificationsController.php
      page_data.php            shared header-data preparation, when first needed
    views/
      account/                 migrated profile/password templates
      auth/                    migrated authentication templates
      sinh_vien/               migrated student templates
      giang_vien/              migrated lecturer templates
      lanh_dao/                migrated leadership templates
      public/                  migrated public content templates
      reports/                 existing HTML/print report templates
      partials/                pure header/footer/sidebar/modal templates
    services/                  optional; created for an actual workflow
  config/
    routes.php                 centralized migrated-route table, new
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
  assets/, img/, vendor/       existing locations retained
  docs/                        migration documentation
```

Do not relocate existing models/helpers/configuration to `app/` merely for uniform naming. Keeping them in place lets old and migrated modules use the same implementation. New views may be grouped by role for discoverability; access policy still belongs to the endpoint, not its view directory.

The mandatory PHP foundation is just four new files: `app/front_controller.php`, `app/Router.php`, `app/View.php`, and `config/routes.php`. A small `app/.htaccess` should deny direct HTTP access to the new internal directory while allowing PHP filesystem includes; it must not change access to existing legacy directories. Confirm the installed Apache directives in Phase 4. Controllers/templates are module work, not infrastructure placeholders. `page_data.php` and services are added only when used. No base controller is required.

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

Do not select a controller, include path, bootstrap file or template from GET/POST values. Direct HTTP access to the internal front controller without a wrapper-supplied key must not dispatch anything; return an infrastructure 404. Apply the same no-dispatch rule to unknown internal route keys. Do not introduce arbitrary legacy-file inclusion as a router fallback. Unmigrated public endpoints never need that fallback.

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

For confirmed exceptions, allow a small, explicitly declared route startup option, interpreted by the front controller: `standard` (default existing bootstrap), `database` (existing database configuration only), `session` (existing standalone session start), or `none` (no common startup). A standard route that formerly started its session first can declare `pre_session=guarded` or `pre_session=unguarded`, preserving its original session-status check or unconditional call. These optional cases are added when such an endpoint migrates; Phase 4 need only implement ordinary startup. They are initialization choices, not an extensible middleware system. Nonstandard controllers load only the concrete dependencies needed by their verified actions; they must not pull in ordinary bootstrap indirectly through a view.

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

## 12. Coexistence and later implementation sequence

Phase 4 should add only the four minimal PHP infrastructure files and the scoped internal-directory access rule, with an initially empty migrated-route table, no existing endpoint replacement, no catch-all rewrite and no speculative business controller. Dispatcher/view behavior can be checked in isolation without running business writes. An empty route table keeps the current application operating exactly through its legacy files.

For Phase 5, select and verify one small endpoint. `api/notifications.php` is a plausible pilot because its source has no SQL, writes, HTML layout or model dependency, while exercising bootstrap, routing and its custom login response. This is a recommendation; Phase 5 still requires inspection/validation and must preserve its empty response rather than implement notifications. If an HTML pilot is preferred, `profile.php` is a candidate but includes shared header-query extraction, so its actual change scope is larger.

Each later module migration should:

1. Resolve its applicable behavior-map uncertainties and capture relevant before/after evidence.
2. Add named persistence methods for its page SQL without changing queries or shared callers.
3. Extract verified orchestration only if necessary, and copy existing presentation into pure views.
4. Add the controller/route and replace only that module's public script(s) with wrappers.
5. Verify allowed/disallowed roles, inputs, writes/transaction order, sessions, redirects/status/response body and UI at original URLs.
6. Fix regressions, then commit that module separately before starting another high-risk module.

Leave original shared includes available while unmigrated callers exist. Do not use this design to delete duplicates, repair broken login aliases, implement missing bulk methods or remove diagnostic endpoints. Legacy cleanup belongs to Phase 7 after verified migration. Compatibility wrappers may remain indefinitely where old URLs are still needed; central routing does not require removing them.

If a migration fails critical checks or requires guessing business/auth rules, stop that affected module as AGENTS.md requires. Revert its route/wrapper and its module-specific extraction together to restore the legacy path, keeping unrelated completed modules intact. Do not duplicate the full application into a second legacy tree or add a permanent runtime feature-flag system just for rollback.

## 13. Uncertainties that constrain the design

The [behavior map uncertainty register](behavior-map.md#explicit-uncertainties-and-preservation-checkpoints) remains authoritative. Phase 3 defines separation points, not resolutions.

- U1/U2: live schema, missing profiles and redirect resolution require module verification; no schema redesign or redirect normalization.
- U3/U4/U10: inconsistent limits, status/counter effects and publication/registration-window behavior must stay distinct; do not create one invented rule engine.
- U5: unclear authorization blocks affected migration, not this documentation phase. Controller placement never determines rights.
- U6/U7/U8: missing bulk methods, GitHub startup/user fields and provider outcomes are not filled in by new controllers or global bootstrap changes.
- U9/U11/U13: OTP/reset timing, partial commits, parser failures and output/header order must be preserved/verified before extracting higher-risk flows.
- U12: assignment lookup ambiguity must not be silently fixed while moving SQL into a model.

Installed PHP/Apache behavior, provider/SMTP availability and database constraints have not been runtime-tested in this phase. No new middleware, service or query is justified by an unknown rule.

## 14. Acceptance criteria and Phase 3 validation

The target is ready for incremental implementation when its dispatcher and registry are small, old URLs stay stable, migrated controllers contain no SQL, views have no persistence/actions, and existing models/data contracts remain reusable by legacy callers. Services are optional workflow-specific coordination; configuration/auth/session behavior remains established per endpoint.

For later validation, use focused infrastructure checks for trusted route lookup/unknown paths/view isolation, then each module's recorded behavior comparisons. Test both local subfolder and hosting-root URL assumptions when deployment access permits. Check relative assets/forms/fetch calls, original page context, HTML/JSON/download output and header ordering. Database verification must use an appropriate isolated environment; this document authorizes no live destructive operation.

Phase 3 validation is documentation-only: all required plan topics are specified (front controller, router, controllers, models, optional services, views, middleware decision and configuration); source/document links are checked; prior documentation edits are preserved; only this document and the Phase 3 plan status change. No application source, schema, runtime routing or business module has been modified.
