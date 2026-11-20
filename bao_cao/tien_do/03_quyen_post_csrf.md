# 03 — Quyền dữ liệu, POST và CSRF

Ngày: 21/11/2026. Chưa tạo commit riêng.

## Đã thực hiện

- Kiểm tra vai trò và phiên tập trung trong bootstrap; kiểm tra quyền sở hữu riêng tại các handler đề tài/đăng ký.
- Giảng viên không xem chi tiết nội bộ, sửa, xóa, duyệt hoặc từ chối đăng ký thuộc giảng viên khác. Lãnh đạo có ngoại lệ xử lý theo vai trò.
- Xóa đăng ký xác định đề tài từ bản ghi đăng ký trong database, không tin ID đề tài gửi kèm.
- Sinh viên chỉ xóa bản ghi của mình và đúng trạng thái cho phép. Trang chi tiết không lộ đề tài chưa công khai cho người không đăng ký.
- Chuyển đăng ký, gửi duyệt, duyệt, xóa và đăng xuất sang POST; GET không thay đổi dữ liệu.
- Kiểm tra CSRF cho mọi POST qua bootstrap. Form có token; fetch/XHR cùng origin gửi header token. Hành động từ liên kết/giao diện cũ dùng POST, không nới kiểm tra server.
- Mảng hoặc token không đúng kiểu cũng bị từ chối; không gây exception so sánh token.
- Giữ bộ điều hướng POST chung trong `assets/js/security.js`. Các trang/layout nạp script trước script chức năng.

## Kiểm tra

Đã kiểm tra request trực tiếp thiếu/sai CSRF, GET, quyền chéo và thao tác hàng loạt; đã dùng Edge headless kiểm chứng form và liên kết đăng ký/đăng xuất gửi POST thực sự. Xem báo cáo kiểm thử.
