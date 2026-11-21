# Báo cáo bảo mật và phát triển

## Báo cáo chức năng theo vai trò

- [Sinh viên](sinhvien/README.md): tra cứu, đăng ký, đề tài quan tâm và các chức năng dành cho Sinh viên.
- [Giảng viên](giangvien/README.md): quản lý đề tài, sinh viên hướng dẫn và các chức năng dành cho Giảng viên.
- [Lãnh đạo](lanhdao/README.md): tài khoản, cấu hình, duyệt đề tài, phân công và báo cáo.

Mỗi chức năng hoàn thành có một file Markdown, đặt tên `NN_ten_chuc_nang.md`, ví dụ `sinhvien/01_de_tai_quan_tam.md`. Chỉ tạo báo cáo kết quả khi đã triển khai và kiểm tra; ghi rõ phần chưa hoàn tất hoặc chưa kiểm tra.

Nếu chức năng liên quan nhiều vai trò, đặt báo cáo chính ở vai trò sử dụng chính và liên kết từ mục lục vai trò còn lại. Ví dụ: Đề tài quan tâm đặt ở `sinhvien/`, phần cấu hình giới hạn của Lãnh đạo được mô tả trong cùng báo cáo và liên kết từ `lanhdao/`.

Mỗi báo cáo gồm: mục tiêu, quy tắc đã chốt, thay đổi/file liên quan, thay đổi database, kết quả kiểm tra, cách dùng thử trên demo, giới hạn và nhánh/commit khi có. Không ghi mật khẩu thật, token hoặc dữ liệu cá nhân.

## Báo cáo dùng chung

- `ke_hoach/`: phạm vi, thứ tự và tiêu chí nghiệm thu.
- `tien_do/`: thay đổi thực tế và các việc còn lại.
- `kiem_thu/`: ca kiểm tra, bằng chứng và kết quả.

Các báo cáo bảo mật hiện có giữ nguyên vị trí vì liên quan toàn hệ thống. Đợt bảo mật đã gộp vào một commit sau khi hoàn tất và kiểm tra tổng thể.

## Đợt bảo mật 21/11/2026

- [Kế hoạch](ke_hoach/01_bao_mat.md)
- [Bàn giao và các việc cần làm trên dữ liệu thật](tien_do/05_ban_giao_bao_mat.md)
- [Danh sách ca kiểm tra](kiem_thu/danh_sach_ca_kiem_tra.md)
- [Kết quả kiểm thử](kiem_thu/ket_qua_bao_mat.md)

## Chức năng Sinh viên mới

- [Đề tài quan tâm](sinhvien/01_de_tai_quan_tam.md)
- [Kết quả kiểm thử chức năng](kiem_thu/01_de_tai_quan_tam.md)
