<?php
/**
 * VAI TRÒ MODEL
 */

require_once 'BaseModel.php';

class VaiTroModel extends BaseModel {
    protected $table = 'vai_tro';
    
    /**
     * Lấy vai trò theo mã
     */
    public function findByMa($maVaiTro) {
        return $this->findOneBy(['ma_vai_tro' => $maVaiTro]);
    }
    
    /**
     * Lấy tất cả vai trò
     */
    public function getAllVaiTro() {
        return $this->findAll('id ASC');
    }
}
