<?php
/**
 * SỬA NHANH NGÀY THÔNG BÁO
 */

require_once '../bootstrap.php';

$db = Database::getInstance()->getConnection();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'update_dates') {
            $id = (int)$_POST['id'];
            $ngayBatDau = $_POST['ngay_bat_dau'];
            $ngayKetThuc = $_POST['ngay_ket_thuc'];
            
            $sql = "UPDATE thong_bao SET ngay_bat_dau = ?, ngay_ket_thuc = ? WHERE id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$ngayBatDau, $ngayKetThuc, $id]);
            
            $message = "✅ Đã cập nhật ngày cho thông báo #{$id}";
        } elseif ($_POST['action'] === 'clear_dates') {
            $id = (int)$_POST['id'];
            
            $sql = "UPDATE thong_bao SET ngay_bat_dau = NULL, ngay_ket_thuc = NULL WHERE id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$id]);
            
            $message = "✅ Đã xóa ngày cho thông báo #{$id} (hiển thị vĩnh viễn)";
        } elseif ($_POST['action'] === 'set_today') {
            $id = (int)$_POST['id'];
            $today = date('Y-m-d');
            $nextMonth = date('Y-m-d', strtotime('+1 month'));
            
            $sql = "UPDATE thong_bao SET ngay_bat_dau = ?, ngay_ket_thuc = ? WHERE id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$today, $nextMonth, $id]);
            
            $message = "✅ Đã đặt ngày bắt đầu = hôm nay, kết thúc = +1 tháng cho thông báo #{$id}";
        }
    }
}

// Lấy tất cả thông báo
$sql = "SELECT * FROM thong_bao WHERE nguoi_nhan_id IS NULL ORDER BY created_at DESC";
$stmt = $db->query($sql);
$allNotifications = $stmt->fetchAll();

$today = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa Ngày Thông Báo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .container {
            max-width: 1200px;
            background: white;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }
        .notification-card {
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            background: #f8f9fa;
        }
        .notification-card.active {
            border-color: #10b981;
            background: #f0fdf4;
        }
        .notification-card.inactive {
            border-color: #ef4444;
            background: #fef2f2;
        }
        .btn-action {
            margin: 5px;
        }
        .date-info {
            background: white;
            padding: 10px;
            border-radius: 8px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mb-4">
            <i class="bi bi-calendar-event"></i> Sửa Nhanh Ngày Thông Báo
        </h1>
        
        <div class="alert alert-info">
            <strong>Ngày hôm nay:</strong> <?= date('d/m/Y', strtotime($today)) ?> (<?= $today ?>)
        </div>
        
        <?php if ($message): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= $message ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <div class="mb-3">
            <a href="index.php" class="btn btn-primary">
                <i class="bi bi-house"></i> Trang chủ
            </a>
            <a href="lanh_dao/quan_ly_thong_bao.php" class="btn btn-secondary">
                <i class="bi bi-megaphone"></i> Quản lý thông báo
            </a>
        </div>
        
        <hr>
        
        <?php foreach ($allNotifications as $tb): ?>
            <?php
            $isActive = true;
            $reasons = [];
            
            if ($tb['trang_thai'] !== 'mo') {
                $isActive = false;
                $reasons[] = "Trạng thái: {$tb['trang_thai']}";
            }
            
            if (!empty($tb['ngay_bat_dau']) && $tb['ngay_bat_dau'] > $today) {
                $isActive = false;
                $reasons[] = "Chưa đến ngày bắt đầu";
            }
            
            if (!empty($tb['ngay_ket_thuc']) && $tb['ngay_ket_thuc'] < $today) {
                $isActive = false;
                $reasons[] = "Đã hết hạn";
            }
            
            $cardClass = $isActive ? 'active' : 'inactive';
            $statusIcon = $isActive ? 'bi-check-circle-fill text-success' : 'bi-x-circle-fill text-danger';
            ?>
            
            <div class="notification-card <?= $cardClass ?>">
                <div class="row">
                    <div class="col-md-8">
                        <h4>
                            <i class="bi <?= $statusIcon ?>"></i>
                            #<?= $tb['id'] ?>: <?= htmlspecialchars($tb['tieu_de']) ?>
                        </h4>
                        
                        <div class="date-info">
                            <strong>Ngày bắt đầu:</strong> 
                            <?php if ($tb['ngay_bat_dau']): ?>
                                <span class="badge bg-primary"><?= date('d/m/Y', strtotime($tb['ngay_bat_dau'])) ?></span>
                                <?php if ($tb['ngay_bat_dau'] > $today): ?>
                                    <span class="badge bg-danger">Chưa đến</span>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="badge bg-secondary">Không giới hạn</span>
                            <?php endif; ?>
                            
                            <br>
                            
                            <strong>Ngày kết thúc:</strong> 
                            <?php if ($tb['ngay_ket_thuc']): ?>
                                <span class="badge bg-warning"><?= date('d/m/Y', strtotime($tb['ngay_ket_thuc'])) ?></span>
                                <?php if ($tb['ngay_ket_thuc'] < $today): ?>
                                    <span class="badge bg-danger">Đã hết hạn</span>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="badge bg-secondary">Không giới hạn</span>
                            <?php endif; ?>
                        </div>
                        
                        <?php if (!$isActive): ?>
                            <div class="alert alert-warning mt-2 mb-0">
                                <strong>Không hiển thị vì:</strong> <?= implode(', ', $reasons) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="col-md-4">
                        <h6>Thao tác nhanh:</h6>
                        
                        <!-- Đặt ngày = hôm nay -->
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="set_today">
                            <input type="hidden" name="id" value="<?= $tb['id'] ?>">
                            <button type="submit" class="btn btn-success btn-sm btn-action">
                                <i class="bi bi-calendar-check"></i> Đặt hôm nay → +1 tháng
                            </button>
                        </form>
                        
                        <!-- Xóa ngày (hiển thị vĩnh viễn) -->
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="clear_dates">
                            <input type="hidden" name="id" value="<?= $tb['id'] ?>">
                            <button type="submit" class="btn btn-info btn-sm btn-action">
                                <i class="bi bi-infinity"></i> Hiển thị vĩnh viễn
                            </button>
                        </form>
                        
                        <!-- Form tùy chỉnh -->
                        <button class="btn btn-warning btn-sm btn-action" type="button" 
                                data-bs-toggle="collapse" data-bs-target="#form<?= $tb['id'] ?>">
                            <i class="bi bi-pencil"></i> Tùy chỉnh
                        </button>
                        
                        <div class="collapse mt-3" id="form<?= $tb['id'] ?>">
                            <form method="POST" class="border p-3 rounded bg-white">
                                <input type="hidden" name="action" value="update_dates">
                                <input type="hidden" name="id" value="<?= $tb['id'] ?>">
                                
                                <div class="mb-2">
                                    <label class="form-label">Ngày bắt đầu:</label>
                                    <input type="date" name="ngay_bat_dau" class="form-control form-control-sm" 
                                           value="<?= $tb['ngay_bat_dau'] ?>">
                                </div>
                                
                                <div class="mb-2">
                                    <label class="form-label">Ngày kết thúc:</label>
                                    <input type="date" name="ngay_ket_thuc" class="form-control form-control-sm" 
                                           value="<?= $tb['ngay_ket_thuc'] ?>">
                                </div>
                                
                                <button type="submit" class="btn btn-primary btn-sm w-100">
                                    <i class="bi bi-save"></i> Lưu
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
