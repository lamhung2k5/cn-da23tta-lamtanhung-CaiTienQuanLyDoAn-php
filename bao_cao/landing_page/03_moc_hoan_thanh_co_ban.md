# Mốc hoàn thành cơ bản landing page

Ngày: 21/11/2026. Người dùng đồng ý tạo nhánh và commit tại máy cho đợt landing page.

- Nhánh: `feature/landing-page-co-ban`, tách từ `feature/sinh-vien-co-ban` tại commit `698e9c1`.
- Nội dung commit: `Hoàn thiện cơ bản landing page, hồ sơ giảng viên công khai và quản lý thông báo`.
- Phạm vi: giảng viên công khai từ CSDL; quản lý hồ sơ của Lãnh đạo; migration 003 và công cụ đối chiếu dữ liệu cũ; thông báo chung/đồ án; lịch đăng ký tự động; kiểm thử và báo cáo liên quan.
- Giữ kiến trúc PHP hiện tại. Không đưa cấu hình riêng, log, backup, ảnh/kết quả kiểm thử tạm hoặc dữ liệu CSDL cục bộ vào Git.
- Kiểm thử đã thực hiện trong đợt: 38 kiểm tra giảng viên công khai, 63 kiểm tra thông báo, 112 hồi quy bảo mật đều đạt. Trước commit kiểm tra lại cú pháp PHP các file thay đổi và `git diff --check`; không chạy lại các bộ kiểm thử khi chưa có thay đổi chức năng mới.
- Migration 003 chỉ áp dụng trên demo; chưa áp dụng CSDL thật. Các mốc đăng ký nhập tay cũ vẫn cần Lãnh đạo rà soát như mô tả trong báo cáo thông báo.
- Không thay đổi `main` hoặc nhánh Sinh viên. Chỉ commit tại máy, chưa push GitHub trong bước này.

Chi tiết: [Giảng viên công khai](01_danh_sach_giang_vien_tu_csdl.md), [Hệ thống thông báo](02_he_thong_thong_bao.md). Những dòng “chưa commit/push” trong hai báo cáo là trạng thái tại thời điểm hoàn tất chức năng, trước mốc lưu Git này.
