# Kiểm thử danh sách giảng viên công khai

Yêu cầu: XAMPP MariaDB local, PHP `E:/xampp/php/php.exe`, Node.js 24, PowerShell; Edge nếu chạy browser.

```powershell
./tests/directory/run.ps1 -Browser
```

Lần đầu tạo `qldt_directory_test` từ schema/seed tổng hợp, từ chối ghi đè DB đã tồn tại. Không có lệnh tự xóa DB. Model test thay đổi dữ liệu chỉ trong DB này, sau đó HTTP và browser kiểm tra bật/tắt hồ sơ, cập nhật thông tin và quyền truy cập.

Chạy lại HTTP/browser với fixture còn nguyên sau lần chạy trước:

```powershell
./tests/directory/run.ps1 -ReuseTestDatabase -Browser
```

Máy chủ test chỉ nghe `127.0.0.1:8090`, Edge debug `127.0.0.1:9223`; tiến trình do runner tạo được dừng khi xong. `environment.php` chỉ hoạt động khi bật DIRECTORY_TEST_MODE, đúng tên DB thử và địa chỉ loopback. Log, screenshot và profile Edge ở `storage/baseline/`, không commit.

Kết quả: 11 model, 20 HTTP, 7 browser. Runner khi reuse không chạy lại nhóm model.
