# Hoàn thiện hệ thống thông báo công khai

Ngày thực hiện: 21/11/2026. Đã triển khai, kiểm thử; chờ người dùng dùng thử demo. Chưa commit/push đợt này.

## Vấn đề và kết quả

Trước thay đổi, trang chủ dùng hàm lấy mọi thông báo công khai bất kể khóa/mở hoặc lịch hiển thị. Liên kết đọc thông báo bị comment; đường dẫn trong form quản lý trỏ đến trang thông báo cá nhân yêu cầu đăng nhập. Có nhiều nơi chỉnh nội dung đồ án và lịch nhập tay không liên kết lịch đăng ký thực tế.

Giữ hai mục **Thông báo chung** và **Thông báo đồ án**, giữ kiến trúc PHP hiện tại và bố cục quản lý sẵn có. Không thêm bảng/cột, không ghi lại nội dung thông báo demo hay dữ liệu thật.

## Thông báo chung

- Trang chủ chỉ lấy thông báo có `nguoi_nhan_id IS NULL`, trạng thái `mo`, đã đến ngày bắt đầu và chưa qua ngày kết thúc. Không đặt ngày tương ứng nghĩa là không giới hạn đầu/cuối; ngày kết thúc tính hết ngày theo múi giờ ứng dụng. Hiện tối đa 10 thông báo mới nhất.
- Bấm từng thông báo mở `chi_tiet_thong_bao.php?id=...`, đọc đầy đủ không cần đăng nhập. Escape nội dung, giữ xuống dòng, không thực thi HTML/script.
- Bản ẩn, chưa đến ngày, hết hạn, ID không tồn tại hoặc thông báo cá nhân đều trả 404 ở trang chi tiết công khai.
- Lãnh đạo dùng **Xem trước** trong danh sách để xem bản đã lưu bằng `preview=1`; quyền được kiểm tra phía máy chủ, không thể thêm tham số để vượt quyền. Kể cả Lãnh đạo cũng không dùng trang này để đọc thông báo cá nhân người khác.
- Màn hình quản lý thêm tìm tiêu đề/nội dung, lọc **Chưa đến ngày / Đang hiển thị / Hết hạn / Đã ẩn**, phân trang 20 mục. Trạng thái tính từ dữ liệu hiện có, không thêm enum mới.
- Kiểm tra tiêu đề/nội dung, giới hạn độ dài, mức độ, trạng thái, ngày hợp lệ và thứ tự ngày. Phần quản lý công khai không được update/toggle/delete bản ghi có người nhận riêng, kể cả khi sửa ID gửi lên.
- POST + CSRF + quyền Lãnh đạo theo lớp bảo vệ hiện tại. Chuyển hướng sau khi lưu để tránh thêm trùng khi refresh. Khi lưu sai bản nhập thông báo chung được giữ trong phiên, modal được mở lại để sửa; không giữ CSRF/mật khẩu trong bản nhập.
- Không dùng `link` tùy ý làm đích mở thông báo trang chủ; tạo/sửa bản công khai dùng trang chi tiết theo ID. Thông báo cá nhân và trạng thái đã đọc vẫn dùng luồng riêng.

## Thông báo đồ án

- Tập trung thêm/sửa/xóa/ẩn mốc, chỉnh nội dung trang và lưu ý tại `lanh_dao/quan_ly_noi_dung_do_an.php`.
- Trang `thong_bao_do_an.php` chỉ đọc, không còn form sửa nội tuyến; Lãnh đạo có nút **Chỉnh sửa nội dung** dẫn về màn hình quản lý. Gửi POST vào trang công khai bị từ chối 405 (POST thiếu CSRF bị lớp bảo vệ từ chối trước).
- `lanh_dao/sua_ngay_thong_bao.php` cũ chuyển về quản lý thông báo chung khi GET; không còn đường ghi ngày song song.
- Nội dung trang và lưu ý được lưu trong transaction: nếu lưu ý sai, nội dung trang cũng rollback. Kết quả chỉ báo thành công sau khi lưu được, dùng flash và PRG thay vì tin chuỗi message/error trên URL.
- Kiểm tra mốc theo hệ Cơ sở ngành/Chuyên ngành, ngày lịch thực (không chấp nhận 30/2), thứ tự số nguyên dương, độ dài và ID tồn tại. Danh sách yêu cầu kiểm tra mảng/giới hạn số mục trước khi ghép.
- Lưu ý có trạng thái riêng. Lưu trang không tự mở lưu ý đã ẩn. Khi bản lưu ý mới nhất bị ẩn, không lấy bản cũ đang mở thay thế.
- Khi trang bị khóa/chưa đến ngày/hết hạn, không render tiêu đề riêng, lưu ý hoặc các mốc đang bị ẩn. Bỏ nhãn học kỳ mặc định cố định cũ khi chưa có dữ liệu.
- Sửa lỗi JavaScript cũ: chỉ gán class biểu tượng sáng/tối nếu phần tử tồn tại.

## Lịch hiển thị và lịch đăng ký

Ba khái niệm tách rõ: lịch hiển thị thông báo, hạn công việc (nộp báo cáo/bảo vệ) và thời gian hệ thống cho đăng ký.

Thêm khối **Lịch đăng ký đề tài của hệ thống** trên trang đồ án công khai và màn hình quản lý, đọc trực tiếp `DangKyThoiGian` như luồng đăng ký Sinh viên. Hiển thị giờ mở/đóng theo từng hệ và trạng thái thực tế, kể cả tạm đóng thủ công. Nút cấu hình dẫn về Cài đặt thông số; không nhập lại ngày mở/đóng tại màn hình thông báo.

Các mốc cũ vẫn giữ nguyên. Không tự đoán mốc nào là mở/đóng đăng ký để ghi đè hoặc xóa. Lãnh đạo cần rà và ẩn/sửa mốc đăng ký nhập tay cũ nếu có để tránh trùng khối lịch tự động. Các mốc nộp bài, báo cáo và bảo vệ tiếp tục nhập độc lập. Chưa có phân loại sự kiện riêng cho dữ liệu mốc cũ, chưa có lưu trữ nhiều đợt/năm học dưới dạng từng bộ thông báo độc lập.

## Tập tin chính

- `helpers/ThongBaoPolicy.php`, `bootstrap.php`: kiểm tra dữ liệu và trạng thái hiển thị dùng chung.
- `models/ThongBaoModel.php`, `models/ThongBaoDoAnModel.php`: truy vấn, kiểm tra và phạm vi cập nhật.
- `index.php`, `chi_tiet_thong_bao.php`: danh sách đúng lịch và đọc chi tiết công khai.
- `lanh_dao/quan_ly_thong_bao.php`, các include `announcement_actions`, `announcement_filters`, `announcement_restore`: quản lý và khôi phục bản nhập sau lỗi.
- `lanh_dao/quan_ly_noi_dung_do_an.php`, include `thesis_announcement_actions`: transaction và chỉnh nội dung tập trung.
- `thong_bao_do_an.php`, `includes/registration_schedule.php`, `lanh_dao/sua_ngay_thong_bao.php`: trang chỉ đọc, lịch tự động và điều hướng đường cũ.
- `tests/announcements/`: bộ kiểm thử riêng.

## Kiểm thử

**63/63 kiểm tra riêng đạt** trên CSDL tổng hợp `qldt_announcement_test`: 22 model, 31 HTTP, 10 Edge headless.

Phạm vi gồm lịch đầu/cuối và hết ngày, công khai/ẩn/hết hạn/chưa đến ngày, trang chủ và chi tiết, xem trước theo quyền, phân biệt thông báo cá nhân, escape nội dung, CSRF, tìm/lọc/phân trang, thêm/xóa/toggle, ngày đảo, rollback nội dung trang/lưu ý, lưu ý ẩn độc lập, chặn các đường ghi cũ, lịch cấu hình và tạm đóng. Trình duyệt kiểm tra liên kết, đọc nội dung đầy đủ trên điện thoại và không tràn ngang, modal sửa, khôi phục bản nhập sau lỗi, nút chỉnh sửa đồ án và không có ngoại lệ JavaScript.

Đã chạy lại **112/112 hồi quy bảo mật đạt** bằng `tests/security/run.ps1 -ResetTestDatabase -Browser` trên `qldt_security_test`. Không thay đổi dữ liệu demo hoặc dữ liệu thật qua các bài thử. PHP lint hợp lệ và không phát hiện Warning/Fatal/Notice trong log HTTP riêng của thông báo. Screenshot/log lưu tại `storage/baseline/announcement-*` và `announcements-*`, bị Git bỏ qua. Đã xem ảnh trang chủ desktop và chi tiết mobile.

## Dùng thử demo

1. Lãnh đạo → **Thông báo chung**: tạo nội dung, chọn trạng thái và khoảng hiển thị; lưu.
2. Dùng **Xem trước** xem bản đã lưu; mở cửa sổ riêng chưa đăng nhập để kiểm tra thông báo chỉ hiện khi đang có hiệu lực.
3. Mở trang chủ, bấm thông báo để đọc đầy đủ; ẩn thông báo và kiểm tra cả trang chủ lẫn đường dẫn chi tiết.
4. Lãnh đạo → **Thông báo đồ án**: cập nhật nội dung, lưu ý, mốc nộp bài/bảo vệ. Chọn trạng thái lưu ý độc lập và thử ngày không hợp lệ.
5. Đổi lịch đăng ký tại **Cài đặt thông số** rồi mở trang thông báo đồ án để thấy lịch tự cập nhật. Rà lại các mốc đăng ký nhập tay cũ nếu có.

Giới hạn: xem trước là bản đã lưu, không phải bản chưa bấm lưu; chưa thêm tệp đính kèm, trình soạn thảo HTML, lịch sử sửa/khôi phục bản cũ hoặc nhiều đợt thông báo. Trang quản lý thông báo đồ án khi lưu lỗi hiển thị lại dữ liệu đã lưu; chưa có cơ chế giữ toàn bộ bản nhập như modal thông báo chung. Chưa có trang danh sách công khai để duyệt mọi thông báo cũ ngoài 10 mục trang chủ; danh sách Lãnh đạo đã có phân trang.
