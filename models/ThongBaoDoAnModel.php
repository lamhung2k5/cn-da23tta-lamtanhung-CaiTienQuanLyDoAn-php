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
        $sql = "SELECT * FROM {$this->table} LIMIT 1";
        return $this->queryOne($sql);
    }
    
    /**
     * Update page content
     */
    public function updatePageContent($id, $data) {
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
        $sql = "SELECT * FROM thong_bao_do_an_notice WHERE trang_thai = 'mo' LIMIT 1";
        return $this->queryOne($sql);
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
        if (!$content || $content['trang_thai'] === 'khoa') {
            return false;
        }
        
        $today = date('Y-m-d');
        if ($content['ngay_bat_dau'] && $content['ngay_bat_dau'] > $today) {
            return false;
        }
        if ($content['ngay_ket_thuc'] && $content['ngay_ket_thuc'] < $today) {
            return false;
        }
        
        return true;
    }
}
