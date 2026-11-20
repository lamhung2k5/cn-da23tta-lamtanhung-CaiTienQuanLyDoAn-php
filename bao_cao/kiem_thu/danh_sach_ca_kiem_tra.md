# Danh sách kiểm tra bảo mật

## Bộ tự động

Chạy `tests/security/run.ps1 -Browser` trên môi trường được mô tả trong `tests/security/README.md`. Database thử phải mới; chỉ dùng `-ResetTestDatabase` khi được phép xóa dữ liệu giả lập `qldt_security_test`.

| Nhóm | Cần chứng minh |
|---|---|
| Hồi quy | Trang công khai, login/logout ba vai trò, tạo/sửa/gửi duyệt đề tài, lãnh đạo duyệt, sinh viên đăng ký tự duyệt và xem đề tài |
| Xác thực cũ | GET và POST trực tiếp tới đăng ký, OTP, OAuth, callback và reset công khai đều bị đóng |
| Quyền | Không vào chức năng Lãnh đạo bằng vai trò khác; không sửa/xem/duyệt/từ chối/xóa dữ liệu của người khác; kiểm tra hàng loạt |
| Request | CSRF thiếu, sai hoặc dạng mảng bị từ chối; GET không thực hiện thay đổi |
| Tài khoản | Vai trò lạ bị chặn; cấp thêm Lãnh đạo cần xác nhận; tạo đủ hồ sơ; không khóa Lãnh đạo cuối cùng; khóa/reset làm mất phiên cũ |
| Mật khẩu | MD5 bị từ chối; giới hạn thử tồn tại qua các phiên trình duyệt mới |
| Import | CSV/XLSX hợp lệ; sai cột, sai vai trò, quá lớn, trùng dữ liệu và thực thể XML ngoài bị từ chối; rollback toàn bộ khi lỗi |
| XSS/URL | POST trên form lỗi được escape; JSON không kết thúc thẻ script; URL `javascript:` bị chặn |
| Đồng thời | 10 người tranh một chỗ; 2 người tranh hạn mức GV; cùng SV đăng ký 2 đề tài đồng thời; số lượng không vượt và bộ đếm khớp |
| Trình duyệt | Form có token, script chức năng chạy, liên kết đăng ký/logout gửi POST thực tế |

## Kiểm tra môi trường

- PHP lint toàn bộ source, không tính vendor, cấu hình riêng và script tạm.
- Kiểm tra cú pháp JavaScript dùng chung.
- Apache trả 403 cho file thật trong các thư mục nội bộ/báo cáo/database/runtime.
- So sánh bản dump dữ liệu gốc trước/sau, bỏ dòng thời điểm dump.
- Không stage cấu hình riêng, log, dữ liệu upload hay bản dump thật.

## Khi triển khai

- Rà định dạng mật khẩu, dùng chức năng Lãnh đạo để cấp lại tài khoản cũ; không upload danh sách tài khoản thật vào báo cáo.
- Thay mật khẩu/khóa từng được ghi vào lịch sử Git ở dịch vụ hosting/email tương ứng.
- Kiểm tra lại `.htaccess`, PHP extension, quyền ghi `storage/security/`, HTTPS và cookie trên hosting.
- Sao lưu và kiểm tra dữ liệu lịch sử trước khi sửa bộ đếm cũ nếu có sai lệch.
