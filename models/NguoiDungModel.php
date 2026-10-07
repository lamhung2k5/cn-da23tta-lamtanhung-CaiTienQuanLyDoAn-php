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
        
        if (!$user) {
            return ['success' => false, 'message' => 'Email không tồn tại'];
        }
        
        if ($user['trang_thai'] !== 'active') {
            return ['success' => false, 'message' => 'Tài khoản đã bị khóa'];
        }
        
        // Kiểm tra mật khẩu bằng password_verify()
        $passwordMatch = false;
        $needsRehash = false;

        // 1. Kiểm tra chính bằng password_verify()
        if (password_verify($password, $user['mat_khau'])) {
            $passwordMatch = true;
            // Kiểm tra xem hash có cần rehash (ví dụ khi nâng cấp algo/cost)
            if (password_needs_rehash($user['mat_khau'], PASSWORD_BCRYPT)) {
                $needsRehash = true;
            }
        }
        // 2. Fallback cho mật khẩu cũ (MD5 hoặc Plaintext) và tự động nâng cấp sang password_hash()
        elseif ($user['mat_khau'] === md5($password) || $user['mat_khau'] === $password) {
            $passwordMatch = true;
            $needsRehash = true;
        }
        
        if (!$passwordMatch) {
            return ['success' => false, 'message' => 'Mật khẩu không đúng'];
        }

        // Tự động nâng cấp mật khẩu cũ sang password_hash() BCRYPT
        if ($needsRehash) {
            $newSecureHash = password_hash($password, PASSWORD_BCRYPT);
            $this->update($user['id'], ['mat_khau' => $newSecureHash]);
        }
        
        // Lấy thông tin vai trò
        $sql = "SELECT vt.ma_vai_tro, vt.ten_vai_tro 
                FROM vai_tro vt 
                WHERE vt.id = :vai_tro_id";
        $vaiTro = $this->queryOne($sql, ['vai_tro_id' => $user['vai_tro_id']]);
        
        // Lấy profile_id tùy theo vai trò
        $profileId = null;
        switch ($vaiTro['ma_vai_tro']) {
            case ROLE_GIANG_VIEN:
                $profile = $this->queryOne("SELECT id FROM giang_vien WHERE nguoi_dung_id = :id", ['id' => $user['id']]);
                $profileId = $profile['id'] ?? null;
                break;
            case ROLE_SINH_VIEN:
                $profile = $this->queryOne("SELECT id FROM sinh_vien WHERE nguoi_dung_id = :id", ['id' => $user['id']]);
                $profileId = $profile['id'] ?? null;
                break;
            case ROLE_LANH_DAO:
                $profile = $this->queryOne("SELECT id FROM lanh_dao WHERE nguoi_dung_id = :id", ['id' => $user['id']]);
                $profileId = $profile['id'] ?? null;
                break;
        }
        
        return [
            'success' => true,
            'user' => [
                'id' => $user['id'],
                'email' => $user['email'],
                'ho_ten' => $user['ho_ten'],
                'vai_tro' => $vaiTro['ma_vai_tro'],
                'vai_tro_id' => $user['vai_tro_id'],
                'ten_vai_tro' => $vaiTro['ten_vai_tro'],
                'profile_id' => $profileId
            ]
        ];
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
        $user = $this->findById($userId);
        
        if (!$user) {
            return ['success' => false, 'message' => 'Người dùng không tồn tại'];
        }

        // Kiểm tra mật khẩu cũ bằng password_verify() hoặc fallback
        $oldMatch = password_verify($oldPassword, $user['mat_khau']) || ($user['mat_khau'] === md5($oldPassword)) || ($user['mat_khau'] === $oldPassword);
        
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
        try {
            // Kiểm tra người dùng đã tồn tại chưa
            $existingUser = $this->findByEmail($email);
            if ($existingUser) {
                return [
                    'success' => true,
                    'user' => $existingUser,
                    'message' => 'Người dùng đã tồn tại'
                ];
            }
            
            // Lấy vai trò mặc định (sinh viên)
            require_once 'VaiTroModel.php';
            $vaiTroModel = new VaiTroModel();
            $defaultRole = $vaiTroModel->findByMa(ROLE_SINH_VIEN);
            
            if (!$defaultRole) {
                return [
                    'success' => false,
                    'message' => 'Không tìm thấy vai trò mặc định'
                ];
            }
            
            // Tạo người dùng mới
            $userId = $this->insert([
                'email' => $email,
                'ho_ten' => $hoTen,
                'mat_khau' => hashPassword(bin2hex(random_bytes(16))), // Mật khẩu ngẫu nhiên
                'vai_tro_id' => $defaultRole['id'],
                'trang_thai' => 'active',
                'oauth_provider' => $provider
            ]);
            
            if (!$userId) {
                return [
                    'success' => false,
                    'message' => 'Không thể tạo người dùng'
                ];
            }
            
            // Lấy thông tin người dùng vừa tạo
            $newUser = $this->findById($userId);
            
            return [
                'success' => true,
                'user' => $newUser,
                'message' => 'Tạo người dùng OAuth thành công'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Lỗi tạo người dùng OAuth: ' . $e->getMessage()
            ];
        }
    }
}
