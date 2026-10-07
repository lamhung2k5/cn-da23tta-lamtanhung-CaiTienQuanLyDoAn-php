# Existing Behavior Map

Phase 2, analyzed 2026-10-08. Read alongside [current architecture](current-architecture.md) and [migration plan](../.agent/mvc-refactor-plan.md).

## Scope and evidence conventions

This map describes important workflows in the checked-in PHP implementation. It is not a proposed design. It covers authentication/recovery, profiles, student and lecturer topics/registrations, leadership review/assignments, administration, content and reports. Static introductions and help pages are outside the detailed map; their entry points remain in the architecture inventory.

**Observed** means supported by the cited source, including its called models/helpers. **Uncertain** means source alone does not establish a runtime outcome, deployed configuration, data-dependent result, or intended policy. No HTTP requests, application executions, database queries, imports, email deliveries or OAuth exchanges were performed. Database writes below describe existing code; they were not executed during this analysis. No application source was changed.

URLs below are application-relative physical PHP paths. `G` = `giang_vien`, `S` = `sinh_vien`, `L` = `lanh_dao`. A public/no-guard entry means no application login guard was found on that path, not a statement about external web-server restrictions. Permissions describe code checks, not inferred intended permissions.

## Shared behavior inherited by mapped modules

Sources: [bootstrap](../bootstrap.php), [configuration](../config/config.php), [database](../config/database.php), [helpers](../helpers/functions.php), [BaseModel](../models/BaseModel.php), [header](../includes/header.php).

| Concern | Existing behavior |
| --- | --- |
| Bootstrap/session | Most endpoints load bootstrap, which includes configuration, PDO class, Google/Microsoft configuration, helpers/mail dependencies, HTTPS enforcement and models. Configuration starts a session if absent. Cookie settings: session lifetime, path `/`, HTTPS-dependent secure flag, HttpOnly, SameSite Lax. Several auth scripts start sessions earlier; their initialization is not equivalent. |
| Access guards | `requireLogin()` tests nonempty session `user_id`, sets `error`, then calls `redirect('auth/login.php')`. `requireRole()` additionally requires strict session role equality, redirecting mismatches to `index.php`. G/L scripts often implement their own dual-role check and call `redirect('../auth/login.php')`. |
| Identity | `getCurrentUser()` reads `user_id`, `email`, `ho_ten`, `vai_tro`, `vai_tro_id`, `profile_id` from session. It does not recheck database account status on every request. Profile-dependent handlers usually query profiles by session user ID. |
| Redirects | `redirect(argument)` emits `Location: BASE_URL + argument` and exits. Bare paths such as `login.php` and `duyet_sinh_vien.php` therefore refer to the application root, not automatically the caller's directory. Direct `header('Location: ...')` calls use their literal relative/absolute target. This map records actual arguments; successful navigation for unusual paths is uncertain without HTTP verification. |
| Validation helpers | `sanitize()` trims, strips tags and HTML-escapes strings, recursively for arrays. Email validation uses `FILTER_VALIDATE_EMAIL`; configured password minimum is 6. HTML constraints are not assumed to be server validation. |
| Flash/session timing | Flash helpers write a named session key; reads unset it. Timeout helpers exist but bootstrap does not call `validateSession()`. A configured 3600-second timeout is not evidence of centrally enforced expiry. |
| Persistence | Models use singleton PDO and prepared queries. Base CRUD success is generally statement execution success, not an affected-row check. No live schema, defaults, unique constraints, foreign keys, triggers or cascade behavior were verified. |
| Rendering | Pages generally render their own HTML/inline assets and shared includes. For signed-in users, header reads recent personal notifications and unread count. Every section using that header inherits those reads, even if not repeated under its database row. |
| CSRF | HTML login has explicit token validation. Other handlers below are described from their own code; no global CSRF middleware exists in the inspected bootstrap. A hidden field or helper definition alone does not enforce validation. |

## 1. Local login and logout

Sources: [HTML login](../auth/login.php), [AJAX login](../auth/login_ajax.php), [user model](../models/NguoiDungModel.php), [G alias](../auth/login_giang_vien.php), [S alias](../auth/login_sinh_vien.php), [L alias](../auth/login_lanh_dao.php), [logout](../auth/logout.php).

| Field | Behavior |
| --- | --- |
| Entry/roles | `auth/login.php`: unsigned users; signed-in users redirect home. `auth/login_ajax.php`: no already-signed-in guard, POST only. Three role-specific login scripts are redirect aliases. `auth/logout.php`: no role restriction or method check. |
| Inputs | HTML: POST `email`, `password`, `csrf_token`, optional `role_filter`; GET `role` changes the form and `context` selects presentation/study-track context. AJAX: POST `email`, `password`. |
| Validation | HTML token must equal session token; credentials nonempty. Both delegate to `NguoiDungModel::login`: user must exist and be `active`; password must match `password_verify`, legacy MD5 or plaintext. HTML applies the submitted role filter after the model returns. AJAX has no corresponding role/CSRF validation. |
| Rules | Successful legacy/outdated passwords are rehashed to Bcrypt before the HTML role-filter check. Login resolves database role and matching role-profile ID; absent profile can produce null `profile_id`. |
| DB reads | `nguoi_dung` by email, `vai_tro` by role ID, applicable `giang_vien`/`sinh_vien`/`lanh_dao` profile. |
| DB writes | Conditional `nguoi_dung.mat_khau` rehash. Logout makes no explicit DB write. |
| Session | Both login paths write the six shared identity fields. HTML regenerates ID, writes `login_time`, rotates `csrf_token`, optionally stores valid `context` as `he_dao_tao`, and consumes `error`/`success` flashes. AJAX does not regenerate ID, initialize time or rotate CSRF. Logout clears all values, expires the cookie and destroys the session. |
| Redirect/output | HTML redirects to role dashboard, otherwise home; failures render the login form/message. AJAX returns `success`, `message`, and success `redirect` path without sending a navigation redirect. Aliases call `redirect('login.php?role=...')`. Logout calls `redirect('index.php')`. |
| Uncertain | Aliases' helper arguments target root `login.php`, whereas the inspected login file is under `auth/`. Actual browser results and role/profile integrity are unverified; do not silently correct paths in a refactor. |

## 2. Registration and OTP verification

Sources: [registration](../auth/register.php), [verification](../auth/verify_otp.php), [OTP model](../models/OTPModel.php), [user model](../models/NguoiDungModel.php), [G profile](../models/GiangVienModel.php), [S profile](../models/SinhVienModel.php), [L profile](../models/LanhDaoModel.php), [email helper](../helpers/email.php).

| Field | Behavior |
| --- | --- |
| Entry/roles | `auth/register.php`: no role/login guard. `auth/verify_otp.php`: signed-in users call `redirect('../index.php')`; missing `registration_email` redirects to `auth/register.php`. Registration supports G/S/L branches, including leadership. |
| Inputs | POST `vai_tro`, `email`, `ho_ten`, `mat_khau`, `xac_nhan_mat_khau`. G: `ma_giang_vien`, `khoa`, `chuyen_mon`, phone. S: `ma_sinh_vien`, `lop`, `khoa_hoc`, `chuyen_nganh`, phone. L: `ma_lanh_dao`, `chuc_vu`, `khoa`, phone. Verification: POST `otp_code`; email comes from session. |
| Validation | Nonempty common fields, valid email, minimum password length, confirmation, email uniqueness; selected recognized role requires a nonempty unique role identifier. Registration does not explicitly reject an unknown nonempty role in its switch; verification later requires a matching `vai_tro` row. OTP must be nonempty and match an unverified, unexpired record for the session email. |
| Rules | Registration hashes the password and stages JSON data in OTP storage, not an immediate account. `createOTP` deletes prior records for that email, generates configured 6 digits and a 10-minute expiry. `verifyOTP` marks the matched record verified before account/profile creation. User creation uses `skip_hash=true`, and creates an `active` account. No encompassing verification/account/profile transaction is present. |
| DB reads | Existing email/role-code duplicates in users/profiles; matching `otp_verification`; `vai_tro` lookup. |
| DB writes | Delete/insert `otp_verification`; verification sets `is_verified=1`; then inserts `nguoi_dung` and corresponding role profile with submitted extra fields. |
| Session | Registration consumes flashes, writes `registration_email` and `dev_otp`; send success sets `success`, send failure sets `warning` containing the OTP even outside development mode. Verification reads registration context, removes both keys after completed creation and sets success. |
| Redirect/output | Registration attempts SMTP delivery and continues to `auth/verify_otp.php` even on mail failure. Verification renders OTP errors or redirects to `auth/login.php` after creation. Forms retain existing HTML behavior. |
| Uncertain | Delivery, schema duplicate constraints and recovery after partial creation are unverified. The intended eligibility for self-registering leadership is unknown. A verified OTP may remain consumed when later account/profile creation fails. |

## 3. Forgotten password, CAPTCHA and reset variants

Sources: [forgotten password](../auth/forgot_password.php), [CAPTCHA refresh](../auth/refresh_captcha.php), [alternate reset](../auth/dat-lai-mat-khau.php), [reset](../auth/reset_password.php), [diagnostic reset](../auth/new_reset.php), [user model](../models/NguoiDungModel.php).

| Field | Behavior |
| --- | --- |
| Entry/roles | Forgot-password rejects signed-in users by redirect home. CAPTCHA is a standalone session endpoint with no role/method check. All three reset pages have no login requirement; the signed-in restriction in `reset_password.php` is commented out. |
| Inputs | Forgot: POST `email`, `captcha`. Refresh: none. Alternate: GET `token`, fallback session `reset_token`, POST `password`, `confirm_password`. Standard: GET token, POST fallback `token`, passwords. Diagnostic: GET token and posted passwords. |
| Validation | Forgot requires nonempty email/CAPTCHA and case-insensitive equality with session CAPTCHA; no explicit email-format check in this handler. Standard/diagnostic model lookup requires matching token, `reset_token_expiry > NOW()` and `active` account. Alternate lookup checks token only, despite an expired-link message. All password forms require nonempty matching passwords of at least 6 characters. |
| Rules | Forgot generates a 32-byte random token rendered as hex, expiry +1 hour, saves it before email delivery, and links to the alternate reset. Successful reset writes Bcrypt password and separately clears token/expiry; no encompassing transaction. |
| DB reads/writes | Read user by email or reset token; write `nguoi_dung.reset_token`, `reset_token_expiry`, then `mat_khau`, then null token/expiry. No DB access in refresh. |
| Session | Forgot reads `captcha`, writes `reset_token` on successful delivery; when rendering it creates/replaces CAPTCHA. Refresh creates/replaces `captcha` and echoes the 6-character text. Alternate stores GET token into session, reads it on POST, clears it after success. Standard/alternate set `success`; diagnostic uses flash helpers. |
| Redirect/output | Mail success directly redirects to `BASE_URL + auth/dat-lai-mat-khau.php?token=...`; mail failure renders error but saved token remains. Alternate/standard success send direct relative `Location: login.php`. Diagnostic emits debug text before bootstrap, redirects invalid token with `redirect('login.php')`, and redirects success likewise. |
| Uncertain | Header behavior after diagnostic output depends on runtime buffering. Mail delivery and expired-token data are unverified. Intended policy for the alternate reset's missing expiry/status checks is unknown; do not treat all reset pages as equivalent. |

## 4. External identity providers

Sources: [Google start](../auth/google_login.php), [Google callback](../auth/google_callback.php), [GitHub start](../auth/github_login.php), [GitHub callback](../auth/github_callback.php), [GitHub helper configuration](../config/github_oauth.php), [Microsoft start](../auth/microsoft_login.php), [Microsoft callback](../auth/microsoft_callback.php), [user model](../models/NguoiDungModel.php).

These are three different flows. The table describes callback code branches, not a verified successful provider integration.

| Field | Google | GitHub | Microsoft |
| --- | --- | --- | --- |
| Entry/roles | `auth/google_login.php`, `google_callback.php`; no local role guard | `auth/github_login.php`, `github_callback.php`; signed-in users redirect home | `auth/microsoft_login.php`, `microsoft_callback.php`; no local role guard |
| Inputs/validation | GET error/state/code; state compared to session `oauth_state`; HTTP 200/token/email required | GET error/state/code; nonempty matching `github_oauth_state`; checks token response, user ID and email | GET state/code; state compared to `oauth_state`; token and mail/userPrincipalName required; local user must exist and be active |
| External reads | Token and user APIs | Token/user API; if email empty, searches primary verified email via emails API | Token and Microsoft Graph user API |
| DB reads | User by email; roles, all role profiles; faculty list file when deciding role | Existing user by email; selected branch reads profile code | User by email, role and role-profile ID |
| DB writes/rules | Creates missing active user with default student role/random hashed password/provider; decides role from session selection, otherwise lecturer profile, then leadership profile, then faculty-list email, otherwise student; updates role and creates missing role profile with `OAUTH_` + 10 characters of email MD5. Retries initial lookup/create once for a recognized dropped-DB error. No active-account check in callback. | Does not create an account; missing user calls `redirect('register.php')`. No active-account check. | Does not create account or profile; rejects missing/locked users. |
| Session | Start writes 16-byte-hex `oauth_state`; callback regenerates ID, writes identity and `login_time`, unsets state/selected role; no CSRF rotation here | Helper start would write `github_oauth_state`; callback unsets state before checking code, regenerates ID, writes identity/time/`login_method=github`, rotates CSRF, sets success/error | Start writes 32-byte-hex `oauth_state`; callback writes identity/`login_method=microsoft`; does not regenerate ID or initialize login time/CSRF; unsets `microsoft_oauth_state`, leaving used `oauth_state` |
| Redirect/output | External consent; direct relative login redirect on errors; temporary profile codes go to `update_profile_code.php`, otherwise direct `../<role>/dashboard.php` | External consent if helper callable; errors call `redirect('login.php')`; successful role branches call helper with role-dashboard path or bare `update_profile_code.php`, default home | External consent; errors call helper with bare `login.php`; success uses role-dashboard path, default home |
| Uncertain/source gaps | No inspected writer of `oauth_selected_role` found; callback can consume an existing value but UI selection cannot be assumed. Role-change intent is unknown. | Bootstrap does not include `config/github_oauth.php`, and neither GitHub endpoint includes it. Start calls helpers defined there; callback references its constants. Also `findByEmail()` returns a base user row while callback reads `vai_tro`/`profile_id` without resolving joins. No live schema confirms those fields. A normal successful flow is not established by this source. | Provider response/credentials are unverified; absent role rows/profiles can affect session completeness. State cleanup mismatch is observed, not corrected. |

## 5. OAuth profile-code completion

Source: [profile-code page](../auth/update_profile_code.php).

| Field | Behavior |
| --- | --- |
| Entry/roles | `auth/update_profile_code.php`; any signed-in user. |
| Inputs/validation | POST sanitized `new_code` must be nonempty; checks duplicate code in current role's profile table excluding this user. No server pattern/length check. |
| Rules/reads | Reads profile code by session role/user ID. Only a code beginning `OAUTH_` sets `needsUpdate`; otherwise redirects to role dashboard/home before POST handling. |
| DB writes | Updates corresponding `ma_sinh_vien`, `ma_giang_vien` or `ma_lanh_dao` by `nguoi_dung_id`. |
| Session | Reads identity/role; no explicit identity update. |
| Redirect/output | Renders form/errors; success outputs a JavaScript redirect to `../<role>/dashboard.php` after 2 seconds. |
| Uncertain | Missing profile follows no-update redirect rather than creation. Meaning/format of valid institutional codes is not established; source does not prevent another `OAUTH_` code. |

## 6. Shared profile and password settings

Sources: [profile](../profile.php), [profile editing](../edit-profile.php), [password page](../change_password.php), [settings](../settings.php), [user model](../models/NguoiDungModel.php).

| Field | Behavior |
| --- | --- |
| Entry/roles | `profile.php`, `edit-profile.php`, `change_password.php`, `settings.php`; any signed-in user. |
| Inputs | Edit POST name/phone, G code/department/specialism, S class/cohort/major, L position/department; GET `mode` used in redirect. Password POST `old_password`, `new_password`, `confirm_password`; settings additionally requires presence of `change_password` to enter its password branch. |
| Validation/rules | Edit requires only nonempty trimmed name in server handler; no duplicate G-code check there. Password requires all fields, minimum 6, matching confirmation; model accepts old hashed, MD5 or plaintext password. Updates use current session user's account, with profile branch selected from database role. |
| DB reads | Full user joined to role and applicable profile; password model reads current account. |
| DB writes | Edit writes `nguoi_dung.ho_ten`, then role profile fields when present; G code can change here, S code is not in this update branch. Password writes Bcrypt `mat_khau`. No multi-write transaction in edit. |
| Session | Edit changes `ho_ten`, optional nested `user.ho_ten`, writes/consumes `success_message`; password pages do not rotate identity/session on success. |
| Redirect/output | Profile renders data; edit sends direct `edit-profile.php?mode=<encoded mode>`, then renders new data/message. Password/settings render result inline; no success redirect. Shared header/footer. |
| Uncertain | Schema-level identifier constraints and partial-edit recovery are unverified. GET `mode` is not evidence of changing authorization. |

## 7. Student browsing, dashboard and topic details

Sources: [dashboard](../sinh_vien/dashboard.php), [list](../sinh_vien/danh_sach_de_tai.php), [detail](../sinh_vien/chi_tiet_de_tai.php), [topic model](../models/DeTaiModel.php), [student model](../models/SinhVienModel.php).

| Field | Behavior |
| --- | --- |
| Entry/roles | `sinh_vien/dashboard.php`, `danh_sach_de_tai.php`, `chi_tiet_de_tai.php`; S guard. |
| Inputs/validation | List GET `loai` defaults/falls back to `co_so_nganh`, allows `chuyen_nganh`; optional sanitized `search`. Detail integer GET `id` must be nonzero and resolve to a joined topic row. |
| Rules | Student eligibility flags count approved registrations. Overall complete requires at least one approved CSN AND one approved CN; per-track flag requires at least one approved registration for that track. Listing model returns only approved topics with counter below capacity. Search matches title, description or lecturer name; newest first. Detail lookup does not require topic `da_duyet` in its SQL. |
| DB reads | Student profile, own joined registrations, topic/lecturer/user joins, per-track approved-registration counts. Dashboard counts available topics separately by track. Detail checks any existing registration for student/topic. |
| DB writes | None in these server handlers. |
| Session | Reads shared identity/role; invalid detail sets error flash. |
| Redirect/output | Invalid/missing detail redirects to `sinh_vien/danh_sach_de_tai.php`. Renders dashboard/list/detail and availability/eligibility actions with shared header. List's full-topic grouping does not make full rows available because its model already excludes them. |
| Uncertain | Missing student-profile behavior is not explicitly handled in these paths; runtime notices/data consequences are unverified. UI restrictions must not be substituted for registration-handler validation. |

## 8. Student registration

Sources: [registration action](../sinh_vien/dang_ky_de_tai.php), [registration model](../models/DangKyDeTaiModel.php), [student model](../models/SinhVienModel.php), [topic model](../models/DeTaiModel.php), [notifications](../models/ThongBaoModel.php).

| Field | Behavior |
| --- | --- |
| Entry/roles | `sinh_vien/dang_ky_de_tai.php`; S guard. Action reads GET `id`, with no POST-only requirement. |
| Validation | Nonzero ID; topic exists and is `da_duyet`; student has not completed both approved tracks. Reads lecturer per-track `gioi_han_sv_csn`/`gioi_han_sv_cn`, default 10, and rejects when distinct approved students for that lecturer/track reach limit. Model rejects any existing student/topic registration, missing/unapproved/full topic. |
| Rules | Handler does not call `daDuDeTaiTheoHe` for the requested track. Having one approved track alone is not the same as failing its combined-track check. Model inserts registration directly as `da_duyet`, despite success text asking student to await approval. |
| DB reads | Student profile/approved totals; topic; lecturer profile; distinct approved student count joined through topics; model duplicate and topic lookup. |
| DB writes | In one model transaction: insert `dang_ky_de_tai` with student/topic/status, increment `de_tai.so_luong_da_dang_ky`. After commit, handler inserts lecturer notification in `thong_bao`. Notification is outside that transaction. |
| Session | Reads identity/role/name; writes success/error flash. |
| Redirect/output | Successful registration redirects `sinh_vien/de_tai_cua_toi.php`. Invalid topic/capacity/model failure redirects student list; completed both tracks redirects own topics. No action-page view. |
| Uncertain | Concurrent capacity enforcement and database defaults/constraints are unverified; code does not establish row locking here. Notification failure does not share registration rollback. Intended pending-vs-approved rule is unknown. |

## 9. Student own-topics page and rejected-registration deletion

Sources: [own topics](../sinh_vien/de_tai_cua_toi.php), [delete action](../sinh_vien/xoa_dang_ky.php).

| Field | Behavior |
| --- | --- |
| Entry/roles | Both under `sinh_vien/`, S guard. Own-topics is a page load; delete requires POST. |
| Inputs/validation | Own-topics uses current student, no action input. Delete POST `dang_ky_id` must be truthy; finds row matching ID AND own student ID, requires `tu_choi`. |
| Rules/reads | Own-topics reads joined own registrations; for each approved registration it rejects all pending own registrations in the same track, then re-reads list. Delete reads own profile and registration. |
| DB writes | Own-topics updates pending rows to `tu_choi` with generated reason on page load. Delete removes the rejected row. Neither branch adjusts topic counters or generates workflow notifications. |
| Session | Reads user/role; own-topics uses shared header. Delete uses no result flash. |
| Redirect/output | Own-topics renders status cards/actions. Delete returns JSON `success`/`message`; guard redirects happen before JSON header, so unauthorized requests are not guaranteed JSON. |
| Uncertain | Auto-rejection counter policy and intended cancellation of approved/pending registrations are unknown. Do not describe this endpoint as unrestricted cancellation or decrement counts by assumption. |

## 10. Lecturer topic creation, editing, submission and deletion

Sources: [create](../giang_vien/tao_de_tai.php), [edit](../giang_vien/sua_de_tai.php), [submit](../giang_vien/gui_duyet.php), [delete](../giang_vien/xoa_de_tai.php), [topic model](../models/DeTaiModel.php).

| Field | Behavior |
| --- | --- |
| Entry/roles | Four `giang_vien/` endpoints explicitly allow G or L. They resolve the current user's lecturer profile; creation explicitly rejects an absent one, other paths dereference it during ownership checks. |
| Inputs | Create GET `loai`; POST title/description, `chuyen_nganh` array/string, technology/student requirements/notes, `action`. Edit GET `id`; POST title/description/`he_dao_tao`, major/technology/requirements/notes/`action`. Submit GET `id` OR POST `bulk_approval`, `selected_topics[]`, `loai`. Delete GET `id`. |
| Validation | Create requires allowed track and nonempty title/description. Model rejects same lecturer/title duplication across tracks/statuses and creation when existing lecturer/track topic count is >=100. Edit requires own topic, `nhap` or `tu_choi`, and nonempty title/description/track; no explicit track allowlist or duplicate-title check there. Submit requires own topic in `nhap`; bulk validates each individually. Delete requires own draft/rejected topic with registration counter <=0. |
| Rules | Create sets one student slot, draft status, title copied to `ten_de_tai`, current year/year+1 and semester `HK2` for Jan-Jun else `HK1`; GET track takes precedence. Edit retains slot count, clears rejection reason, default `save_submit` sets pending; any other action sets draft. Submission changes draft to pending. Page comments about unlimited creation do not override the model's 100-per-track check. |
| DB reads | Own lecturer profile/topic; creation duplicate/count checks; all leadership profiles when notifying pending submission. |
| DB writes | Create inserts topic. Edit updates supplied fields/status/reason; pending edit and submission insert notifications for every leadership profile. Submit updates state only, not approval history. Delete deletes topic only. Bulk submit can partially succeed; no transaction wraps topic updates with notifications. |
| Session | Reads identity/role/name; sets success/error, sometimes warning. |
| Redirect/output | Create success: `giang_vien/tao_de_tai.php?loai=...` for `save_continue`, otherwise list by track. Invalid track calls bare `redirect('chon_loai_de_tai.php')`. Edit/submit/delete generally redirect to role list preserving track; failures can render create/edit error or flash and redirect. Bulk submission reports counts/errors. |
| Uncertain | Missing-profile dereferences, schema deletion effects and duplicate constraints remain unverified. `taoNhieuDeTai()` separately imposes 10 topics and is not called by these endpoints; no inspected call site establishes a bulk-creation UI. Do not merge that unused method's limits into this flow. |

## 11. Lecturer lists, dashboards and registration-review presentation

Sources: [topic list](../giang_vien/danh_sach_de_tai.php), [dashboard](../giang_vien/dashboard.php), [topic detail](../giang_vien/chi_tiet_de_tai.php), [approved students](../giang_vien/danh_sach_sinh_vien.php), [pending review](../giang_vien/duyet_sinh_vien.php), [leadership pending page](../lanh_dao/sinh_vien_cho_duyet.php), [registration model](../models/DangKyDeTaiModel.php).

| Field | Behavior |
| --- | --- |
| Entry/roles | All listed pages, including `lanh_dao/sinh_vien_cho_duyet.php`, use G/L checks. Dashboard/list/approved/pending pages explicitly require own lecturer profile. Topic detail requires own topic. |
| Inputs/validation | Track `loai` defaults/falls back to CSN on lists/review; topic list optional sanitized `trang_thai`; detail integer `id`. Pending view supports integer `xem`, substring filters `search_mssv`, `search_ten`, `search_lop`. |
| Rules | Topic list scopes to own lecturer and track/status, orders newest first; counts tracks without status filter. Approved students scope to own lecturer and `da_duyet`, then split tracks. Pending page groups own pending registrations by topic; approved-student/track pairs across all lecturers affect eligibility counts/presentation. Dashboard reads statistics/recent topics/pending students. |
| DB reads | User/lecturer/topics and registration/student/user joins; approved student/track pairs. Leadership pending page calls `getDanhSachDangKyAll` instead of own-lecturer query, while still requiring own lecturer profile. Its filtered SQL appends `AND` to the final join condition, not a separate WHERE. |
| DB writes | No explicit writes in these page handlers. |
| Session | Identity/role; missing profile or invalid ownership sets error. Shared header adds notification reads. |
| Redirect/output | Missing profile calls `redirect('../index.php')`; invalid detail redirects lecturer list. Renders existing sidebar/cards/tables, grouped review UI and search/filter controls. |
| Uncertain | Intended cross-lecturer visibility on leadership pending page is unknown. Its helper's no-filter branch has no initialized `$params`; behavior of that unused branch is not inferred. UI eligibility badges do not establish server approval checks. |

## 12. Lecturer registration approval/rejection and bulk handlers

Sources: [single handler](../giang_vien/xu_ly_duyet.php), [bulk handler](../giang_vien/xu_ly_duyet_hang_loat.php), [registration model](../models/DangKyDeTaiModel.php), [notification model](../models/ThongBaoModel.php).

| Field | Behavior |
| --- | --- |
| Entry/roles | Both `giang_vien/` handlers allow G/L. Bulk explicitly requires own lecturer profile; single handler does not resolve/check lecturer ownership. |
| Inputs/validation | Single approve: GET `action=duyet`, `id`; reject: POST `action=tu_choi`, `dang_ky_id`, nonempty sanitized `ly_do`. Redirect context `loai`, `xem`, `redirect`. Model checks registration exists; no prior-state, per-track eligibility, topic capacity or owner check in these model methods. |
| Rules/DB reads | Read registration; success handler reads associated topic for student notification. Approving an already approved row is not blocked by the inspected code. |
| DB writes | Model approval transaction sets `da_duyet`, timestamp and `nguoi_duyet_id`, increments topic counter again. Rejection transaction sets `tu_choi` and reason, without decrementing counter. After model commit, handler inserts student notification. |
| Session | Identity/role/user ID; success/error flashes. |
| Redirect/output | Single success/failure redirects to `giang_vien/dashboard.php` if requested, otherwise pending-review page with track and optional `xem`; empty rejection reason returns dashboard or track view without `xem`. Unknown action returns pending-review page. |
| Bulk source branches | POST `duyet_hang_loat`/`tu_choi_hang_loat` parses comma-separated `dang_ky_ids` into integers; requires nonempty array and rejection reason when applicable. `duyet_tu_dong` requires either `duyet_csn` or `duyet_cn` equal to `'1'`. Calls `duyetHangLoat`, `tuChoiHangLoat`, `duyetTuDongThongMinh` respectively, then would flash counts and call bare `redirect('duyet_sinh_vien.php')`. |
| Uncertain/source gap | Those three called methods are absent from `DangKyDeTaiModel` and `BaseModel`; no alternate definitions were found. Their intended rules/writes/counts are unknown. Do not document a successful bulk/automatic approval algorithm. Single-action ownership policy and repeated-approval counter behavior need verification before migration. |

## 13. Lecturer registration deletion variants

Sources: [rejected-row handler](../giang_vien/xu_ly_xoa_dang_ky.php), [same-track handler](../giang_vien/xoa_dang_ky_gv.php).

| Field | Rejected-row handler | Same-track handler |
| --- | --- | --- |
| Entry/roles | `giang_vien/xu_ly_xoa_dang_ky.php`, G/L | `giang_vien/xoa_dang_ky_gv.php`, G/L |
| Inputs | GET integer `id`, `de_tai_id` | GET `id`, `loai` default CSN, optional `xem` |
| Validation/rules | Both IDs nonzero; registration exists; supplied topic belongs to current lecturer; registration status must be rejected. Does not compare registration's topic ID with supplied topic ID. | Joins registration to its actual topic and scopes to own lecturer. Counts student's approved registrations in same track and requires count >0; query does not exclude the row being deleted and no target-status restriction is present. |
| DB reads/writes | Profiles, registration, supplied topic; deletes registration only | Profiles, registration/topic and approved same-track count; deletes registration only |
| Session | Identity/role and result flashes | Identity/role and result flashes |
| Redirect/output | Topic detail with supplied `de_tai_id`, or own-topic list on guard failure | Pending-review view preserving track/optional `xem`; exception becomes error flash |
| Uncertain | Intended pairing/ownership policy is unknown. No explicit counter adjustment or notification. | Count can include an approved target itself. Intended rule and counter reconciliation are unknown; no explicit counter adjustment or notification. |

## 14. Leadership topic approval/rejection

Sources: [review page](../lanh_dao/duyet_de_tai.php), [approved page](../lanh_dao/de_tai_da_duyet.php), [handler](../lanh_dao/xu_ly_duyet.php), [topic model](../models/DeTaiModel.php), [notifications](../models/ThongBaoModel.php).

| Field | Behavior |
| --- | --- |
| Entry/roles | Three `lanh_dao/` endpoints use L guard. |
| Inputs | Pages: GET allowed `loai`, optional integer `giang_vien`, `id`. Handler: GET `action=duyet`/`id` or `duyet_hang_loat`/comma-separated `ids`; POST `action=tu_choi`, scalar/comma-separated `de_tai_id`, `ly_do`; GET/POST redirect context `loai`, `redirect`, `gv_id`. |
| Validation/rules | Page track defaults/falls back to CSN, filters model results by lecturer/detail. Rejection reason required. Bulk skips nonpositive IDs. Model approval/rejection does not fetch current topic state or enforce pending-only transitions; recorded old state is hardcoded `cho_duyet`. |
| DB reads | Pending or approved topics joined to lecturer/users (oldest first), target topic/info for notifications. |
| DB writes | Per-topic transaction: update topic to `da_duyet`, or `tu_choi` plus reason; insert `lich_su_duyet_de_tai` with approver/old/new/reason. Approval does not clear an existing rejection reason. After commit insert lecturer notification. Bulk processes independently, allowing partial success. |
| Session | Reads identity/role/user ID; writes success/error/warning counts/messages. |
| Redirect/output | `lanh_dao/duyet_de_tai.php?loai=...` normally; single approve/reject supports requested dashboard target with `gv_id` and `loai`; bulk approve always review page. Unknown action returns review page. Pages render grouped topics, details and action controls. |
| Uncertain | Missing target/foreign-key effects and notifications after committed update are unverified. A pending-only UI is not evidence the handler/model enforces the same rule. |

## 15. Leadership examiner assignments

Sources: [assignment page](../lanh_dao/danh_sach_phan_cong.php), [simple handler](../lanh_dao/xu_ly_phan_cong.php), [dedicated handler](../lanh_dao/xu_ly_phan_cong_cham.php), [registration model](../models/DangKyDeTaiModel.php), [assignment JavaScript](../lanh_dao/js_phan_cong_cham.js).

| Field | Behavior |
| --- | --- |
| Entry/roles | Page and both handlers use L guard. Page GET `loai` defaults/falls back to CSN; optional integer `xem_gv` selects a supervisor detail view from loaded statistics. |
| Reads/presentation | Model lists approved registrations joined to student/topic/supervisor and groups/counts by track. Lecturer statistics sorted by name. Rendering separately recovers registration ID/examiner using student code and topic-title subqueries, then reads examiner/user; query chooses first matching title/registration. |
| Simple action | POST fields `action=phan_cong`, integer `dang_ky_id`, `giang_vien_id`; nonzero registration ID required, but no explicit method check/existence checks. Empty examiner sets null; otherwise updates `giang_vien_cham_id`. Does not update assignment date. Returns JSON `success`/`message` for statement result/error/invalid action. |
| Dedicated action | POST-only; positive `dang_ky_id`; integer `giang_vien_cham_id` default 0. Reads registration and requires existence; 0 clears examiner/date; otherwise reads lecturer and requires existence, updates examiner and `ngay_phan_cong_cham=NOW()`. |
| Rules | Neither handler checks approved status, whether examiner equals supervisor, or an examiner workload limit. Dedicated action does not require `action=phan_cong`. |
| Session | Both read login/role. No workflow session writes beyond generic unauthorized error; no assignment notification. |
| Redirect/output | Page renders tables and examiner-selection UI; its inline `saveGiangVienCham()` posts to the simple handler with `action=phan_cong`. Separate `js_phan_cong_cham.js` posts to the dedicated handler with its distinct field names, and updates cells on success. Dedicated handler returns helper JSON including `data:null` and HTTP 400 for `jsonError`; simple handler has its own JSON shape/status defaults. Guards can redirect before JSON headers. |
| Uncertain | Ambiguous duplicate titles can affect rendered IDs; deployed data was not inspected. A standalone client script's presence does not establish that a particular page loads it. Preserve observed call sites and distinct field/date/error contracts. |

## 16. Leadership user list, account creation and imports

Sources: [accounts page](../lanh_dao/quan_ly_nguoi_dung.php), [create handler](../lanh_dao/xu_ly_tao_nguoi_dung.php), [student import](../auth/import_sinh_vien.php), [lecturer import](../auth/import_giang_vien.php).

| Field | Behavior |
| --- | --- |
| Entry/roles | Leadership list/create use L guard. Both `auth/import_*.php` pages have no login/role guard. Imports run on POST with nonempty uploaded `file`; create runs on POST, otherwise returns list. |
| List inputs/reads/output | Reads all users joined to roles and left-joined role codes, newest ID first; role counts and all roles for form; consumes error/success/warning. Renders list/create tabs, client search/role filters, import links. No account-delete/lock mutation is established by this page handler. |
| Creation inputs/validation | Role, name/email/password/confirmation and role extra fields like registration; S phone is `so_dien_thoai_sv`, L department/phone are `khoa_ld`/`so_dien_thoai_ld`. Requires common fields, valid email, minimum password, confirmation, unused email and recognized-role code; resolves role row before insert. |
| Creation rules/writes | Directly inserts active user with Bcrypt and role profile, no OTP and no enclosing user/profile transaction. Result/error flashes; redirects `lanh_dao/quan_ly_nguoi_dung.php`. |
| Import inputs | Upload extension csv/xlsx/xls; CSV header names map fields. S required code/name/email, optional class/cohort/major/password. G required code/name/email, optional department/specialism/degree/phone/password/`vai_tro` (default G). Missing password key defaults to institutional code; an explicitly empty password value is not the same as missing key. |
| Import validation | Upload success and extension allowlist; required row values; email/code duplicate queries. No explicit email-format or password-length check. G resolves supplied/default role and skips unknown role, while still always inserting a lecturer profile. |
| Import reads/writes | Read users/profiles/roles. Per accepted row, transaction inserts active `nguoi_dung` with `PASSWORD_DEFAULT` hash and corresponding S or G profile, commits and counts success. Duplicate/invalid rows continue; caught errors call rollback and collect errors. File-level import is not one transaction. |
| Session/redirect/output | Imports consume error/success flashes; results are local variables rendered on same page, no post-success redirect. Parsing reads temporary upload; xlsx/xls both use ZIP/XML first-sheet parser. Forms display counts/row errors. |
| Uncertain | Binary `.xls` support is not established by a ZIP/XML parser. Malformed CSV column counts, unreadable workbook and errors before transaction may escape row handling; do not promise graceful failure. Intended unauthenticated imports and G import role/profile mismatch are unknown. |

## 17. Leadership settings and menu links

Sources: [settings page](../lanh_dao/cai_dat_thong_so.php), [settings handler](../lanh_dao/xu_ly_cai_dat.php), [setting model](../models/CaiDatModel.php), [menu editor](../lanh_dao/cau_hinh_menu.php), [menu helper](../helpers/functions.php).

| Field | Behavior |
| --- | --- |
| Entry/roles | All three leadership pages/actions use L guard. `xu_ly_cai_dat.php` requires POST; default action is `update_setting`. Menu editor handles POST on same page. |
| Reads | Settings page reads `cai_dat`, lecturer lists/capacity data. Handler reads existing setting rows and relevant lecturer/topic counts. Menu reads `config/links_config.json`; shared helper merges configured links into defaults. |
| Plan actions | `update_ke_hoach`: trimmed `so_ke_hoach`, `ngay_ke_hoach`; CN variant uses `_cn` fields/keys. Allows empty values, no date-format check; updates existing or inserts missing `cai_dat` rows with `updated_at`. Two writes are not one transaction. |
| Single capacity | `update_single_limit`: `giang_vien_id`, `loai` in csn/cn, numeric `so_de_tai` 1..100; casts number to int and updates lecturer `gioi_han_sv_csn`/`gioi_han_sv_cn`. Reads topic count and notifies lecturer with topic-limit wording, warning if topic count exceeds new value. No existence/affected-row rejection before reporting success. |
| Combined capacity | `update_giang_vien_limit`: truthy lecturer ID and numeric `so_de_tai_csn`, `so_de_tai_cn`, each 1..50. Updates/inserts `cai_dat` keys `gv_limit_csn_<id>` and `gv_limit_cn_<id>`; does not write lecturer capacity columns used by student registration. |
| Default setting | POST `key_name`, `value`; key allowlist: `so_de_tai_toi_da_gv`, `so_de_tai_toi_da_sv`, `cho_phep_dang_ky`, `nam_hoc_hien_tai`, `hoc_ky_hien_tai`. Counts must be numeric >=1 (no integer-only test); permission value must be 0/1. Model updates existing setting only; execution success is not proof a missing key was inserted. |
| Session/redirect/output | Plan/single-limit branches return JSON when `X-Requested-With` is XMLHttpRequest; normal results use session flashes except silent successful capacity updates, then redirect settings page with `#tab-ke-hoach` or `#tab-gioi-han`. Combined/default paths use redirects/flashes. Menu POST writes JSON for `ket_qua_thi`, `bieu_mau_sinh_vien`, `bieu_mau_giang_vien` (missing fields become empty), reports result inline without redirect. |
| Validation/menu writes | No server URL validation in menu handler; file write replaces config with its three-field object. No DB write for menu. |
| Uncertain | Effective intended topic/student limits differ between storage paths, messages and model enforcement. Search found `cho_phep_dang_ky` in the settings handler but no registration read of that key; do not assume disabling it blocks registration. File permissions, date parsing and missing-key behavior require runtime verification. |

## 18. Personal notifications and JSON API

Sources: [notification page](../thong_bao.php), [API](../api/notifications.php), [model](../models/ThongBaoModel.php), [header](../includes/header.php).

| Field | Behavior |
| --- | --- |
| Entry/roles | `thong_bao.php`: any signed-in user. `api/notifications.php`: explicit login check, not role restriction; no method/action dispatch. |
| Inputs/validation | Page GET `action=mark_read`, integer `id`; or `mark_all_read`; GET `filter=unread` versus any other value. Single mark requires row's `nguoi_nhan_id` equal current user. |
| Rules/reads | Page lists current recipient's rows newest first, optionally unread only, without list limit. Header reads unread count and five recent rows via model. Workflow notification helpers resolve recipient user ID from role profile IDs and insert title/body/type/link. |
| DB writes | Single mark sets `da_doc=1`; mark-all updates current user's unread rows. No API DB read/write: API currently returns empty notifications and unread count zero. |
| Session | Page/API read identity; mark-all sets success flash; header reads session user/role for navigation. |
| Redirect/output | Single mark redirects to stored link via BASE_URL-prefix helper if nonempty, otherwise `thong_bao.php`; unauthorized/not-found row returns page. Mark-all redirects page. API returns JSON success/list/count, or `success:false` and not-logged-in message, without an explicit 401 status. |
| Uncertain | Stored absolute/relative link navigation must follow actual helper behavior. Do not connect API to model or add mark-read support as part of MVC preservation. |

## 19. Public notice administration and quick date editor

Sources: [public-notice admin](../lanh_dao/quan_ly_thong_bao.php), [quick dates](../lanh_dao/sua_ngay_thong_bao.php), [model](../models/ThongBaoModel.php), [home](../index.php).

| Field | Behavior |
| --- | --- |
| Entry/roles | Admin requires L; quick-date endpoint has no login/role guard. Home is public with role-dashboard redirects for signed-in users. |
| Inputs | Admin POST `action=add/update/delete/toggle_status`, IDs, title/body/type/link/start/end/status. Date editor POST `action=update_dates/clear_dates/set_today`, `id`, date fields. |
| Validation/rules | Add requires nonempty trimmed title; update positive ID/nonempty title; delete/toggle positive ID. Empty dates become null. No server date ordering, status/type allowlist, URL validation or public-recipient check on mutation ID. Quick editor casts ID but does not require positive/existing ID or valid date range. |
| DB reads/writes | Admin reads public recipient-null rows via `getAllThongBaoCongKhai(50)`; add inserts recipient null/read=0/type `thong_bao`, update writes notice fields, delete deletes row, toggle reads row and flips mo/khoa. Quick editor updates dates for supplied ID (not constrained to public recipient), clears to null or sets today/+1 month; lists all public rows. |
| Session | Admin reads identity and uses shared header; quick editor only inherits bootstrap session. Inline message/error variables, not PRG flashes. |
| Redirect/output | Both editors render same page after POST. Home calls all-public lookup with limit 10 before role redirect; no date/status filter in that lookup. Database exceptions on home are logged and replaced by empty list. |
| Uncertain | The model also has active/date-filtered methods, but home/admin use the unfiltered one; intended publication policy is unknown. No automatic inference that closed/expired notices disappear from home. Missing-row writes and DB date acceptance are unverified. |

## 20. Project page, timeline and notice content

Sources: [admin editor](../lanh_dao/quan_ly_noi_dung_do_an.php), [public page](../thong_bao_do_an.php), [content model](../models/ThongBaoDoAnModel.php).

| Field | Behavior |
| --- | --- |
| Entry/roles | Admin editor requires L. Public page permits reads without login; POST editing occurs only when current session role is L. |
| Inputs | Admin POST `action`, `current_tab`, page title/subtitle/date badge/status/start/end; notice title/body; timeline ID/track/order/title/body/day/month/year/status and `yeu_cau[]`. Public POST `save_timeline`, `save_notice`, `save_page` with corresponding fields; timeline requirements passed as raw submitted value. GET admin `tab`, `message`, `error` affect presentation. |
| Validation/rules | Handlers largely take expected POST fields directly; no explicit server date/order/text/track validation or CSRF check in these branches. Admin joins nonempty requirement entries with `|`. Timeline add always inserts `mo`; toggle flips mo/khoa after lookup. |
| DB reads | First page-content row (LIMIT 1, no ordering); admin timelines include all states, sorted track/order; admin notice latest ID. Public timelines only `mo`, public notice first `mo` row; public also reads page activation. |
| DB writes | Admin updates/inserts page content and notice, inserts/updates/deletes/toggles timeline. `update_page` optionally also writes notice with page status. Public saves timeline or existing/fallback page/notice through same model. These writes have no shared transaction. |
| Session | Shared identity/role and header notification reads; result messages carried in query parameters for admin PRG, not session flashes. |
| Redirect/output | Admin redirects directly to `PHP_SELF?tab=...&message=...`/error after POST. Public re-renders after POST. Page inactive if missing content, status exactly `khoa`, before start or after end date; inactive renders lock message instead of timelines. Active shows track timelines/notice/countdowns and L editing controls. Countdown uses deadline 23:59:59 and expired/today/<=7/<=30/later display classes. |
| Uncertain | Multiple content/notice rows can make first-row selection ambiguous; absent notice dereference in public save path is not verified. Timeline/publication dates control this page, not proven enforcement of student registration deadlines. |

## 21. Reports and leadership dashboard

Sources: [CSV export](../giang_vien/xuat_bao_cao_duyet.php), [leadership reports](../lanh_dao/xuat_bao_cao.php), [leadership dashboard](../lanh_dao/dashboard.php), [leadership model](../models/LanhDaoModel.php), [registration model](../models/DangKyDeTaiModel.php).

| Field | Behavior |
| --- | --- |
| Entry/roles | Lecturer CSV allows G/L and requires own lecturer profile. Leadership report/dashboard use L guard. |
| Inputs/validation | CSV GET `filter=csn/cn`, otherwise all. Leadership report GET `loai` validated/default CSN; supervisor `giang_vien_id`, class `lop`, `mode` int default 2, `sort` default gvcham, `export=excel/pdf`; also reads `tu_ngay`, `den_ngay`, `search`. Dashboard has no request input in its server preparation. |
| Reads/rules | CSV lists own pending registrations, filters track, loads topic counters/capacities. Leadership report lists approved registrations in selected track, optional supervisor and class LIKE filter, ordered supervisor name/class/newest registration. Date values appear in export headings but do not add SQL predicates; submitted `search` has no server filtering use. UI has separate client table search. Settings supply plan number/date; reports perform additional examiner/name/degree queries. |
| Dashboard reads | Overall counts of lecturers/students/topics/pending/approved topics/approved registrations; oldest pending topics, per-supervisor registration statistics and current L profile. No explicit server write. |
| DB writes | None in inspected report/export/dashboard handlers. |
| Session | Identity/role; missing CSV lecturer profile sets error and calls `redirect('../index.php')`; shared header for ordinary pages. |
| Redirect/output | CSV sends timestamped `.csv` attachment, UTF-8 BOM, 14 columns including code/name/class/cohort/major/email/topic/track/counters/availability/date/note. Leadership excel sends `.xls` attachment containing HTML tables with BOM, not a native binary workbook. Mode 1 supervisor-only; mode 2 includes examiner and groups by requested supervisor/examiner (unassigned placeholder). `export=pdf` emits printable HTML with print controls/automatic browser print, not a server-generated PDF. Ordinary report renders tables/actions; dashboard cards/statistics. |
| Uncertain | Download rendering, print layout and browser/client sorting behavior were not tested. Displayed date headings do not prove date-filtered report data. No native PDF generator should be introduced under behavior preservation without a separate request. |

## 22. Public topic directory and lecturer details

Sources: [public topics](../danh_sach_de_tai_cong_khai.php), [lecturer details](../giang_vien_chi_tiet_v2.php), [faculty page](../khoa_cntt.php), [home](../index.php).

| Field | Behavior |
| --- | --- |
| Entry/roles | Public topic and lecturer-detail scripts have no login/role guard. Lecturer detail includes database configuration directly, not full bootstrap. Faculty page starts its own session. |
| Inputs/validation | Topic GET `he_dao_tao`, `search`, `giang_vien`, `sort`. Valid nonempty track adds filter; lecturer filter passed as parameter; sort switches among title asc/desc, remaining slots asc/desc, created asc/default desc. Lecturer detail GET required `email`, optional `filter` and `he_dao_tao` default all. Missing email renders error and exits. |
| Rules/reads | Public topic directory queries approved topics with capacity remaining, joins lecturer/users, loads lecturer dropdown; title/description/lecturer-name search. Lecturer detail resolves account/profile by email; if no account, case-insensitive faculty-file match gives display-only fallback with no topics, otherwise redirects faculty page. Existing lecturer profile lists approved topics, including full ones, and queries all topics separately for statistics. Non-all track adds filter; read `filter` does not change its approved-only topic SQL. |
| DB writes | None in these handlers. |
| Session | Public directory inherits bootstrap/shared header; lecturer detail does not initialize/read session identity for access. Home reads role and routes to dashboard after notice read. |
| Redirect/output | Directory renders filters/sorting/topic cards. Lecturer detail displays explicit connection error page on caught DB failure, missing-email error, faculty fallback or lecturer/topics/statistics; not-found email sends direct `Location: khoa_cntt.php`. |
| Uncertain | Faculty records/live profiles and unexpected topic status/track values are unverified. No claim that GET `filter` grants access to drafts. Static introduction/help/diagnostic content is inventoried but not fully behavior-mapped here. |

## Explicit uncertainties and preservation checkpoints

These are findings to verify before migrating an affected module, not fixes authorized by Phase 2.

| ID | Uncertain or conflicting behavior | Evidence / preservation implication |
| --- | --- | --- |
| U1 | Live schema/data and record integrity | Defaults, indexes, cascades, triggers, role/profile presence and date coercion were not queried. Do not invent schema dependencies or missing-profile business rules. |
| U2 | Redirect resolution | Helpers always prefix BASE_URL, while multiple auth/lecturer callers use bare names or `../`; direct headers coexist. Keep actual emitted target distinct from an intended destination. |
| U3 | Topic limit intent | Create model enforces 100/track; unused bulk-create model enforces 10; settings use different keys/columns; page comments say unlimited. Model path, not comment, defines observed single-create behavior. |
| U4 | Registration lifecycle/counters | Create writes approved and increments; approval increments again without pending check; rejection/deletion/own-page auto-rejection do not decrement. Source establishes these effects, not intended reconciliation. |
| U5 | Intended authorization | Single registration approval lacks owner validation; deletion variants differ; leadership pending page allows G/L; imports/date editor have no guard. Do not infer intended privileges from folder names. |
| U6 | Missing bulk model methods | Three bulk/automatic-review calls have no implementation in inspected model/inheritance. Successful writes/output cannot be mapped beyond the call boundary. |
| U7 | GitHub wiring/user fields | Visible include path does not load GitHub helpers/constants; callback expects unresolved role/profile fields. Runtime success depends on unverified external initialization/schema. |
| U8 | Provider outcomes | OAuth availability, role-selection writer, network responses and provider credentials were not exercised. Google/Microsoft/GitHub branches differ in active checks, account creation and session handling. |
| U9 | Recovery/OTP failure handling | Alternate token reset lacks model expiry/status check; OTP consumed before account creation; debug output may affect headers; mail delivery not verified. Preserve distinctions. |
| U10 | Publication/registration timing | Home uses unfiltered notices; project page has visibility dates; registration handler does not read global permission key. No source evidence for a unified registration-window rule. |
| U11 | Imports and reports | ZIP-based `.xls` parsing, malformed files, per-row rollback edge cases, browser print and spreadsheet interpretation remain untested. Format labels do not establish native XLS/PDF generation. |
| U12 | Assignment record selection | Render-time lookup uses student code/topic title and LIMIT 1 instead of model-returned registration ID; duplicate-title selection is data-dependent. |
| U13 | Runtime/error boundaries | Most success paths assume valid rows, dependencies and writes; mail/notification work often occurs after separate commits. No general atomicity, idempotence or uniform JSON/error status is established. |

## Phase 2 validation

All ten plan dimensions are covered for important modules: entry points, roles, inputs, validation, business rules, database reads/writes, session effects, redirects and output. Evidence includes endpoint preparation/action branches, called model methods, shared helpers, selected render-time queries and client/report output paths. Missing implementations and data/environment-dependent outcomes are explicitly labeled instead of filled in.

Verification is documentation-only: check source links, required coverage, whitespace, Phase 2-only plan update and final changed-file scope. Application source and the pre-existing architecture-document edits remain untouched. No runtime behavior verification is claimed, and Phase 3 or later work was not executed.
