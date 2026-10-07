<?php
/**
 * OTP MODEL
 * Xử lý logic OTP verification
 */

require_once 'BaseModel.php';

class OTPModel extends BaseModel {
    protected $table = 'otp_verification';
    
    /**
     * Tạo OTP mới
     */
    public function createOTP($email, $vaiTro, $data) {
        // Xóa OTP cũ của email này
        $this->deleteOldOTP($email);
        
        $otpCode = generateOTP(OTP_LENGTH);
        $expiresAt = date('Y-m-d H:i:s', strtotime('+' . OTP_EXPIRE_MINUTES . ' minutes'));
        
        $id = $this->insert([
            'email' => $email,
            'otp_code' => $otpCode,
            'vai_tro' => $vaiTro,
            'data' => json_encode($data, JSON_UNESCAPED_UNICODE),
            'expires_at' => $expiresAt,
            'is_verified' => 0
        ]);
        
        return [
            'id' => $id,
            'otp_code' => $otpCode,
            'expires_at' => $expiresAt
        ];
    }
    
    /**
     * Xác thực OTP
     */
    public function verifyOTP($email, $otpCode) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE email = :email 
                AND otp_code = :otp_code 
                AND is_verified = 0 
                AND expires_at > NOW()
                ORDER BY id DESC LIMIT 1";
        
        $otp = $this->queryOne($sql, [
            'email' => $email,
            'otp_code' => $otpCode
        ]);
        
        if (!$otp) {
            return ['success' => false, 'message' => 'Mã OTP không hợp lệ hoặc đã hết hạn'];
        }
        
        // Đánh dấu đã xác thực
        $this->update($otp['id'], ['is_verified' => 1]);
        
        return [
            'success' => true,
            'data' => json_decode($otp['data'], true),
            'vai_tro' => $otp['vai_tro']
        ];
    }
    
    /**
     * Xóa OTP cũ
     */
    public function deleteOldOTP($email) {
        $sql = "DELETE FROM {$this->table} WHERE email = :email";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['email' => $email]);
    }
    
    /**
     * Xóa OTP hết hạn (chạy định kỳ)
     */
    public function cleanExpiredOTP() {
        $sql = "DELETE FROM {$this->table} WHERE expires_at < NOW()";
        return $this->db->exec($sql);
    }
}
