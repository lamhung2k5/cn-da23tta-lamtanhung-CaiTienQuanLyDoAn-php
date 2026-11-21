-- Apply once (safe to repeat) to the selected database; no existing rows are deleted.
SET NAMES utf8mb4;
CREATE TABLE IF NOT EXISTS de_tai_quan_tam (
    id INT NOT NULL AUTO_INCREMENT,
    sinh_vien_id INT NOT NULL,
    de_tai_id INT NULL,
    tieu_de_da_luu VARCHAR(500) NOT NULL,
    he_dao_tao_da_luu ENUM('co_so_nganh','chuyen_nganh') NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_quan_tam_sinh_vien_de_tai (sinh_vien_id, de_tai_id),
    KEY idx_quan_tam_de_tai (de_tai_id),
    CONSTRAINT fk_quan_tam_sinh_vien FOREIGN KEY (sinh_vien_id) REFERENCES sinh_vien(id) ON DELETE CASCADE,
    CONSTRAINT fk_quan_tam_de_tai FOREIGN KEY (de_tai_id) REFERENCES de_tai(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT IGNORE INTO cai_dat (key_name, value, mo_ta) VALUES
('gioi_han_de_tai_quan_tam', '6', 'Số đề tài quan tâm tối đa mỗi sinh viên, chung hai hệ'),
('cho_phep_dang_ky', '1', 'Cho phép đăng ký trong các khoảng thời gian đã cấu hình'),
('dang_ky_csn_bat_dau', '2026-09-22 07:00:00', 'Mở đăng ký Cơ sở ngành'),
('dang_ky_csn_ket_thuc', '2026-12-23 23:59:59', 'Đóng đăng ký Cơ sở ngành'),
('dang_ky_cn_bat_dau', '2026-09-24 07:00:00', 'Mở đăng ký Chuyên ngành'),
('dang_ky_cn_ket_thuc', '2026-12-25 23:59:59', 'Đóng đăng ký Chuyên ngành');
