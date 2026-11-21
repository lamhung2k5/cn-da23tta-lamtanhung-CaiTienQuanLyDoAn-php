# Kiểm thử Đề tài quan tâm

Môi trường hiện tại: XAMPP Windows ở `E:/xampp`, PHP 8.2, MariaDB local `127.0.0.1:3306`, root không mật khẩu, PowerShell. Kiểm tra trình duyệt cần Node.js 24 và Microsoft Edge.

Từ gốc dự án:

```powershell
./tests/followed/run.ps1 -ResetTestDatabase -Browser
```

Switch reset chỉ xóa database giả lập có tên cố định `qldt_follow_test`. Bỏ switch này nếu chạy lần đầu và muốn từ chối ghi đè database đã có. Không chạy từng script thay đổi dữ liệu trên database thật.

Runner tạo schema/seed và các đề tài mẫu, chạy PHP chỉ trên loopback cổng 8089, xác minh tên database, rồi lần lượt kiểm tra HTTP → cạnh tranh thêm quan tâm → biên thời gian → trình duyệt. Cổng debug Edge 9222 phải trống nếu dùng `-Browser`. Các tiến trình thử được dừng khi hoàn tất.

Phạm vi: giới hạn chung hai hệ, thêm/bỏ/không lưu trùng, danh sách riêng, CSRF, vai trò, giảm giới hạn không mất dữ liệu, đề tài ẩn/xóa, sức chứa và đăng ký trước/trong/sau thời hạn. Hai nhóm cạnh tranh chạy 10 lần thêm khác nhau và 8 lần thêm trùng đồng thời.

Mọi dữ liệu, kết quả JSON, log và ảnh chụp sinh ở `storage/baseline/` (bị Git bỏ qua). Demo `qldt_demo` và database thật `qldt_database` không được dùng làm database chạy bộ test này.

Hồi quy hệ thống cũ chạy bằng `tests/security/run.ps1`; fixture bảo mật mở lịch 2000–2100 để kiểm tra quyền/sức chứa không phụ thuộc ngày thực tế. Các ca lịch đăng ký được kiểm tra riêng trong bộ này.

Bổ sung: kiểm tra khóa quan tâm toàn bộ hệ đã đăng ký, hệ còn lại vẫn dùng được, mục cũ vẫn bỏ được. Đề tài một chỗ bị người khác đăng ký phải hiện đúng nhãn và khóa Đăng ký trong danh sách quan tâm; danh sách đăng ký thông thường vẫn ẩn đề tài đó.
