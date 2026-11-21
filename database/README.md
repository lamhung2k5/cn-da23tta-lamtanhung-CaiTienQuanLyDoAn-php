# Database baseline — bước 0

Ngày chụp: 20/11/2026. Nguồn: MariaDB 10.4.32 trên localhost.

- `schema.sql`: cấu trúc thực tế của 13 bảng, không có dữ liệu ứng dụng và đã bỏ giá trị bộ đếm AUTO_INCREMENT của dữ liệu thật.
- `seed.sql`: dữ liệu hoàn toàn giả lập: 3 vai trò, 5 tài khoản, 6 đề tài và 1 đăng ký bị từ chối. Không được import seed vào database đang sử dụng.
- Schema giữ nguyên hiện trạng: **chưa có bảng `otp_verification`**. Theo phạm vi xác nhận ngày 21/11/2026, đăng ký tự do/OTP đã đóng; không cần bổ sung bảng này cho luồng đang sử dụng.
- Không có `CREATE DATABASE`, `USE` hoặc `DROP TABLE` trong hai file. Người cài chọn database mới trước khi import. Seed chỉ dùng một lần trên schema trống; import lặp sẽ bị trùng khóa.

## Import bằng XAMPP trên Windows

Trong PowerShell, từ thư mục gốc dự án:

```powershell
& E:/xampp/mysql/bin/mysql.exe -h 127.0.0.1 -u root -e "CREATE DATABASE qldt_demo CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
& E:/xampp/mysql/bin/mysql.exe --default-character-set=utf8mb4 -h 127.0.0.1 -u root qldt_demo -e "SOURCE database/schema.sql; SOURCE database/seed.sql;"
```

Nếu `qldt_demo` đã tồn tại, chọn một tên database mới; không xóa database thật để chạy lại. Có thể import hai file theo thứ tự tương tự bằng phpMyAdmin. Nếu MySQL local có mật khẩu, thêm `-p` để nhập qua lời nhắc.

Sao chép `config/local.example.php` thành `config/local.php` nếu chưa có. Trong mảng cấu hình riêng, đặt `LOCAL_DB_NAME` thành `qldt_demo` để ứng dụng local sử dụng database mẫu. File riêng hiện có chứa cấu hình cá nhân phải được giữ lại; chỉ thêm/sửa khóa cần thiết. Khi quay về dữ liệu đang dùng, đặt lại `qldt_database`. Biến môi trường cùng tên được ưu tiên hơn file riêng.

Chạy `composer install`, bật Apache/MySQL, truy cập `http://localhost/WebsiteQuanLyDeTai/`.

| Vai trò | Email mẫu |
|---|---|
| Sinh viên 01 | sv1@example.test |
| Sinh viên 02 | sv2@example.test |
| Giảng viên 01 | gv1@example.test |
| Giảng viên 02 | gv2@example.test |
| Lãnh đạo | ld1@example.test |

Mật khẩu chung của **tài khoản mẫu**: `BaselineDemo!2026`. Chỉ dùng ở môi trường local. Hai giảng viên có hồ sơ ID 1/2; tài khoản người dùng tương ứng ID 3/4. Sinh viên có hồ sơ ID 1/2; lãnh đạo có tài khoản ID 5.

## Đối chiếu nhanh các ca kiểm tra

Thực hiện trên database mới import, theo thứ tự để dễ đối chiếu:

1. Mở trang chủ, đề tài công khai, thông báo và thông báo đồ án. Thử vào dashboard khi chưa đăng nhập.
2. Đăng nhập các tài khoản mẫu theo ba vai trò; kiểm tra dashboard và đăng xuất. Sinh viên không được vào dashboard lãnh đạo.
3. GV01 tạo một đề tài nháp; sửa đề tài ID 4 rồi gửi duyệt. Lãnh đạo duyệt đề tài ID 3.
4. SV01 đăng ký đề tài ID 1; phải được duyệt ngay và `so_luong_da_dang_ky` bằng 1. SV02 đăng ký cùng đề tài phải bị chặn vì đủ chỗ (ca tuần tự).
5. SV02 xóa đăng ký bị từ chối có ID 1. SV01 không được xóa đăng ký đã duyệt theo quy tắc hiện tại.
6. Trên dữ liệu mẫu, dùng GV02 gọi thao tác duyệt với ID đăng ký mới của SV01; đối chiếu `nguoi_duyet_id`. Sau đó dùng GV01 duyệt lặp hai lần và đối chiếu bộ đếm với số đăng ký thật. Đây là ca kiểm tra bản vá: giảng viên khác phải bị chặn và duyệt lặp không tăng bộ đếm. Chỉ chạy trên database mẫu.
7. Các endpoint đăng ký tự do/OTP/OAuth/reset công khai phải bị đóng. Chỉ đăng nhập bằng tài khoản do Lãnh đạo cấp/import.

Kết quả và các giới hạn nằm trong `bao_tri/ket_qua_buoc_0.md`. Database thử `qldt_baseline_test` đã được dùng cho lần kiểm tra này; dữ liệu thử sau chạy không đại diện cho seed nguyên trạng.

## Bản sao lưu dữ liệu đang dùng

Bản dump riêng trước bảo trì nằm tại `storage/backups/qldt_database-before-maintenance-2026-11-20.sql` trên máy hiện tại. Toàn bộ thư mục này bị Git bỏ qua và Apache từ chối truy cập web. Không upload bản dump chứa dữ liệu thật lên GitHub. File dump đã tạo thành công; chưa thực hiện diễn tập phục hồi toàn bộ bản dump riêng.

`.htaccess` trong `database/` chặn tải schema/seed trực tiếp qua Apache. Chỉ import qua CLI hoặc phpMyAdmin.

## Cập nhật Đề tài quan tâm (migration 002)

Bộ schema/seed mới đã có bảng và cấu hình quan tâm. Với database có sẵn, sao lưu rồi import `database/migrations/002_de_tai_quan_tam.sql` vào đúng database; hoặc chạy `php database/apply_002.php` theo cấu hình cục bộ đang chọn. Áp dụng migration trước khi mở trang Sinh viên trên database đó. Migration không xóa dữ liệu và không ghi đè cài đặt đã có.

Máy hiện tại đã áp dụng cho `qldt_demo`; chưa áp dụng cho database thật `qldt_database`. Lịch đăng ký và giới hạn xem tại Lãnh đạo → Cài đặt thông số → Đăng ký đề tài. Báo cáo: `bao_cao/sinhvien/01_de_tai_quan_tam.md`.
