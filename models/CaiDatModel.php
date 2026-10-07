<?php
/**
 * MODEL CÀI ĐẶT
 * Quản lý các cài đặt hệ thống
 */

class CaiDatModel extends BaseModel {
    protected $table = 'cai_dat';
    
    /**
     * Lấy giá trị cài đặt theo key
     */
    public function getByKey($key) {
        $sql = "SELECT * FROM {$this->table} WHERE key_name = :key LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['key' => $key]);
        return $stmt->fetch();
    }
    
    /**
     * Cập nhật giá trị cài đặt
     */
    public function updateByKey($key, $value) {
        $sql = "UPDATE {$this->table} SET value = :value, updated_at = NOW() WHERE key_name = :key";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'key' => $key,
            'value' => $value
        ]);
    }
    
    /**
     * Lấy tất cả cài đặt
     */
    public function getAllSettings() {
        $sql = "SELECT * FROM {$this->table} ORDER BY id ASC";
        $stmt = $this->db->query($sql);
        $results = $stmt->fetchAll();
        
        // Chuyển thành mảng key => value
        $settings = [];
        foreach ($results as $row) {
            $settings[$row['key_name']] = $row;
        }
        
        return $settings;
    }
    
    /**
     * Lấy số đề tài tối đa giảng viên được phép tạo
     */
    public function getSoDeTaiToiDaGV() {
        $setting = $this->getByKey('so_de_tai_toi_da_gv');
        return $setting ? (int)$setting['value'] : 100;
    }
    
    /**
     * Cập nhật số đề tài tối đa giảng viên
     */
    public function updateSoDeTaiToiDaGV($soLuong) {
        return $this->updateByKey('so_de_tai_toi_da_gv', $soLuong);
    }
}
