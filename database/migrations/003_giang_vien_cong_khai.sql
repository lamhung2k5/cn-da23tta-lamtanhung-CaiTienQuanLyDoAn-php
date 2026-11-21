-- MariaDB (XAMPP): existing and new profiles remain hidden until leadership publishes them.
ALTER TABLE giang_vien
    ADD COLUMN IF NOT EXISTS chuc_vu_cong_khai VARCHAR(100) NOT NULL DEFAULT 'Giảng viên',
    ADD COLUMN IF NOT EXISTS so_noi_bo VARCHAR(30) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS hien_thi_cong_khai TINYINT(1) NOT NULL DEFAULT 0;
