<?php
/**
 * IMPORT GIẢNG VIÊN TỪ FILE CSV/EXCEL
 * Cho phép lãnh đạo import không cần đăng nhập
 */

require_once '../bootstrap.php';

$error = getFlashMessage('error');
$success = getFlashMessage('success');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_FILES['file'])) {
    $file = $_FILES['file'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error = 'Lỗi upload file';
    } else {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['csv', 'xlsx', 'xls'])) {
            $error = 'Chỉ chấp nhận file CSV hoặc Excel';
        } else {
            $db = Database::getInstance();
            $conn = $db->getConnection();

            $imported = 0;
            $errors = [];

            function readExcelFile($filePath)
            {
                $zip = new ZipArchive();
                if ($zip->open($filePath) !== true)
                    return null;

                $sharedStrings = [];
                if (($index = $zip->locateName('xl/sharedStrings.xml')) !== false) {
                    $xml = simplexml_load_string($zip->getFromIndex($index));
                    foreach ($xml->si as $si) {
                        $sharedStrings[] = (string) $si->t;
                    }
                }

                $sheetData = [];
                if (($index = $zip->locateName('xl/worksheets/sheet1.xml')) !== false) {
                    $xml = simplexml_load_string($zip->getFromIndex($index));
                    $rows = $xml->sheetData->row;

                    $header = [];
                    foreach ($rows[0]->c as $cell) {
                        $attr = $cell->attributes();
                        $cellId = (string) $attr['r'];
                        preg_match('/^([A-Z]+)/', $cellId, $match);
                        $col = $match[1];
                        $val = isset($cell->v) ? (string) $cell->v : (isset($cell->is->t) ? $sharedStrings[(int) $cell->is->t] : '');
                        $header[$col] = $val;
                    }

                    for ($i = 1; $i < count($rows); $i++) {
                        $rowData = [];
                        foreach ($rows[$i]->c as $cell) {
                            $attr = $cell->attributes();
                            $cellId = (string) $attr['r'];
                            preg_match('/^([A-Z]+)/', $cellId, $match);
                            $col = $match[1];
                            if (isset($cell->v)) {
                                $val = (string) $cell->v;
                            } elseif (isset($cell->is->t)) {
                                $val = $sharedStrings[(int) $cell->is->t];
                            } else {
                                $val = '';
                            }
                            $rowData[$col] = $val;
                        }
                        $sheetData[] = array_combine($header, $rowData);
                    }
                }

                $zip->close();
                return $sheetData;
            }

            $rows = [];
            if ($ext === 'csv') {
                $handle = fopen($file['tmp_name'], 'r');
                $header = fgetcsv($handle);

                while (($row = fgetcsv($handle)) !== false) {
                    $rows[] = array_combine($header, $row);
                }
                fclose($handle);
            } elseif (in_array($ext, ['xlsx', 'xls'])) {
                $rows = readExcelFile($file['tmp_name']);
            }

            foreach ($rows as $data) {
                $maGV = trim($data['ma_giang_vien'] ?? '');
                $hoTen = trim($data['ho_ten'] ?? '');
                $email = trim($data['email'] ?? '');
                $khoa = trim($data['khoa'] ?? '');
                $chuyenMon = trim($data['chuyen_mon'] ?? '');
                $hocHamVi = trim($data['hoc_ham_hoc_vi'] ?? '');
                $soDienThoai = trim($data['so_dien_thoai'] ?? '');
                $matKhau = $data['mat_khau'] ?? $maGV;
                $vaiTroInput = trim($data['vai_tro'] ?? 'giang_vien');

                if (empty($maGV) || empty($hoTen) || empty($email)) {
                    $errors[] = "Thiếu thông tin: $maGV";
                    continue;
                }

                try {
                    $check = $conn->prepare("SELECT id FROM nguoi_dung WHERE email = ?");
                    $check->execute([$email]);
                    if ($check->fetch()) {
                        $errors[] = "Email đã tồn tại: $email";
                        continue;
                    }

                    $checkGV = $conn->prepare("SELECT id FROM giang_vien WHERE ma_giang_vien = ?");
                    $checkGV->execute([$maGV]);
                    if ($checkGV->fetch()) {
                        $errors[] = "Mã GV đã tồn tại: $maGV";
                        continue;
                    }

                    $passwordHash = password_hash($matKhau, PASSWORD_DEFAULT);

                    $vaiTroStmt = $conn->prepare("SELECT id FROM vai_tro WHERE ma_vai_tro = ?");
                    $vaiTroStmt->execute([$vaiTroInput ?: 'giang_vien']);
                    $vaiTro = $vaiTroStmt->fetch();
                    if (!$vaiTro) {
                        $errors[] = "Vai trò không hợp lệ: $vaiTroInput (dòng $maGV)";
                        continue;
                    }
                    $vaiTroId = $vaiTro['id'];

                    $conn->beginTransaction();

                    $stmt = $conn->prepare("INSERT INTO nguoi_dung (ho_ten, email, mat_khau, vai_tro_id, trang_thai, created_at) VALUES (?, ?, ?, ?, 'active', NOW())");
                    $stmt->execute([$hoTen, $email, $passwordHash, $vaiTroId]);
                    $userId = $conn->lastInsertId();

                    $stmt2 = $conn->prepare("INSERT INTO giang_vien (nguoi_dung_id, ma_giang_vien, hoc_ham_hoc_vi, khoa, chuyen_mon, so_dien_thoai, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
                    $stmt2->execute([$userId, $maGV, $hocHamVi, $khoa, $chuyenMon, $soDienThoai]);

                    $conn->commit();
                    $imported++;

                } catch (Exception $e) {
                    $conn->rollBack();
                    $errors[] = "Lỗi import $maGV: " . $e->getMessage();
                }
            }

            if ($imported > 0) {
                $success = "Đã import $imported giảng viên";
            }
            if (!empty($errors)) {
                $error = implode('<br>', $errors);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Import giảng viên</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>

<body>
    <div class="container py-5">
        <div class="card">
            <div class="card-header">
                <h4><i class="bi bi-upload"></i> Import giảng viên</h4>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success"><?= $success ?></div>
                <?php endif; ?>
                <div class="alert alert-info">
                    <h6>Hướng dẫn format file:</h6>
                    <p class="mb-2">File CSV/Excel cần có các cột sau (tên cột phải chính xác):</p>
                    <ul class="mb-0">
                        <li><strong>ma_giang_vien</strong> (bắt buộc): Mã giảng viên</li>
                        <li><strong>ho_ten</strong> (bắt buộc): Họ và tên</li>
                        <li><strong>email</strong> (bắt buộc): Email</li>
                        <li><strong>khoa</strong> (tùy chọn): Khoa</li>
                        <li><strong>chuyen_mon</strong> (tùy chọn): Chuyên môn</li>
                        <li><strong>hoc_ham_hoc_vi</strong> (tùy chọn): Học hàm / Học vị</li>
                        <li><strong>so_dien_thoai</strong> (tùy chọn): Số điện thoại</li>
                        <li><strong>mat_khau</strong> (tùy chọn): Mật khẩu (nếu không có sẽ dùng mã giảng viên)</li>
                        <li><strong>vai_tro</strong> (tùy chọn): Vai trò (vd: <code>giang_vien</code>,
                            <code>lanh_dao</code> - mặc định: <code>giang_vien</code>)</li>
                    </ul>
                </div>

                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <input type="file" name="file" class="form-control" accept=".csv,.xlsx,.xls" required>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-upload"></i> Import
                    </button>
                    <?php
                    // Kiểm tra nếu là lãnh đạo thì quay về dashboard lãnh đạo
                    $backUrl = 'register.php';
                    if (isLoggedIn()) {
                        $currentUser = getCurrentUser();
                        if ($currentUser['vai_tro'] === ROLE_LANH_DAO) {
                            $backUrl = '../lanh_dao/quan_ly_nguoi_dung.php';
                        }
                    }
                    ?>
                    <a href="<?= $backUrl ?>" class="btn btn-secondary">Quay lại</a>
                </form>
            </div>
        </div>
    </div>
</body>

</html>