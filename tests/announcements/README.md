# Kiểm thử thông báo công khai

Yêu cầu XAMPP local, PHP `E:/xampp/php/php.exe`, MariaDB `127.0.0.1:3306`, Node.js 24, PowerShell; Edge nếu chọn browser.

```powershell
./tests/announcements/run.ps1 -Browser
```

Lần đầu tạo CSDL tổng hợp `qldt_announcement_test`, từ chối ghi đè nếu đã tồn tại. Không dùng CSDL demo hoặc thật. Không có lệnh tự xóa CSDL.

Chạy lại HTTP/browser với fixture hiện có:

```powershell
./tests/announcements/run.ps1 -ReuseTestDatabase -Browser
```

Máy chủ PHP chỉ nghe `127.0.0.1:8091`, Edge debug loopback cổng 9224, các tiến trình do runner tạo được dừng khi xong. `environment.php` chỉ hoạt động khi đúng tên DB thử, cờ ANNOUNCEMENT_TEST_MODE và truy cập loopback. Fixture ID, log, screenshot/profile Edge ở `storage/baseline/`, không commit.

Kết quả: 22 model + 31 HTTP + 10 browser = 63. Chế độ reuse không chạy lại nhóm model. HTTP/browser thay đổi thông báo và cấu hình lịch trong DB thử; không dùng fixture này làm demo chính thức.
