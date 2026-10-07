# MVC Refactor Rules

This repository contains a legacy PHP application that is being
incrementally refactored to MVC.

## Primary Goal

Reorganize the codebase into a clear MVC architecture while preserving
the existing system behavior.

## Mandatory Rules

- Do not rewrite the entire application at once.
- Refactor incrementally by module.
- Preserve all existing business rules.
- Preserve the current database schema.
- Do not rename tables or columns.
- Preserve existing data behavior.
- Preserve existing URLs whenever practical.
- Preserve HTML, CSS, JavaScript and visual appearance.
- Do not redesign the UI.
- Do not add new features during the MVC refactor.
- Inspect the legacy implementation before changing behavior.
- Never guess unknown business rules.

## Architecture Rules

- Use a Front Controller.
- Routes should be centralized.
- Controllers handle HTTP/request coordination.
- Controllers must not contain raw SQL.
- Views must not query the database.
- Views should contain presentation logic only.
- Database access belongs in Models or Repositories.
- Complex business logic should be placed in Services when necessary.
- Authentication and authorization behavior must remain unchanged.

## Migration Rules

For each module:

1. Inspect the legacy implementation.
2. Identify current behavior.
3. Refactor only that module.
4. Preserve database behavior.
5. Preserve UI behavior.
6. Validate the result.
7. Fix regressions before moving on.
8. Commit successful work separately.

Do not migrate multiple high-risk modules at the same time.

## Safety

Stop the affected migration instead of guessing if:

- existing business behavior cannot be determined;
- a database schema modification appears necessary;
- destructive database operations are required;
- authentication or authorization behavior is unclear;
- critical verification fails.