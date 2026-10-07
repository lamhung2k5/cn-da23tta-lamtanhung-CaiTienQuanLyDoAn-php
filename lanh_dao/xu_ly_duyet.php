<?php
/**
 * XỬ LÝ DUYỆT/TỪ CHỐI ĐỀ TÀI
 */

require_once '../bootstrap.php';
requireRole(ROLE_LANH_DAO);

$user = getCurrentUser();
$deTaiModel = new DeTaiModel();
$thongBaoModel = new ThongBaoModel();

// Xử lý duyệt hàng loạt
if (isset($_GET['action']) && $_GET['action'] === 'duyet_hang_loat' && isset($_GET['ids'])) {
    $ids = explode(',', $_GET['ids']);
    $successCount = 0;
    $errorCount = 0;
    
    foreach ($ids as $id) {
        $deTaiId = (int)$id;
        if ($deTaiId <= 0) continue;
        
        $result = $deTaiModel->duyetDeTai($deTaiId, $user['id']);
        
        if ($result['success']) {
            $successCount++;
            // Lấy thông tin để gửi thông báo
            $deTai = $deTaiModel->findById($deTaiId);
            // Gửi thông báo cho giảng viên
            $thongBaoModel->thongBaoDeTaiDuocDuyet($deTai['giang_vien_id'], $deTai['tieu_de']);
        } else {
            $errorCount++;
        }
    }
    
    if ($successCount > 0) {
        setFlashMessage('success', "Đã duyệt thành công {$successCount} đề tài");
    }
    if ($errorCount > 0) {
        setFlashMessage('warning', "Có {$errorCount} đề tài không thể duyệt");
    }
    
    redirect('lanh_dao/duyet_de_tai.php?loai=' . ($_GET['loai'] ?? 'co_so_nganh'));
}

// Xử lý duyệt
if (isset($_GET['action']) && $_GET['action'] === 'duyet' && isset($_GET['id'])) {
    $deTaiId = (int)$_GET['id'];
    
    // Lấy thông tin đề tài trước khi duyệt
    $sql = "SELECT dt.*, gv.ma_giang_vien, nd.ho_ten as ten_giang_vien
            FROM de_tai dt
            JOIN giang_vien gv ON dt.giang_vien_id = gv.id
            JOIN nguoi_dung nd ON gv.nguoi_dung_id = nd.id
            WHERE dt.id = :id";
    $deTai = $deTaiModel->queryOne($sql, ['id' => $deTaiId]);
    
    $result = $deTaiModel->duyetDeTai($deTaiId, $user['id']);
    
    if ($result['success']) {
        // Gửi thông báo cho giảng viên
        $thongBaoModel->thongBaoDeTaiDuocDuyet($deTai['giang_vien_id'], $deTai['tieu_de']);
        
        // Tạo thông báo chi tiết
        $loaiDeTai = $deTai['he_dao_tao'] === 'co_so_nganh' ? 'Cơ sở ngành' : 'Chuyên ngành';
        $tenGV = $deTai['ten_giang_vien'] ?? 'Giảng viên';
        setFlashMessage('success', "Duyệt đề tài {$loaiDeTai} thành công cho giảng viên: {$tenGV}");
    } else {
        setFlashMessage('error', $result['message']);
    }
    
    // Kiểm tra redirect
    $redirectPage = isset($_GET['redirect']) && $_GET['redirect'] === 'dashboard' 
        ? 'lanh_dao/dashboard.php?gv_id=' . ($_GET['gv_id'] ?? '') . '&loai=' . ($_GET['loai'] ?? 'co_so_nganh')
        : 'lanh_dao/duyet_de_tai.php?loai=' . ($_GET['loai'] ?? 'co_so_nganh');
    redirect($redirectPage);
}

// Xử lý từ chối
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'tu_choi') {
    $deTaiIds = $_POST['de_tai_id'];
    $lyDo = sanitize($_POST['ly_do']);
    $redirectParam = $_POST['redirect'] ?? '';
    $gvId = $_POST['gv_id'] ?? '';
    
    if (empty($lyDo)) {
        setFlashMessage('error', 'Vui lòng nhập lý do từ chối');
        $redirectPage = $redirectParam === 'dashboard' 
            ? 'lanh_dao/dashboard.php?gv_id=' . $gvId . '&loai=' . ($_POST['loai'] ?? 'co_so_nganh')
            : 'lanh_dao/duyet_de_tai.php?loai=' . ($_POST['loai'] ?? 'co_so_nganh');
        redirect($redirectPage);
    }
    
    // Xử lý nhiều đề tài (nếu có dấu phẩy)
    if (strpos($deTaiIds, ',') !== false) {
        $ids = explode(',', $deTaiIds);
        $successCount = 0;
        $errorCount = 0;
        
        foreach ($ids as $id) {
            $deTaiId = (int)$id;
            if ($deTaiId <= 0) continue;
            
            $result = $deTaiModel->tuChoiDeTai($deTaiId, $user['id'], $lyDo);
            
            if ($result['success']) {
                $successCount++;
                // Lấy thông tin để gửi thông báo
                $deTai = $deTaiModel->findById($deTaiId);
                // Gửi thông báo cho giảng viên
                $thongBaoModel->thongBaoDeTaiBiTuChoi($deTai['giang_vien_id'], $deTai['tieu_de'], $lyDo);
            } else {
                $errorCount++;
            }
        }
        
        if ($successCount > 0) {
            setFlashMessage('success', "Đã từ chối thành công {$successCount} đề tài");
        }
        if ($errorCount > 0) {
            setFlashMessage('warning', "Có {$errorCount} đề tài không thể từ chối");
        }
    } else {
        // Xử lý một đề tài
        $deTaiId = (int)$deTaiIds;
        
        // Lấy thông tin đề tài trước khi từ chối
        $sql = "SELECT dt.*, gv.ma_giang_vien, nd.ho_ten as ten_giang_vien
                FROM de_tai dt
                JOIN giang_vien gv ON dt.giang_vien_id = gv.id
                JOIN nguoi_dung nd ON gv.nguoi_dung_id = nd.id
                WHERE dt.id = :id";
        $deTai = $deTaiModel->queryOne($sql, ['id' => $deTaiId]);
        
        $result = $deTaiModel->tuChoiDeTai($deTaiId, $user['id'], $lyDo);
        
        if ($result['success']) {
            // Gửi thông báo cho giảng viên
            $thongBaoModel->thongBaoDeTaiBiTuChoi($deTai['giang_vien_id'], $deTai['tieu_de'], $lyDo);
            
            // Tạo thông báo chi tiết
            $loaiDeTai = $deTai['he_dao_tao'] === 'co_so_nganh' ? 'Cơ sở ngành' : 'Chuyên ngành';
            $tenGV = $deTai['ten_giang_vien'] ?? 'Giảng viên';
            setFlashMessage('success', "Từ chối đề tài {$loaiDeTai} thành công cho {$tenGV}");
        } else {
            setFlashMessage('error', $result['message']);
        }
    }
    
    // Kiểm tra redirect
    $redirectPage = $redirectParam === 'dashboard' 
        ? 'lanh_dao/dashboard.php?gv_id=' . $gvId . '&loai=' . ($_POST['loai'] ?? 'co_so_nganh')
        : 'lanh_dao/duyet_de_tai.php?loai=' . ($_POST['loai'] ?? 'co_so_nganh');
    redirect($redirectPage);
}

// Nếu không có action hợp lệ
redirect('lanh_dao/duyet_de_tai.php?loai=' . ($_GET['loai'] ?? $_POST['loai'] ?? 'co_so_nganh'));
