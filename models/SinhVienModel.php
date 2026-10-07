<?php
/**
 * SINH VIÊN MODEL
 */

require_once 'BaseModel.php';

class SinhVienModel extends BaseModel {
    protected $table = 'sinh_vien';
    
    /**
     * Tạo profile sinh viên
     */
    public function createProfile($nguoiDungId, $data) {
        return $this->insert([
            'nguoi_dung_id' => $nguoiDungId,
            'ma_sinh_vien' => $data['ma_sinh_vien'],
            'lop' => $data['lop'] ?? null,
            'khoa_hoc' => $data['khoa_hoc'] ?? null,
            'chuyen_nganh' => $data['chuyen_nganh'] ?? null,
            'so_dien_thoai' => $data['so_dien_thoai'] ?? null
        ]);
    }
    
    /**
     * Lấy thông tin sinh viên theo nguoi_dung_id
     */
    public function getByNguoiDungId($nguoiDungId) {
        return $this->findOneBy(['nguoi_dung_id' => $nguoiDungId]);
    }
    
    /**
     * Kiểm tra MSSV đã tồn tại
     */
    public function mssvExists($maSinhVien) {
        return $this->findOneBy(['ma_sinh_vien' => $maSinhVien]) !== null;
    }
    
    /**
     * Kiểm tra sinh viên đã đăng ký đề tài chưa
     */
    public function daDangKyDeTai($sinhVienId) {
        $sql = "SELECT COUNT(*) as total 
                FROM dang_ky_de_tai 
                WHERE sinh_vien_id = :sinh_vien_id 
                AND trang_thai = 'da_duyet'";
        
        $result = $this->queryOne($sql, ['sinh_vien_id' => $sinhVienId]);
        return $result['total'] > 0;
    }
    
    /**
     * Kiểm tra sinh viên đã đủ số lượng đề tài đã duyệt (1 CSN + 1 CN)
     */
    public function daDuSoLuongDeTai($sinhVienId) {
        $sql = "SELECT 
                    SUM(CASE WHEN dt.he_dao_tao = 'co_so_nganh' THEN 1 ELSE 0 END) as csn_count,
                    SUM(CASE WHEN dt.he_dao_tao = 'chuyen_nganh' THEN 1 ELSE 0 END) as cn_count
                FROM dang_ky_de_tai dk
                JOIN de_tai dt ON dk.de_tai_id = dt.id
                WHERE dk.sinh_vien_id = :sinh_vien_id 
                AND dk.trang_thai = 'da_duyet'";
        
        $result = $this->queryOne($sql, ['sinh_vien_id' => $sinhVienId]);
        
        // Đã đủ khi có ít nhất 1 CSN và 1 CN đã duyệt
        return ($result['csn_count'] >= 1 && $result['cn_count'] >= 1);
    }
    
    /**
     * Kiểm tra sinh viên đã có đề tài đã duyệt theo hệ đào tạo
     */
    public function daDuDeTaiTheoHe($sinhVienId, $heDaoTao) {
        $sql = "SELECT COUNT(*) as count
                FROM dang_ky_de_tai dk
                JOIN de_tai dt ON dk.de_tai_id = dt.id
                WHERE dk.sinh_vien_id = :sinh_vien_id 
                AND dt.he_dao_tao = :he_dao_tao
                AND dk.trang_thai = 'da_duyet'";
        
        $result = $this->queryOne($sql, [
            'sinh_vien_id' => $sinhVienId,
            'he_dao_tao' => $heDaoTao
        ]);
        
        return $result['count'] >= 1;
    }
    
    /**
     * Lấy đề tài đã đăng ký của sinh viên
     */
    public function getDeTaiDaDangKy($sinhVienId) {
        $sql = "SELECT dk.*, dt.ten_de_tai, dt.tieu_de, dt.he_dao_tao,
                       gv.ma_giang_vien, nd.ho_ten as ten_giang_vien
                FROM dang_ky_de_tai dk
                JOIN de_tai dt ON dk.de_tai_id = dt.id
                JOIN giang_vien gv ON dt.giang_vien_id = gv.id
                JOIN nguoi_dung nd ON gv.nguoi_dung_id = nd.id
                WHERE dk.sinh_vien_id = :sinh_vien_id
                ORDER BY dk.ngay_dang_ky DESC";
        
        return $this->query($sql, ['sinh_vien_id' => $sinhVienId]);
    }
}
