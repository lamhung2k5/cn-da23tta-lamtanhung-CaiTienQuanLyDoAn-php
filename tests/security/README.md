# Kiểm thử bảo mật trên XAMPP Windows

Chỉ dùng dữ liệu giả lập trong database cố định `qldt_security_test`. Không chạy các ca thay đổi dữ liệu trên database thật.

Yêu cầu của bộ kiểm tra hiện tại: PowerShell, XAMPP tại `E:/xampp`, MySQL local `127.0.0.1:3306` với tài khoản root không mật khẩu, PHP 8.2, curl; ZIP DLL cho XLSX. Tùy chọn trình duyệt cần Node.js 24 và Microsoft Edge. Đây là cấu hình kiểm tra của máy phát triển, không phải cấu hình tài khoản production.

Từ gốc dự án, lần đầu:

```powershell
./tests/security/run.ps1 -Browser
```

Nếu database thử đã tồn tại và có thể xóa toàn bộ dữ liệu giả lập trong đó:

```powershell
./tests/security/run.ps1 -ResetTestDatabase -Browser
```

Switch reset chỉ xóa tên database thử viết cố định trong script. Mặc định từ chối ghi đè database đã có. Script khởi chạy PHP trên loopback cổng 8088, bật ZIP riêng cho tiến trình và dừng khi xong; không sửa `php.ini` hay cấu hình database thật. Cổng 9222 dùng riêng cho kiểm tra Edge; phải trống trước khi chạy `-Browser`.

Thứ tự: hồi quy → kiểm tra truy cập/CSRF/XSS/tài khoản → kiểm tra Model/kết nối DB → CSV → XLSX → cạnh tranh đăng ký → trình duyệt (tùy chọn). Chạy từng file riêng chỉ khi đã chuẩn bị đúng trạng thái fixture; ưu tiên dùng runner để giữ đúng thứ tự.

Kết quả JSON, log và fixture upload sinh trong `storage/baseline/`, bị Git bỏ qua. `environment.php` chỉ trả tên database khi máy chủ test có cờ môi trường phù hợp và request đến từ loopback; Apache chặn cả thư mục `tests/`.

Các mật khẩu trong fixture là mật khẩu giả lập có công khai, không dùng cho tài khoản thật. Bộ kiểm tra không gọi Google/GitHub/Microsoft hay gửi email.
