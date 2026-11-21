<?php
/**
 * THÔNG BÁO ĐỒ ÁN MODEL
 * Model for managing thong_bao_do_an page content
 */

require_once 'BaseModel.php';

class ThongBaoDoAnModel extends BaseModel {
    protected $table = 'thong_bao_do_an_content';
    
    /**
     * Get page content
     */
    public function getPageContent() {
        $sql = "SELECT * FROM {$this->table} ORDER BY id DESC LIMIT 1";
        return $this->queryOne($sql);
    }
    
    /**
     * Update page content
     */
    public function updatePageContent($id, $data) {
        $data = [
            'page_title'=>ThongBaoPolicy::text($data['page_title'] ?? '', 'Tiêu đề trang', 500, true),
            'subtitle'=>ThongBaoPolicy::text($data['subtitle'] ?? '', 'Mô tả', 15000),
            'date_badge'=>ThongBaoPolicy::text($data['date_badge'] ?? '', 'Nhãn năm học', 100),
            'trang_thai'=>ThongBaoPolicy::visibility($data['trang_thai'] ?? 'khoa')
        ] + ThongBaoPolicy::dates($data);
        if ($id) { $this->requireContentRow($id, $this->table); }
        if (!empty($id)) {
            $sql = "UPDATE {$this->table} SET 
                page_title = ?, 
                subtitle = ?, 
                date_badge = ?,
                trang_thai = ?,
                ngay_bat_dau = ?,
                ngay_ket_thuc = ?
                WHERE id = ?";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $data['page_title'],
                $data['subtitle'],
                $data['date_badge'],
                $data['trang_thai'] ?? 'mo',
                $data['ngay_bat_dau'] ?? null,
                $data['ngay_ket_thuc'] ?? null,
                $id
            ]);
        } else {
            $sql = "INSERT INTO {$this->table} (page_title, subtitle, date_badge, trang_thai, ngay_bat_dau, ngay_ket_thuc) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $data['page_title'],
                $data['subtitle'],
                $data['date_badge'],
                $data['trang_thai'] ?? 'mo',
                $data['ngay_bat_dau'] ?? null,
                $data['ngay_ket_thuc'] ?? null
            ]);
        }
    }
    
    /**
     * Get timeline items (public view - only active items)
     */
    public function getTimelineItems($loai = null) {
        if ($loai) {
            $sql = "SELECT * FROM thong_bao_do_an_timeline 
                    WHERE loai = ? AND trang_thai = 'mo'
                    ORDER BY thu_tu ASC";
            return $this->query($sql, [$loai]);
        } else {
            $sql = "SELECT * FROM thong_bao_do_an_timeline 
                    WHERE trang_thai = 'mo'
                    ORDER BY loai, thu_tu ASC";
            return $this->query($sql);
        }
    }
    
    /**
     * Get timeline items for admin (all items regardless of status)
     */
    public function getTimelineItemsForAdmin($loai = null) {
        if ($loai) {
            $sql = "SELECT * FROM thong_bao_do_an_timeline 
                    WHERE loai = ?
                    ORDER BY thu_tu ASC";
            return $this->query($sql, [$loai]);
        } else {
            $sql = "SELECT * FROM thong_bao_do_an_timeline 
                    ORDER BY loai, thu_tu ASC";
            return $this->query($sql);
        }
    }
    
    /**
     * Get timeline item by ID
     */
    public function getTimelineItemById($id) {
        return $this->findById($id, 'thong_bao_do_an_timeline');
    }
    
    /**
     * Add timeline item
     */
    public function addTimelineItem($data) {
        $data = $this->validateTimeline($data);
        $sql = "INSERT INTO thong_bao_do_an_timeline 
                (loai, thu_tu, tieu_de, noi_dung, ngay, thang, nam, yeu_cau, trang_thai) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'mo')";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['loai'],
            $data['thu_tu'],
            $data['tieu_de'],
            $data['noi_dung'],
            $data['ngay'],
            $data['thang'],
            $data['nam'] ?? date('Y'),
            $data['yeu_cau']
        ]);
    }
    
    /**
     * Update timeline item
     */
    public function updateTimelineItem($id, $data) {
        $this->requireContentRow($id, 'thong_bao_do_an_timeline');
        $data = $this->validateTimeline($data);
        $sql = "UPDATE thong_bao_do_an_timeline SET 
            loai = ?, 
            thu_tu = ?, 
            tieu_de = ?, 
            noi_dung = ?,
            ngay = ?,
            thang = ?,
            nam = ?,
            yeu_cau = ?,
            trang_thai = ?
            WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['loai'],
            $data['thu_tu'],
            $data['tieu_de'],
            $data['noi_dung'],
            $data['ngay'],
            $data['thang'],
            $data['nam'],
            $data['yeu_cau'],
            $data['trang_thai'] ?? 'mo',
            $id
        ]);
    }
    
    /**
     * Delete timeline item
     */
    public function deleteTimelineItem($id) {
        $this->requireContentRow($id, 'thong_bao_do_an_timeline');
        $sql = "DELETE FROM thong_bao_do_an_timeline WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    /**
     * Toggle timeline item status
     */
    public function toggleTimelineStatus($id) {
        $item = $this->findById($id, 'thong_bao_do_an_timeline');
        if ($item) {
            $newStatus = $item['trang_thai'] === 'mo' ? 'khoa' : 'mo';
            return $this->update($id, ['trang_thai' => $newStatus], 'thong_bao_do_an_timeline');
        }
        return false;
    }
    
    /**
     * Get important notice
     */
    public function getNotice() {
        $row = $this->getNoticeForAdmin();
        return $row && $row['trang_thai'] === 'mo' ? $row : null;
    }
    
    /**
     * Get notice for admin (any status)
     */
    public function getNoticeForAdmin() {
        $sql = "SELECT * FROM thong_bao_do_an_notice ORDER BY id DESC LIMIT 1";
        return $this->queryOne($sql);
    }
    
    /**
     * Get notice by ID
     */
    public function getNoticeById($id) {
        return $this->findById($id, 'thong_bao_do_an_notice');
    }
    
    /**
     * Update notice
     */
    public function updateNotice($id, $data) {
        $data = ['tieu_de'=>ThongBaoPolicy::text($data['tieu_de'] ?? '', 'Tiêu đề lưu ý', 500, true),
            'noi_dung'=>ThongBaoPolicy::text($data['noi_dung'] ?? '', 'Nội dung lưu ý', 15000),
            'trang_thai'=>ThongBaoPolicy::visibility($data['trang_thai'] ?? 'khoa')];
        if ($id) { $this->requireContentRow($id, 'thong_bao_do_an_notice'); }
        if (!empty($id)) {
            $sql = "UPDATE thong_bao_do_an_notice SET 
                tieu_de = ?, 
                noi_dung = ?,
                trang_thai = ?
                WHERE id = ?";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $data['tieu_de'],
                $data['noi_dung'],
                $data['trang_thai'] ?? 'mo',
                $id
            ]);
        } else {
            $sql = "INSERT INTO thong_bao_do_an_notice (tieu_de, noi_dung, trang_thai) VALUES (?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $data['tieu_de'],
                $data['noi_dung'],
                $data['trang_thai'] ?? 'mo'
            ]);
        }
    }
    
    /**
     * Check if page is active (visible to students)
     */
    public function isPageActive() {
        $content = $this->getPageContent();
        return $content && ThongBaoPolicy::state($content) === 'active';
    }

    private function requireContentRow($id, $table) {
        $id = ThongBaoPolicy::positiveId($id);
        if (!$this->findById($id, $table)) { throw new DomainException('Không tìm thấy nội dung cần cập nhật.'); }
    }

    private function validateTimeline(array $data) {
        if (!in_array($data['loai'] ?? '', ['co_so_nganh','chuyen_nganh'], true)) { throw new DomainException('Hệ đào tạo không hợp lệ.'); }
        foreach (['ngay','thang','nam','thu_tu'] as $field) {
            $value = filter_var($data[$field] ?? null, FILTER_VALIDATE_INT);
            if ($value === false || $value === null || $value < 1) { throw new DomainException('Ngày hoặc thứ tự mốc không hợp lệ.'); }
            $data[$field] = $value;
        }
        if ($data['nam'] < 1000 || $data['nam'] > 9999 || !checkdate($data['thang'], $data['ngay'], $data['nam']) || $data['thu_tu'] > 10000) {
            throw new DomainException('Ngày hoặc thứ tự mốc không hợp lệ.');
        }
        $data['tieu_de'] = ThongBaoPolicy::text($data['tieu_de'] ?? '', 'Tiêu đề mốc', 500, true);
        $data['noi_dung'] = ThongBaoPolicy::text($data['noi_dung'] ?? '', 'Nội dung mốc', 15000);
        $data['yeu_cau'] = ThongBaoPolicy::text($data['yeu_cau'] ?? '', 'Yêu cầu', 15000);
        $data['trang_thai'] = ThongBaoPolicy::visibility($data['trang_thai'] ?? 'mo');
        return $data;
    }
}
