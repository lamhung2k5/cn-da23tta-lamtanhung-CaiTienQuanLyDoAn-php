# 01 — Thu hẹp xác thực

Ngày: 21/11/2026. Nhánh: `develop/maintenance-baseline`. Chưa tạo commit riêng cho nhóm này.

## Mục tiêu

Chỉ đăng nhập bằng tài khoản nhà trường cấp trong `nguoi_dung`.

## Đã thực hiện

- Đóng đăng ký, OTP đăng ký, OAuth (cả trang bắt đầu và callback), reset công khai, cập nhật mã OAuth, CAPTCHA cũ và login AJAX không dùng. Request trực tiếp GET/POST bị trả 404.
- Giữ một handler đăng nhập email/mật khẩu; các trang login theo vai trò chỉ chuyển hướng về handler chung.
- Không nạp OAuth/OTP trong bootstrap; không tạo bảng OTP mới. Vô hiệu hóa `info.php`.
- Giới hạn đăng nhập theo tài khoản (5 lần sai/15 phút) và IP (20 lần sai/15 phút), lưu ở máy chủ và khóa file khi kiểm tra/cập nhật. Phiên trình duyệt mới không xóa giới hạn.
- Dùng `password_verify()`; bỏ nhận MD5/plaintext. Mật khẩu mới 8–72 byte; không tự sửa mật khẩu thật.
- Mọi request có phiên đều đối chiếu trạng thái/vai trò/mật khẩu với database; đổi quyền, đổi mật khẩu, khóa tài khoản hoặc hết thời gian sẽ mất quyền truy cập. Kiểm tra phiên theo database riêng để test không tác động phiên thật.

## Kiểm tra

Xem `../kiem_thu/ket_qua_bao_mat.md`: login ba vai trò, endpoint cũ, giới hạn đăng nhập và mất hiệu lực phiên. Có 20 tài khoản trong dữ liệu hiện tại không có định dạng hash bắt đầu bằng `$`; cần Lãnh đạo rà và cấp lại mật khẩu. Thống kê này không xuất danh sách người dùng hay mật khẩu vào báo cáo.

## Giới hạn

Chưa thực hiện cấp lại mật khẩu cho tài khoản thật. Khóa bí mật đã từng công khai phải được thay tại dịch vụ tương ứng bởi chủ tài khoản. Không khôi phục chức năng email/OAuth đã ngừng dùng.
