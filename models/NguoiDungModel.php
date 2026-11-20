<?php
/**
 * NGƯỜI DÙNG MODEL
 * Xử lý logic liên quan đến người dùng
 */

require_once 'BaseModel.php';

class NguoiDungModel extends BaseModel {
    protected $table = 'nguoi_dung';
    
    /**
     * Tìm người dùng theo email
     */
    public function findByEmail($email) {
        return $this->findOneBy(['email' => $email]);
    }
    
    /**
     * Kiểm tra email đã tồn tại
     */
    public function emailExists($email) {
        return $this->findByEmail($email) !== null;
    }
    
    /**
     * Đăng nhập
     */
    public function login($email, $password) {
        $user = $this->findByEmail($email);
        if (!$user || $user['trang_thai'] !== 'active' || !verifyPassword($password, $user['mat_khau'])) {
            return ['success' => false, 'message' => 'Tài khoản hoặc mật khẩu không hợp lệ.'];
        }
        if (password_get_info($user['mat_khau'])['algoName'] === 'bcrypt' && password_needs_rehash($user['mat_khau'], PASSWORD_BCRYPT)) {
            $user['mat_khau'] = password_hash($password, PASSWORD_BCRYPT);
            $this->update($user['id'], ['mat_khau' => $user['mat_khau']]);
        }
        $role = $this->queryOne('SELECT ma_vai_tro, ten_vai_tro FROM vai_tro WHERE id=?', [$user['vai_tro_id']]);
        $tables = [ROLE_SINH_VIEN => 'sinh_vien', ROLE_GIANG_VIEN => 'giang_vien', ROLE_LANH_DAO => 'lanh_dao'];
        if (!$role || !isset($tables[$role['ma_vai_tro']])) { return ['success' => false, 'message' => 'Tài khoản không hợp lệ.']; }
        $profile = $this->queryOne('SELECT id FROM ' . $tables[$role['ma_vai_tro']] . ' WHERE nguoi_dung_id=?', [$user['id']]);
        if (!$profile) { return ['success' => false, 'message' => 'Tài khoản chưa được cấp hồ sơ.']; }
        return ['success' => true, 'user' => [
            'id' => $user['id'], 'email' => $user['email'], 'ho_ten' => $user['ho_ten'],
            'vai_tro' => $role['ma_vai_tro'], 'vai_tro_id' => $user['vai_tro_id'],
            'ten_vai_tro' => $role['ten_vai_tro'], 'profile_id' => $profile['id'],
            'auth_fingerprint' => accountSessionFingerprint($user)
        ]];
    }
    
    /**
     * Tạo người dùng mới
     */
    public function createUser($data) {
        $matKhau = $data['mat_khau'];
        
        // Hash mật khẩu bằng password_hash() BCRYPT
        if (!isset($data['skip_hash']) || !$data['skip_hash']) {
            $matKhau = password_hash($matKhau, PASSWORD_BCRYPT);
        }
        
        return $this->insert([
            'email' => $data['email'],
            'mat_khau' => $matKhau,
            'ho_ten' => $data['ho_ten'],
            'vai_tro_id' => $data['vai_tro_id'],
            'trang_thai' => 'active'
        ]);
    }
    
    /**
     * Đổi mật khẩu
     */
    public function changePassword($userId, $oldPassword, $newPassword) {
        if ((int)$userId !== (int)($_SESSION['user_id'] ?? 0) || !is_string($newPassword) || strlen($newPassword)<PASSWORD_MIN_LENGTH || strlen($newPassword)>72) {
            return ['success' => false, 'message' => 'Mật khẩu mới phải có 8–72 byte.'];
        }
        $user = $this->findById($userId);
        
        if (!$user) {
            return ['success' => false, 'message' => 'Người dùng không tồn tại'];
        }

        // Kiểm tra mật khẩu cũ bằng password_verify() hoặc fallback
        $oldMatch = verifyPassword($oldPassword, $user['mat_khau']);
        
        if (!$oldMatch) {
            return ['success' => false, 'message' => 'Mật khẩu cũ không đúng'];
        }
        
        $newHash = password_hash($newPassword, PASSWORD_BCRYPT);
        $this->update($userId, ['mat_khau' => $newHash]);
        
        return ['success' => true, 'message' => 'Đổi mật khẩu thành công'];
    }
    
    /**
     * Lấy thông tin đầy đủ của người dùng
     */
    public function getUserFullInfo($userId) {
        $sql = "SELECT nd.*, vt.ma_vai_tro, vt.ten_vai_tro
                FROM nguoi_dung nd
                JOIN vai_tro vt ON nd.vai_tro_id = vt.id
                WHERE nd.id = :id";
        
        $user = $this->queryOne($sql, ['id' => $userId]);
        
        if (!$user) {
            return null;
        }
        
        // Lấy thông tin profile
        switch ($user['ma_vai_tro']) {
            case ROLE_GIANG_VIEN:
                $profile = $this->queryOne("SELECT * FROM giang_vien WHERE nguoi_dung_id = :id", ['id' => $userId]);
                break;
            case ROLE_SINH_VIEN:
                $profile = $this->queryOne("SELECT * FROM sinh_vien WHERE nguoi_dung_id = :id", ['id' => $userId]);
                break;
            case ROLE_LANH_DAO:
                $profile = $this->queryOne("SELECT * FROM lanh_dao WHERE nguoi_dung_id = :id", ['id' => $userId]);
                break;
            default:
                $profile = null;
        }
        
        $user['profile'] = $profile;
        return $user;
    }
    
    /**
     * Lưu token reset password
     */
    public function saveResetToken($userId, $token, $expiry) {
        return $this->update($userId, [
            'reset_token' => $token,
            'reset_token_expiry' => $expiry
        ]);
    }
    
    /**
     * Tìm người dùng theo reset token
     */
    public function findByResetToken($token) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE reset_token = :token 
                AND reset_token_expiry > NOW()
                AND trang_thai = 'active'";
        return $this->queryOne($sql, ['token' => $token]);
    }
    
    /**
     * Cập nhật mật khẩu
     */
    public function updatePassword($userId, $password) {
        return $this->update($userId, [
            'mat_khau' => password_hash($password, PASSWORD_BCRYPT)
        ]);
    }
    
    /**
     * Xóa reset token
     */
    public function clearResetToken($userId) {
        return $this->update($userId, [
            'reset_token' => null,
            'reset_token_expiry' => null
        ]);
    }
    
    /**
     * Tạo người dùng OAuth
     */
    public function createOAuthUser($email, $hoTen, $provider) {
        throw new LogicException('OAuth account creation is disabled.');
    }
}
