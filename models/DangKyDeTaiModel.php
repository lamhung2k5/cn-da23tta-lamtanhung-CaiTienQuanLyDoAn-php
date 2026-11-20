<?php
/**
 * ĐĂNG KÝ ĐỀ TÀI MODEL
 */

require_once 'BaseModel.php';
require_once __DIR__ . '/RegistrationWorkflow.php';

class DangKyDeTaiModel extends BaseModel {
    protected $table = 'dang_ky_de_tai';
    use RegistrationWorkflow;
    
    /**
     * Sinh viên đăng ký đề tài
     */

    
    /**
     * Lấy danh sách sinh viên đăng ký đề tài của giảng viên
     */
    public function getDanhSachDangKy($giangVienId, $filters = []) {
        $sql = "SELECT dk.*, 
                       sv.ma_sinh_vien, sv.lop, sv.khoa_hoc, sv.chuyen_nganh,
                       nd.ho_ten as ten_sinh_vien, nd.email as email_sinh_vien,
                       dt.tieu_de, dt.he_dao_tao
                FROM dang_ky_de_tai dk
                JOIN sinh_vien sv ON dk.sinh_vien_id = sv.id
                JOIN nguoi_dung nd ON sv.nguoi_dung_id = nd.id
                JOIN de_tai dt ON dk.de_tai_id = dt.id
                WHERE dt.giang_vien_id = :giang_vien_id";
        
        $params = ['giang_vien_id' => $giangVienId];
        
        if (!empty($filters['trang_thai'])) {
            $sql .= " AND dk.trang_thai = :trang_thai";
            $params['trang_thai'] = $filters['trang_thai'];
        }
        
        if (!empty($filters['de_tai_id'])) {
            $sql .= " AND dk.de_tai_id = :de_tai_id";
            $params['de_tai_id'] = $filters['de_tai_id'];
        }
        
        $sql .= " ORDER BY dk.ngay_dang_ky DESC";
        
        return $this->query($sql, $params);
    }
     /**
    * Lấy danh sách sinh viên đăng ký đề tài của tất cả giảng viên
     */
    public function getDanhSachDangKyAll($filters = []) {
        $sql = "SELECT dk.*, 
                       sv.ma_sinh_vien, sv.lop, sv.khoa_hoc, sv.chuyen_nganh,
                       nd.ho_ten as ten_sinh_vien, nd.email as email_sinh_vien,
                       dt.tieu_de, dt.he_dao_tao, dt.giang_vien_id
                FROM dang_ky_de_tai dk
                JOIN sinh_vien sv ON dk.sinh_vien_id = sv.id
                JOIN nguoi_dung nd ON sv.nguoi_dung_id = nd.id
                JOIN de_tai dt ON dk.de_tai_id = dt.id WHERE 1=1";
        $params = [];
               
        if (!empty($filters['trang_thai'])) {
            $sql .= " AND dk.trang_thai = :trang_thai";
            $params['trang_thai'] = $filters['trang_thai'];
        }
        
        if (!empty($filters['de_tai_id'])) {
            $sql .= " AND dk.de_tai_id = :de_tai_id";
            $params['de_tai_id'] = $filters['de_tai_id'];
        }
        
        $sql .= " ORDER BY dk.ngay_dang_ky DESC";
        
        return $this->query($sql, $params);
    }
    
    /**
     * Giảng viên duyệt sinh viên
     */

    
    /**
     * Giảng viên từ chối sinh viên
     */

    
    /**
     * Lấy danh sách phân công chính thức (cho lãnh đạo)
     */
    public function getDanhSachPhanCong() {
        $sql = "SELECT 
                    sv.ma_sinh_vien,
                    nd_sv.ho_ten as ten_sinh_vien,
                    sv.lop,
                    dt.tieu_de as ten_de_tai,
                    dt.he_dao_tao,
                    gv.ma_giang_vien,
                    nd_gv.ho_ten as ten_giang_vien,
                    dk.ghi_chu
                FROM dang_ky_de_tai dk
                JOIN sinh_vien sv ON dk.sinh_vien_id = sv.id
                JOIN nguoi_dung nd_sv ON sv.nguoi_dung_id = nd_sv.id
                JOIN de_tai dt ON dk.de_tai_id = dt.id
                JOIN giang_vien gv ON dt.giang_vien_id = gv.id
                JOIN nguoi_dung nd_gv ON gv.nguoi_dung_id = nd_gv.id
                WHERE dk.trang_thai = 'da_duyet'
                ORDER BY gv.ma_giang_vien, sv.ma_sinh_vien";
        
        return $this->query($sql);
    }
    
    /**
     * Thống kê phân công theo giảng viên
     */
    public function getThongKePhanCongTheoGiangVien() {
        $sql = "SELECT 
                    gv.id,
                    gv.ma_giang_vien,
                    nd.ho_ten as ten_giang_vien,
                    COUNT(CASE WHEN dk.trang_thai = 'da_duyet' AND dt.he_dao_tao = 'co_so_nganh' THEN 1 END) as sv_csn,
                    COUNT(CASE WHEN dk.trang_thai = 'da_duyet' AND dt.he_dao_tao = 'chuyen_nganh' THEN 1 END) as sv_cn,
                    COUNT(CASE WHEN dk.trang_thai = 'da_duyet' THEN 1 END) as tong_sv
                FROM giang_vien gv
                JOIN nguoi_dung nd ON gv.nguoi_dung_id = nd.id
                LEFT JOIN de_tai dt ON gv.id = dt.giang_vien_id
                LEFT JOIN dang_ky_de_tai dk ON dt.id = dk.de_tai_id
                GROUP BY gv.id
                ORDER BY nd.ho_ten";
        
        return $this->query($sql);
    }
}
