# Danh sách giảng viên công khai từ CSDL

Ngày thực hiện: 21/11/2026. Đã triển khai và kiểm thử, chờ người dùng dùng thử demo. Chưa commit/push đợt này.

## Mục tiêu và quy tắc

Trước đây `khoa_cntt.php` lấy danh sách cố định từ `config/faculty_members.php`, sau đó tìm từng email trong CSDL để bổ sung điện thoại. Trang chi tiết cũng dựng hồ sơ giả nếu chỉ tìm thấy trong file cấu hình. Thông tin không thống nhất với tài khoản hệ thống.

Sau thay đổi, danh sách lấy từ `giang_vien` JOIN `nguoi_dung` và `vai_tro`. Chỉ hồ sơ giảng viên thuộc tài khoản có vai trò Giảng viên hoặc Lãnh đạo, tài khoản đang active và được bật hiển thị công khai mới xuất hiện. Lãnh đạo chưa có hồ sơ giảng viên không tự xuất hiện. Không tạo tài khoản từ dữ liệu giới thiệu cũ.

Tài khoản mới mặc định ẩn. Lãnh đạo duyệt việc công khai. Chức vụ giới thiệu (Giảng viên, Trưởng khoa, Phó Trưởng khoa) không thay đổi vai trò đăng nhập hoặc trường `la_lanh_dao`. Khóa tài khoản tự làm hồ sơ biến mất; mở lại tài khoản sẽ hiển thị nếu cờ công khai còn bật.

## Giao diện và cách dùng demo

1. Đăng nhập bằng tài khoản Lãnh đạo demo đã cấp.
2. Chọn **Quản lý nội dung → Giới thiệu giảng viên**, hoặc đi từ nút trên trang Quản lý tài khoản.
3. Chọn **Chỉnh sửa** hồ sơ đã có. Sửa chức vụ, học hàm/học vị, số nội bộ, khoa, chuyên môn, điện thoại; bật **Hiển thị trên trang giới thiệu**, rồi Lưu thông tin.
4. Bấm **Xem trang công khai**, hoặc mở `khoa_cntt.php`. Danh sách, bộ lọc chức vụ, số lượng và thông tin chi tiết lấy dữ liệu thật.
5. Bỏ bật công khai để ẩn hồ sơ. Đường dẫn chi tiết trực tiếp cũng trả 404. Dữ liệu hồ sơ và tài khoản không bị xóa.

Họ tên/email dùng nguồn tài khoản hiện tại, không có bản sao trong hồ sơ công khai. Học hàm/học vị được ghép trước tên khi hiển thị; nên nhập họ tên tài khoản không lặp học vị. Hiện schema chưa có ảnh đại diện, danh sách/chi tiết dùng biểu tượng mặc định, chưa bổ sung chức năng upload ảnh.

Demo hiện có hai hồ sơ giảng viên, đều giữ ẩn sau migration. Không tự công khai hoặc điền thông tin giả cho demo; người dùng bật hồ sơ muốn dùng thử qua màn hình Lãnh đạo.

## CSDL và chuyển dữ liệu

Migration `database/migrations/003_giang_vien_cong_khai.sql` thêm vào `giang_vien`:

| Trường | Ý nghĩa |
|---|---|
| `chuc_vu_cong_khai` | VARCHAR(100), mặc định Giảng viên |
| `so_noi_bo` | VARCHAR(30), có thể trống |
| `hien_thi_cong_khai` | TINYINT(1), mặc định 0 |

Đã cập nhật `database/schema.sql` cho cài mới. `database/apply_003.php` chỉ chạy CLI, dùng CSDL đang cấu hình. Migration dùng cú pháp MariaDB `ADD COLUMN IF NOT EXISTS`, đã áp dụng trên `qldt_demo`; chưa áp dụng vào `qldt_database` thật. Chạy lại không reset thông tin/cờ công khai. Không thay mật khẩu hoặc quyền tài khoản.

`database/import_faculty_directory.php` mặc định chỉ preview, đối chiếu email với hồ sơ hợp lệ hiện có. Chỉ với `--apply` mới bổ sung trường trống: số nội bộ, điện thoại, khoa, chuyên môn. Không ghi đè thông tin đang có; không tự nhập chức vụ, không bật công khai, không tạo tài khoản. Chức vụ cũ cần Lãnh đạo xem lại và chọn trên màn hình quản lý.

Preview demo: 0 hồ sơ khớp dữ liệu cũ, 0 trường cần bổ sung. Chưa chạy nhập `--apply`. File cấu hình cũ được giữ để đối chiếu nhưng không còn được trang danh sách/chi tiết dùng lúc chạy.

## Chi tiết công khai và bảo mật

- Liên kết mới dùng ID hồ sơ giảng viên. Liên kết email cũ vẫn hoạt động nếu hồ sơ thật đang được công khai, không dùng dữ liệu dự phòng cố định.
- Hồ sơ ẩn, không tồn tại, tài khoản khóa hoặc vai trò không phù hợp đều không trả thông tin chi tiết.
- Chỉ hiển thị và thống kê đề tài đã duyệt, kể cả khi truyền `filter=all` hoặc tham số khác. Không lộ tổng số đề tài nháp/chờ duyệt qua thống kê.
- Thao tác cập nhật yêu cầu Lãnh đạo, POST và CSRF qua lớp bảo vệ hiện có. Kiểm tra ID, độ dài dữ liệu, danh sách chức vụ và trạng thái; không nhận cập nhật quyền qua form này.
- Escape thông tin khi render để tránh HTML/script từ dữ liệu hồ sơ.
- Sửa lỗi JavaScript cũ ở trang Khoa: kiểm tra biểu tượng `theme-icon` tồn tại trước khi đổi class.

## Tập tin chính

- `models/GiangVienModel.php`: truy vấn danh sách, hồ sơ công khai, cập nhật giới thiệu và đề tài đã duyệt.
- `khoa_cntt.php`, `includes/public_faculty_grid.php`: danh sách từ CSDL, giữ giao diện cũ.
- `giang_vien_chi_tiet_v2.php`, `includes/public_faculty_detail_data.php`: tra cứu hồ sơ thật và thống kê công khai.
- `lanh_dao/gioi_thieu_giang_vien.php`, `lanh_dao/xu_ly_gioi_thieu_giang_vien.php`: quản lý thông tin và công khai hồ sơ.
- Sidebar Lãnh đạo, Quản lý tài khoản: liên kết đến màn hình mới.
- Migration 003, CLI apply/import, schema và `tests/directory/`.

## Kiểm tra

Đạt **38 kiểm tra**: 11 model, 20 HTTP, 7 trình duyệt. CSDL tổng hợp riêng `qldt_directory_test`, không dùng dữ liệu thật. Chặn quyền sai theo cơ chế hiện có: chuyển về dashboard của vai trò; thiếu CSRF trả 403; GET vào handler trả 405. Hồ sơ ẩn trả 404 cả qua ID và email cũ.

Kiểm tra trường hợp mặc định ẩn, bật/tắt, tài khoản khóa, vai trò thay đổi còn hồ sơ, chức vụ không cấp quyền, nhập sai/độ dài/mảng, migration chạy lại, chỉ công khai đề tài đã duyệt và bộ lọc hệ. HTTP kiểm tra escape script, không còn danh sách cố định, cập nhật thành công và cập nhật sai không phá dữ liệu hiện có.

Edge headless kiểm tra số lượng, lọc chức vụ, lọc hệ đề tài, form Lãnh đạo và không có ngoại lệ JavaScript. Đã xem ảnh danh sách trên điện thoại và form quản lý trên máy tính. Ảnh/log nằm ở `storage/baseline/directory-*`, bị Git bỏ qua. PHP lint các file đợt này hợp lệ.

Giữ kiến trúc PHP hiện tại. Chưa tách CSS/JS hoặc chuyển MVC. Chưa chạy lại toàn bộ bộ kiểm thử bảo mật và Sinh viên trong đợt này.
