<?php
/**
 * XUẤT DANH SÁCH
 */

require_once '../bootstrap.php';
requireRole(ROLE_LANH_DAO);

$user = getCurrentUser();
$pageTitle = 'Xuất danh sách - Lãnh đạo';

$dangKyModel = new DangKyDeTaiModel();
$deTaiModel = new DeTaiModel();
$giangVienModel = new GiangVienModel();
$caiDatModel = new CaiDatModel();

// Lấy thông tin kế hoạch từ cài đặt
$settings = $caiDatModel->getAllSettings();
$soKeHoach = $settings['so_ke_hoach']['value'] ?? '...';
$ngayKeHoach = $settings['ngay_ke_hoach']['value'] ?? '';
$ngayKeHoachFormatted = $ngayKeHoach ? date('d/m/Y', strtotime($ngayKeHoach)) : '../../....';

// Xử lý loại đề tài - mặc định là CSN
$loaiDeTai = isset($_GET['loai']) ? trim($_GET['loai']) : 'co_so_nganh';
if (!in_array($loaiDeTai, ['co_so_nganh', 'chuyen_nganh'])) {
    $loaiDeTai = 'co_so_nganh';
}

// Lấy tham số lọc
$heDaoTao = $loaiDeTai; // Sử dụng loại đề tài từ tab
$giangVienId = $_GET['giang_vien_id'] ?? '';
$lop = $_GET['lop'] ?? '';
$tuNgay = $_GET['tu_ngay'] ?? '';
$denNgay = $_GET['den_ngay'] ?? '';
$search = $_GET['search'] ?? '';

// Lấy dữ liệu danh sách phân công chấm
$sql = "SELECT dk.*, sv.ma_sinh_vien, sv.lop, nd_sv.ho_ten as ten_sinh_vien,
               dt.tieu_de as ten_de_tai, dt.he_dao_tao,
               nd_gv.ho_ten as ten_giang_vien, gv.ma_giang_vien, gv.hoc_ham_hoc_vi,
               dk.ghi_chu
        FROM dang_ky_de_tai dk
        JOIN sinh_vien sv ON dk.sinh_vien_id = sv.id
        JOIN nguoi_dung nd_sv ON sv.nguoi_dung_id = nd_sv.id
        JOIN de_tai dt ON dk.de_tai_id = dt.id
        JOIN giang_vien gv ON dt.giang_vien_id = gv.id
        JOIN nguoi_dung nd_gv ON gv.nguoi_dung_id = nd_gv.id
        WHERE dk.trang_thai = 'da_duyet'
        AND dt.he_dao_tao = :he_dao_tao";
$params = ['he_dao_tao' => $heDaoTao];
if ($giangVienId) {
    $sql .= " AND dt.giang_vien_id = :giang_vien_id";
    $params['giang_vien_id'] = $giangVienId;
}
if ($lop) {
    $sql .= " AND sv.lop LIKE :lop";
    $params['lop'] = "%$lop%";
}
$sql .= " ORDER BY nd_gv.ho_ten ASC, sv.lop ASC, dk.ngay_dang_ky DESC";
$danhSachBaoCao = $dangKyModel->query($sql, $params);

// Tính thống kê nhanh
$thongKe = [
    'tong_sv' => count($danhSachBaoCao),
    'sv_csn' => 0,
    'sv_cn' => 0,
    'chua_co_de_tai' => 0
];

// Đếm tổng số theo loại (lấy từ toàn bộ dữ liệu)
$sqlTongCSN = "SELECT COUNT(*) as total FROM dang_ky_de_tai dk
               JOIN de_tai dt ON dk.de_tai_id = dt.id
               WHERE dk.trang_thai = 'da_duyet' AND dt.he_dao_tao = 'co_so_nganh'";
$resultCSN = $dangKyModel->queryOne($sqlTongCSN);
$tongCSN = $resultCSN['total'] ?? 0;

$sqlTongCN = "SELECT COUNT(*) as total FROM dang_ky_de_tai dk
              JOIN de_tai dt ON dk.de_tai_id = dt.id
              WHERE dk.trang_thai = 'da_duyet' AND dt.he_dao_tao = 'chuyen_nganh'";
$resultCN = $dangKyModel->queryOne($sqlTongCN);
$tongCN = $resultCN['total'] ?? 0;

// Lấy danh sách giảng viên cho dropdown
$danhSachGiangVien = $giangVienModel->getAllWithStats();

// Lấy chế độ xuất (1: chỉ GV hướng dẫn, 2: cả GV hướng dẫn và GV chấm)
$exportMode = isset($_GET['mode']) ? (int)$_GET['mode'] : 2;

// Lấy tùy chọn sắp xếp cho Mode 2 (gvhd: theo GV hướng dẫn, gvcham: theo GV chấm)
$sortBy = isset($_GET['sort']) ? $_GET['sort'] : 'gvcham';

// Xử lý xuất Excel
if (isset($_GET['export']) && $_GET['export'] === 'excel') {
    $tenFile = 'Danh_sach_phan_cong_' . date('Y-m-d') . '.xls';
    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $tenFile . '"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    echo "\xEF\xBB\xBF"; // UTF-8 BOM
    
    $heLabel = '';
    if ($heDaoTao === 'co_so_nganh') {
        $heLabel = 'CƠ SỞ NGÀNH';
    } elseif ($heDaoTao === 'chuyen_nganh') {
        $heLabel = 'CHUYÊN NGÀNH';
    }
    
    // Số cột tùy theo chế độ xuất
    $totalCols = $exportMode == 2 ? 8 : 7;
    
    echo "<table border='0' style='width: 100%;'>";
    echo "<tr><td colspan='" . $totalCols . "' style='text-align: center; font-size: 16pt; font-weight: bold; padding: 5px;'>";
    echo $exportMode == 1 ? "DANH SÁCH PHÂN CÔNG" : "DANH SÁCH PHÂN CÔNG CHẤM";
    echo "</td></tr>";
    
    echo "<tr><td colspan='" . $totalCols . "' style='text-align: center; font-size: 14pt; font-weight: bold; padding: 5px;'>";
    echo "BÁO CÁO THỰC TẬP ĐỒ ÁN " . $heLabel;
    echo "</td></tr>";
    
    echo "<tr><td colspan='" . $totalCols . "' style='text-align: center; font-size: 10pt; font-style: italic; padding: 5px;'>";
    echo "(Đính kèm Kế hoạch số " . htmlspecialchars($soKeHoach) . "/KH-KT&CN ngày " . htmlspecialchars($ngayKeHoachFormatted) . ")";
    echo "</td></tr>";
    
    // Thông tin bộ lọc
    $filterInfo = [];
    if ($giangVienId) {
        foreach ($danhSachGiangVien as $gv) {
            if ($gv['id'] == $giangVienId) {
                $filterInfo[] = "Giảng viên: " . $gv['ho_ten'];
                break;
            }
        }
    }
    if ($lop) {
        $filterInfo[] = "Lớp: " . $lop;
    }
    if ($tuNgay) {
        $filterInfo[] = "Từ ngày: " . date('d/m/Y', strtotime($tuNgay));
    }
    if ($denNgay) {
        $filterInfo[] = "Đến ngày: " . date('d/m/Y', strtotime($denNgay));
    }
    
    if (!empty($filterInfo)) {
        echo "<tr><td colspan='" . $totalCols . "' style='text-align: center; font-style: italic; padding: 5px;'>";
        echo "(" . implode(" - ", $filterInfo) . ")";
        echo "</td></tr>";
    }
    
    // Bảng dữ liệu với border
    echo "<table border='1' style='border-collapse: collapse; width: 100%; border: 2px solid black;'>";
    echo "<tr style='background-color: #D3D3D3; color: black; font-weight: bold; text-align: center;'>";
    echo "<th style='border: 2px solid black; padding: 8px;'>STT</th>";
    echo "<th style='border: 2px solid black; padding: 8px;'>MSSV</th>";
    echo "<th style='border: 2px solid black; padding: 8px;'>Họ và tên sinh viên</th>";
    echo "<th style='border: 2px solid black; padding: 8px;'>Mã lớp</th>";
    echo "<th style='border: 2px solid black; padding: 8px;'>Tên đề tài</th>";
    echo "<th style='border: 2px solid black; padding: 8px;'>Giảng viên hướng dẫn</th>";
    if ($exportMode == 2) {
        echo "<th style='border: 2px solid black; padding: 8px;'>Giảng viên chấm</th>";
    }
    echo "<th style='border: 2px solid black; padding: 8px;'>Ghi chú</th>";
    echo "</tr>";
    
    // Nhóm dữ liệu theo giảng viên
    $nhomTheoGiangVien = [];
    $db = Database::getInstance()->getConnection();
    
    if ($exportMode == 2) {
        // Mode 2: Có 2 tùy chọn sắp xếp
        if ($sortBy === 'gvhd') {
            // Sắp xếp theo giảng viên hướng dẫn
            foreach ($danhSachBaoCao as $item) {
                $key = $item['ten_giang_vien'];
                if (!isset($nhomTheoGiangVien[$key])) {
                    $nhomTheoGiangVien[$key] = [
                        'giang_vien' => $item['ten_giang_vien'],
                        'hoc_ham_hoc_vi' => $item['hoc_ham_hoc_vi'] ?? '',
                        'de_tai' => []
                    ];
                }
                $nhomTheoGiangVien[$key]['de_tai'][] = $item;
            }
        } else {
            // Sắp xếp theo giảng viên chấm (mặc định)
            foreach ($danhSachBaoCao as $item) {
                // Lấy thông tin giảng viên chấm
                $stmt = $db->prepare("SELECT nd.ho_ten, gv.hoc_ham_hoc_vi
                                     FROM dang_ky_de_tai dk
                                     LEFT JOIN giang_vien gv ON dk.giang_vien_cham_id = gv.id
                                     LEFT JOIN nguoi_dung nd ON gv.nguoi_dung_id = nd.id
                                     WHERE dk.id = :dang_ky_id");
                $stmt->execute(['dang_ky_id' => $item['id']]);
                $gvCham = $stmt->fetch();
                
                $key = $gvCham && $gvCham['ho_ten'] ? $gvCham['ho_ten'] : 'Chưa phân công';
                
                if (!isset($nhomTheoGiangVien[$key])) {
                    $nhomTheoGiangVien[$key] = [
                        'giang_vien' => $key,
                        'hoc_ham_hoc_vi' => $gvCham['hoc_ham_hoc_vi'] ?? '',
                        'de_tai' => []
                    ];
                }
                $nhomTheoGiangVien[$key]['de_tai'][] = $item;
            }
        }
    } else {
        // Mode 1: Nhóm theo giảng viên hướng dẫn
        foreach ($danhSachBaoCao as $item) {
            $key = $item['ten_giang_vien'];
            if (!isset($nhomTheoGiangVien[$key])) {
                $nhomTheoGiangVien[$key] = [
                    'giang_vien' => $item['ten_giang_vien'],
                    'hoc_ham_hoc_vi' => $item['hoc_ham_hoc_vi'] ?? '',
                    'de_tai' => []
                ];
            }
            $nhomTheoGiangVien[$key]['de_tai'][] = $item;
        }
    }
    
    $stt = 1;
    foreach ($nhomTheoGiangVien as $nhom) {
        $soLuongDeTai = count($nhom['de_tai']);
        
        // Đếm số lượng đề tài đã được phân công giảng viên chấm
        $soLuongDaPhanCong = 0;
        foreach ($nhom['de_tai'] as $item) {
            $stmtCheck = $db->prepare("SELECT giang_vien_cham_id FROM dang_ky_de_tai WHERE id = :dang_ky_id");
            $stmtCheck->execute(['dang_ky_id' => $item['id']]);
            $checkResult = $stmtCheck->fetch();
            if ($checkResult && !empty($checkResult['giang_vien_cham_id'])) {
                $soLuongDaPhanCong++;
            }
        }
        
        // Hiển thị học hàm/học vị + tên giảng viên (cho cột nhóm)
        $gvNhom = '';
        if (!empty($nhom['hoc_ham_hoc_vi'])) {
            $gvNhom = htmlspecialchars($nhom['hoc_ham_hoc_vi']) . '. ';
        }
        $gvNhom .= htmlspecialchars($nhom['giang_vien']);
        
        foreach ($nhom['de_tai'] as $idx => $item) {
            // Lấy thông tin giảng viên hướng dẫn
            $gvhd = '';
            if (!empty($item['hoc_ham_hoc_vi'])) {
                $gvhd = htmlspecialchars($item['hoc_ham_hoc_vi']) . '. ';
            }
            $gvhd .= htmlspecialchars($item['ten_giang_vien']);
            
            // Lấy thông tin giảng viên chấm
            $stmt = $db->prepare("SELECT nd.ho_ten, gv.hoc_ham_hoc_vi
                                 FROM dang_ky_de_tai dk
                                 LEFT JOIN giang_vien gv ON dk.giang_vien_cham_id = gv.id
                                 LEFT JOIN nguoi_dung nd ON gv.nguoi_dung_id = nd.id
                                 WHERE dk.id = :dang_ky_id");
            $stmt->execute(['dang_ky_id' => $item['id']]);
            $gvCham = $stmt->fetch();
            
            $tenGVCham = '';
            if ($gvCham && $gvCham['ho_ten']) {
                if (!empty($gvCham['hoc_ham_hoc_vi'])) {
                    $tenGVCham = htmlspecialchars($gvCham['hoc_ham_hoc_vi']) . '. ';
                }
                $tenGVCham .= htmlspecialchars($gvCham['ho_ten']);
            }
            
            echo "<tr>";
            echo "<td style='border-top: 2px solid black; border-bottom: 2px solid black; border-left: 2px solid black; border-right: 2px solid black; padding: 5px; text-align: center;'>" . $stt . "</td>";
            echo "<td style='border-top: 2px solid black; border-bottom: 2px solid black; border-left: 2px solid black; border-right: 2px solid black; padding: 5px;'>" . htmlspecialchars($item['ma_sinh_vien']) . "</td>";
            echo "<td style='border-top: 2px solid black; border-bottom: 2px solid black; border-left: 2px solid black; border-right: 2px solid black; padding: 5px;'>" . htmlspecialchars($item['ten_sinh_vien']) . "</td>";
            echo "<td style='border-top: 2px solid black; border-bottom: 2px solid black; border-left: 2px solid black; border-right: 2px solid black; padding: 5px;'>" . htmlspecialchars($item['lop']) . "</td>";
            echo "<td style='border-top: 2px solid black; border-bottom: 2px solid black; border-left: 2px solid black; border-right: 2px solid black; padding: 5px;'>" . htmlspecialchars($item['ten_de_tai']) . "</td>";
            
            if ($exportMode == 2) {
                // Mode 2: Hiển thị cả 2 cột
                // Cột được nhóm (rowspan) phụ thuộc vào sortBy
                if ($sortBy === 'gvhd') {
                    // Nhóm theo GV hướng dẫn
                    if ($idx === 0) {
                        echo "<td style='border-top: 2px solid black; border-bottom: 2px solid black; border-left: 2px solid black; border-right: 2px solid black; padding: 5px; vertical-align: middle;' rowspan='" . $soLuongDeTai . "'>" . $gvNhom . "</td>";
                    }
                    echo "<td style='border-top: 2px solid black; border-bottom: 2px solid black; border-left: 2px solid black; border-right: 2px solid black; padding: 5px;'>" . ($tenGVCham ?: '') . "</td>";
                } else {
                    // Nhóm theo GV chấm
                    echo "<td style='border-top: 2px solid black; border-bottom: 2px solid black; border-left: 2px solid black; border-right: 2px solid black; padding: 5px;'>" . $gvhd . "</td>";
                    if ($idx === 0) {
                        echo "<td style='border-top: 2px solid black; border-bottom: 2px solid black; border-left: 2px solid black; border-right: 2px solid black; padding: 5px; vertical-align: middle;' rowspan='" . $soLuongDeTai . "'>" . $gvNhom . "</td>";
                    }
                }
            } else {
                // Mode 1: Chỉ có cột GV hướng dẫn
                if ($idx === 0) {
                    echo "<td style='border-top: 2px solid black; border-bottom: 2px solid black; border-left: 2px solid black; border-right: 2px solid black; padding: 5px; vertical-align: middle;' rowspan='" . $soLuongDeTai . "'>" . $gvNhom . "</td>";
                }
            }
            
            // Ghi chú - hiển thị ở dòng đầu tiên
            if ($idx === 0) {
                echo "<td style='border-top: 2px solid black; border-bottom: 2px solid black; border-left: 2px solid black; border-right: 2px solid black; padding: 5px; text-align: center; vertical-align: middle;' rowspan='" . $soLuongDeTai . "'>" . $soLuongDaPhanCong . "</td>";
            }
            
            echo "</tr>";
            $stt++;
        }
    }
    
    echo "</table>";
    exit();
}

// Xử lý xuất PDF - Tạo trang HTML để in
if (isset($_GET['export']) && $_GET['export'] === 'pdf') {
    $heLabel = '';
    if ($heDaoTao === 'co_so_nganh') {
        $heLabel = 'CƠ SỞ NGÀNH';
    } elseif ($heDaoTao === 'chuyen_nganh') {
        $heLabel = 'CHUYÊN NGÀNH';
    }
    ?>
    <!DOCTYPE html>
    <html lang="vi">
    <head>
        <meta charset="UTF-8">
        <title></title>
      <style>
    @page { 
        size: A4 landscape; 
        margin: 10mm 15mm;
    }

    body { 
        font-family: 'Times New Roman', Times, serif; 
        font-size: 10pt; 
    }

    h2 { 
        text-align: center; 
        margin-bottom: 3px; 
        text-transform: uppercase; 
        font-size: 14pt; 
        font-weight: bold; 
    }

    .phu-de { 
        text-align: center; 
        font-size: 13pt; 
        margin-bottom: 3px; 
        font-weight: bold; 
    }

    .dinh-kem, .subtitle, .date { 
        text-align: center; 
        font-size: 9pt; 
        margin-bottom: 5px; 
        font-style: italic; 
    }

    .date { margin-bottom: 15px; }

    table { 
        width: 100%; 
        border-collapse: collapse; 
        font-size: 9pt; 
    }

    th, td { 
        border: 1px solid #000; 
        padding: 4px; 
        text-align: left; 
    }

    th { 
        background-color: #fff; 
        color: #000; 
        font-weight: bold; 
        text-align: center; 
    }

    /* Ẩn khi in */
    @media print { 
        .no-print { display: none; }

        th { 
            background-color: #fff !important; 
            color: #000 !important; 
            -webkit-print-color-adjust: exact; 
            print-color-adjust: exact; 
        }
        
        /* Ẩn header và footer mặc định của trình duyệt */
        @page {
            margin: 10mm 15mm;
        }
        
        body {
            margin: 0;
            padding-top: 0 !important;
        }
        
        /* Ẩn navbar, sidebar và các phần tử không cần thiết khi in */
        .navbar,
        .sidebar,
        .col-md-2,
        nav,
        .flash-messages-container,
        .alert {
            display: none !important;
        }
        
        /* Main content chiếm toàn bộ chiều rộng khi in */
        .col-md-10 {
            width: 100% !important;
            margin-left: 0 !important;
            padding: 0 !important;
        }
        
        .container-fluid {
            margin-left: 0 !important;
            padding: 0 !important;
        }
    }

    .no-print { 
        margin: 20px; 
        text-align: center; 
    }
</style>

<div class="no-print">
    <button onclick="window.history.back()" class="btn-back">
        <i class="bi bi-chevron-double-left"></i> Quay lại
    </button>

    <button onclick="window.print()" class="btn-print">
        <i class="bi bi-printer"></i> In PDF
    </button>
</div>

<style>
    .btn-back {
        padding: 10px 15px;
        font-size: 14px;
        cursor: pointer;
        margin-right: 10px;
        background-color: #0088ff;
        color: white;
        border: none;
        border-radius: 5px;
    }

    .btn-print {
        padding: 10px 15px;
        font-size: 14px;
        cursor: pointer;
        background-color: #dc3545;
        color: white;
        border: none;
        border-radius: 5px;
    }

    .btn-back:hover { background-color: #0077dd; }
    .btn-print:hover { background-color: #bb2d3b; }
</style>
        
        <h2><?= $exportMode == 1 ? 'DANH SÁCH PHÂN CÔNG' : 'DANH SÁCH PHÂN CÔNG CHẤM' ?></h2>
        <p class="phu-de">BÁO CÁO THỰC TẬP ĐỒ ÁN <?= $heLabel ?></p>
        <p class="dinh-kem">(Đính kèm Kế hoạch số <?= htmlspecialchars($soKeHoach) ?>/KH-KT&CN ngày <?= htmlspecialchars($ngayKeHoachFormatted) ?>)</p>
        
        <table>
            <thead>
                <tr>
                    <th style='width: 4%;'>STT</th>
                    <th style='width: 5%;'>MSSV</th>
                    <th style='width: 15%;'>Họ và tên sinh viên</th>
                    <th style='width: 5%;'>Mã lớp</th>
                    <th style='width: <?= $exportMode == 2 ? '30%' : '35%' ?>;'>Tên đề tài</th>
                    <th style='width: <?= $exportMode == 2 ? '15%' : '20%' ?>;'>Giảng viên hướng dẫn</th>
                    <?php if ($exportMode == 2): ?>
                    <th style='width: 15%;'>Giảng viên chấm</th>
                    <?php endif; ?>
                    <th style='width: 5%;'>Ghi chú</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                // Nhóm dữ liệu theo giảng viên
                $nhomTheoGiangVien = [];
                $db = Database::getInstance()->getConnection();
                
                if ($exportMode == 2) {
                    // Mode 2: Có 2 tùy chọn sắp xếp
                    if ($sortBy === 'gvhd') {
                        // Sắp xếp theo giảng viên hướng dẫn
                        foreach ($danhSachBaoCao as $item) {
                            $key = $item['ten_giang_vien'];
                            if (!isset($nhomTheoGiangVien[$key])) {
                                $nhomTheoGiangVien[$key] = [
                                    'giang_vien' => $item['ten_giang_vien'],
                                    'hoc_ham_hoc_vi' => $item['hoc_ham_hoc_vi'] ?? '',
                                    'de_tai' => []
                                ];
                            }
                            $nhomTheoGiangVien[$key]['de_tai'][] = $item;
                        }
                    } else {
                        // Sắp xếp theo giảng viên chấm (mặc định)
                        foreach ($danhSachBaoCao as $item) {
                            // Lấy thông tin giảng viên chấm
                            $stmt = $db->prepare("SELECT nd.ho_ten, gv.hoc_ham_hoc_vi
                                                 FROM dang_ky_de_tai dk
                                                 LEFT JOIN giang_vien gv ON dk.giang_vien_cham_id = gv.id
                                                 LEFT JOIN nguoi_dung nd ON gv.nguoi_dung_id = nd.id
                                                 WHERE dk.id = :dang_ky_id");
                            $stmt->execute(['dang_ky_id' => $item['id']]);
                            $gvCham = $stmt->fetch();
                            
                            $key = $gvCham && $gvCham['ho_ten'] ? $gvCham['ho_ten'] : 'Chưa phân công';
                            
                            if (!isset($nhomTheoGiangVien[$key])) {
                                $nhomTheoGiangVien[$key] = [
                                    'giang_vien' => $key,
                                    'hoc_ham_hoc_vi' => $gvCham['hoc_ham_hoc_vi'] ?? '',
                                    'de_tai' => []
                                ];
                            }
                            $nhomTheoGiangVien[$key]['de_tai'][] = $item;
                        }
                    }
                } else {
                    // Mode 1: Nhóm theo giảng viên hướng dẫn
                    foreach ($danhSachBaoCao as $item) {
                        $key = $item['ten_giang_vien'];
                        if (!isset($nhomTheoGiangVien[$key])) {
                            $nhomTheoGiangVien[$key] = [
                                'giang_vien' => $item['ten_giang_vien'],
                                'hoc_ham_hoc_vi' => $item['hoc_ham_hoc_vi'] ?? '',
                                'de_tai' => []
                            ];
                        }
                        $nhomTheoGiangVien[$key]['de_tai'][] = $item;
                    }
                }
                
                $stt = 1;
                foreach ($nhomTheoGiangVien as $nhom) {
                    $soLuongDeTai = count($nhom['de_tai']);
                    
                    // Đếm số lượng đề tài đã được phân công giảng viên chấm
                    $soLuongDaPhanCong = 0;
                    foreach ($nhom['de_tai'] as $item) {
                        $stmtCheck = $db->prepare("SELECT giang_vien_cham_id FROM dang_ky_de_tai WHERE id = :dang_ky_id");
                        $stmtCheck->execute(['dang_ky_id' => $item['id']]);
                        $checkResult = $stmtCheck->fetch();
                        if ($checkResult && !empty($checkResult['giang_vien_cham_id'])) {
                            $soLuongDaPhanCong++;
                        }
                    }
                    
                    // Hiển thị học hàm/học vị + tên giảng viên (cho cột nhóm)
                    $gvNhom = '';
                    if (!empty($nhom['hoc_ham_hoc_vi'])) {
                        $gvNhom = htmlspecialchars($nhom['hoc_ham_hoc_vi']) . '. ';
                    }
                    $gvNhom .= htmlspecialchars($nhom['giang_vien']);
                    
                    foreach ($nhom['de_tai'] as $idx => $item) {
                        // Lấy thông tin giảng viên hướng dẫn
                        $gvhd = '';
                        if (!empty($item['hoc_ham_hoc_vi'])) {
                            $gvhd = htmlspecialchars($item['hoc_ham_hoc_vi']) . '. ';
                        }
                        $gvhd .= htmlspecialchars($item['ten_giang_vien']);
                        
                        // Lấy thông tin giảng viên chấm
                        $stmt = $db->prepare("SELECT nd.ho_ten, gv.hoc_ham_hoc_vi
                                             FROM dang_ky_de_tai dk
                                             LEFT JOIN giang_vien gv ON dk.giang_vien_cham_id = gv.id
                                             LEFT JOIN nguoi_dung nd ON gv.nguoi_dung_id = nd.id
                                             WHERE dk.id = :dang_ky_id");
                        $stmt->execute(['dang_ky_id' => $item['id']]);
                        $gvCham = $stmt->fetch();
                        
                        $tenGVCham = '';
                        if ($gvCham && $gvCham['ho_ten']) {
                            if (!empty($gvCham['hoc_ham_hoc_vi'])) {
                                $tenGVCham = htmlspecialchars($gvCham['hoc_ham_hoc_vi']) . '. ';
                            }
                            $tenGVCham .= htmlspecialchars($gvCham['ho_ten']);
                        }
                        
                        echo "<tr>";
                        echo "<td style='text-align:center;'>" . $stt . "</td>";
                        echo "<td>" . htmlspecialchars($item['ma_sinh_vien']) . "</td>";
                        echo "<td>" . htmlspecialchars($item['ten_sinh_vien']) . "</td>";
                        echo "<td>" . htmlspecialchars($item['lop']) . "</td>";
                        echo "<td>" . htmlspecialchars($item['ten_de_tai']) . "</td>";
                        
                        if ($exportMode == 2) {
                            // Mode 2: Hiển thị cả 2 cột
                            // Cột được nhóm (rowspan) phụ thuộc vào sortBy
                            if ($sortBy === 'gvhd') {
                                // Nhóm theo GV hướng dẫn
                                if ($idx === 0) {
                                    echo "<td style='vertical-align: middle;' rowspan='" . $soLuongDeTai . "'>" . $gvNhom . "</td>";
                                }
                                echo "<td>" . ($tenGVCham ?: '') . "</td>";
                            } else {
                                // Nhóm theo GV chấm
                                echo "<td>" . $gvhd . "</td>";
                                if ($idx === 0) {
                                    echo "<td style='vertical-align: middle;' rowspan='" . $soLuongDeTai . "'>" . $gvNhom . "</td>";
                                }
                            }
                        } else {
                            // Mode 1: Chỉ có cột GV hướng dẫn
                            if ($idx === 0) {
                                echo "<td style='vertical-align: middle;' rowspan='" . $soLuongDeTai . "'>" . $gvNhom . "</td>";
                            }
                        }
                        
                        // Ghi chú - hiển thị ở dòng đầu tiên
                        if ($idx === 0) {
                            echo "<td style='text-align:center; vertical-align: middle;' rowspan='" . $soLuongDeTai . "'>" . $soLuongDaPhanCong . "</td>";
                        }
                        
                        echo "</tr>";
                        $stt++;
                    }
                }
                ?>
            </tbody>
        </table>
        
        <script>
            window.onload = function() {
                setTimeout(function() { window.print(); }, 500);
            };
        </script>
    </body>
    </html>
    <?php
    exit();
}

// Include header
include_once __DIR__ . '/../includes/header.php';
?>

<style>
    .stat-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 12px;
        padding: 20px;
        text-align: center;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        transition: transform 0.3s ease;
    }
    .stat-card:hover {
        transform: translateY(-5px);
    }
    .stat-card.primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    .stat-card.success {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }
    .stat-card.info {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    }
    .stat-card.warning {
        background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
    }
    .stat-card h3 {
        font-size: 2.5rem;
        font-weight: 700;
        margin: 10px 0;
    }
    .stat-card p {
        margin: 0;
        font-size: 0.9rem;
        opacity: 0.9;
    }
    .filter-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        padding: 25px;
        margin-bottom: 25px;
    }
    .filter-card h5 {
        font-weight: 700;
        margin-bottom: 20px;
        color: #333;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .btn-filter {
        background: linear-gradient(135deg, #224bffff 0%, #224bffff 100%);
        border: none;
        color: white;
        padding: 10px 25px;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    .btn-filter:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        color: white;
    }
    .btn-reset {
        background: linear-gradient(135deg, #ff0000ff 0%, #ff0000ff 100%);
        border: none;
        color: white;
        padding: 10px 25px;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    .btn-reset:hover {
        color: white !important;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(255, 0, 0, 0.4);
    }
    .btn-reset:hover i {
        color: white !important;
    }
    .btn-export {
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 600;
        border: none;
        transition: all 0.3s ease;
    }
    .btn-export.excel {
        background: linear-gradient(135deg, #139030ff 0%, #139030ff 100%);
        color: white;
    }
    .btn-export.pdf {
        background: linear-gradient(135deg, #ff0019ff 0%, #ff0019ff 100%);
        color: white;
    }
    .btn-export:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }
    .table-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        overflow: hidden;
    }
    .table-card .card-header {
        background: linear-gradient(135deg, #2f59ffff 0%, #2f59ffff 100%);
        color: white;
        font-weight: 700;
        padding: 15px 20px;
        border: none;
    }
    .search-box {
        position: relative;
    }
    .search-box input {
        padding-left: 40px;
        border-radius: 8px;
        border: 1px solid #000000ff;
    }
    .search-box i {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #000000ff;
    }
    .badge-status {
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
    }
    /* Căn chỉnh độ rộng cột cho bảng thống kê theo hệ */
    #dataTable thead th {
        white-space: nowrap;
        vertical-align: middle;
    }
    #dataTable tbody td {
        vertical-align: middle;
    }
    #dataTable thead th:first-child,
    #dataTable tbody td:first-child {
        padding-left: 20px;
    }
    /* Cố định độ rộng cột STT */
    #dataTable thead th:first-child,
    #dataTable tbody td:first-child,
    #dataTable2 thead th:first-child,
    #dataTable2 tbody td:first-child {
        width: 60px;
        min-width: 60px;
        max-width: 60px;
    }
</style>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-2 sidebar">
            <nav class="nav flex-column">
                <div class="nav-section-title">QUẢN LÝ HỆ THỐNG</div>
                <a class="nav-link" href="dashboard.php">
                    <i class="bi bi-house-door"></i> Trang chủ
                </a>
                <a class="nav-link" href="duyet_de_tai.php">
                    <i class="bi bi-journal-check"></i> Duyệt đề tài
                </a>
                <a class="nav-link" href="de_tai_da_duyet.php">
                    <i class="bi bi-journal-check"></i> Đề tài đã duyệt
                </a>
                <a class="nav-link" href="danh_sach_phan_cong.php">
                    <i class="bi bi-person-check"></i> Phân công chấm
                </a>
                <a class="nav-link active" href="xuat_bao_cao.php">
                    <i class="bi bi-file-earmark-text"></i> Xuất danh sách
                </a>
                <a class="nav-link" href="cai_dat_thong_so.php">
                    <i class="bi bi-gear"></i> Cài đặt thông số
                </a>
                <a class="nav-link" href="quan_ly_nguoi_dung.php">
                    <i class="bi bi-people"></i> Quản lý tài khoản
                </a>
                
                <div class="nav-section-title">QUẢN LÝ NỘI DUNG</div>
                <a class="nav-link" href="quan_ly_noi_dung_do_an.php">
                    <i class="bi bi-file-earmark-text"></i> Thông báo đồ án
                </a>
                <a class="nav-link" href="quan_ly_thong_bao.php">
                    <i class="bi bi-megaphone"></i> Thông báo chung
                </a>
                <a class="nav-link" href="cau_hinh_menu.php">
                    <i class="bi bi-link-45deg"></i> Cập nhật liên kết
                </a>
                
                <div class="nav-section-title">CHỨC NĂNG GIẢNG VIÊN</div>
                <a class="nav-link" href="../giang_vien/dashboard.php">
                    <i class="bi bi-person-workspace"></i> Chế độ Giảng viên
                </a>
            </nav>
        </div>

        <!-- Main content -->
        <div class="col-md-10 p-4" style="margin-bottom: 100px;">
            <!-- Welcome Card -->
            <div class="card mb-4 fade-in-up border-0 shadow-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h3 class="mb-2 text-dark">
                                Xuất danh sách 
                                <strong><?= $loaiDeTai === 'co_so_nganh' ? 'Cơ sở ngành' : 'Chuyên ngành' ?></strong>
                            </h3>
                            <p class="mb-0 text-muted">
                                Xuất danh sách đề tài dưới dạng Excel hoặc PDF.
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <!-- Export buttons -->
                            <div class="d-flex gap-2 justify-content-end">
                                <button type="button" class="btn btn-export excel" data-export-type="excel">
                                    <i class="bi bi-file-earmark-excel"></i> Xuất Excel
                                </button>
                                <button type="button" class="btn btn-export pdf" data-export-type="pdf">
                                    <i class="bi bi-file-earmark-pdf"></i> Xuất PDF
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs chọn loại đề tài -->
            <ul class="nav nav-tabs mb-4" role="tablist">
                <li class="nav-item" role="presentation">
                    <a class="nav-link <?= $loaiDeTai === 'co_so_nganh' ? 'active' : '' ?>" 
                       href="?loai=co_so_nganh<?= $giangVienId ? '&giang_vien_id=' . $giangVienId : '' ?><?= $lop ? '&lop=' . urlencode($lop) : '' ?>">
                        <i class="bi bi-journal-code"></i> Cơ sở ngành
                        <span class="badge bg-<?= $loaiDeTai === 'co_so_nganh' ? 'primary' : 'secondary' ?> ms-1"><?= $tongCSN ?></span>
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link <?= $loaiDeTai === 'chuyen_nganh' ? 'active' : '' ?>" 
                       href="?loai=chuyen_nganh<?= $giangVienId ? '&giang_vien_id=' . $giangVienId : '' ?><?= $lop ? '&lop=' . urlencode($lop) : '' ?>">
                        <i class="bi bi-mortarboard"></i> Chuyên ngành
                        <span class="badge bg-<?= $loaiDeTai === 'chuyen_nganh' ? 'success' : 'secondary' ?> ms-1"><?= $tongCN ?></span>
                    </a>
                </li>
            </ul>

            <!-- Hidden form for export functionality -->
            <form method="GET" action="" id="filterForm" style="display: none;">
                <input type="hidden" name="loai" value="<?= $loaiDeTai ?>">
            </form>

            <div class="table-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>
                        Danh sách sinh viên và đề tài
                    </span>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($danhSachBaoCao)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-inbox" style="font-size: 4rem; color: #dee2e6;"></i>
                            <p class="text-muted mt-3">Không có dữ liệu</p>
                        </div>
                    <?php else: ?>
                        <!-- Bảng chế độ 1: Chỉ GV hướng dẫn -->
                        <div class="table-responsive" id="tableMode1">
                            <table class="table table-hover table-bordered mb-0" id="dataTable">
                                <thead class="table-light">
                                    <tr>
                                        <th style='width: 60px;'>STT</th>
                                        <th style='width: 120px;'>MSSV</th>
                                        <th>Họ và tên sinh viên</th>
                                        <th style='width: 100px;'>Mã lớp</th>
                                        <th>Tên đề tài</th>
                                        <th style='width: 120px;'>Loại đề tài</th>
                                        <th>Giảng viên hướng dẫn</th>
                                        <th>Giảng viên chấm</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    // Nhóm dữ liệu theo giảng viên
                                    $nhomTheoGiangVien = [];
                                    foreach ($danhSachBaoCao as $item) {
                                        $key = $item['ten_giang_vien'];
                                        if (!isset($nhomTheoGiangVien[$key])) {
                                            $nhomTheoGiangVien[$key] = [
                                                'giang_vien' => $item['ten_giang_vien'],
                                                'hoc_ham_hoc_vi' => $item['hoc_ham_hoc_vi'] ?? '',
                                                'de_tai' => []
                                            ];
                                        }
                                        $nhomTheoGiangVien[$key]['de_tai'][] = $item;
                                    }
                                    
                                    $stt = 1;
                                    foreach ($nhomTheoGiangVien as $nhom) {
                                        $soLuongDeTai = count($nhom['de_tai']);
                                        
                                        // Hiển thị học hàm/học vị + tên giảng viên
                                        $gvhd = '';
                                        if (!empty($nhom['hoc_ham_hoc_vi'])) {
                                            $gvhd = htmlspecialchars($nhom['hoc_ham_hoc_vi']) . '. ';
                                        }
                                        $gvhd .= htmlspecialchars($nhom['giang_vien']);
                                        
                                        foreach ($nhom['de_tai'] as $idx => $item) {
                                            // Lấy thông tin giảng viên chấm
                                            $db = Database::getInstance()->getConnection();
                                            $stmt = $db->prepare("SELECT nd.ho_ten, gv.hoc_ham_hoc_vi
                                                                 FROM dang_ky_de_tai dk
                                                                 LEFT JOIN giang_vien gv ON dk.giang_vien_cham_id = gv.id
                                                                 LEFT JOIN nguoi_dung nd ON gv.nguoi_dung_id = nd.id
                                                                 WHERE dk.id = :dang_ky_id");
                                            $stmt->execute(['dang_ky_id' => $item['id']]);
                                            $gvCham = $stmt->fetch();
                                            
                                            $tenGVCham = '';
                                            if ($gvCham && $gvCham['ho_ten']) {
                                                if (!empty($gvCham['hoc_ham_hoc_vi'])) {
                                                    $tenGVCham = htmlspecialchars($gvCham['hoc_ham_hoc_vi']) . '. ';
                                                }
                                                $tenGVCham .= htmlspecialchars($gvCham['ho_ten']);
                                            } else {
                                                $tenGVCham = 'Chưa phân công';
                                            }
                                            
                                            echo "<tr>";
                                            echo "<td class='text-center'>" . $stt . "</td>";
                                            echo "<td>" . htmlspecialchars($item['ma_sinh_vien']) . "</td>";
                                            echo "<td>" . htmlspecialchars($item['ten_sinh_vien']) . "</td>";
                                            echo "<td>" . htmlspecialchars($item['lop']) . "</td>";
                                            echo "<td>" . htmlspecialchars($item['ten_de_tai']) . "</td>";
                                            echo "<td><span class='badge bg-" . ($item['he_dao_tao'] === 'co_so_nganh' ? 'primary' : 'success') . "'>" 
                                                 . getHeDaoTaoLabel($item['he_dao_tao']) . "</span></td>";
                                            
                                            // Chỉ hiển thị giảng viên hướng dẫn ở dòng đầu tiên của nhóm
                                            if ($idx === 0) {
                                                echo "<td style='vertical-align: middle;' rowspan='" . $soLuongDeTai . "'>" . $gvhd . "</td>";
                                            }
                                            
                                            // Hiển thị giảng viên chấm ở mỗi dòng
                                            echo "<td>" . $tenGVCham . "</td>";
                                            
                                            echo "</tr>";
                                            $stt++;
                                        }
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Chuyển đổi giữa 2 chế độ xem
document.getElementById('btnViewMode1')?.addEventListener('click', function() {
    document.getElementById('tableMode1').style.display = 'block';
    document.getElementById('tableMode2').style.display = 'none';
    this.classList.add('active');
    document.getElementById('btnViewMode2').classList.remove('active');
});

document.getElementById('btnViewMode2')?.addEventListener('click', function() {
    document.getElementById('tableMode1').style.display = 'none';
    document.getElementById('tableMode2').style.display = 'block';
    this.classList.add('active');
    document.getElementById('btnViewMode1').classList.remove('active');
    
    // Tải lại bảng Mode 2 với tùy chọn sắp xếp hiện tại
    loadTableMode2(currentSortBy);
});

// Hàm tải bảng Mode 2 với tùy chọn sắp xếp
function loadTableMode2(sortBy) {
    currentSortBy = sortBy;
    // Reload trang với tham số mode=2 và sort
    const url = new URL(window.location.href);
    url.searchParams.set('view_mode', '2');
    url.searchParams.set('sort_view', sortBy);
    // Không reload, chỉ cập nhật URL
    window.history.replaceState({}, '', url);
}

// Tìm kiếm trong bảng
document.getElementById('searchTable')?.addEventListener('keyup', function() {
    const searchValue = this.value.toLowerCase();
    const table = document.getElementById('dataTable');
    const rows = table?.getElementsByTagName('tbody')[0]?.getElementsByTagName('tr');
    
    if (rows) {
        for (let i = 0; i < rows.length; i++) {
            const row = rows[i];
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchValue) ? '' : 'none';
        }
    }
});
</script>
<!-- Modal lựa chọn chế độ xuất -->
<div class="modal fade" id="exportModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            
            <!-- Header -->
            <div class="modal-header bg-primary text-white rounded-top-4">
                <h5 class="modal-title fw-semibold">
                Xuất danh sách
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <!-- Body -->
            <div class="modal-body p-4">
                <div class="d-grid gap-3">

                    <!-- Option 1 -->
                    <div class="export-option-wrapper">
                        <button type="button"
                            class="export-option p-3 rounded-3 border text-start bg-white w-100"
                            data-export-mode="1">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-person fs-4 text-primary me-3"></i>
                                <div>
                                    <div class="fw-semibold">Theo giảng viên hướng dẫn</div>
                                </div>
                            </div>
                        </button>
                    </div>

                    <!-- Option 2 -->
                    <div class="export-option-wrapper">
                        <button type="button"
                            class="export-option p-3 rounded-3 border text-start bg-white w-100"
                            data-export-mode="2">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-people fs-4 text-success me-3"></i>
                                <div>
                                    <div class="fw-semibold">Theo giảng viên chấm</div>
                                </div>
                            </div>
                        </button>
                        
                        <!-- Form sắp xếp cho Option 2 - hiển thị ngay bên dưới -->
                        <div id="sortFormOption2" class="mt-3 p-3 bg-light rounded-3" style="display: none;">
                            <label class="form-label fw-semibold mb-2">Sắp xếp theo:</label>
                            <select id="sortByGVCham" class="form-select">
                                <option value="">-- Chọn cách sắp xếp --</option>
                                <option value="gvhd">Giảng viên hướng dẫn</option>
                                <option value="gvcham">Giảng viên chấm</option>
                            </select>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>
<style>
.export-option {
    transition: all 0.2s ease;
}

.export-option:hover {
    background-color: #f8f9fa;
    border-color: #0d6efd;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.export-option.selected {
    border-color: #198754 !important;
    border-width: 2px !important;
    background-color: #f8f9fa !important;
}

.export-option-wrapper {
    position: relative;
}
</style>

<script>
// Xử lý xuất Excel và PDF
let currentExportType = '';
let currentSortBy = 'gvcham'; // Mặc định sắp xếp theo GV chấm
const exportModalElement = document.getElementById('exportModal');

// Khi click vào nút xuất Excel hoặc PDF
document.querySelectorAll('.btn-export').forEach(btn => {
    btn.addEventListener('click', function() {
        currentExportType = this.getAttribute('data-export-type');
        // Sử dụng jQuery nếu có, nếu không dùng vanilla JS
        if (typeof $ !== 'undefined' && $.fn.modal) {
            $('#exportModal').modal('show');
        } else if (typeof bootstrap !== 'undefined') {
            const modal = new bootstrap.Modal(exportModalElement);
            modal.show();
        } else {
            // Fallback: thêm class trực tiếp
            exportModalElement.classList.add('show');
            exportModalElement.style.display = 'block';
            document.body.classList.add('modal-open');
            const backdrop = document.createElement('div');
            backdrop.className = 'modal-backdrop fade show';
            backdrop.id = 'modalBackdrop';
            document.body.appendChild(backdrop);
        }
    });
});

// Khi chọn chế độ xuất trong modal
document.querySelectorAll('[data-export-mode]').forEach(btn => {
    btn.addEventListener('click', function() {
        const mode = this.getAttribute('data-export-mode');
        
        // Reset tất cả các button
        document.querySelectorAll('[data-export-mode]').forEach(b => {
            b.classList.remove('selected');
        });
        
        // Highlight button được chọn
        this.classList.add('selected');
        
        // Nếu chọn mode 1, xuất ngay
        if (mode === '1') {
            document.getElementById('sortFormOption2').style.display = 'none';
            exportWithMode(mode);
        } 
        // Nếu chọn mode 2, hiển thị form sắp xếp
        else if (mode === '2') {
            const sortForm = document.getElementById('sortFormOption2');
            sortForm.style.display = 'block';
            // Reset dropdown về trạng thái chưa chọn
            document.getElementById('sortByGVCham').value = '';
        }
    });
});

// Xử lý khi chọn tùy chọn sắp xếp - tự động xuất
document.getElementById('sortByGVCham')?.addEventListener('change', function() {
    if (this.value) {
        currentSortBy = this.value;
        exportWithMode('2');
    }
});

// Hàm xuất dữ liệu
function exportWithMode(mode) {
    const form = document.getElementById('filterForm');
    const formData = new FormData(form);
    
    // Tạo URL với các tham số hiện tại
    let url = '?export=' + currentExportType + '&mode=' + mode;
    
    // Thêm tham số sắp xếp nếu là mode 2
    if (mode === '2') {
        url += '&sort=' + currentSortBy;
    }
    
    // Thêm các tham số từ form
    for (let [key, value] of formData.entries()) {
        if (value) {
            url += '&' + key + '=' + encodeURIComponent(value);
        }
    }
    
    // Đóng modal
    if (typeof $ !== 'undefined' && $.fn.modal) {
        $('#exportModal').modal('hide');
    } else if (typeof bootstrap !== 'undefined') {
        const modal = bootstrap.Modal.getInstance(exportModalElement);
        if (modal) modal.hide();
    } else {
        // Fallback: xóa class trực tiếp
        exportModalElement.classList.remove('show');
        exportModalElement.style.display = 'none';
        document.body.classList.remove('modal-open');
        const backdrop = document.getElementById('modalBackdrop');
        if (backdrop) backdrop.remove();
    }
    
    // Chuyển hướng đến URL xuất
    window.location.href = url;
}

// Reset modal khi đóng
exportModalElement?.addEventListener('hidden.bs.modal', function() {
    document.getElementById('sortFormOption2').style.display = 'none';
    document.getElementById('sortByGVCham').value = '';
    document.querySelectorAll('[data-export-mode]').forEach(b => {
        b.classList.remove('selected');
    });
});

// Xử lý đóng modal bằng nút close
document.querySelectorAll('[data-bs-dismiss="modal"]').forEach(btn => {
    btn.addEventListener('click', function() {
        if (typeof $ !== 'undefined' && $.fn.modal) {
            $('#exportModal').modal('hide');
        } else if (typeof bootstrap !== 'undefined') {
            const modal = bootstrap.Modal.getInstance(exportModalElement);
            if (modal) modal.hide();
        } else {
            exportModalElement.classList.remove('show');
            exportModalElement.style.display = 'none';
            document.body.classList.remove('modal-open');
            const backdrop = document.getElementById('modalBackdrop');
            if (backdrop) backdrop.remove();
        }
    });
});
</script>


<?php include_once __DIR__ . '/includes/modal_quan_ly_tai_khoan.php'; ?>

</body>
</html>
