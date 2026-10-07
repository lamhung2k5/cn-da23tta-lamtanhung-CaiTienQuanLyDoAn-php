<?php
/**
 * ĐỀ TÀI MODEL
 */

require_once 'BaseModel.php';

class DeTaiModel extends BaseModel {
    protected $table = 'de_tai';
    
    /**
     * Tạo đề tài mới
     */
    public function createDeTai($data) {
        // Kiểm tra trùng lặp tên đề tài
        if ($this->kiemTraTrungTenDeTai($data['giang_vien_id'], $data['tieu_de'])) {
            throw new Exception("Đề tài '{$data['tieu_de']}' đã tồn tại. Vui lòng chọn tên khác.");
        }
        
        // Kiểm tra giới hạn số lượng đề tài trước khi tạo
        $soLuongHienTai = $this->demSoLuongDeTaiTheoHe($data['giang_vien_id'], $data['he_dao_tao']);
        
        if ($soLuongHienTai >= 100) {
            throw new Exception("Bạn đã đạt giới hạn tối đa 100 đề tài " . 
                ($data['he_dao_tao'] === 'co_so_nganh' ? 'cơ sở ngành' : 'chuyên ngành'));
        }
        
        return $this->insert([
            'tieu_de' => $data['tieu_de'],
            'ten_de_tai' => $data['ten_de_tai'] ?? $data['tieu_de'],
            'mo_ta' => $data['mo_ta'],
            'giang_vien_id' => $data['giang_vien_id'],
            'he_dao_tao' => $data['he_dao_tao'],
            'so_luong_sv' => $data['so_luong_sv'] ?? 1,
            'trang_thai' => $data['trang_thai'] ?? STATUS_NHAP,
            'chuyen_nganh' => $data['chuyen_nganh'] ?? null,
            'ngon_ngu_cong_cu' => $data['ngon_ngu_cong_cu'] ?? null,
            'cong_nghe' => $data['cong_nghe'] ?? null,
            'yeu_cau_sinh_vien' => $data['yeu_cau_sinh_vien'] ?? null,
            'ghi_chu' => $data['ghi_chu'] ?? null,
            'nam_hoc' => $data['nam_hoc'] ?? null,
            'hoc_ky' => $data['hoc_ky'] ?? null
        ]);
    }
    
    /**
     * Kiểm tra trùng lặp tên đề tài
     */
    public function kiemTraTrungTenDeTai($giangVienId, $tieuDe) {
        $sql = "SELECT COUNT(*) as so_luong 
                FROM de_tai 
                WHERE giang_vien_id = :giang_vien_id 
                AND tieu_de = :tieu_de";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'giang_vien_id' => $giangVienId,
            'tieu_de' => $tieuDe
        ]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$result['so_luong'] > 0;
    }
    
    /**
     * Tạo nhiều đề tài cùng lúc (bulk create)
     */
    public function taoNhieuDeTai($giangVienId, $danhSachDeTai, $heDaoTao) {
        $ketQua = [
            'thanh_cong' => 0,
            'that_bai' => 0,
            'loi' => []
        ];
        
        // Kiểm tra giới hạn tổng
        $soLuongHienTai = $this->demSoLuongDeTaiTheoHe($giangVienId, $heDaoTao);
        $soLuongMoi = count($danhSachDeTai);
        
        if ($soLuongHienTai + $soLuongMoi > 10) {
            $coTheTao = 10 - $soLuongHienTai;
            throw new Exception("Chỉ có thể tạo thêm {$coTheTao} đề tài " . 
                ($heDaoTao === 'co_so_nganh' ? 'cơ sở ngành' : 'chuyên ngành') . 
                ". Bạn đang cố tạo {$soLuongMoi} đề tài.");
        }
        
        foreach ($danhSachDeTai as $index => $deTai) {
            try {
                // Kiểm tra trùng lặp
                if ($this->kiemTraTrungTenDeTai($giangVienId, $deTai['tieu_de'])) {
                    $ketQua['that_bai']++;
                    $ketQua['loi'][] = "Đề tài #" . ($index + 1) . ": '{$deTai['tieu_de']}' đã tồn tại";
                    continue;
                }
                
                // Tạo đề tài
                $this->insert([
                    'tieu_de' => $deTai['tieu_de'],
                    'ten_de_tai' => $deTai['tieu_de'],
                    'mo_ta' => $deTai['mo_ta'] ?? 'Mô tả đề tài',
                    'giang_vien_id' => $giangVienId,
                    'he_dao_tao' => $heDaoTao,
                    'so_luong_sv' => $deTai['so_luong_sv'] ?? 1,
                    'trang_thai' => STATUS_NHAP,
                    'chuyen_nganh' => $deTai['chuyen_nganh'] ?? null,
                    'cong_nghe' => $deTai['cong_nghe'] ?? null,
                    'yeu_cau_sinh_vien' => $deTai['yeu_cau_sinh_vien'] ?? null,
                    'ghi_chu' => $deTai['ghi_chu'] ?? null,
                    'nam_hoc' => date('Y') . '-' . (date('Y') + 1),
                    'hoc_ky' => 'HK' . (date('n') <= 6 ? '2' : '1')
                ]);
                
                $ketQua['thanh_cong']++;
                
            } catch (Exception $e) {
                $ketQua['that_bai']++;
                $ketQua['loi'][] = "Đề tài #" . ($index + 1) . ": " . $e->getMessage();
            }
        }
        
        return $ketQua;
    }
    
    /**
     * Đếm tổng số đề tài của giảng viên
     */
    public function countDeTaiByGiangVien($giangVienId) {
        $sql = "SELECT COUNT(*) as so_luong 
                FROM de_tai 
                WHERE giang_vien_id = :giang_vien_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['giang_vien_id' => $giangVienId]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$result['so_luong'];
    }
    
    /**
     * Đếm số lượng đề tài theo hệ đào tạo của giảng viên
     */
    public function demSoLuongDeTaiTheoHe($giangVienId, $heDaoTao) {
        $sql = "SELECT COUNT(*) as so_luong 
                FROM de_tai 
                WHERE giang_vien_id = :giang_vien_id 
                AND he_dao_tao = :he_dao_tao";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'giang_vien_id' => $giangVienId,
            'he_dao_tao' => $heDaoTao
        ]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$result['so_luong'];
    }
    
    /**
     * Alias method cho demSoLuongDeTaiTheoHe
     * Đếm số lượng đề tài theo giảng viên và loại (hệ đào tạo)
     */
    public function countDeTaiByGiangVienAndType($giangVienId, $loaiDeTai) {
        return $this->demSoLuongDeTaiTheoHe($giangVienId, $loaiDeTai);
    }
    
    /**
     * Lấy thống kê số lượng đề tài của giảng viên
     */
    public function getThongKeDeTai($giangVienId) {
        $sql = "SELECT 
                    he_dao_tao,
                    COUNT(*) as so_luong,
                    SUM(CASE WHEN trang_thai = 'da_duyet' THEN 1 ELSE 0 END) as da_duyet,
                    SUM(CASE WHEN trang_thai = 'cho_duyet' THEN 1 ELSE 0 END) as cho_duyet,
                    SUM(CASE WHEN trang_thai = 'nhap' THEN 1 ELSE 0 END) as nhap
                FROM de_tai 
                WHERE giang_vien_id = :giang_vien_id 
                GROUP BY he_dao_tao";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['giang_vien_id' => $giangVienId]);
        
        $result = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[$row['he_dao_tao']] = $row;
        }
        
        return $result;
    }
    
    /**
     * Lấy danh sách đề tài của giảng viên
     */
    public function getDeTaiByGiangVien($giangVienId, $filters = []) {
        $sql = "SELECT dt.*, 
                       (dt.so_luong_sv - dt.so_luong_da_dang_ky) as con_lai
                FROM de_tai dt
                WHERE dt.giang_vien_id = :giang_vien_id";
        
        $params = ['giang_vien_id' => $giangVienId];
        
        if (!empty($filters['trang_thai'])) {
            $sql .= " AND dt.trang_thai = :trang_thai";
            $params['trang_thai'] = $filters['trang_thai'];
        }
        
        if (!empty($filters['he_dao_tao'])) {
            $sql .= " AND dt.he_dao_tao = :he_dao_tao";
            $params['he_dao_tao'] = $filters['he_dao_tao'];
        }
        
        $sql .= " ORDER BY dt.created_at DESC";
        
        return $this->query($sql, $params);
    }
    
    /**
     * Lấy đề tài đã duyệt cho sinh viên
     */
    public function getDeTaiDaDuyet($filters = []) {
        $sql = "SELECT dt.*, 
                       gv.ma_giang_vien,
                       nd.ho_ten as ten_giang_vien,
                       (dt.so_luong_sv - dt.so_luong_da_dang_ky) as con_lai
                FROM de_tai dt
                JOIN giang_vien gv ON dt.giang_vien_id = gv.id
                JOIN nguoi_dung nd ON gv.nguoi_dung_id = nd.id
                WHERE dt.trang_thai = 'da_duyet'
                AND dt.so_luong_da_dang_ky < dt.so_luong_sv";
        
        $params = [];
        
        if (!empty($filters['he_dao_tao'])) {
            $sql .= " AND dt.he_dao_tao = :he_dao_tao";
            $params['he_dao_tao'] = $filters['he_dao_tao'];
        }
        
        if (!empty($filters['search'])) {
            $searchValue = '%' . $filters['search'] . '%';
            $sql .= " AND (dt.tieu_de LIKE :search1 OR dt.mo_ta LIKE :search2 OR nd.ho_ten LIKE :search3)";
            $params['search1'] = $searchValue;
            $params['search2'] = $searchValue;
            $params['search3'] = $searchValue;
        }
        
        $sql .= " ORDER BY dt.created_at DESC";
        
        return $this->query($sql, $params);
    }
    
    /**
     * Lấy đề tài chờ duyệt (cho lãnh đạo)
     */
    public function getDeTaiChoDuyet() {
        $sql = "SELECT dt.*, 
                       gv.ma_giang_vien,
                       nd.ho_ten as ten_giang_vien,
                       nd.email as email_giang_vien
                FROM de_tai dt
                JOIN giang_vien gv ON dt.giang_vien_id = gv.id
                JOIN nguoi_dung nd ON gv.nguoi_dung_id = nd.id
                WHERE dt.trang_thai = 'cho_duyet'
                ORDER BY dt.created_at ASC";
        
        return $this->query($sql);
    }
    
    /**
     * Lấy đề tài đã duyệt (cho lãnh đạo)
     */
    public function getDeTaiLanhDaoDuyet() {
        $sql = "SELECT dt.*, 
                       gv.ma_giang_vien,
                       nd.ho_ten as ten_giang_vien,
                       nd.email as email_giang_vien
                FROM de_tai dt
                JOIN giang_vien gv ON dt.giang_vien_id = gv.id
                JOIN nguoi_dung nd ON gv.nguoi_dung_id = nd.id
                WHERE dt.trang_thai = 'da_duyet'
                ORDER BY dt.created_at ASC";
        
        return $this->query($sql);
    }
    
    /**
     * Duyệt đề tài
     */
    public function duyetDeTai($deTaiId, $nguoiDuyetId) {
        $this->db->beginTransaction();
        
        try {
            // Cập nhật trạng thái đề tài
            $this->update($deTaiId, ['trang_thai' => STATUS_DA_DUYET]);
            
            // Lưu lịch sử
            $this->luuLichSuDuyet($deTaiId, $nguoiDuyetId, STATUS_CHO_DUYET, STATUS_DA_DUYET, null);
            
            $this->db->commit();
            return ['success' => true, 'message' => 'Duyệt đề tài thành công'];
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()];
        }
    }
    
    /**
     * Từ chối đề tài
     */
    public function tuChoiDeTai($deTaiId, $nguoiDuyetId, $lyDo) {
        $this->db->beginTransaction();
        
        try {
            // Cập nhật trạng thái và lý do
            $this->update($deTaiId, [
                'trang_thai' => STATUS_TU_CHOI,
                'ly_do_tu_choi' => $lyDo
            ]);
            
            // Lưu lịch sử
            $this->luuLichSuDuyet($deTaiId, $nguoiDuyetId, STATUS_CHO_DUYET, STATUS_TU_CHOI, $lyDo);
            
            $this->db->commit();
            return ['success' => true, 'message' => 'Từ chối đề tài thành công'];
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()];
        }
    }
    
    /**
     * Lưu lịch sử duyệt
     */
    private function luuLichSuDuyet($deTaiId, $nguoiDuyetId, $trangThaiCu, $trangThaiMoi, $lyDo) {
        $sql = "INSERT INTO lich_su_duyet_de_tai 
                (de_tai_id, nguoi_duyet_id, trang_thai_cu, trang_thai_moi, ly_do) 
                VALUES (:de_tai_id, :nguoi_duyet_id, :trang_thai_cu, :trang_thai_moi, :ly_do)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'de_tai_id' => $deTaiId,
            'nguoi_duyet_id' => $nguoiDuyetId,
            'trang_thai_cu' => $trangThaiCu,
            'trang_thai_moi' => $trangThaiMoi,
            'ly_do' => $lyDo
        ]);
    }
    
    /**
     * Gửi đề tài chờ duyệt
     */
    public function guiChoDuyet($deTaiId) {
        return $this->update($deTaiId, ['trang_thai' => STATUS_CHO_DUYET]);
    }
    
    /**
     * Tăng số lượng đã đăng ký
     */
    public function tangSoLuongDangKy($deTaiId) {
        $sql = "UPDATE de_tai SET so_luong_da_dang_ky = so_luong_da_dang_ky + 1 WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $deTaiId]);
    }
    
    /**
     * Giảm số lượng đã đăng ký
     */
    public function giamSoLuongDangKy($deTaiId) {
        $sql = "UPDATE de_tai SET so_luong_da_dang_ky = so_luong_da_dang_ky - 1 WHERE id = :id AND so_luong_da_dang_ky > 0";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $deTaiId]);
    }
}
