# 04 — Đăng ký tự duyệt và bảo vệ dữ liệu

Ngày: 21/11/2026. Chưa tạo commit riêng.

## Quy tắc đã được xác nhận

Sinh viên bấm đăng ký thì được duyệt ngay nếu còn chỗ, chưa vượt giới hạn và đợt đăng ký đang mở. Không còn thông báo yêu cầu chờ giảng viên.

## Đã thực hiện

- Luồng đăng ký/duyệt/xóa dùng transaction và khóa theo thứ tự Giảng viên → Sinh viên → Đề tài → Đăng ký; mức cô lập READ COMMITTED để đếm đúng sau khi chờ khóa.
- Kiểm tra sức chứa, giới hạn hướng dẫn và tối đa một đề tài được duyệt theo mỗi hệ của Sinh viên trong giao dịch.
- Bộ đếm lấy theo số đăng ký `da_duyet`; duyệt lặp không tăng bộ đếm, không tạo thông báo duyệt lặp.
- Bản ghi chờ cũ vẫn được xét duyệt đúng quyền; không tự chuyển trạng thái hàng loạt dữ liệu thật.
- Bổ sung phần xử lý hàng loạt còn thiếu; duyệt tự động giữ chờ những bản ghi chưa đủ điều kiện thay vì tự từ chối không có lý do.
- Escape HTML, JSON nhúng script, thông báo/nhãn DOM và URL cấu hình. Sửa các điểm in trực tiếp dữ liệu POST trên form lỗi.
- BaseModel kiểm tra tên bảng/cột và biểu thức sắp xếp trước khi ghép SQL. Câu SQL nghiệp vụ tiếp tục dùng prepared statement.
- Chặn công thức trong xuất CSV; không trả exception chi tiết ra người dùng. Không chuyển đổi dữ liệu đã escape từ trước để tránh làm hỏng nội dung cũ.
- Chặn web vào thư mục code nội bộ, cấu hình, Git, báo cáo, database và dữ liệu runtime. Bổ sung các header bảo mật phù hợp giao diện hiện tại; CSP chưa hạn chế script inline vì giao diện cũ còn sử dụng.

## Kiểm tra

14 tiến trình đồng thời kiểm tra ba tình huống: cùng một chỗ, cùng hạn mức Giảng viên và cùng Sinh viên ở hai đề tài. Mỗi nhóm chỉ có một đăng ký được chấp nhận; bộ đếm khớp dữ liệu. Có kiểm tra XSS, URL và hồi quy giao diện.

## Chưa thực hiện

Chưa tự sửa bộ đếm đã sai trong database thật, chưa đổi khóa tại dịch vụ hosting/email. Cần đối chiếu dữ liệu thật và sao lưu trước khi áp dụng sửa dữ liệu lịch sử; bản vá ngăn sai lệch mới và đồng bộ đề tài khi thao tác liên quan.
