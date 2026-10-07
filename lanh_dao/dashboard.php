<?php
/**
 * DASHBOARD LÃNH ĐẠO
 */

require_once '../bootstrap.php';
requireRole(ROLE_LANH_DAO);

$user = getCurrentUser();
$pageTitle = 'Dashboard - Lãnh đạo';

$lanhDaoModel = new LanhDaoModel();
$deTaiModel = new DeTaiModel();
$dangKyModel = new DangKyDeTaiModel();

$lanhDao = $lanhDaoModel->getByNguoiDungId($user['id']);

// Thống kê tổng quan
$thongKe = $lanhDaoModel->getThongKeTongQuan();

// Đề tài chờ duyệt
$deTaiChoDuyet = $deTaiModel->getDeTaiChoDuyet();

// Thống kê phân công theo giảng viên
$thongKePhanCong = $dangKyModel->getThongKePhanCongTheoGiangVien();

// Include header
include_once __DIR__ . '/../includes/header.php';
?>

<style>
    .btn-outline-dark:hover {
        background-color: #212529 !important;
        border-color: #212529 !important;
        color: white !important;
    }

    .btn-outline-dark:hover i {
        color: white !important;
    }

    /* Quick Action Buttons - No Hover Effect */
    .btn-quick-action {
        background-color: #ffffff !important;
        border: 2px solid #212529 !important;
        color: #212529 !important;
    }

    .btn-quick-action:hover {
        background-color: #ffffff !important;
        border-color: #212529 !important;
        color: #212529 !important;
        transform: none !important;
        box-shadow: none !important;
    }

    .btn-quick-action:hover .text-warning {
        color: #ffc107 !important;
    }

    .btn-quick-action:hover .text-primary {
        color: #0d6efd !important;
    }

    .btn-quick-action:hover .text-success {
        color: #198754 !important;
    }

    .btn-quick-action:hover .text-info {
        color: #0dcaf0 !important;
    }

    .btn-quick-action:hover .text-danger {
        color: #dc3545 !important;
    }

    .btn-quick-action:active {
        background-color: #ffffff !important;
        border-color: #212529 !important;
        transform: none !important;
    }
</style>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-2 sidebar">
            <nav class="nav flex-column">
                <div class="nav-section-title">QUẢN LÝ HỆ THỐNG</div>
                <a class="nav-link active" href="dashboard.php">
                    <i class="bi bi-house-door"></i> Trang chủ
                </a>
                <a class="nav-link" href="duyet_de_tai.php">
                    <i class="bi bi-journal-check"></i> Duyệt đề tài
                </a>
                <a class="nav-link" href="de_tai_da_duyet.php">
                    <i class="bi bi-journal-check"></i> Đề tài đã duyệt
                </a>
                <a class="nav-link" href="sinh_vien_cho_duyet.php">
                    <i class="bi bi-journal-check"></i> Sinh viên chờ GV duyệt
                </a>
                <a class="nav-link" href="danh_sach_phan_cong.php">
                    <i class="bi bi-person-check"></i> Phân công chấm
                </a>
                <a class="nav-link" href="xuat_bao_cao.php">
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
        <div class="col-md-10 p-4">
            <!-- Welcome Card -->
            <div class="card mb-4 fade-in-up border-0 shadow-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h3 class="mb-2 text-dark">
                                Xin chào, <strong><?= htmlspecialchars($user['ho_ten']) ?></strong>.
                            </h3>
                            <p class="mb-0 text-muted">
                                Chào mừng lãnh đạo đến với hệ thống quản lý đề tài cơ sở ngành và chuyên ngành.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Thống kê tổng quan -->
            <div class="row mb-4">
                <!-- Đề tài CSN chờ duyệt -->
                <div class="col-md-6 mb-4">
                    <div class="card shadow-sm h-100 fade-in-up" style="animation-delay: 0.6s;">
                        <div
                            class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                            <span>
                                Đề tài Cơ sở ngành chờ duyệt
                            </span>
                            <?php
                            $deTaiCSN = array_filter($deTaiChoDuyet, function ($dt) {
                                return $dt['he_dao_tao'] === 'co_so_nganh';
                            });
                            ?>
                            <?php if (count($deTaiCSN) > 0): ?>
                                <span class="badge bg-light text-dark fw-bold"><?= count($deTaiCSN) ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="card-body" style="max-height: 450px; overflow-y: auto;">
                            <style>
                                /* Xóa mũi tên accordion */
                                .gv-accordion-button::after {
                                    display: none !important;
                                }

                                .accordion-button {
                                    font-size: 0.9rem;
                                    padding: 0.75rem 1rem;
                                }

                                /* Thiết kế accordion giảng viên */
                                .gv-accordion-item {
                                    border: 2px solid #e3e6f0 !important;
                                    border-radius: 8px !important;
                                    margin-bottom: 12px !important;
                                    background: #ffffff;
                                    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
                                }

                                .gv-accordion-button {
                                    background: white !important;
                                    color: #212529 !important;
                                    border-radius: 6px !important;
                                    font-weight: 600;
                                    border: none !important;
                                }

                                .gv-accordion-button:not(.collapsed) {
                                    background: white !important;
                                    color: #212529 !important;
                                }

                                .gv-accordion-button i {
                                    color: #212529 !important;
                                }

                                .gv-accordion-button:not(.collapsed) i {
                                    color: #212529 !important;
                                }

                                .gv-accordion-body {
                                    background: #f8f9fa;
                                    border-radius: 0 0 6px 6px;
                                    padding: 0.5rem !important;
                                }

                                .de-tai-item {
                                    background: white;
                                    border: 1px solid #dee2e6;
                                    border-radius: 6px;
                                    margin-bottom: 6px;
                                }

                                .gv-accordion-body {
                                    background: #f8f9fa;
                                    border-radius: 0 0 6px 6px;
                                    padding: 0.5rem !important;
                                }

                                .de-tai-item {
                                    background: white;
                                    border: 1px solid #dee2e6;
                                    border-radius: 6px;
                                    margin-bottom: 6px;
                                    transition: all 0.2s ease;
                                }

                                .de-tai-item:hover {
                                    border-color: #000000ff;
                                    box-shadow: 0 2px 4px rgba(13, 110, 253, 0.1);
                                    transform: translateX(2px);
                                }
                            </style>
                            <?php if (empty($deTaiCSN)): ?>
                                <div class="text-center py-5">
                                    <i class="bi bi-check-circle text-success" style="font-size: 3rem; opacity: 0.3;"></i>
                                    <p class="text-muted mt-3 mb-0">Không có đề tài Cơ sở ngành chờ duyệt</p>
                                </div>
                            <?php else: ?>
                                <?php
                                // Nhóm đề tài theo giảng viên
                                $deTaiTheoGV = [];
                                foreach ($deTaiCSN as $dt) {
                                    $gvId = $dt['giang_vien_id'];
                                    if (!isset($deTaiTheoGV[$gvId])) {
                                        $deTaiTheoGV[$gvId] = [
                                            'ten_giang_vien' => $dt['ten_giang_vien'],
                                            'de_tai' => []
                                        ];
                                    }
                                    $deTaiTheoGV[$gvId]['de_tai'][] = $dt;
                                }

                                // Sắp xếp đề tài theo thứ tự mới nhất lên đầu
                                foreach ($deTaiTheoGV as $gvId => &$gvData) {
                                    usort($gvData['de_tai'], function ($a, $b) {
                                        return strtotime($b['created_at']) - strtotime($a['created_at']);
                                    });
                                }
                                unset($gvData);
                                ?>
                                <div class="accordion" id="accordionCSN">
                                    <?php
                                    $index = 0;
                                    foreach ($deTaiTheoGV as $gvId => $gvData):
                                        $index++;
                                        ?>
                                        <div class="accordion-item gv-accordion-item csn">
                                            <h2 class="accordion-header">
                                                <button class="accordion-button gv-accordion-button collapsed" type="button"
                                                    data-bs-toggle="collapse" data-bs-target="#collapseCSN<?= $gvId ?>"
                                                    aria-expanded="false">
                                                    <i class="bi bi-person-down me-2" style="font-size: 1.2rem;"></i>
                                                    <strong><?= htmlspecialchars($gvData['ten_giang_vien']) ?></strong>
                                                    <span class="badge bg-primary ms-auto"><?= count($gvData['de_tai']) ?> đề
                                                        tài</span>
                                                </button>
                                            </h2>
                                            <div id="collapseCSN<?= $gvId ?>" class="accordion-collapse collapse"
                                                data-bs-parent="#accordionCSN">
                                                <div class="accordion-body gv-accordion-body">
                                                    <div class="list-group list-group-flush">
                                                        <?php
                                                        $sttCSN = 1;
                                                        foreach ($gvData['de_tai'] as $dt):
                                                            ?>
                                                            <div
                                                                class="list-group-item de-tai-item px-3 py-2 d-flex justify-content-between align-items-center border-0">
                                                                <div class="d-flex align-items-start flex-grow-1">
                                                                    <span class="badge bg-primary text-white me-2"
                                                                        style="min-width: 30px;"><?= $sttCSN++ ?></span>
                                                                    <small
                                                                        class="fw-semibold"><?= htmlspecialchars($dt['tieu_de']) ?></small>
                                                                </div>
                                                                <button type="button" class="btn btn-info btn-sm ms-2"
                                                                    title="Xem chi tiết"
                                                                    onclick="xemChiTietDeTai(<?= $dt['id'] ?>, <?= $gvId ?>)">
                                                                    <i class="bi bi-eye"></i>
                                                                </button>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="text-end mt-3">
                                    <a href="duyet_de_tai.php?loai=co_so_nganh" class="btn btn-primary btn-sm">
                                        Xem tất cả danh sách
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Đề tài CN chờ duyệt -->
                <div class="col-md-6 mb-4">
                    <div class="card shadow-sm h-100 fade-in-up" style="animation-delay: 0.7s;">
                        <div
                            class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                            <span>
                                Đề tài Chuyên ngành chờ duyệt
                            </span>
                            <?php
                            $deTaiCN = array_filter($deTaiChoDuyet, function ($dt) {
                                return $dt['he_dao_tao'] === 'chuyen_nganh';
                            });
                            ?>
                            <?php if (count($deTaiCN) > 0): ?>
                                <span class="badge bg-light text-dark fw-bold"><?= count($deTaiCN) ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="card-body" style="max-height: 450px; overflow-y: auto;">
                            <?php if (empty($deTaiCN)): ?>
                                <div class="text-center py-5">
                                    <i class="bi bi-check-circle text-success" style="font-size: 3rem; opacity: 0.3;"></i>
                                    <p class="text-muted mt-3 mb-0">Không có đề tài Chuyên ngành chờ duyệt</p>
                                </div>
                            <?php else: ?>
                                <?php
                                // Nhóm đề tài theo giảng viên
                                $deTaiTheoGV = [];
                                foreach ($deTaiCN as $dt) {
                                    $gvId = $dt['giang_vien_id'];
                                    if (!isset($deTaiTheoGV[$gvId])) {
                                        $deTaiTheoGV[$gvId] = [
                                            'ten_giang_vien' => $dt['ten_giang_vien'],
                                            'de_tai' => []
                                        ];
                                    }
                                    $deTaiTheoGV[$gvId]['de_tai'][] = $dt;
                                }

                                // Sắp xếp đề tài theo thứ tự mới nhất lên đầu
                                foreach ($deTaiTheoGV as $gvId => &$gvData) {
                                    usort($gvData['de_tai'], function ($a, $b) {
                                        return strtotime($b['created_at']) - strtotime($a['created_at']);
                                    });
                                }
                                unset($gvData);
                                ?>
                                <div class="accordion" id="accordionCN">
                                    <?php
                                    $index = 0;
                                    foreach ($deTaiTheoGV as $gvId => $gvData):
                                        $index++;
                                        ?>
                                        <div class="accordion-item gv-accordion-item cn">
                                            <h2 class="accordion-header">
                                                <button class="accordion-button gv-accordion-button cn collapsed" type="button"
                                                    data-bs-toggle="collapse" data-bs-target="#collapseCN<?= $gvId ?>"
                                                    aria-expanded="false">
                                                    <i class="bi bi-person-down me-2" style="font-size: 1.2rem;"></i>
                                                    <strong><?= htmlspecialchars($gvData['ten_giang_vien']) ?></strong>
                                                    <span class="badge bg-success ms-auto"><?= count($gvData['de_tai']) ?> đề
                                                        tài</span>
                                                </button>
                                            </h2>
                                            <div id="collapseCN<?= $gvId ?>" class="accordion-collapse collapse"
                                                data-bs-parent="#accordionCN">
                                                <div class="accordion-body gv-accordion-body">
                                                    <div class="list-group list-group-flush">
                                                        <?php
                                                        $sttCN = 1;
                                                        foreach ($gvData['de_tai'] as $dt):
                                                            ?>
                                                            <div
                                                                class="list-group-item de-tai-item px-3 py-2 d-flex justify-content-between align-items-center border-0">
                                                                <div class="d-flex align-items-start flex-grow-1">
                                                                    <span class="badge bg-success text-white me-2"
                                                                        style="min-width: 30px;"><?= $sttCN++ ?></span>
                                                                    <small
                                                                        class="fw-semibold"><?= htmlspecialchars($dt['tieu_de']) ?></small>
                                                                </div>
                                                                <button type="button" class="btn btn-info btn-sm ms-2"
                                                                    title="Xem chi tiết"
                                                                    onclick="xemChiTietDeTai(<?= $dt['id'] ?>, <?= $gvId ?>)">
                                                                    <i class="bi bi-eye"></i>
                                                                </button>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="text-end mt-3">
                                    <a href="duyet_de_tai.php?loai=chuyen_nganh" class="btn btn-primary btn-sm">
                                        Xem tất cả danh sách
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card fade-in-up" style="animation-delay: 0.8s;">
                <div class="card-header">
                    Thao tác nhanh
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <a href="duyet_de_tai.php"
                                class="btn btn-outline-dark text-dark w-100 py-3 position-relative btn-quick-action">
                                <i class="bi bi-journal-check text-warning fs-4 d-block mb-2"></i>
                                Duyệt đề tài
                                <?php if (($thongKe['de_tai_cho_duyet'] ?? 0) > 0): ?>
                                    <span
                                        class="position-absolute top-0 end-0 translate-middle badge rounded-pill bg-danger">
                                        <?= $thongKe['de_tai_cho_duyet'] ?>
                                    </span>
                                <?php endif; ?>
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="danh_sach_phan_cong.php"
                                class="btn btn-outline-dark text-dark w-100 py-3 btn-quick-action">
                                <i class="bi bi-person-check text-primary fs-4 d-block mb-2"></i>
                                Phân công chấm
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="xuat_bao_cao.php"
                                class="btn btn-outline-dark text-dark w-100 py-3 btn-quick-action">
                                <i class="bi bi-file-earmark-text text-success fs-4 d-block mb-2"></i>
                                Xuất danh sách
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="cai_dat_thong_so.php"
                                class="btn btn-outline-dark text-dark w-100 py-3 btn-quick-action">
                                <i class="bi bi-gear text-info fs-4 d-block mb-2"></i>
                                Cài đặt thông số
                            </a>
                        </div>
                    </div>

                    <!-- Hàng thứ 2: Quản lý nội dung -->
                    <div class="row g-3 mt-2">
                        <div class="col-md-3">
                            <a href="quan_ly_nguoi_dung.php"
                                class="btn btn-outline-dark text-dark w-100 py-3 btn-quick-action">
                                <i class="bi bi-people text-success fs-4 d-block mb-2"></i>
                                Quản lý tài khoản
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="quan_ly_noi_dung_do_an.php"
                                class="btn btn-outline-dark text-dark w-100 py-3 btn-quick-action">
                                <i class="bi bi-file-earmark-text text-danger fs-4 d-block mb-2"></i>
                                Thông báo đồ án
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="quan_ly_thong_bao.php"
                                class="btn btn-outline-dark text-dark w-100 py-3 btn-quick-action">
                                <i class="bi bi-megaphone text-warning fs-4 d-block mb-2"></i>
                                Thông báo chung
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="cau_hinh_menu.php"
                                class="btn btn-outline-dark text-dark w-100 py-3 btn-quick-action">
                                <i class="bi bi-link-45deg text-primary fs-4 d-block mb-2"></i>
                                Cập nhật liên kết
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Modal chi tiết đề tài -->
<div class="modal fade" id="chiTietDeTaiModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-body p-0" id="chiTietDeTaiContent">
                <!-- Nội dung chi tiết sẽ được load bằng JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" id="btnDuyetDeTai">
                    <i class="bi bi-check-circle me-1"></i> Duyệt đề tài
                </button>
                <button type="button" class="btn btn-danger" id="btnTuChoiDeTai">
                    <i class="bi bi-x-circle me-1"></i> Từ chối
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal từ chối -->
<div class="modal fade" id="tuChoiModal" tabindex="-1" style="z-index: 1060;">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title fs-4">
                    Từ chối đề tài
                </h5>
            </div>
            <form id="tuChoiForm">
                <div class="modal-body p-4">
                    <input type="hidden" id="tuChoiDeTaiId">

                    <div class="alert alert-light border mb-4 p-3">
                        <strong class="fs-5">Tên đề tài:</strong> <span id="tenDeTaiTuChoi"
                            class="text-dark fs-5"></span>
                    </div>

                    <div class="mb-3">
                        <textarea id="lyDoTuChoi" class="form-control fs-5" rows="6" required
                            placeholder="Nhập lý do từ chối..." style="min-height: 150px;"></textarea>
                    </div>
                </div>
                <div class="modal-footer"
                    style="padding: 1rem !important; display: flex !important; justify-content: flex-end !important; gap: 0.5rem !important;">
                    <button type="button" class="btn btn-primary btn-lg" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-danger btn-lg">
                        Xác nhận từ chối
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const deTaiData = <?= json_encode(array_values($deTaiChoDuyet)) ?>;
    let currentGvId = null;
    let currentDeTaiId = null;

    function xemChiTietDeTai(deTaiId, gvId) {
        console.log('xemChiTietDeTai called:', deTaiId, gvId);
        currentDeTaiId = deTaiId;
        currentGvId = gvId;

        const deTai = deTaiData.find(dt => dt.id == deTaiId);
        console.log('Found deTai:', deTai);
        if (!deTai) {
            console.error('Không tìm thấy đề tài với ID:', deTaiId);
            return;
        }

        const isCSN = deTai.he_dao_tao === 'co_so_nganh';
        const headerClass = isCSN ? 'csn' : 'cn';

        let html = `
        <style>
            .detail-header {
                padding: 2rem;
                color: white;
            }
            .detail-header.csn {
                background: linear-gradient(135deg, #0052a8 0%, #003d82 100%);
            }
            .detail-header.cn {
                background: linear-gradient(135deg, #1cc88a 0%, #17a673 100%);
            }
            .detail-title {
                font-size: 1.8rem;
                font-weight: 700;
                margin-bottom: 1rem;
            }
            .detail-badge {
                background: white;
                padding: 0.5rem 1.2rem;
                border-radius: 20px;
                font-weight: 600;
            }
            .detail-badge.csn { color: #000000ff; }
            .detail-badge.cn { color: #000000ff; }
            .detail-body {
                padding: 2rem;
            }
            .info-row {
                display: flex;
                align-items: center;
                padding: 1rem;
                background: #f8f9fa;
                border-radius: 8px;
                border-left: 4px solid #0052a8;
                margin-bottom: 1rem;
            }
            .info-row.cn { border-left-color: #1cc88a; }
            .info-icon {
                font-size: 1.5rem;
                margin-right: 1rem;
                color: #000000ff;
            }
            .info-icon.cn { color: #000000ff; }
            .info-label {
                font-weight: 600;
                color: #6c757d;
                font-size: 0.9rem;
                display: block;
                margin-bottom: 0.25rem;
            }
            .info-value {
                font-weight: 700;
                color: #000;
                font-size: 1.1rem;
            }
            .section-box {
                background: #f8f9fa;
                border-radius: 8px;
                padding: 1.5rem;
                margin-bottom: 1.5rem;
                border: 1px solid #dee2e6;
            }
            .section-title {
                font-size: 1.1rem;
                font-weight: 700;
                color: #212529;
                margin-bottom: 1rem;
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }
            .section-content {
                color: #212529;
                line-height: 1.6;
                white-space: pre-line;
                font-weight: 600;
            }
        </style>
        
        <div class="detail-header ${headerClass}">
            <div class="d-flex justify-content-between align-items-start">
                <h1 class="detail-title">${escapeHtml(deTai.tieu_de)}</h1>
                <span class="detail-badge ${headerClass}">
                    ${isCSN ? 'Cơ sở ngành' : 'Chuyên ngành'}
                </span>
            </div>
        </div>
        
        <div class="detail-body">
            <div class="row mb-3">
                <div class="col-6">
                    <div class="info-row ${isCSN ? '' : 'cn'}">
                        <i class="bi bi-person-circle info-icon ${isCSN ? '' : 'cn'}"></i>
                        <div>
                            <span class="info-label">Giảng viên hướng dẫn:</span>
                            <div class="info-value">${escapeHtml(deTai.ten_giang_vien)}</div>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="info-row ${isCSN ? '' : 'cn'}">
                        <i class="bi bi-people-fill info-icon ${isCSN ? '' : 'cn'}"></i>
                        <div>
                            <span class="info-label">Số lượng sinh viên:</span>
                            <div class="info-value">${deTai.so_luong_sv} sinh viên</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="section-box">
                <div class="section-title">
                    <i class="bi bi-file-text"></i>
                    Mô tả đề tài
                </div>
                <div class="section-content">${escapeHtml(deTai.mo_ta).replace(/\n/g, '<br>')}</div>
            </div>
    `;

        if (deTai.cong_nghe) {
            html += `
            <div class="section-box">
                <div class="section-title">
                    <i class="bi bi-code-slash"></i>
                    Công nghệ sử dụng
                </div>
                <div class="section-content">${escapeHtml(deTai.cong_nghe)}</div>
            </div>
        `;
        }

        if (deTai.yeu_cau_sinh_vien) {
            html += `
            <div class="section-box">
                <div class="section-title">
                    <i class="bi bi-list-check"></i>
                    Yêu cầu sinh viên
                </div>
                <div class="section-content">${escapeHtml(deTai.yeu_cau_sinh_vien).replace(/\n/g, '<br>')}</div>
            </div>
        `;
        }

        if (deTai.ghi_chu) {
            html += `
            <div class="section-box">
                <div class="section-title">
                    <i class="bi bi-sticky"></i>
                    Ghi chú
                </div>
                <div class="section-content">${escapeHtml(deTai.ghi_chu).replace(/\n/g, '<br>')}</div>
            </div>
        `;
        }

        html += `</div>`;

        document.getElementById('chiTietDeTaiContent').innerHTML = html;

        console.log('Opening modal chi tiết...');

        // Mở modal chi tiết
        const chiTietModal = new bootstrap.Modal(document.getElementById('chiTietDeTaiModal'));
        chiTietModal.show();

        console.log('Modal chi tiết đã mở');
    }

    // Xử lý duyệt đề tài
    document.getElementById('btnDuyetDeTai').addEventListener('click', function () {
        if (!currentDeTaiId || !confirm('Bạn có chắc muốn duyệt đề tài này?')) return;

        const deTai = deTaiData.find(dt => dt.id == currentDeTaiId);
        const loai = deTai.he_dao_tao === 'co_so_nganh' ? 'co_so_nganh' : 'chuyen_nganh';

        window.location.href = `xu_ly_duyet.php?action=duyet&id=${currentDeTaiId}&loai=${loai}&redirect=dashboard&gv_id=${currentGvId}`;
    });

    // Xử lý từ chối đề tài
    document.getElementById('btnTuChoiDeTai').addEventListener('click', function () {
        if (!currentDeTaiId) return;

        // Tìm thông tin đề tài
        const deTai = deTaiData.find(dt => dt.id == currentDeTaiId);

        document.getElementById('tuChoiDeTaiId').value = currentDeTaiId;
        document.getElementById('tenDeTaiTuChoi').textContent = deTai ? deTai.tieu_de : '';
        document.getElementById('lyDoTuChoi').value = '';

        const modal = new bootstrap.Modal(document.getElementById('tuChoiModal'));
        modal.show();

        // Focus vào textarea sau khi modal hiển thị
        document.getElementById('tuChoiModal').addEventListener('shown.bs.modal', function () {
            document.getElementById('lyDoTuChoi').focus();
        }, { once: true });
    });

    // Submit form từ chối
    document.getElementById('tuChoiForm').addEventListener('submit', function (e) {
        e.preventDefault();

        const lyDo = document.getElementById('lyDoTuChoi').value.trim();
        if (!lyDo) {
            alert('Vui lòng nhập lý do từ chối');
            return;
        }

        const deTai = deTaiData.find(dt => dt.id == currentDeTaiId);
        const loai = deTai.he_dao_tao === 'co_so_nganh' ? 'co_so_nganh' : 'chuyen_nganh';

        // Tạo form và submit
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'xu_ly_duyet.php';

        const fields = {
            'action': 'tu_choi',
            'de_tai_id': currentDeTaiId,
            'loai': loai,
            'ly_do': lyDo,
            'redirect': 'dashboard',
            'gv_id': currentGvId
        };

        for (const [key, value] of Object.entries(fields)) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            input.value = value;
            form.appendChild(input);
        }

        document.body.appendChild(form);
        form.submit();
    });

    // Tự động mở accordion khi quay lại từ trang duyệt
    document.addEventListener('DOMContentLoaded', function () {
        const urlParams = new URLSearchParams(window.location.search);
        const gvId = urlParams.get('gv_id');
        const loai = urlParams.get('loai');

        if (gvId && loai) {
            // Chỉ mở accordion của loại đề tài tương ứng
            let accordionId = '';

            if (loai === 'co_so_nganh') {
                accordionId = 'collapseCSN' + gvId;
            } else if (loai === 'chuyen_nganh') {
                accordionId = 'collapseCN' + gvId;
            }

            const accordion = document.getElementById(accordionId);

            if (accordion) {
                const bsCollapse = new bootstrap.Collapse(accordion, {
                    show: true
                });
            }

            // Xóa tham số khỏi URL sau khi mở accordion
            const newUrl = window.location.pathname;
            window.history.replaceState({}, '', newUrl);
        }
    });

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
</script>

<?php
?>


<!-- Modal Quản lý tài khoản -->
<div class="modal fade" id="quanLyNguoiDungModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"> Quản lý tài khoản</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <p class="text-muted">Tạo tài khoản mới trong hệ thống</p>
                    <div class="mt-2">
                        <a href="../auth/import_sinh_vien.php" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-upload"></i> Import sinh viên
                        </a>
                        <a href="../auth/import_giang_vien.php" class="btn btn-sm btn-outline-success">
                            <i class="bi bi-upload"></i> Import giảng viên
                        </a>
                    </div>
                </div>

                <div id="alertContainer"></div>

                <form method="POST" action="xu_ly_tao_nguoi_dung.php" id="createUserForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Vai trò <span class="text-danger">*</span></label>
                            <select name="vai_tro" id="vaiTroModal" class="form-select" required>
                                <option value="">-- Chọn vai trò --</option>
                                <option value="giang_vien">Giảng viên</option>
                                <option value="sinh_vien">Sinh viên</option>
                                <option value="lanh_dao">Lãnh đạo</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Họ tên <span class="text-danger">*</span></label>
                            <input type="text" name="ho_ten" class="form-control" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Mật khẩu <span class="text-danger">*</span></label>
                            <input type="password" name="mat_khau" class="form-control" minlength="6" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Xác nhận mật khẩu <span class="text-danger">*</span></label>
                            <input type="password" name="xac_nhan_mat_khau" class="form-control" required>
                        </div>
                    </div>

                    <!-- Giảng viên fields -->
                    <div id="giangVienFieldsModal" class="additional-fields-modal">
                        <hr>
                        <h6>Thông tin giảng viên</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Mã giảng viên <span class="text-danger">*</span></label>
                                <input type="text" name="ma_giang_vien" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Khoa</label>
                                <input type="text" name="khoa" class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Chuyên môn</label>
                                <input type="text" name="chuyen_mon" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Số điện thoại</label>
                                <input type="text" name="so_dien_thoai" class="form-control">
                            </div>
                        </div>
                    </div>

                    <!-- Sinh viên fields -->
                    <div id="sinhVienFieldsModal" class="additional-fields-modal">
                        <hr>
                        <h6>Thông tin sinh viên</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Mã sinh viên <span class="text-danger">*</span></label>
                                <input type="text" name="ma_sinh_vien" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Lớp</label>
                                <input type="text" name="lop" class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Khóa học</label>
                                <input type="text" name="khoa_hoc" class="form-control" placeholder="VD: 2021-2025">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Chuyên ngành</label>
                                <input type="text" name="chuyen_nganh" class="form-control">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Số điện thoại</label>
                            <input type="text" name="so_dien_thoai_sv" class="form-control">
                        </div>
                    </div>

                    <!-- Lãnh đạo fields -->
                    <div id="lanhDaoFieldsModal" class="additional-fields-modal">
                        <hr>
                        <h6>Thông tin lãnh đạo</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Mã lãnh đạo <span class="text-danger">*</span></label>
                                <input type="text" name="ma_lanh_dao" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Chức vụ</label>
                                <input type="text" name="chuc_vu" class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Khoa</label>
                                <input type="text" name="khoa_ld" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Số điện thoại</label>
                                <input type="text" name="so_dien_thoai_ld" class="form-control">
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 mt-3">
                        Tạo tài khoản
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .additional-fields-modal {
        display: none;
        margin-top: 15px;
        padding: 15px;
        background: rgba(248, 250, 252, 0.8);
        border-radius: 10px;
        border: 1px solid #f1f5f9;
    }

    .additional-fields-modal h6 {
        color: #334155;
        font-size: 16px;
        font-weight: 600;
        margin-bottom: 15px;
        padding-bottom: 6px;
        border-bottom: 2px solid #e2e8f0;
    }

    #quanLyNguoiDungModal .modal-dialog {
        max-width: 700px;
    }

    #quanLyNguoiDungModal .modal-content {
        border-radius: 16px;
        border: none;
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
    }

    #quanLyNguoiDungModal .modal-header {
        background: linear-gradient(135deg, #0d6efd 0%, #0052a8 100%);
        color: white;
        border-radius: 16px 16px 0 0;
        padding: 1.5rem;
    }

    #quanLyNguoiDungModal .modal-title {
        font-size: 1.5rem;
        font-weight: 700;
    }

    #quanLyNguoiDungModal .btn-close {
        filter: brightness(0) invert(1);
    }

    #quanLyNguoiDungModal .modal-body {
        padding: 2rem;
    }

    #quanLyNguoiDungModal .form-control,
    #quanLyNguoiDungModal .form-select {
        height: 44px;
        border: 1.5px solid #f1f5f9;
        border-radius: 10px;
        padding: 0 14px;
        font-size: 14px;
        transition: all 0.3s ease;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    #quanLyNguoiDungModal .form-control:focus,
    #quanLyNguoiDungModal .form-select:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.12);
    }

    #quanLyNguoiDungModal .btn-primary {
        height: 48px;
        background: linear-gradient(135deg, #0d6efd 0%, #0052a8 100%);
        border: none;
        border-radius: 10px;
        font-size: 15px;
        font-weight: 500;
        transition: all 0.3s ease;
        box-shadow: 0 4px 14px rgba(13, 110, 253, 0.25);
    }

    #quanLyNguoiDungModal .btn-primary:hover {
        background: linear-gradient(135deg, #0052a8 0%, #003d82 100%);
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(13, 110, 253, 0.35);
    }

    #quanLyNguoiDungModal .text-center p {
        color: #64748b;
        font-size: 14px;
    }
</style>

<script>
    document.getElementById('vaiTroModal').addEventListener('change', function () {
        const selectedRole = this.value;

        document.querySelectorAll('.additional-fields-modal').forEach(el => {
            el.style.display = 'none';
            el.querySelectorAll('input').forEach(input => input.removeAttribute('required'));
        });

        if (selectedRole === 'giang_vien') {
            let fields = document.getElementById('giangVienFieldsModal');
            fields.style.display = 'block';
            fields.querySelector('[name="ma_giang_vien"]').setAttribute('required', 'required');
        } else if (selectedRole === 'sinh_vien') {
            let fields = document.getElementById('sinhVienFieldsModal');
            fields.style.display = 'block';
            fields.querySelector('[name="ma_sinh_vien"]').setAttribute('required', 'required');
        } else if (selectedRole === 'lanh_dao') {
            let fields = document.getElementById('lanhDaoFieldsModal');
            fields.style.display = 'block';
            fields.querySelector('[name="ma_lanh_dao"]').setAttribute('required', 'required');
        }
    });

    function showQuanLyTaiKhoan() {
        const modal = new bootstrap.Modal(document.getElementById('quanLyNguoiDungModal'));
        modal.show();
    }
</script>