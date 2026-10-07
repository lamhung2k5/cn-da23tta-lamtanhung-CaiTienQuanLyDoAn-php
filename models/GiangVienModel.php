<?php
/**
 * GIẢNG VIÊN MODEL
 */

require_once 'BaseModel.php';

class GiangVienModel extends BaseModel {
    protected $table = 'giang_vien';
    
    /**
     * Tạo profile giảng viên
     */
    public function createProfile($nguoiDungId, $data) {
        return $this->insert([
            'nguoi_dung_id' => $nguoiDungId,
            'ma_giang_vien' => $data['ma_giang_vien'],
            'khoa' => $data['khoa'] ?? null,
            'chuyen_mon' => $data['chuyen_mon'] ?? null,
            'so_dien_thoai' => $data['so_dien_thoai'] ?? null
        ]);
    }
    
    /**
     * Lấy thông tin giảng viên theo nguoi_dung_id
     */
    public function getByNguoiDungId($nguoiDungId) {
        return $this->findOneBy(['nguoi_dung_id' => $nguoiDungId]);
    }
    
    /**
     * Kiểm tra mã giảng viên đã tồn tại
     */
    public function maExists($maGiangVien) {
        return $this->findOneBy(['ma_giang_vien' => $maGiangVien]) !== null;
    }
    
    /**
     * Thống kê đề tài của giảng viên
     */
    public function getThongKeDeTai($giangVienId) {
        $sql = "SELECT 
                    COUNT(*) as tong_de_tai,
                    SUM(CASE WHEN he_dao_tao = 'co_so_nganh' THEN 1 ELSE 0 END) as de_tai_csn,
                    SUM(CASE WHEN he_dao_tao = 'chuyen_nganh' THEN 1 ELSE 0 END) as de_tai_cn,
                    SUM(CASE WHEN trang_thai = 'cho_duyet' THEN 1 ELSE 0 END) as cho_duyet,
                    SUM(CASE WHEN trang_thai = 'da_duyet' THEN 1 ELSE 0 END) as da_duyet,
                    SUM(CASE WHEN trang_thai = 'tu_choi' THEN 1 ELSE 0 END) as tu_choi,
                    SUM(CASE WHEN trang_thai = 'nhap' THEN 1 ELSE 0 END) as nhap
                FROM de_tai 
                WHERE giang_vien_id = :giang_vien_id";
        
        return $this->queryOne($sql, ['giang_vien_id' => $giangVienId]);
    }
    
    /**
     * Kiểm tra giảng viên đã đủ đề tài chưa
     */
    public function isDuDeTai($giangVienId) {
        $thongKe = $this->getThongKeDeTai($giangVienId);
        
        return $thongKe['de_tai_csn'] >= SO_DE_TAI_CO_SO_NGANH && 
               $thongKe['de_tai_cn'] >= SO_DE_TAI_CHUYEN_NGANH;
    }
    
    /**
     * Lấy danh sách giảng viên với thống kê
     */
    public function getAllWithStats() {
        $sql = "SELECT 
                    gv.*,
                    nd.ho_ten,
                    nd.email,
                    COUNT(dt.id) as tong_de_tai,
                    SUM(CASE WHEN dt.trang_thai = 'da_duyet' THEN 1 ELSE 0 END) as de_tai_duyet,
                    SUM(dt.so_luong_da_dang_ky) as tong_sinh_vien
                FROM giang_vien gv
                JOIN nguoi_dung nd ON gv.nguoi_dung_id = nd.id
                LEFT JOIN de_tai dt ON gv.id = dt.giang_vien_id
                GROUP BY gv.id
                ORDER BY nd.ho_ten ASC";
        
        return $this->query($sql);
    }
}
