# Đánh giá dự án: Kiến trúc MVC, giao diện và bảo mật

> Đây là đánh giá ban đầu trước đợt vá. Phạm vi xác thực đã được xác nhận lại: chỉ tài khoản trường cấp, Lãnh đạo quản lý tài khoản, không mở đăng ký/OAuth/OTP. Kết quả hiện tại và việc còn lại xem [báo cáo bảo mật](../bao_cao/tien_do/05_ban_giao_bao_mat.md).

> Phạm vi: khoảng 110 file PHP (không tính `vendor/`). Kết quả lấy từ việc quét tự động toàn bộ code và đọc kỹ một số file tiêu biểu.

---

## 1. Kiến trúc: hiện tại chưa phải MVC

### Hiện trạng
- **Không có Controller.** Mỗi file `.php` trong `sinh_vien/`, `giang_vien/`, `lanh_dao/` tự làm hết: kiểm tra quyền, đọc request, gọi SQL, xử lý nghiệp vụ, rồi xuất HTML/CSS/JS.
- **SQL nằm rải rác ngoài Model.** Rất nhiều câu SQL viết thẳng trong trang giao diện, ví dụ:

| File | Số dòng | Số câu SQL trong file | Số khối `<style>`/`<script>` |
|---|---|---|---|
| `lanh_dao/quan_ly_noi_dung_do_an.php` | 1688 | 18 | 1 / 1 |
| `lanh_dao/xu_ly_cai_dat.php` | 347 | 20 | – |
| `lanh_dao/quan_ly_thong_bao.php` | 1546 | 12 | 1 / 1 |
| `lanh_dao/xuat_bao_cao.php` | 1283 | 13 | 4 / 3 |
| `danh_sach_de_tai_cong_khai.php` | 855 | 12 | 1 / 2 |
| `auth/google_callback.php` | 326 | 16 | – |
| `sinh_vien/dang_ky_de_tai.php` | 84 | SQL đếm chỗ còn trống viết thẳng trong trang | – |
| `lanh_dao/includes/modal_quan_ly_tai_khoan.php` | 259 | 5 (SQL ngay trong một *modal*) | 1 / 1 |

- **Model chỉ là lớp bọc SQL**, không chứa nghiệp vụ. Ví dụ, quy tắc "giới hạn số SV của giảng viên" lại nằm trong trang `dang_ky_de_tai.php`.
- **Nhiều file trùng hoặc rác:** `login_giang_vien.php`, `login_lanh_dao.php`, `login_sinh_vien.php`; `reset_password.php`, `new_reset.php`, `dat-lai-mat-khau.php` (3 trang reset mật khẩu); `giang_vien_chi_tiet_v2.php`; `info.php`; `api/notifications.php` (mới là TODO).
- **Copy-paste code:** `xu_ly_cai_dat.php` lặp đoạn "SELECT, nếu có thì UPDATE, không thì INSERT" và đoạn "nếu là AJAX thì trả JSON" hàng chục lần.
- `bootstrap.php` luôn nạp đủ 12 model cho mọi request (chưa có autoload).

### Đề xuất cấu trúc mới (MVC nhẹ, vẫn chạy được trên XAMPP/InfinityFree)

```text
/
├── public/                  # Document root DUY NHẤT được truy cập từ web
│   ├── index.php            # Front controller (router)
│   ├── .htaccess            # Rewrite mọi request về index.php
│   └── assets/ (css, js, img)
├── app/
│   ├── Core/                # Router, Controller, View, Database, Request, Session, Csrf, Auth
│   ├── Middleware/          # AuthMiddleware, RoleMiddleware, CsrfMiddleware
│   ├── Controllers/
│   │   ├── AuthController.php
│   │   ├── SinhVien/  (DeTaiController, DangKyController, DashboardController)
│   │   ├── GiangVien/ (DeTaiController, DuyetController, ...)
│   │   └── LanhDao/   (DuyetDeTaiController, NguoiDungController, CaiDatController, ...)
│   ├── Services/            # Nghiệp vụ: DangKyService (giới hạn SV, transaction), DuyetService, OtpService, MailService
│   ├── Models/              # Chỉ truy cập dữ liệu (Repository)
│   └── Views/
│       ├── layouts/   (main.php, auth.php, dashboard.php)
│       ├── partials/  (navbar, sidebar, flash, pagination, table, modal_confirm, badge)
│       ├── sinh_vien/ giang_vien/ lanh_dao/ auth/ public/
├── config/                  # Đọc từ .env, không ghi mật khẩu trong code
├── storage/ (logs, uploads) # Không truy cập được từ web
├── database/schema.sql      # Hiện chưa có
└── .env / .env.example
```

**Luồng xử lý một request:** `public/index.php` → Router → Middleware (đăng nhập, vai trò, CSRF) → Controller → Service/Model → `View::render('sinh_vien/de_tai/index', [...])` trong layout dùng chung.

---

## 2. Giao diện: chưa tái sử dụng được

- **22 file tự viết `<!DOCTYPE html>`**; chỉ khoảng 28 file include `includes/header.php`. Mỗi trang auth (`login`, `register`, `forgot_password`, `verify_otp`, `reset_password`, `new_reset`, `dat-lai-mat-khau`) tự viết lại toàn bộ khung và CSS nền.
- **Link CDN Bootstrap 5.3.0 bị nhúng 61 lần** ở nhiều nơi khác nhau.
- **CSS/JS viết thẳng trong từng trang:** `index.php` dài 3827 dòng, `lanh_dao/dashboard.php` có 4 khối `<style>`, `cau_hinh_menu.php` 1764 dòng.
- Ba trang `gioi_thieu_giang_vien.php`, `gioi_thieu_lanh_dao.php`, `gioi_thieu_sinh_vien.php` đều khoảng 708–745 dòng, gần như giống nhau.
- `getStatusBadge()` trả về HTML từ helper và không escape giá trị.

**Đề xuất:**
- 3 layout: `public`, `auth`, `dashboard` (sidebar tự đổi theo vai trò).
- Partials dùng lại: `flash`, `table`, `pagination`, `modal_confirm`, `form_field`, `status_badge`, `empty_state`.
- Gom CSS vào `assets/css/app.css` cộng file riêng cho từng module; JS vào `assets/js/*.js`; trang không chứa `<style>`.
- Helper `e($str)` để escape, dùng thống nhất `<?= e($x) ?>`.

---

## 3. Lỗi bảo mật (xếp theo mức độ)

### 🔴 Nghiêm trọng

| # | Lỗi | Vị trí | Hậu quả |
|---|---|---|---|
| S1 | **Ai cũng tự đăng ký được tài khoản Lãnh đạo (admin)** | [register.php](file:///e:/xampp/htdocs/auth/register.php#L76-L92), danh sách vai trò lấy từ `getAllVaiTro()` | Chiếm toàn quyền hệ thống |
| S2 | **Trang import SV/GV không kiểm tra đăng nhập** (comment ghi rõ "*không cần đăng nhập*") | [import_sinh_vien.php](file:///e:/xampp/htdocs/auth/import_sinh_vien.php#L1-L12), `import_giang_vien.php` | Người lạ tạo hàng loạt tài khoản |
| S3 | **`sua_ngay_thong_bao.php` không kiểm tra quyền** | [sua_ngay_thong_bao.php](file:///e:/xampp/htdocs/lanh_dao/sua_ngay_thong_bao.php#L6-L43) | Ai cũng sửa được thông báo |
| S4 | **Mật khẩu ghi thẳng trong code**: DB InfinityFree (2 bộ), App Password Gmail | `config/database.php`, `config/config.php`, [info.php](file:///e:/xampp/htdocs/info.php) | Lộ DB và email; `info.php` còn công khai cấu hình server |
| S5 | **Hiện mã OTP ra màn hình khi gửi mail lỗi, kể cả production** | [register.php](file:///e:/xampp/htdocs/auth/register.php#L130-L137) | Vô hiệu hóa xác minh email |
| S6 | **Token đặt lại mật khẩu không kiểm tra hạn** (trang này tự query, bỏ qua `reset_token_expiry`) | [dat-lai-mat-khau.php](file:///e:/xampp/htdocs/auth/dat-lai-mat-khau.php#L28-L43) | Link reset dùng được mãi |
| S7 | **Hầu như không có CSRF**: chỉ `login.php` và `github_callback` kiểm tra; `validateCSRF()` có viết nhưng không ai gọi | Toàn bộ form/xử lý của `giang_vien/`, `lanh_dao/`, `sinh_vien/` | Bị lừa bấm link là mất dữ liệu |
| S8 | **Thao tác thay đổi dữ liệu bằng GET**: xóa đề tài, duyệt, duyệt hàng loạt, xóa đăng ký, đăng ký đề tài | `giang_vien/xoa_de_tai.php`, `xu_ly_duyet.php`, `xu_ly_xoa_dang_ky.php`, `xoa_dang_ky_gv.php`, `lanh_dao/xu_ly_duyet.php`, `sinh_vien/dang_ky_de_tai.php` | Chỉ cần một thẻ `<img src=...>` là thực thi được |

### 🟠 Cao

| # | Lỗi | Vị trí |
|---|---|---|
| H1 | Đăng nhập không giới hạn số lần thử (dễ bị dò mật khẩu); captcha sinh bằng `rand()` | `auth/login.php`, `helpers/functions.php` |
| H2 | Chấp nhận mật khẩu **MD5 và plaintext** | [functions.php](file:///e:/xampp/htdocs/helpers/functions.php#L93-L102), `NguoiDungModel.php` dòng 53 và 136 |
| H3 | OTP sinh bằng `rand()` (không an toàn về mật mã) và chưa thấy giới hạn số lần nhập | [functions.php](file:///e:/xampp/htdocs/helpers/functions.php#L120-L122) |
| H4 | **XSS:** khoảng 116 chỗ `<?= $row['...'] ?>` không escape; `tao_de_tai.php` và `update_profile_code.php` in thẳng `$_POST` | Ví dụ `giang_vien/tao_de_tai.php` dòng 166–220 |
| H5 | Lỗi DB và exception hiển thị ra người dùng (`'Lỗi: ' . $e->getMessage()`), kèm `display_errors=1` khi không phải InfinityFree | Hầu hết các file xử lý |
| H6 | IDOR: `xoa_de_tai.php` cho Lãnh đạo vào nhưng lại so `giang_vien_id`; cần rà quyền sở hữu ở mọi trang `?id=` | `giang_vien/*.php` |
| H7 | Microsoft OAuth so `oauth_state` nhưng xóa `microsoft_oauth_state` (dùng chung state với Google) | `auth/microsoft_callback.php` dòng 10 và 115 |

### 🟡 Trung bình / thấp
- **Race condition khi đăng ký đề tài:** đếm chỗ trống rồi mới insert, không có transaction hay khóa dòng, nên có thể vượt giới hạn.
- `sanitize()` vừa `strip_tags` vừa `htmlspecialchars` **trước khi lưu DB**, nên dữ liệu bị escape hai lần và làm hỏng nội dung. Cách đúng: lưu dữ liệu gốc, chỉ escape khi xuất ra.
- `BaseModel::findBy/count/insert/update` ghép tên cột trực tiếp vào SQL; `findAll($orderBy)` cũng ghép thẳng. Nên có whitelist cột.
- `session_start()` bị gọi trùng (`dat-lai-mat-khau.php`). Timeout hiện là timeout theo lần hoạt động, nhưng `validateSession()` chưa được gọi trong bootstrap.
- `.htaccess`: các thư mục `config/`, `models/`, `helpers/`, `vendor/` vẫn truy cập trực tiếp được từ web. Header CSP và HSTS chưa có.
- Upload import: chỉ kiểm tra đuôi file, chưa kiểm tra MIME hay kích thước. XML đọc bằng `simplexml_load_string` nhưng chưa kiểm soát kích thước file.
- `generateCSRF()` có sẵn nhưng các form không dùng.

---

## 4. Lộ trình đề xuất

| Giai đoạn | Việc | Ước lượng |
|---|---|---|
| **0. Vá khẩn cấp** (không đổi cấu trúc) | Sửa S1–S8, H2, H3, H5, H7; xóa `info.php`; chuyển mật khẩu sang `.env` và **đổi ngay** mật khẩu DB/Gmail; chặn `config/`, `models/`... trong `.htaccess` | Nhỏ |
| **1. Nền tảng** | `app/Core` (Router, View, Auth, Csrf, Request), autoload PSR-4 qua Composer, `.env`, layout và partials, helper `e()` | Vừa |
| **2. Chuyển dần từng module** | Thứ tự: Auth → Sinh viên → Giảng viên → Lãnh đạo. Mỗi trang cũ thành Controller + Service + View; SQL chuyển vào Model | Lớn |
| **3. Hoàn thiện** | `database/schema.sql`, gộp CSS/JS, dọn file trùng, kiểm thử thủ công từng luồng | Vừa |
