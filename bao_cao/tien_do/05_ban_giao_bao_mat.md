# Bàn giao đợt bảo mật

Ngày: 21/11/2026. Nhánh: `develop/maintenance-baseline`.

Đợt này gộp toàn bộ thay đổi vào **một commit cuối**, không có commit trung gian. `main` vẫn giữ cấu trúc nộp đồ án, không được hợp nhất mã nguồn trong đợt này.

## Đã hoàn tất ở mã nguồn

Xem các báo cáo `01`–`04` và [kết quả kiểm thử](../kiem_thu/ket_qua_bao_mat.md). Bộ kiểm tra tái lập nằm trong `tests/security/`. Báo cáo bước 0 giữ nguyên kết quả lịch sử; quy tắc đăng ký đã được xác nhận lại là tự duyệt.

## Việc cần làm với dữ liệu và dịch vụ thật

1. **Cấp lại mật khẩu cũ:** dữ liệu hiện tại có 364 tài khoản; 20 tài khoản không có hash bắt đầu bằng `$`. Hai tài khoản Lãnh đạo đều có hash bắt đầu bằng `$`. Đây là thống kê định dạng, không xác nhận mật khẩu cụ thể. Tài khoản MD5/plaintext sẽ không đăng nhập được bằng cơ chế mới; Lãnh đạo cấp lại qua chức năng đã bổ sung. Không tự thay mật khẩu thật trong lần bảo trì này.
2. **Thu hồi khóa cũ:** đổi mật khẩu DB hosting và App Password email từng có trong code/lịch sử Git; cập nhật cấu hình riêng. Bản vá không tự thu hồi khóa ở dịch vụ ngoài và không viết lại lịch sử repository.
3. **Dữ liệu lịch sử:** chưa chạy sửa hàng loạt bộ đếm thật. Luồng mới đồng bộ bộ đếm khi xử lý đề tài liên quan; cần đối chiếu riêng dữ liệu đã sai trước đó.
4. **Hosting:** kiểm tra HTTPS, quyền ghi thư mục security, `.htaccess` và extension ZIP. CSV hoạt động khi ZIP chưa bật; kiểm tra XLSX đã thực hiện bằng cách bật ZIP riêng cho tiến trình thử, không sửa `php.ini` của máy.

## Giới hạn

Đây là đợt sửa những lỗi đã xác định và kiểm tra trong phạm vi ứng dụng, không phải chứng nhận hệ thống không còn mọi lỗ hổng. Chưa kiểm tra tải lớn, pentest độc lập hay toàn bộ môi trường hosting production. Các thao tác giao diện phụ thuộc JavaScript cần được kiểm tra lại khi thay layout.

## Vị trí dữ liệu riêng

`storage/backups/`, `storage/baseline/`, `storage/security/` và `config/local.php` bị Git bỏ qua. Không đưa các file này vào `main` hay bộ nộp đồ án. Các mật khẩu trong `database/seed.sql` và `tests/security/` chỉ là fixture giả lập công khai.

## Xác minh cuối

- 119 file PHP kiểm tra cú pháp: không lỗi.
- Ba file JavaScript dùng chung kiểm tra cú pháp: không lỗi; các trang trong ca Edge không có exception JavaScript.
- 103 ca tích hợp + 9 ca CLI bổ sung đều đạt; 13 URL nội bộ trả 403.
- Database gốc không thay đổi sau đợt kiểm tra.
