<?php
/**
 * LÃNH ĐẠO MODEL
 */

require_once 'BaseModel.php';

class LanhDaoModel extends BaseModel {
    protected $table = 'lanh_dao';
    
    /**
     * Tạo profile lãnh đạo
     */
    public function createProfile($nguoiDungId, $data) {
        return $this->insert([
            'nguoi_dung_id' => $nguoiDungId,
            'ma_lanh_dao' => $data['ma_lanh_dao'],
            'chuc_vu' => $data['chuc_vu'] ?? null,
            'khoa' => $data['khoa'] ?? null,
            'so_dien_thoai' => $data['so_dien_thoai'] ?? null
        ]);
    }
    
    /**
     * Lấy thông tin lãnh đạo theo nguoi_dung_id
     */
    public function getByNguoiDungId($nguoiDungId) {
        return $this->findOneBy(['nguoi_dung_id' => $nguoiDungId]);
    }
    
    /**
     * Kiểm tra mã lãnh đạo đã tồn tại
     */
    public function maExists($maLanhDao) {
        return $this->findOneBy(['ma_lanh_dao' => $maLanhDao]) !== null;
    }
    
    /**
     * Thống kê tổng quan hệ thống
     */
    public function getThongKeTongQuan() {
        $sql = "SELECT 
                    (SELECT COUNT(*) FROM giang_vien) as tong_giang_vien,
                    (SELECT COUNT(*) FROM sinh_vien) as tong_sinh_vien,
                    (SELECT COUNT(*) FROM de_tai) as tong_de_tai,
                    (SELECT COUNT(*) FROM de_tai WHERE trang_thai = 'cho_duyet') as de_tai_cho_duyet,
                    (SELECT COUNT(*) FROM de_tai WHERE trang_thai = 'da_duyet') as de_tai_da_duyet,
                    (SELECT COUNT(*) FROM dang_ky_de_tai WHERE trang_thai = 'da_duyet') as tong_dang_ky";
        
        return $this->queryOne($sql);
    }
}
