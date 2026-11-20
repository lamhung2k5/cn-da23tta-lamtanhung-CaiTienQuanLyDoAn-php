# 02 — Quản lý tài khoản bởi Lãnh đạo

Ngày: 21/11/2026. Chưa tạo commit riêng.

## Đã thực hiện

- Trang tạo/import và sửa ngày thông báo yêu cầu `ROLE_LANH_DAO` ở server.
- Tạo tài khoản chỉ nhận ba vai trò đã định nghĩa; tạo người dùng và hồ sơ trong cùng transaction.
- Cấp thêm Lãnh đạo cần xác nhận mật khẩu của Lãnh đạo hiện tại; xác nhận dùng chung giới hạn thử mật khẩu.
- Bổ sung form khóa/mở khóa/cấp lại mật khẩu trong trang quản lý tài khoản, có POST, CSRF và xác nhận mật khẩu. Chặn tự khóa và khóa Lãnh đạo cuối cùng đang hoạt động.
- Khóa/mở khóa và reset làm mất hiệu lực phiên cũ. Ghi thao tác cấp/import/khóa/reset vào log riêng, không ghi mật khẩu.
- Import chỉ cấp Sinh viên hoặc Giảng viên theo từng endpoint; không cho file import tự cấp quyền Lãnh đạo.
- CSV/XLSX tối đa 2 MB, 2000 dòng, 30 cột; kiểm tra loại file, header, dữ liệu bắt buộc và mật khẩu riêng. Không dùng mã sinh viên/giảng viên làm mật khẩu mặc định.
- XLSX giới hạn số file và dung lượng giải nén, không đọc DTD/thực thể ngoài. Không nhận `.xls` vì parser không hỗ trợ định dạng nhị phân đó.
- Một dòng import lỗi sẽ rollback toàn bộ lần import. ZIP chưa bật thì hiển thị hướng dẫn dùng CSV.

## Kiểm tra

Đã kiểm tra cấp tài khoản có hồ sơ, từ chối vai trò lạ/cấp Lãnh đạo thiếu xác nhận, khóa/reset, CSV đúng/sai/quá lớn, rollback, XLSX hợp lệ và XML có thực thể ngoài. Xem báo cáo kiểm thử.

## Phạm vi

Không bổ sung chức năng đổi vai trò tùy ý hoặc xóa tài khoản. Nếu phát triển các chức năng đó sau này, phải bảo vệ Lãnh đạo cuối cùng và cập nhật hồ sơ theo vai trò trong transaction.
