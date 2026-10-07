        # Current Architecture

        Analysis date: 2026-10-08. Scope: Phase 1 of `.agent/mvc-refactor-plan.md`.

        This is a source-based description of the existing application, not a target design or a complete behavior map. No application requests, database operations, email deliveries, or OAuth flows were executed. Application source, schema, assets, and behavior were left unchanged.

        ## 1. Architecture summary

        The application is native PHP organized around directly accessible page and action scripts. It has a shared bootstrap, procedural helpers, reusable HTML includes, and PDO-backed model classes. It does not currently have a front controller, centralized router, separate controller/view directories, repositories, or a service layer.

        Individual scripts coordinate requests, enforce access checks, validate inputs, call models or execute SQL, redirect, and render HTML. Models combine persistence with some business rules. HTML pages contain inline CSS and JavaScript as well as shared assets. The README's MVC description should not be treated as evidence that these boundaries already exist.

        Typical request flow:

        ```text
        Browser -> directly addressed PHP script
                -> bootstrap.php
                -> config/config.php (constants and session)
                -> config/database.php (Database class)
                -> Google/Microsoft configuration
                -> helpers (including Composer-loaded PHPMailer)
                -> enforceHttps()
                -> explicit model includes
                -> page-specific authorization and request handling
                -> models and/or direct PDO SQL
                -> redirect, JSON, report output, or HTML with shared includes
        ```

        This flow has exceptions: `khoa_cntt.php` starts its own session, `auth/refresh_captcha.php` is a standalone session endpoint, and Google login uses `auth/oauth_config.php`. Some scripts start a session before loading the bootstrap.

        ## 2. Entry points and routing

        URLs correspond to physical `.php` files. All paths below are relative to the application base URL; local configuration uses `/WebsiteQuanLyDeTai/`, while detected InfinityFree deployments use `/`.

        | Area | Existing entry points | Responsibility |
        | --- | --- | --- |
        | Home and public content | `index.php`, `khoa_cntt.php`, `gioi_thieu_sinh_vien.php`, `gioi_thieu_giang_vien.php`, `gioi_thieu_lanh_dao.php`, `giang_vien_chi_tiet_v2.php`, `danh_sach_de_tai_cong_khai.php`, `thong_bao_do_an.php`, `info.php`, `help.php` | Landing pages, introductions, public topic/lecturer information, project notices, informational pages and host/PDO diagnostics (info.php) |
        | Shared account pages | `profile.php`, `edit-profile.php`, `settings.php`, `change_password.php`, `thong_bao.php` | Signed-in profile, account settings/password changes, personal notifications |
        | Authentication | `auth/login.php`, `login_ajax.php`, `login_sinh_vien.php`, `login_giang_vien.php`, `login_lanh_dao.php`, `logout.php`, `register.php`, `verify_otp.php`, `refresh_captcha.php` | HTML/JSON login, role login redirects, logout, registration and verification |
        | Password recovery | `auth/forgot_password.php`, `reset_password.php`, `dat-lai-mat-khau.php`, `new_reset.php` | Multiple separate recovery/reset implementations; `new_reset.php` contains diagnostic output |
        | External identity | `auth/google_login.php`, `google_callback.php`, `github_login.php`, `github_callback.php`, `microsoft_login.php`, `microsoft_callback.php`, `update_profile_code.php` | Provider redirects/callbacks and completion of role-specific identifiers |
        | Account imports | `auth/import_sinh_vien.php`, `auth/import_giang_vien.php` | CSV/Excel account import pages and handlers |
        | Student | `sinh_vien/dashboard.php`, `danh_sach_de_tai.php`, `chi_tiet_de_tai.php`, `dang_ky_de_tai.php`, `de_tai_cua_toi.php`, `xoa_dang_ky.php` | Browse, register for, inspect, and cancel topics |
        | Lecturer | `giang_vien/dashboard.php`, `chon_loai_de_tai.php`, `danh_sach_de_tai.php`, `tao_de_tai.php`, `sua_de_tai.php`, `chi_tiet_de_tai.php`, `gui_duyet.php`, `xoa_de_tai.php` | Topic creation, editing, submission, deletion and listing |
        | Lecturer registration/reports | `giang_vien/danh_sach_sinh_vien.php`, `duyet_sinh_vien.php`, `xu_ly_duyet.php`, `xu_ly_duyet_hang_loat.php`, `xu_ly_xoa_dang_ky.php`, `xoa_dang_ky_gv.php`, `xuat_bao_cao_duyet.php` | Registration review, removal and reporting |
        | Leadership review | `lanh_dao/dashboard.php`, `duyet_de_tai.php`, `de_tai_da_duyet.php`, `sinh_vien_cho_duyet.php`, `xu_ly_duyet.php` | Department-level review and statistics |
        | Leadership assignments/reports | `lanh_dao/danh_sach_phan_cong.php`, `xu_ly_phan_cong.php`, `xu_ly_phan_cong_cham.php`, `xuat_bao_cao.php` | Examiner assignments and reports |
        | Leadership administration | `lanh_dao/quan_ly_nguoi_dung.php`, `xu_ly_tao_nguoi_dung.php`, `cai_dat_thong_so.php`, `xu_ly_cai_dat.php`, `cau_hinh_menu.php` | Accounts, system parameters and menu links |
        | Leadership content | `lanh_dao/quan_ly_thong_bao.php`, `quan_ly_noi_dung_do_an.php`, `sua_ngay_thong_bao.php` | Notifications, project-page content and notice dates |
        | JSON API | `api/notifications.php` | Signed-in notification response; currently returns an empty list and zero unread count |

        Within each grouped row, filenames after the first share its directory. Includes, helpers, models and configuration are internal dependencies rather than intended UI entry points, even though they are inside the web directory.

        `index.php` fetches public notifications and redirects signed-in users to a dashboard according to session role. It is a home page, not a dispatcher for other scripts. `.htaccess` enables rewriting and contains file/directory conditions but no active `RewriteRule` dispatching to `index.php`; its HTTPS rewrite is commented out. No centralized route registry was found.

        Routing also occurs through links, form actions, JavaScript requests and `header('Location: ...')`. `helpers/functions.php::redirect()` prefixes `BASE_URL` and exits; direct `header()` calls coexist with it. Some callers pass bare filenames or `../` paths, so redirect destinations must later be mapped from actual call sites rather than normalized by assumption. Query parameters select actions and context, including `id`, `action`, `loai`, `role`, `context` and filters. Some writes are triggered by GET, such as student registration and notification read actions.

        ## 3. Database connection and access

        `config/database.php` defines a singleton `Database` that creates a MySQL PDO connection to configured database `qldt`. Configuration chooses local or InfinityFree settings by host/path detection. PDO uses `utf8mb4`, exception errors, associative fetches and native prepared statements. A failed non-root connection attempts a root connection; `getConnection()` checks an existing connection with `SELECT 1` and reconnects on recognized dropped-connection errors. Merely including this file defines the class; constructing models or explicitly requesting the singleton opens the connection.

        `models/BaseModel.php` provides `findById`, `findAll`, `findBy`, `findOneBy`, `insert`, `update`, `delete`, `count`, `query` and `queryOne`. Concrete models use SQL and these generic methods. Calling `query()` with SQL written in a page still leaves SQL ownership in that page. Many scripts also call `Database::getInstance()->getConnection()` and execute PDO directly.

        | Model | Tables referenced / architectural responsibility |
        | --- | --- |
        | `NguoiDungModel` | `nguoi_dung`, `vai_tro`, role profile tables; login, password changes/reset tokens, account creation and full user lookup |
        | `VaiTroModel` | `vai_tro`; role lookup |
        | `GiangVienModel`, `SinhVienModel`, `LanhDaoModel` | `giang_vien`, `sinh_vien`, `lanh_dao`; profile access, counts and joins with users/topics/registrations |
        | `DeTaiModel` | `de_tai`, `lich_su_duyet_de_tai`; topic rules, approval history and registration counters |
        | `DangKyDeTaiModel` | `dang_ky_de_tai` with topic/student/lecturer/user joins; registration, approval, assignment statistics |
        | `OTPModel` | `otp_verification`; OTP storage, verification and cleanup |
        | `ThongBaoModel` | `thong_bao` and role profiles; personal/public notices and workflow notification generation |
        | `ThongBaoDoAnModel` | `thong_bao_do_an_content`, `thong_bao_do_an_timeline`, `thong_bao_do_an_notice`; public page content, timeline and notice administration |
        | `CaiDatModel` | `cai_dat`; key/value settings and topic-limit configuration |

        These are source-observed table references, not a verified live schema or foreign-key inventory. No SQL schema dump or migration definitions were found in the inspected repository. Relationships observed in joins include `nguoi_dung.vai_tro_id -> vai_tro.id`, role profiles' `nguoi_dung_id -> nguoi_dung.id`, `de_tai.giang_vien_id -> giang_vien.id`, and registration references to student/topic IDs. Examiner assignments update `dang_ky_de_tai.giang_vien_cham_id`; the dedicated assignment handler also manages `ngay_phan_cong_cham`.

        Transactions are implemented inside some models, including registration creation and approval. Registration creation uses `DeTaiModel` on the singleton connection and updates topic counters. Page-level deletion/assignment/settings operations also access these same records. Their transaction boundaries and counter effects are dependencies to preserve, not independent CRUD operations.

        ## 4. Authentication

        `auth/login.php` validates its CSRF token, delegates credentials to `NguoiDungModel::login()`, optionally filters by role, regenerates the session ID, writes user identity/context, rotates CSRF and redirects by role. `auth/login_ajax.php` delegates to the same login model and returns JSON, but its session handling differs: it does not use the HTML login's regeneration, CSRF rotation or `login_time` initialization.

        The login model rejects missing users and non-`active` accounts. It accepts password hashes through `password_verify()` and supports legacy MD5/plaintext passwords; successful legacy or outdated hashes are upgraded to Bcrypt. It resolves `vai_tro` through `vai_tro` and resolves `profile_id` from the applicable role profile table.

        Google, GitHub and Microsoft have separate scripts/configuration and callback implementations. They exchange provider credentials, look up/create or update accounts and role profiles, write session fields and redirect. Their handling must not be assumed equivalent to local login. For example, Google callback contains role/profile creation logic; Microsoft login/callback use `oauth_state`, while callback cleanup unsets `microsoft_oauth_state`.

        Registration coordinates user/profile creation, `OTPModel` and email helpers; `verify_otp.php` uses `registration_email` and `dev_otp` session context. Recovery uses account reset-token fields and separate reset pages. `dat-lai-mat-khau.php` performs its own token lookup, whereas `reset_password.php` and `new_reset.php` call the model lookup. Do not merge these paths before their actual validation differences are mapped.

        `auth/logout.php` invokes `destroySecureSession()` to clear session values, expire the session cookie and destroy the session, then redirects home.

        ## 5. Session usage

        `config/config.php` normally starts the PHP session only when absent, with lifetime zero, path `/`, HTTPS-dependent `secure`, `httponly=true` and `samesite=Lax`. HTTPS detection considers the HTTPS flag, forwarded protocol and port. `BASE_URL` is built from the protocol, request host and environment-dependent path. Bootstrap invokes PHP-level HTTPS enforcement for hosts other than exactly `localhost` and `127.0.0.1`.

        | Session data | Consumers / purpose |
        | --- | --- |
        | `user_id`, `email`, `ho_ten`, `vai_tro`, `vai_tro_id`, `profile_id` | Login/callback writers; shared user helpers, navigation and role modules |
        | `login_time`, `login_method` | Timing helpers and provider-specific login context; initialization varies by login path |
        | `he_dao_tao` | Study-track context stored by HTML login from its context parameter |
        | `csrf_token` | Shared CSRF helpers and selected forms/handlers; enforcement is page-specific |
        | `error`, `success` | Flash messages; helper reads consume the value |
        | `captcha` | CAPTCHA refresh/validation context |
        | `registration_email`, `dev_otp` | Registration/OTP verification context |
        | `reset_token` | Alternate password-reset page context |
        | `oauth_state`, `github_oauth_state`, `oauth_selected_role` | Provider state/role context; provider implementations differ |

        This lists principal observed keys rather than an exhaustive per-endpoint read/write map. `SESSION_TIMEOUT` is configured as 3600 seconds. `checkSessionTimeout`, `refreshSessionTimeout`, `validateSession` and `createSecureSession` exist in the helper file, but no application calls to `validateSession()` or `createSecureSession()` were found. Bootstrap does not invoke timeout validation despite the helper comment suggesting it should. Defining these helpers does not establish centralized timeout enforcement.

        Standalone/early `session_start()` calls exist in `auth/refresh_captcha.php`, `auth/dat-lai-mat-khau.php`, Google login/callback and `khoa_cntt.php`; these paths must be checked individually before consolidating initialization.

        ## 6. Authorization and roles

        Role constants are `giang_vien`, `sinh_vien` and `lanh_dao`. Authorization is distributed across scripts:

        - `isLoggedIn()` tests a nonempty session `user_id`; `getCurrentUser()` returns session identity without a database refresh.
        - `hasRole()` compares session `vai_tro` strictly. `requireLogin()` sets an error and redirects to login. `requireRole()` calls it and redirects mismatched roles to `index.php`.
        - Student scripts use `requireRole(ROLE_SINH_VIEN)`. Most leadership administration/action scripts use `requireRole(ROLE_LANH_DAO)`.
        - Lecturer scripts such as `dashboard.php`, `tao_de_tai.php` and `xu_ly_duyet.php` explicitly allow either lecturer or leadership roles. Some then require an actual lecturer profile. Folder name alone does not determine permitted roles.
        - Shared account/notification pages and `auth/update_profile_code.php` use `requireLogin()`. `api/notifications.php` implements its own JSON login check.
        - Both import pages have no login/role guard. `lanh_dao/sua_ngay_thong_bao.php` also performs notice writes without a login/role guard. These are observed exceptions, not inferred intended policy.
        - Object-level checks occur separately from role checks: for example, `thong_bao.php` checks notification recipient ownership before marking one notification read. Role guards alone do not prove record ownership enforcement across other actions.

        Phase 1 does not correct or endorse these differences. Intended policy for exceptional endpoints and complete ownership rules must be established before an affected migration; do not silently add or remove permissions during refactoring.

        ## 7. Files mixing persistence, business logic and presentation

        | Files / examples | Existing mixed responsibilities |
        | --- | --- |
        | `includes/header.php` | Fetches unread counts and recent notifications through `ThongBaoModel`, derives current user/page state, renders navigation and markup. A shared presentation include currently queries the database. |
        | `giang_vien/dashboard.php`, `duyet_sinh_vien.php`, `chi_tiet_de_tai.php` | Access checks, profile/model calls, page-authored SQL, filtering/counts, HTML/CSS/JavaScript |
        | `sinh_vien/chi_tiet_de_tai.php`, `de_tai_cua_toi.php` | User/profile/topic handling, raw SQL or direct PDO, registration presentation |
        | `lanh_dao/quan_ly_nguoi_dung.php`, `sinh_vien_cho_duyet.php`, `sua_ngay_thong_bao.php` | Request handling and SQL together with account/review/date-editing HTML |
        | `lanh_dao/cai_dat_thong_so.php`, `danh_sach_phan_cong.php`, `xuat_bao_cao.php` | Database access both during preparation and within rendering sections, settings/assignment/report logic, presentation |
        | `auth/import_sinh_vien.php`, `import_giang_vien.php` | Upload validation, CSV/ZIP/XML parsing, account/profile SQL writes, result handling and import forms |
        | `auth/update_profile_code.php`, `dat-lai-mat-khau.php` | Identity/token validation, page-authored SQL and updates, redirects and forms |
        | `thong_bao.php` | Notification SQL, read actions/recipient checks, filtering and rendering |
        | `danh_sach_de_tai_cong_khai.php`, `giang_vien_chi_tiet_v2.php` | Public data queries and HTML presentation |

        Other pages mix coordination and business rules with HTML even when they use only model methods, such as `auth/login.php`, `register.php`, `giang_vien/tao_de_tai.php`, `settings.php` and `edit-profile.php`. Action-only scripts also contain SQL outside models, including student registration/cancellation, lecturer registration removal, leadership review/settings/assignment handlers and OAuth callbacks. These are separate boundary violations from SQL embedded in rendered pages.

        Presentation is distributed among root/role pages, `includes/header.php`, `includes/footer.php`, role-specific sidebar/modal includes, `assets/css/style.css`, `assets/js/main.js`, leadership assets and `lanh_dao/js_phan_cong_cham.js`. Inline styles/scripts and relative asset URLs are substantial parts of the current UI contract.

        ## 8. Dependencies between modules

        | Module | Dependencies and shared effects |
        | --- | --- |
        | All bootstrap-based endpoints | Configuration/session constants, HTTPS helper, explicit model loading and PHPMailer Composer autoload; even non-email pages depend on the bootstrap's mail include |
        | Authentication/accounts | User/role/profile tables; OTP persistence and SMTP; provider configurations and HTTP calls; role dashboards depend on session identity/profile resolution |
        | Lecturer topic management | Lecturer profile, `DeTaiModel`, `CaiDatModel`; topic state/counters feed student lists, leadership review and reports |
        | Student registration | Student eligibility methods, approved topics, lecturer capacity fields, registration transactions, topic counters and lecturer notifications |
        | Lecturer registration approval/removal | Registration/topic/student records and recipient notifications; shared records also drive leadership assignment and reports |
        | Leadership topic review | Topic states and approval history; publishes availability for student/public listings and sends lecturer notifications |
        | Leadership examiner assignments | Registration records and lecturer IDs; shared assignment fields feed listings and reports; two distinct action scripts exist |
        | System parameters | `cai_dat` and direct profile updates in `xu_ly_cai_dat.php`; consumers include lecturer capacity and topic pages |
        | Personal/public notifications | `ThongBaoModel` ties topic and registration actions to recipient profiles; header and `thong_bao.php` read the same data; the JSON API remains a separate placeholder |
        | Project content/timeline | Leadership editors and public `thong_bao_do_an.php` share `ThongBaoDoAnModel`; general notices separately use `thong_bao` |
        | Menu configuration | `lanh_dao/cau_hinh_menu.php` writes `config/links_config.json`; `getMenuLinks()` reads it with defaults for navigation |
        | Dashboards/reports/public directory | Cross-module joins over accounts, profiles, topics and registrations; rely on current state meanings and assignment data |

        Important examples of split rule ownership: `sinh_vien/dang_ky_de_tai.php` checks student totals and lecturer capacity before calling the transactional registration model. The model independently checks topic status/capacity and updates counters. Its current insert writes `da_duyet`, even though its success message asks the student to await approval. `giang_vien/tao_de_tai.php` explicitly omits topic-limit enforcement despite limit-related settings/constants elsewhere. These source observations show why comments, labels and configurable values cannot replace inspection of executed logic.

        ## 9. Runtime and external dependencies

        - PHP and Apache/XAMPP-style deployment; MySQL access via PDO MySQL. No PHP framework or application class autoloader is declared; model loading is explicit.
        - Composer declares `phpmailer/phpmailer: ^6.8`; `helpers/phpmailer.php` requires `vendor/autoload.php`. Local vendor files and `composer.lock` exist but are ignored by Git.
        - SMTP email helpers support registration/recovery flows; OAuth scripts use provider HTTP calls, including cURL. Credentials reside in configuration files; their values are deliberately omitted here.
        - Import scripts use `ZipArchive` and SimpleXML for Excel-format parsing alongside CSV handling. Accepted filename extensions are not evidence that every Excel variant has equivalent support.
        - Frontend dependencies include CDN Bootstrap, Bootstrap Icons and Google Fonts, plus local styles, JavaScript and images. UI behavior also depends on inline scripts and role-specific assets.
        - Menu links persist in a JSON file rather than a database table. `UPLOAD_PATH` is configured, but configuration alone does not prove a particular upload workflow uses it.

        ## 10. Validation and limits

        Phase 1 tasks are covered above: entry points/routing (section 2), database access (3), authentication (4), sessions (5), authorization (6), mixed files (7), and major modules/dependencies (2 and 8).

        Validation consisted of reading repository instructions and the plan, inspecting tracked files and key implementations, and searching application PHP for guards, session operations, SQL, database access, includes and helper call sites. The final change scope is restricted to this document and the Phase 1 status in the migration plan. No automated application test suite was found in the inspected repository; runtime regression testing is outside this documentation-only phase.

        Unverified details remain explicit: the deployed Apache configuration, live database schema/constraints and data, provider/SMTP availability, and complete per-endpoint business/ownership rules. No schema changes, destructive operations or policy decisions were needed for this analysis. Later phases must inspect and verify the affected paths before migrating them; this document does not authorize migration or cleanup of apparently redundant endpoints.
