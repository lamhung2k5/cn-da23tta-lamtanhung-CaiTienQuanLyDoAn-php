# Kế hoạch bảo mật

## Quy tắc đã xác nhận

Chỉ đăng nhập email/mật khẩu bằng tài khoản nhà trường cấp trong `nguoi_dung`. Có ba vai trò: Sinh viên, Giảng viên, Lãnh đạo. Lãnh đạo quản lý tài khoản; không thêm vai trò quản trị riêng. Đóng đăng ký tự do, OTP đăng ký, OAuth và reset công khai không sử dụng. Không bổ sung bảng OTP cho luồng đã ngừng dùng.

Ngày 21/11/2026: Sinh viên đăng ký được duyệt ngay nếu thỏa giới hạn. Trạng thái, thông báo và bộ đếm phải nhất quán; thao tác duyệt cũ phải không tăng bộ đếm lặp. Các bản ghi chờ từ trước vẫn được xử lý có kiểm tra quyền.

## Thứ tự

1. Đóng đường xác thực cũ; bảo vệ cấu hình và báo cáo.
2. Bảo vệ cấp/import tài khoản cho Lãnh đạo.
3. Kiểm tra quyền sở hữu dữ liệu.
4. POST và CSRF ở mọi thao tác thay đổi dữ liệu.
5. Giới hạn đăng nhập; xác minh phiên với database và mật khẩu an toàn.
6. Bảo toàn trạng thái/bộ đếm đăng ký và giao dịch khi cạnh tranh.
7. Rà XSS, SQL động, import và thông tin lỗi.

## Nghiệm thu

Kiểm tra trên database giả lập; giữ dữ liệu thật nguyên vẹn. Phân biệt đạt, không đạt và chưa kiểm tra. Lưu báo cáo theo nhóm, kiểm tra hồi quy trước một commit duy nhất. Các khóa từng công khai phải được chủ tài khoản thay ở dịch vụ tương ứng; việc sửa code không tự thu hồi khóa cũ.
