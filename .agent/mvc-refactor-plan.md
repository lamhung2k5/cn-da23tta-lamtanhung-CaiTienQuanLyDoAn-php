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

Status: NOT STARTED

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

Status: NOT STARTED

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

Status: NOT STARTED

Create only the minimum infrastructure required for MVC.

The legacy application must remain operational.

---

## Phase 5 — Pilot Module

Status: NOT STARTED

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
