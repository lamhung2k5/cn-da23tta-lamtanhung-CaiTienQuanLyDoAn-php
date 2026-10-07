<?php
/**
 * XỬ LÝ CẬP NHẬT CÀI ĐẶT
 */

require_once '../bootstrap.php';
requireRole(ROLE_LANH_DAO);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('lanh_dao/cai_dat_thong_so.php');
}

// Kiểm tra action
$action = $_POST['action'] ?? 'update_setting';

// Xử lý cập nhật thông tin kế hoạch
if ($action === 'update_ke_hoach') {
    $soKeHoach = trim($_POST['so_ke_hoach'] ?? '');
    $ngayKeHoach = trim($_POST['ngay_ke_hoach'] ?? '');
    
    // Cho phép lưu giá trị rỗng (để xóa dữ liệu)
    try {
        $db = Database::getInstance()->getConnection();
        
        // Cập nhật số kế hoạch
        $stmt = $db->prepare("SELECT id FROM cai_dat WHERE key_name = 'so_ke_hoach'");
        $stmt->execute();
        $existing = $stmt->fetch();
        
        if ($existing) {
            $stmt = $db->prepare("UPDATE cai_dat SET value = :value, updated_at = NOW() WHERE key_name = 'so_ke_hoach'");
            $stmt->execute(['value' => $soKeHoach]);
        } else {
            $stmt = $db->prepare("INSERT INTO cai_dat (key_name, value, mo_ta, updated_at) VALUES ('so_ke_hoach', :value, 'Số kế hoạch hiển thị trong báo cáo', NOW())");
            $stmt->execute(['value' => $soKeHoach]);
        }
        
        // Cập nhật ngày kế hoạch
        $stmt = $db->prepare("SELECT id FROM cai_dat WHERE key_name = 'ngay_ke_hoach'");
        $stmt->execute();
        $existing = $stmt->fetch();
        
        if ($existing) {
            $stmt = $db->prepare("UPDATE cai_dat SET value = :value, updated_at = NOW() WHERE key_name = 'ngay_ke_hoach'");
            $stmt->execute(['value' => $ngayKeHoach]);
        } else {
            $stmt = $db->prepare("INSERT INTO cai_dat (key_name, value, mo_ta, updated_at) VALUES ('ngay_ke_hoach', :value, 'Ngày kế hoạch hiển thị trong báo cáo', NOW())");
            $stmt->execute(['value' => $ngayKeHoach]);
        }
        
        // Nếu là AJAX request, trả về JSON
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Đã lưu']);
            exit;
        }
        
        $_SESSION['success'] = 'Cập nhật thông tin kế hoạch thành công';
    } catch (Exception $e) {
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit;
        }
        $_SESSION['error'] = 'Lỗi: ' . $e->getMessage();
    }
    
    redirect('lanh_dao/cai_dat_thong_so.php#tab-ke-hoach');
}

// Xử lý cập nhật thông tin kế hoạch chuyên ngành
if ($action === 'update_ke_hoach_cn') {
    $soKeHoachCN = trim($_POST['so_ke_hoach_cn'] ?? '');
    $ngayKeHoachCN = trim($_POST['ngay_ke_hoach_cn'] ?? '');
    
    // Cho phép lưu giá trị rỗng (để xóa dữ liệu)
    try {
        $db = Database::getInstance()->getConnection();
        
        // Cập nhật số kế hoạch chuyên ngành
        $stmt = $db->prepare("SELECT id FROM cai_dat WHERE key_name = 'so_ke_hoach_cn'");
        $stmt->execute();
        $existing = $stmt->fetch();
        
        if ($existing) {
            $stmt = $db->prepare("UPDATE cai_dat SET value = :value, updated_at = NOW() WHERE key_name = 'so_ke_hoach_cn'");
            $stmt->execute(['value' => $soKeHoachCN]);
        } else {
            $stmt = $db->prepare("INSERT INTO cai_dat (key_name, value, mo_ta, updated_at) VALUES ('so_ke_hoach_cn', :value, 'Số kế hoạch chuyên ngành hiển thị trong báo cáo', NOW())");
            $stmt->execute(['value' => $soKeHoachCN]);
        }
        
        // Cập nhật ngày kế hoạch chuyên ngành
        $stmt = $db->prepare("SELECT id FROM cai_dat WHERE key_name = 'ngay_ke_hoach_cn'");
        $stmt->execute();
        $existing = $stmt->fetch();
        
        if ($existing) {
            $stmt = $db->prepare("UPDATE cai_dat SET value = :value, updated_at = NOW() WHERE key_name = 'ngay_ke_hoach_cn'");
            $stmt->execute(['value' => $ngayKeHoachCN]);
        } else {
            $stmt = $db->prepare("INSERT INTO cai_dat (key_name, value, mo_ta, updated_at) VALUES ('ngay_ke_hoach_cn', :value, 'Ngày kế hoạch chuyên ngành hiển thị trong báo cáo', NOW())");
            $stmt->execute(['value' => $ngayKeHoachCN]);
        }
        
        // Nếu là AJAX request, trả về JSON
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Đã lưu']);
            exit;
        }
        
        $_SESSION['success'] = 'Cập nhật thông tin kế hoạch chuyên ngành thành công';
    } catch (Exception $e) {
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit;
        }
        $_SESSION['error'] = 'Lỗi: ' . $e->getMessage();
    }
    
    redirect('lanh_dao/cai_dat_thong_so.php#tab-ke-hoach');
}

// Xử lý cập nhật số đề tài đơn lẻ (CSN hoặc CN)
if ($action === 'update_single_limit') {
    $giangVienId = $_POST['giang_vien_id'] ?? '';
    $loai = $_POST['loai'] ?? '';
    $soDeTai = $_POST['so_de_tai'] ?? '';
    
    if (empty($giangVienId) || empty($loai) || empty($soDeTai)) {
        // Nếu là AJAX request, trả về JSON
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Thiếu thông tin cài đặt']);
            exit;
        }
        $_SESSION['error'] = 'Thiếu thông tin cài đặt';
        redirect('lanh_dao/cai_dat_thong_so.php#tab-gioi-han');
    }
    
    if (!in_array($loai, ['csn', 'cn'])) {
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Loại đề tài không hợp lệ']);
            exit;
        }
        $_SESSION['error'] = 'Loại đề tài không hợp lệ';
        redirect('lanh_dao/cai_dat_thong_so.php#tab-gioi-han');
    }
    
    if (!is_numeric($soDeTai) || $soDeTai < 1 || $soDeTai > 100) {
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Số sinh viên phải từ 1 đến 100']);
            exit;
        }
        $_SESSION['error'] = 'Số sinh viên phải từ 1 đến 100';
        redirect('lanh_dao/cai_dat_thong_so.php#tab-gioi-han');
    }
    
    try {
        $db = Database::getInstance()->getConnection();
        
        // Ghi thẳng vào bảng giang_vien - đúng nơi trang hiển thị đọc
        $column = $loai === 'csn' ? 'gioi_han_sv_csn' : 'gioi_han_sv_cn';
        $stmt = $db->prepare("UPDATE giang_vien SET {$column} = :value WHERE id = :id");
        $stmt->execute(['value' => (int)$soDeTai, 'id' => $giangVienId]);
        
        // Gửi thông báo cho giảng viên
        $giangVienModel = new GiangVienModel();
        $giangVien = $giangVienModel->findById($giangVienId);
        
        if ($giangVien) {
            $thongBaoModel = new ThongBaoModel();
            $loaiDeTaiText = $loai === 'csn' ? 'Cơ sở ngành' : 'Chuyên ngành';
            
            // Kiểm tra số đề tài hiện tại
            $heDaoTao = $loai === 'csn' ? 'co_so_nganh' : 'chuyen_nganh';
            $stmt = $db->prepare("SELECT COUNT(*) as total FROM de_tai 
                                 WHERE giang_vien_id = :giang_vien_id 
                                 AND he_dao_tao = :he_dao_tao");
            $stmt->execute([
                'giang_vien_id' => $giangVienId,
                'he_dao_tao' => $heDaoTao
            ]);
            $result = $stmt->fetch();
            $soDeTaiHienTai = $result['total'] ?? 0;
            
            // Tạo nội dung thông báo
            if ($soDeTaiHienTai > $soDeTai) {
                // Trường hợp vượt quá giới hạn mới
                $noiDung = "Giới hạn đề tài {$loaiDeTaiText} của bạn đã được thay đổi thành {$soDeTai} đề tài. Hiện tại bạn có {$soDeTaiHienTai} đề tài. Vui lòng xóa bớt " . ($soDeTaiHienTai - $soDeTai) . " đề tài để có thể gửi duyệt.";
                $loaiThongBao = 'warning';
            } else {
                // Trường hợp bình thường
                $noiDung = "Giới hạn đề tài {$loaiDeTaiText} của bạn đã được cập nhật thành {$soDeTai} đề tài.";
                $loaiThongBao = 'info';
            }
            
            $thongBaoModel->taoThongBao(
                $giangVien['nguoi_dung_id'],
                'Cập nhật giới hạn đề tài',
                $noiDung,
                $loaiThongBao,
                'giang_vien/danh_sach_de_tai.php?loai=' . $heDaoTao
            );
        }
        
        // Nếu là AJAX request, trả về JSON
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Đã lưu']);
            exit;
        }
        
        // Không hiển thị thông báo success để UX mượt mà hơn (silent update)
    } catch (Exception $e) {
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit;
        }
        $_SESSION['error'] = 'Lỗi: ' . $e->getMessage();
    }
    
    redirect('lanh_dao/cai_dat_thong_so.php#tab-gioi-han');
}

// Xử lý cập nhật số đề tài cho giảng viên cụ thể (cả CSN và CN)
if ($action === 'update_giang_vien_limit') {
    $giangVienId = $_POST['giang_vien_id'] ?? '';
    $soDeTaiCSN = $_POST['so_de_tai_csn'] ?? '';
    $soDeTaiCN = $_POST['so_de_tai_cn'] ?? '';
    
    if (empty($giangVienId) || empty($soDeTaiCSN) || empty($soDeTaiCN)) {
        $_SESSION['error'] = 'Thiếu thông tin cài đặt';
        redirect('lanh_dao/cai_dat_thong_so.php#tab-gioi-han');
    }
    
    // Validate giá trị
    if (!is_numeric($soDeTaiCSN) || $soDeTaiCSN < 1 || $soDeTaiCSN > 50) {
        $_SESSION['error'] = 'Số đề tài CSN phải từ 1 đến 50';
        redirect('lanh_dao/cai_dat_thong_so.php#tab-gioi-han');
    }
    
    if (!is_numeric($soDeTaiCN) || $soDeTaiCN < 1 || $soDeTaiCN > 50) {
        $_SESSION['error'] = 'Số đề tài CN phải từ 1 đến 50';
        redirect('lanh_dao/cai_dat_thong_so.php#tab-gioi-han');
    }
    
    try {
        $db = Database::getInstance()->getConnection();
        
        // Cập nhật số đề tài CSN
        $keyNameCSN = 'gv_limit_csn_' . $giangVienId;
        $stmt = $db->prepare("SELECT id FROM cai_dat WHERE key_name = :key_name");
        $stmt->execute(['key_name' => $keyNameCSN]);
        $existingCSN = $stmt->fetch();
        
        if ($existingCSN) {
            $stmt = $db->prepare("UPDATE cai_dat SET value = :value, updated_at = NOW() WHERE key_name = :key_name");
            $stmt->execute(['value' => $soDeTaiCSN, 'key_name' => $keyNameCSN]);
        } else {
            $stmt = $db->prepare("INSERT INTO cai_dat (key_name, value, mo_ta, updated_at) VALUES (:key_name, :value, :mo_ta, NOW())");
            $stmt->execute([
                'key_name' => $keyNameCSN,
                'value' => $soDeTaiCSN,
                'mo_ta' => 'Số đề tài CSN tối đa cho giảng viên ID ' . $giangVienId
            ]);
        }
        
        // Cập nhật số đề tài CN
        $keyNameCN = 'gv_limit_cn_' . $giangVienId;
        $stmt = $db->prepare("SELECT id FROM cai_dat WHERE key_name = :key_name");
        $stmt->execute(['key_name' => $keyNameCN]);
        $existingCN = $stmt->fetch();
        
        if ($existingCN) {
            $stmt = $db->prepare("UPDATE cai_dat SET value = :value, updated_at = NOW() WHERE key_name = :key_name");
            $stmt->execute(['value' => $soDeTaiCN, 'key_name' => $keyNameCN]);
        } else {
            $stmt = $db->prepare("INSERT INTO cai_dat (key_name, value, mo_ta, updated_at) VALUES (:key_name, :value, :mo_ta, NOW())");
            $stmt->execute([
                'key_name' => $keyNameCN,
                'value' => $soDeTaiCN,
                'mo_ta' => 'Số đề tài CN tối đa cho giảng viên ID ' . $giangVienId
            ]);
        }
        
        // Không hiển thị thông báo success để UX mượt mà hơn (silent update)
    } catch (Exception $e) {
        $_SESSION['error'] = 'Lỗi: ' . $e->getMessage();
    }
    
    redirect('lanh_dao/cai_dat_thong_so.php#tab-gioi-han');
}

// Xử lý cập nhật cài đặt mặc định
$keyName = $_POST['key_name'] ?? '';
$value = $_POST['value'] ?? '';

if (empty($keyName)) {
    $_SESSION['error'] = 'Thiếu thông tin cài đặt';
    redirect('lanh_dao/cai_dat_thong_so.php');
}

// Validate dữ liệu
$validKeys = ['so_de_tai_toi_da_gv', 'so_de_tai_toi_da_sv', 'cho_phep_dang_ky', 'nam_hoc_hien_tai', 'hoc_ky_hien_tai'];

if (!in_array($keyName, $validKeys)) {
    $_SESSION['error'] = 'Cài đặt không hợp lệ';
    redirect('lanh_dao/cai_dat_thong_so.php');
}

// Validate giá trị số
if (in_array($keyName, ['so_de_tai_toi_da_gv', 'so_de_tai_toi_da_sv'])) {
    if (!is_numeric($value) || $value < 1) {
        $_SESSION['error'] = 'Giá trị phải là số nguyên dương';
        redirect('lanh_dao/cai_dat_thong_so.php');
    }
}

// Validate cho phép đăng ký
if ($keyName === 'cho_phep_dang_ky') {
    if (!in_array($value, ['0', '1'])) {
        $_SESSION['error'] = 'Giá trị không hợp lệ';
        redirect('lanh_dao/cai_dat_thong_so.php');
    }
}

try {
    $caiDatModel = new CaiDatModel();
    $result = $caiDatModel->updateByKey($keyName, $value);
    
    if ($result) {
        $_SESSION['success'] = 'Cập nhật cài đặt thành công';
    } else {
        $_SESSION['error'] = 'Không thể cập nhật cài đặt';
    }
} catch (Exception $e) {
    $_SESSION['error'] = 'Lỗi: ' . $e->getMessage();
}

redirect('lanh_dao/cai_dat_thong_so.php');
