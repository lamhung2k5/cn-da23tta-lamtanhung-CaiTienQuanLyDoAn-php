<?php
/**
 * THÔNG BÁO MODEL
 */

require_once 'BaseModel.php';

class ThongBaoModel extends BaseModel {
    protected $table = 'thong_bao';
    
    /**
     * Tạo thông báo mới
     */
    public function taoThongBao($nguoiNhanId, $tieuDe, $noiDung, $loai = 'info', $link = null) {
        return $this->insert([
            'nguoi_nhan_id' => $nguoiNhanId,
            'tieu_de' => $tieuDe,
            'noi_dung' => $noiDung,
            'loai' => $loai,
            'link' => $link,
            'da_doc' => 0
        ]);
    }
    
    /**
     * Lấy thông báo của người dùng
     */
    public function getThongBaoByNguoiDung($nguoiDungId, $limit = 10) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE nguoi_nhan_id = :nguoi_nhan_id 
                ORDER BY created_at DESC 
                LIMIT {$limit}";
        
        return $this->query($sql, ['nguoi_nhan_id' => $nguoiDungId]);
    }
    
    /**
     * Đếm thông báo chưa đọc
     */
    public function countChuaDoc($nguoiDungId) {
        return $this->count([
            'nguoi_nhan_id' => $nguoiDungId,
            'da_doc' => 0
        ]);
    }
    
    /**
     * Đánh dấu đã đọc
     */
    public function danhDauDaDoc($thongBaoId) {
        return $this->update($thongBaoId, ['da_doc' => 1]);
    }
    
    /**
     * Đánh dấu tất cả đã đọc
     */
    public function danhDauTatCaDaDoc($nguoiDungId) {
        $sql = "UPDATE {$this->table} SET da_doc = 1 WHERE nguoi_nhan_id = :nguoi_nhan_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['nguoi_nhan_id' => $nguoiDungId]);
    }
    
    /**
     * Gửi thông báo khi đề tài được duyệt
     */
    public function thongBaoDeTaiDuocDuyet($giangVienId, $tenDeTai) {
        // Lấy nguoi_dung_id từ giang_vien_id
        $sql = "SELECT nguoi_dung_id FROM giang_vien WHERE id = :id";
        $gv = $this->queryOne($sql, ['id' => $giangVienId]);
        
        if ($gv) {
            return $this->taoThongBao(
                $gv['nguoi_dung_id'],
                'Đề tài được duyệt',
                "Đề tài \"{$tenDeTai}\" của bạn đã được lãnh đạo phê duyệt.",
                'success',
                'giang_vien/danh_sach_de_tai.php'
            );
        }
        return false;
    }
    
    /**
     * Gửi thông báo khi đề tài bị từ chối
     */
    public function thongBaoDeTaiBiTuChoi($giangVienId, $tenDeTai, $lyDo) {
        $sql = "SELECT nguoi_dung_id FROM giang_vien WHERE id = :id";
        $gv = $this->queryOne($sql, ['id' => $giangVienId]);
        
        if ($gv) {
            return $this->taoThongBao(
                $gv['nguoi_dung_id'],
                'Đề tài bị từ chối',
                "Đề tài \"{$tenDeTai}\" của bạn đã bị từ chối. Lý do: {$lyDo}",
                'danger',
                'giang_vien/danh_sach_de_tai.php'
            );
        }
        return false;
    }
    
    /**
     * Gửi thông báo khi sinh viên đăng ký đề tài
     */
    public function thongBaoSinhVienDangKy($giangVienId, $tenSinhVien, $tenDeTai) {
        $sql = "SELECT nguoi_dung_id FROM giang_vien WHERE id = :id";
        $gv = $this->queryOne($sql, ['id' => $giangVienId]);
        
        if ($gv) {
            return $this->taoThongBao(
                $gv['nguoi_dung_id'],
                'Có sinh viên đăng ký đề tài',
                "Sinh viên {$tenSinhVien} đã đăng ký đề tài \"{$tenDeTai}\" của bạn.",
                'info',
                'giang_vien/duyet_sinh_vien.php'
            );
        }
        return false;
    }
    
    /**
     * Gửi thông báo khi giảng viên duyệt sinh viên
     */
    public function thongBaoSinhVienDuocDuyet($sinhVienId, $tenDeTai) {
        $sql = "SELECT nguoi_dung_id FROM sinh_vien WHERE id = :id";
        $sv = $this->queryOne($sql, ['id' => $sinhVienId]);
        
        if ($sv) {
            return $this->taoThongBao(
                $sv['nguoi_dung_id'],
                'Đăng ký đề tài được duyệt',
                "Đăng ký đề tài \"{$tenDeTai}\" của bạn đã được giảng viên phê duyệt.",
                'success',
                'sinh_vien/de_tai_cua_toi.php'
            );
        }
        return false;
    }
    
    /**
     * Gửi thông báo khi giảng viên từ chối sinh viên
     */
    public function thongBaoSinhVienBiTuChoi($sinhVienId, $tenDeTai, $lyDo) {
        $sql = "SELECT nguoi_dung_id FROM sinh_vien WHERE id = :id";
        $sv = $this->queryOne($sql, ['id' => $sinhVienId]);
        
        if ($sv) {
            return $this->taoThongBao(
                $sv['nguoi_dung_id'],
                'Đăng ký đề tài bị từ chối',
                "Đăng ký đề tài \"{$tenDeTai}\" của bạn đã bị từ chối. Lý do: {$lyDo}",
                'warning',
                'sinh_vien/danh_sach_de_tai.php'
            );
        }
        return false;
    }
    
    /**
     * Lấy thông báo công khai (cho trang chủ)
     * Chỉ lấy thông báo đang trong khoảng thời gian hiệu lực
     */
    public function getThongBaoCongKhai($limit = 5) {
        $today = date('Y-m-d');
        $sql = "SELECT * FROM {$this->table} 
                WHERE nguoi_nhan_id IS NULL
                AND trang_thai = 'mo'
                AND (ngay_bat_dau IS NULL OR ngay_bat_dau <= '{$today}')
                AND (ngay_ket_thuc IS NULL OR ngay_ket_thuc >= '{$today}')
                ORDER BY created_at DESC 
                LIMIT {$limit}";
        
        return $this->query($sql);
    }
    
    /**
     * Lấy TẤT CẢ thông báo công khai (cho trang quản lý - không lọc theo ngày)
     */
    public function getAllThongBaoCongKhai($limit = 50) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE nguoi_nhan_id IS NULL
                ORDER BY created_at DESC 
                LIMIT {$limit}";
        
        return $this->query($sql);
    }
    
    /**
     * Tạo thông báo công khai
     */
    public function taoThongBaoCongKhai($tieuDe, $noiDung, $loai = 'info', $link = null, $loaiThongBao = 'thong_bao', $ngayBatDau = null, $ngayKetThuc = null, $trangThai = 'mo') {
        // Use NULL for public announcements
        $pdo = $this->db->prepare("INSERT INTO {$this->table} (nguoi_nhan_id, tieu_de, noi_dung, loai, link, da_doc, loai_thong_bao, ngay_bat_dau, ngay_ket_thuc, trang_thai) VALUES (NULL, ?, ?, ?, ?, 0, ?, ?, ?, ?)");
        return $pdo->execute([$tieuDe, $noiDung, $loai, $link, $loaiThongBao, $ngayBatDau, $ngayKetThuc, $trangThai]);
    }
    
    /**
     * Lấy thông báo đồ án đang hoạt động (theo ngày và trạng thái)
     */
    public function getThongBaoDoAnActive($limit = 5) {
        $today = date('Y-m-d');
        $sql = "SELECT * FROM {$this->table} 
                WHERE nguoi_nhan_id IS NULL 
                AND loai_thong_bao = 'thong_bao_do_an'
                AND trang_thai = 'mo'
                AND (ngay_bat_dau IS NULL OR ngay_bat_dau <= '{$today}')
                AND (ngay_ket_thuc IS NULL OR ngay_ket_thuc >= '{$today}')
                ORDER BY created_at DESC 
                LIMIT {$limit}";
        
        return $this->query($sql);
    }
    
    /**
     * Lấy thông báo đồ án
     */
    public function getThongBaoDoAn($limit = 5) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE nguoi_nhan_id IS NULL AND loai_thong_bao = 'thong_bao_do_an'
                ORDER BY created_at DESC 
                LIMIT {$limit}";
        
        return $this->query($sql);
    }
    
    /**
     * Cập nhật thông báo
     */
    public function capNhatThongBao($id, $tieuDe, $noiDung, $loai = 'info', $link = null, $loaiThongBao = 'thong_bao', $ngayBatDau = null, $ngayKetThuc = null, $trangThai = 'mo') {
        $sql = "UPDATE {$this->table} SET tieu_de = ?, noi_dung = ?, loai = ?, link = ?, loai_thong_bao = ?, ngay_bat_dau = ?, ngay_ket_thuc = ?, trang_thai = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$tieuDe, $noiDung, $loai, $link, $loaiThongBao, $ngayBatDau, $ngayKetThuc, $trangThai, $id]);
    }
    
    /**
     * Khóa/Mở thông báo
     */
    public function toggleTrangThai($id) {
        $tb = $this->findById($id);
        if ($tb) {
            $newStatus = $tb['trang_thai'] === 'mo' ? 'khoa' : 'mo';
            return $this->update($id, ['trang_thai' => $newStatus]);
        }
        return false;
    }
    
    /**
     * Kiểm tra trang thái của trang thông báo đồ án
     * Trả về true nếu đang mở, false nếu đang khóa
     */
    public function kiemTraTrangThaiThongBaoDoAn() {
        $today = date('Y-m-d');
        $sql = "SELECT COUNT(*) as dem FROM {$this->table} 
                WHERE nguoi_nhan_id IS NULL 
                AND loai_thong_bao = 'thong_bao_do_an'
                AND trang_thai = 'mo'
                AND (ngay_bat_dau IS NULL OR ngay_bat_dau <= '{$today}')
                AND (ngay_ket_thuc IS NULL OR ngay_ket_thuc >= '{$today}')";
        
        $result = $this->queryOne($sql);
        return ($result['dem'] ?? 0) > 0;
    }
}
