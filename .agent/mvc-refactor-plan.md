# MVC Refactor Plan

## Goal

Incrementally migrate this legacy PHP application to MVC while preserving:

- business behavior;
- database schema;
- current UI;
- authentication/session behavior;
- existing functionality.

---

## Phase 1 — Current Architecture Analysis

Status: COMPLETE

Completed: 2026-10-08. Analysis documented in `docs/current-architecture.md`.
Validation: source inspection only; no application source, behavior, or database changes.

Tasks:

- identify entry points;
- identify current routing;
- identify database connection and database access;
- identify authentication;
- identify session usage;
- identify authorization/roles;
- identify major modules;
- identify files mixing SQL, HTML and business logic;
- identify dependencies between modules.

Output:

docs/current-architecture.md

---

## Phase 2 — Behavior Mapping

Status: COMPLETE

Completed: 2026-10-08. Existing behavior mapped in `docs/behavior-map.md`.
Validation: source inspection and documentation checks; uncertainties explicitly recorded; no application source changes.

For important modules identify:

- URL/entry point;
- allowed roles;
- inputs;
- validation;
- business rules;
- database reads;
- database writes;
- session reads/writes;
- redirects;
- views/output.

Output:

docs/behavior-map.md

---

## Phase 3 — Target MVC Architecture

Status: COMPLETE

Completed: 2026-10-08. Minimal native-PHP target architecture designed in `docs/mvc-architecture.md`.
Validation: documentation/source review and link/scope checks; no application source or business modules modified.

Define:

- Front Controller;
- Router;
- Controllers;
- Models/Repositories;
- Services where necessary;
- Views;
- Middleware if necessary;
- configuration structure.

Output:

docs/mvc-architecture.md

Do not migrate business modules during this phase.

---

## Phase 4 — MVC Infrastructure

Status: COMPLETE

Completed: 2026-10-08. Added the front controller, exact-path router, isolated view renderer, empty route table, and internal Apache access rule defined in docs/mvc-architecture.md.
Validation: PHP 8.2 syntax checks passed for all four new PHP files; isolated routing, dispatch, view isolation/path confinement, and pre-bootstrap 404 checks passed. Local Apache returned 403 for internal PHP files and 200 for an existing static asset.
Scope: no business modules migrated; existing endpoints, bootstrap, database schema, and application behavior were not changed. Business flows were not exercised against the database.

Create only the minimum infrastructure required for MVC.

The legacy application must remain operational.

---

## Phase 5 — Pilot Module

Status: COMPLETE

Completed: 2026-10-08. Migrated only `api/notifications.php` to its existing URL wrapper, one centralized route, and `NotificationsController::api()`.
Selection: architecture dependency map identifies this JSON placeholder as independent of database-backed notification workflows; target architecture section 12 recommends it as a low-risk pilot.
Validation: PHP syntax checks; 36 legacy/MVC behavior comparisons with real bootstrap/helpers and isolated sessions; retained exception-response comparison; local Apache anonymous response/header/cookie checks. All passed against behavior-map section 18. Details and verification limits: `docs/phase5-verification.md`.
Scope: empty notification response, custom login failure, HTTP methods/status, sessions, database behavior, and original URL preserved. No other module migrated; no schema, UI, models, helpers, or bootstrap changes.

Select one low-risk module.

Process:

1. inspect legacy implementation;
2. migrate to MVC;
3. preserve behavior;
4. preserve database interaction;
5. preserve UI;
6. verify;
7. fix regressions;
8. commit.

---

## Phase 6 — Incremental Migration

Status: NOT STARTED

Determine migration order based on dependencies.

For each module:

1. inspect;
2. document behavior if necessary;
3. migrate;
4. verify;
5. repair regressions;
6. update this plan;
7. commit;
8. continue to the next module.

---

## Phase 7 — Legacy Cleanup

Status: NOT STARTED

Only after migrated functionality is verified:

- remove confirmed dead files;
- remove obsolete includes;
- remove duplicate code;
- find SQL remaining in Controllers or Views;
- find direct database access from Views;
- identify legacy routing that is no longer used.

---

## Phase 8 — Final Audit

Status: NOT STARTED

Verify:

- MVC boundaries;
- routes;
- authentication;
- authorization;
- sessions;
- business rules;
- database behavior;
- UI;
- error handling.

Output:

docs/mvc-refactor-report.md
