# Kết quả kiểm thử đợt bảo mật

Ngày: 21/11/2026 (Asia/Saigon). Nhánh: `develop/maintenance-baseline`, bắt đầu từ commit `2692b3e`.

## Môi trường và phạm vi

PHP 8.2.12, MariaDB 10.4.32, PowerShell trên Windows; Edge headless và Node.js 24.11.1. Schema và seed giả lập được import mới vào `qldt_security_test`. Máy chủ PHP chỉ bind loopback cổng 8088; ZIP bật riêng cho tiến trình thử. Không gửi email, không gọi OAuth và không dùng dữ liệu người dùng thật để thử đăng nhập.

## Tổng hợp

- **112/112** ca trong lần chạy runner đầy đủ cuối cùng đạt: 103 ca HTTP/import/cạnh tranh/trình duyệt và 9 ca CLI Model/kết nối.
- Nhóm **9/9** ca CLI đạt gồm mật khẩu sau reset, bỏ plaintext, whitelist SQL, CSV/URL và reconnect DB; đã được chạy cùng runner cuối.
- **13/13** URL file nội bộ được Apache chặn HTTP 403.
- Bản dump dữ liệu gốc trước/sau giống nhau khi bỏ dòng thời điểm hoàn tất dump.
- Kiểm tra cú pháp PHP/JavaScript thực hiện riêng; kết quả được ghi trong bản bàn giao.
- Thử XLSX khi ZIP chưa bật trả hướng dẫn dùng CSV; sau khi bật ZIP riêng cho test, XLSX hợp lệ import được và XML có thực thể ngoài bị từ chối.

Không xem việc thiếu bảng OTP là lỗi cần bổ sung: chức năng đăng ký/OTP đã được đóng theo phạm vi xác nhận. Đăng ký đề tài tự duyệt là hành vi mong đợi đã được xác nhận, thay cho giả định chờ duyệt trong báo cáo bước 0.

## Chi tiết tích hợp

| Ca kiểm tra | Kết quả | Bằng chứng |
|---|---|---|
| Public / | Đạt | HTTP 200, PHP diagnostics=0 |
| Public /danh_sach_de_tai_cong_khai.php | Đạt | HTTP 200, PHP diagnostics=0 |
| Public /thong_bao.php | Đạt | HTTP 200, PHP diagnostics=0 |
| Public /thong_bao_do_an.php | Đạt | HTTP 200, PHP diagnostics=0 |
| Guest blocked from student dashboard | Đạt | /WebsiteQuanLyDeTai/auth/login.php |
| Login sv1@example.test | Đạt | /WebsiteQuanLyDeTai/sinh_vien/dashboard.php |
| Login sv2@example.test | Đạt | /WebsiteQuanLyDeTai/sinh_vien/dashboard.php |
| Login gv1@example.test | Đạt | /WebsiteQuanLyDeTai/giang_vien/dashboard.php |
| Login gv2@example.test | Đạt | /WebsiteQuanLyDeTai/giang_vien/dashboard.php |
| Login ld1@example.test | Đạt | /WebsiteQuanLyDeTai/lanh_dao/dashboard.php |
| Dashboard sinh_vien/dashboard.php | Đạt | HTTP 200, PHP diagnostics=0 |
| Dashboard giang_vien/dashboard.php | Đạt | HTTP 200, PHP diagnostics=0 |
| Dashboard lanh_dao/dashboard.php | Đạt | HTTP 200, PHP diagnostics=0 |
| Student blocked from leadership dashboard | Đạt | /WebsiteQuanLyDeTai/sinh_vien/dashboard.php |
| Lecturer creates draft | Đạt | Matching rows=1 |
| Lecturer edits own draft | Đạt | Checked persisted title |
| Lecturer submits topic | Đạt | Checked persisted status |
| Leadership approves topic | Đạt | Checked persisted status |
| Student registers topic | Đạt | Registration ID=2 |
| Registration is immediately approved (confirmed rule) | Đạt | da_duyet |
| Full topic blocks second student (sequential) | Đạt | Sequential check only; no concurrency claim |
| Student views own topics | Đạt | HTTP 200 |
| Student deletes rejected registration | Đạt | JSON success and row removed |
| Student cannot delete approved registration | Đạt | Existing rule permits deletion only after rejection |
| Other lecturer cannot approve registration | Đạt | Approver user ID=0 (4 is other lecturer) |
| Repeated approval preserves registration counter | Đạt | Stored counter=1; actual registrations=1 |
| Unused OTP endpoint disabled | Đạt | HTTP 404 |
| Logout clears access | Đạt | /WebsiteQuanLyDeTai/auth/login.php |
| Disabled register GET/POST | Đạt | GET=404, POST=404 |
| Disabled verify_otp GET/POST | Đạt | GET=404, POST=404 |
| Disabled google_login GET/POST | Đạt | GET=404, POST=404 |
| Disabled google_callback GET/POST | Đạt | GET=404, POST=404 |
| Disabled github_login GET/POST | Đạt | GET=404, POST=404 |
| Disabled github_callback GET/POST | Đạt | GET=404, POST=404 |
| Disabled microsoft_login GET/POST | Đạt | GET=404, POST=404 |
| Disabled microsoft_callback GET/POST | Đạt | GET=404, POST=404 |
| Disabled forgot_password GET/POST | Đạt | GET=404, POST=404 |
| Disabled reset_password GET/POST | Đạt | GET=404, POST=404 |
| Disabled new_reset GET/POST | Đạt | GET=404, POST=404 |
| Disabled dat-lai-mat-khau GET/POST | Đạt | GET=404, POST=404 |
| Disabled update_profile_code GET/POST | Đạt | GET=404, POST=404 |
| Disabled login_ajax GET/POST | Đạt | GET=404, POST=404 |
| Disabled refresh_captcha GET/POST | Đạt | GET=404, POST=404 |
| Login sv1@example.test | Đạt | /WebsiteQuanLyDeTai/sinh_vien/dashboard.php |
| Login sv2@example.test | Đạt | /WebsiteQuanLyDeTai/sinh_vien/dashboard.php |
| Login gv1@example.test | Đạt | /WebsiteQuanLyDeTai/giang_vien/dashboard.php |
| Login gv2@example.test | Đạt | /WebsiteQuanLyDeTai/giang_vien/dashboard.php |
| Login ld1@example.test | Đạt | /WebsiteQuanLyDeTai/lanh_dao/dashboard.php |
| Student blocked auth/import_sinh_vien.php | Đạt | /WebsiteQuanLyDeTai/sinh_vien/dashboard.php |
| Student blocked auth/import_giang_vien.php | Đạt | /WebsiteQuanLyDeTai/sinh_vien/dashboard.php |
| Student blocked lanh_dao/sua_ngay_thong_bao.php | Đạt | /WebsiteQuanLyDeTai/sinh_vien/dashboard.php |
| Student blocked lanh_dao/quan_ly_nguoi_dung.php | Đạt | /WebsiteQuanLyDeTai/sinh_vien/dashboard.php |
| Missing CSRF rejected | Đạt | HTTP 403 |
| Array CSRF rejected without exception | Đạt | HTTP 403 |
| Wrong CSRF rejected | Đạt | HTTP 403 |
| GET cannot mutate sinh_vien/dang_ky_de_tai.php?id=2 | Đạt | HTTP 405 |
| GET cannot mutate giang_vien/xu_ly_duyet.php?action=duyet&id=2 | Đạt | HTTP 405 |
| GET cannot mutate giang_vien/xoa_de_tai.php?id=4 | Đạt | HTTP 405 |
| GET cannot mutate giang_vien/gui_duyet.php?id=4 | Đạt | HTTP 405 |
| GET cannot mutate lanh_dao/xu_ly_duyet.php?action=duyet&id=4 | Đạt | HTTP 405 |
| GET cannot mutate auth/logout.php | Đạt | HTTP 405 |
| Cross-owner topic edit denied | Đạt | HTTP 403 |
| Cross-owner topic details denied | Đạt | HTTP 403 |
| Cross-owner rejection denied | Đạt | HTTP 403 |
| Cross-owner bulk approval denied | Đạt | HTTP 403 |
| Student cannot delete another registration | Đạt | HTTP 403 |
| Unknown role rejected | Đạt | HTTP 400 |
| Leadership creation requires password confirmation | Đạt | HTTP 403 |
| Leadership creates complete student account | Đạt | HTTP 200 |
| Rejected topic form escapes submitted HTML | Đạt | HTTP 200 |
| JSON embedded in scripts escapes closing tag | Đạt | Checked script data encoding |
| Unsafe URL scheme rejected | Đạt | HTTP 400 |
| Last leadership account remains active | Đạt | Self-lock denied |
| Lock invalidates an existing student session | Đạt | /WebsiteQuanLyDeTai/auth/login.php |
| Password reset invalidates existing session | Đạt | /WebsiteQuanLyDeTai/auth/login.php |
| Legacy MD5 password rejected | Đạt | /WebsiteQuanLyDeTai/auth/login.php |
| Login rate limit survives new sessions | Đạt | Six failed attempts from independent sessions |
| Login ld1@example.test | Đạt | /WebsiteQuanLyDeTai/lanh_dao/dashboard.php |
| Login gv1@example.test | Đạt | /WebsiteQuanLyDeTai/giang_vien/dashboard.php |
| Leadership imports valid CSV | Đạt | HTTP 200 |
| Import cannot grant leadership role | Đạt | HTTP 200 |
| Import rolls back earlier rows after duplicate | Đạt | HTTP 200 |
| Lecturer cannot import accounts | Đạt | HTTP 302 |
| Malformed CSV row rejected | Đạt | HTTP 200 |
| Oversized import rejected | Đạt | HTTP 200 |
| Login ld1@example.test | Đạt | /WebsiteQuanLyDeTai/lanh_dao/dashboard.php |
| Valid XLSX imported with ZIP enabled | Đạt | HTTP 200 |
| XLSX external entity rejected | Đạt | HTTP 200 |
| Concurrent registration preserves slot limit | Đạt | Successful registrations=1 |
| Concurrent registration preserves lecturer limit | Đạt | Successful registrations=1 |
| Concurrent registration preserves student limit | Đạt | Successful registrations=1 |
| All counters match approved registrations | Đạt | Mismatched topics=0 |
| Leadership account forms contain CSRF | Đạt |  |
| Account security controls visible | Đạt |  |
| CSRF forms giang_vien/dashboard.php | Đạt |  |
| CSRF forms giang_vien/danh_sach_de_tai.php | Đạt |  |
| CSRF forms giang_vien/duyet_sinh_vien.php | Đạt |  |
| CSRF forms giang_vien/tao_de_tai.php?loai=co_so_nganh | Đạt |  |
| CSRF forms edit-profile.php | Đạt |  |
| Student registration link submits POST | Đạt |  |
| Registration returns to own topics | Đạt |  |
| Logout link submits POST | Đạt |  |
| No JavaScript runtime exceptions | Đạt |  |

## Kiểm tra Model và kết nối

| Ca kiểm tra | Kết quả | Bằng chứng |
|---|---|---|
| Leadership-issued reset password authenticates | Đạt | CLI on synthetic test database |
| Old password no longer authenticates after reset | Đạt | CLI on synthetic test database |
| Plaintext password fallback is disabled | Đạt | CLI on synthetic test database |
| Dynamic SQL rejects invalid column | Đạt | CLI on synthetic test database |
| Dynamic SQL rejects invalid order | Đạt | CLI on synthetic test database |
| Dynamic SQL rejects invalid table | Đạt | CLI on synthetic test database |
| CSV formula output is neutralized | Đạt | CLI on synthetic test database |
| Unsafe URL scheme is neutralized | Đạt | CLI on synthetic test database |
| Database singleton reconnects after lost connection | Đạt | CLI on synthetic test database |

## Môi trường và dữ liệu thật

| Ca kiểm tra | Kết quả | Bằng chứng |
|---|---|---|
| Apache blocks config/database.php | Đạt | HTTP 403 |
| Apache blocks config/local.php | Đạt | HTTP 403 |
| Apache blocks models/BaseModel.php | Đạt | HTTP 403 |
| Apache blocks helpers/security.php | Đạt | HTTP 403 |
| Apache blocks vendor/autoload.php | Đạt | HTTP 403 |
| Apache blocks includes/header.php | Đạt | HTTP 403 |
| Apache blocks giang_vien/includes/sidebar_back_button.php | Đạt | HTTP 403 |
| Apache blocks lanh_dao/includes/account_security_form.php | Đạt | HTTP 403 |
| Apache blocks bao_cao/README.md | Đạt | HTTP 403 |
| Apache blocks database/schema.sql | Đạt | HTTP 403 |
| Apache blocks storage/backups/qldt_database-before-maintenance-2026-11-20.sql | Đạt | HTTP 403 |
| Apache blocks tests/security/environment.php | Đạt | HTTP 403 |
| Apache blocks .git/config | Đạt | HTTP 403 |
| Original database unchanged | Đạt | Compare complete dumps excluding completion timestamp |

## Cách chạy lại

Xem `tests/security/README.md`. Cần database giả lập mới; `-ResetTestDatabase` chỉ được dùng với dữ liệu thử. Kết quả/log runtime bị Git bỏ qua, báo cáo Markdown này giữ bằng chứng tổng hợp. Không chạy từng ca thay đổi dữ liệu tùy ý trên database thật.

## Giới hạn và việc tiếp theo

Đã kiểm tra các luồng/phạm vi trên, chưa pentest độc lập, chưa kiểm tra tải lớn hoặc xác minh hosting production. Chủ tài khoản cần thu hồi khóa cũ tại hosting/email; Lãnh đạo cần cấp lại mật khẩu cho các tài khoản định dạng cũ. Không tự sửa dữ liệu thật hoặc lịch sử Git trong lần này.