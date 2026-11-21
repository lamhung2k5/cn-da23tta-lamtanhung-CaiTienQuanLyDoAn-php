-- Synthetic local demo data only. Import into a NEW database after schema.sql.
-- Demo password for all five users: BaselineDemo!2026 (never use in production).
SET NAMES utf8mb4;
START TRANSACTION;
INSERT INTO vai_tro (id, ma_vai_tro, ten_vai_tro) VALUES
(1, 'sinh_vien', 'Sinh viên'), (2, 'giang_vien', 'Giảng viên'), (3, 'lanh_dao', 'Lãnh đạo');
INSERT INTO nguoi_dung (id, email, mat_khau, ho_ten, vai_tro_id, trang_thai) VALUES
(1, 'sv1@example.test', '$2y$10$FzoLZVP3dGnKhoN3l8Bxi.GL5WIYyl9R7fT1XHI9QL1EFO512gRWG', 'Sinh viên mẫu 01', 1, 'active'),
(2, 'sv2@example.test', '$2y$10$FzoLZVP3dGnKhoN3l8Bxi.GL5WIYyl9R7fT1XHI9QL1EFO512gRWG', 'Sinh viên mẫu 02', 1, 'active'),
(3, 'gv1@example.test', '$2y$10$FzoLZVP3dGnKhoN3l8Bxi.GL5WIYyl9R7fT1XHI9QL1EFO512gRWG', 'Giảng viên mẫu 01', 2, 'active'),
(4, 'gv2@example.test', '$2y$10$FzoLZVP3dGnKhoN3l8Bxi.GL5WIYyl9R7fT1XHI9QL1EFO512gRWG', 'Giảng viên mẫu 02', 2, 'active'),
(5, 'ld1@example.test', '$2y$10$FzoLZVP3dGnKhoN3l8Bxi.GL5WIYyl9R7fT1XHI9QL1EFO512gRWG', 'Lãnh đạo mẫu 01', 3, 'active');
INSERT INTO sinh_vien (id, nguoi_dung_id, ma_sinh_vien, lop, khoa_hoc, chuyen_nganh) VALUES
(1, 1, 'DEMO_SV01', 'DEMO_CNTT', '2023', 'Công nghệ thông tin'),
(2, 2, 'DEMO_SV02', 'DEMO_CNTT', '2023', 'Công nghệ thông tin');
INSERT INTO giang_vien (id, nguoi_dung_id, ma_giang_vien, khoa, chuyen_mon, gioi_han_sv_csn, gioi_han_sv_cn) VALUES
(1, 3, 'DEMO_GV01', 'Công nghệ thông tin', 'Phát triển web', 10, 10),
(2, 4, 'DEMO_GV02', 'Công nghệ thông tin', 'Cơ sở dữ liệu', 10, 10);
INSERT INTO lanh_dao (id, nguoi_dung_id, ma_lanh_dao, chuc_vu, khoa) VALUES
(1, 5, 'DEMO_LD01', 'Lãnh đạo mẫu', 'Công nghệ thông tin');
INSERT INTO de_tai (id, ma_de_tai, ten_de_tai, tieu_de, mo_ta, giang_vien_id, he_dao_tao, so_luong_sv, so_luong_da_dang_ky, trang_thai, chuyen_nganh, nam_hoc, hoc_ky) VALUES
(1, 'DEMO_CSN01', 'Đề tài mẫu CSN', 'Đề tài mẫu CSN', 'Dữ liệu giả lập cho kiểm tra đăng ký.', 1, 'co_so_nganh', 1, 0, 'da_duyet', 'Công nghệ thông tin', '2026-2027', 'HK1'),
(2, 'DEMO_CN01', 'Đề tài mẫu CN', 'Đề tài mẫu CN', 'Dữ liệu giả lập chuyên ngành.', 1, 'chuyen_nganh', 1, 0, 'da_duyet', 'Công nghệ thông tin', '2026-2027', 'HK1'),
(3, 'DEMO_WAIT', 'Đề tài chờ duyệt', 'Đề tài chờ duyệt', 'Dùng để kiểm tra lãnh đạo duyệt đề tài.', 1, 'co_so_nganh', 1, 0, 'cho_duyet', 'Công nghệ thông tin', '2026-2027', 'HK1'),
(4, 'DEMO_DRAFT', 'Đề tài nháp', 'Đề tài nháp', 'Dùng để kiểm tra sửa và gửi duyệt.', 1, 'co_so_nganh', 1, 0, 'nhap', 'Công nghệ thông tin', '2026-2027', 'HK1'),
(5, 'DEMO_OTHER', 'Đề tài giảng viên 02', 'Đề tài giảng viên 02', 'Dùng để kiểm tra quyền sở hữu.', 2, 'co_so_nganh', 1, 0, 'da_duyet', 'Công nghệ thông tin', '2026-2027', 'HK1'),
(6, 'DEMO_REJECT', 'Đề tài có đăng ký từ chối', 'Đề tài có đăng ký từ chối', 'Dùng để kiểm tra xóa đăng ký bị từ chối.', 1, 'co_so_nganh', 1, 0, 'da_duyet', 'Công nghệ thông tin', '2026-2027', 'HK1');
INSERT INTO dang_ky_de_tai (id, sinh_vien_id, de_tai_id, trang_thai, ly_do_tu_choi) VALUES
(1, 2, 6, 'tu_choi', 'Phản hồi mẫu để kiểm tra chức năng xóa.');
INSERT INTO cai_dat (key_name, value, mo_ta) VALUES
('so_de_tai_toi_da_gv', '100', 'Cấu hình mẫu');
INSERT INTO thong_bao (tieu_de, noi_dung, loai, loai_thong_bao, trang_thai) VALUES
('Thông báo mẫu', 'Nội dung giả lập để kiểm tra trang công khai.', 'info', 'thong_bao', 'mo');
INSERT INTO thong_bao_do_an_content (page_title, subtitle, date_badge, trang_thai) VALUES
('Thông báo đồ án mẫu', 'Dữ liệu thử nghiệm, không phải lịch chính thức.', '2026-2027', 'mo');
INSERT INTO thong_bao_do_an_notice (tieu_de, noi_dung, trang_thai) VALUES
('Lưu ý mẫu', 'Chỉ dùng cho kiểm tra trên localhost.', 'mo');
INSERT INTO thong_bao_do_an_timeline (loai, thu_tu, tieu_de, noi_dung, ngay, thang, nam, trang_thai) VALUES
('dang_ky', 1, 'Mốc đăng ký mẫu', 'Mốc thời gian giả lập.', 20, 11, 2026, 'mo');
INSERT IGNORE INTO cai_dat (key_name, value, mo_ta) VALUES
('gioi_han_de_tai_quan_tam', '6', 'Số đề tài quan tâm tối đa mỗi sinh viên, chung hai hệ'),
('cho_phep_dang_ky', '1', 'Cho phép đăng ký trong các khoảng thời gian đã cấu hình'),
('dang_ky_csn_bat_dau', '2026-09-22 07:00:00', 'Mở đăng ký Cơ sở ngành'),
('dang_ky_csn_ket_thuc', '2026-12-23 23:59:59', 'Đóng đăng ký Cơ sở ngành'),
('dang_ky_cn_bat_dau', '2026-09-24 07:00:00', 'Mở đăng ký Chuyên ngành'),
('dang_ky_cn_ket_thuc', '2026-12-25 23:59:59', 'Đóng đăng ký Chuyên ngành');

COMMIT;