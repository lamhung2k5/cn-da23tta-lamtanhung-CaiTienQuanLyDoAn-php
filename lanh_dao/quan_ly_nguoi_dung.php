<?php
/**
 * QUẢN LÝ TÀI KHOẢN - LÃNH ĐẠO
 */

require_once '../bootstrap.php';
requireRole(ROLE_LANH_DAO);

$user = getCurrentUser();
$pageTitle = 'Quản lý tài khoản - Lãnh đạo';
$currentPage = 'quan_ly_nguoi_dung.php';

// Flash messages
$error = getFlashMessage('error');
$success = getFlashMessage('success');
$warning = getFlashMessage('warning');

// Lấy danh sách người dùng từ Database
$db = Database::getInstance()->getConnection();
$sql = "
    SELECT nd.id, nd.email, nd.ho_ten, nd.trang_thai, nd.created_at, 
           vt.ma_vai_tro, vt.ten_vai_tro,
           gv.ma_giang_vien, sv.ma_sinh_vien, ld.ma_lanh_dao
    FROM nguoi_dung nd
    JOIN vai_tro vt ON nd.vai_tro_id = vt.id
    LEFT JOIN giang_vien gv ON nd.id = gv.nguoi_dung_id
    LEFT JOIN sinh_vien sv ON nd.id = sv.nguoi_dung_id
    LEFT JOIN lanh_dao ld ON nd.id = ld.nguoi_dung_id
    ORDER BY nd.id DESC
";
$stmt = $db->prepare($sql);
$stmt->execute();
$danhSachNguoiDung = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Thống kê số lượng
$tongTaiKhoan = count($danhSachNguoiDung);
$tongGiangVien = 0;
$tongSinhVien = 0;
$tongLanhDao = 0;

foreach ($danhSachNguoiDung as $u) {
    if ($u['ma_vai_tro'] === ROLE_GIANG_VIEN)
        $tongGiangVien++;
    elseif ($u['ma_vai_tro'] === ROLE_SINH_VIEN)
        $tongSinhVien++;
    elseif ($u['ma_vai_tro'] === ROLE_LANH_DAO)
        $tongLanhDao++;
}

// Lấy danh sách vai trò cho form
$vaiTroModel = new VaiTroModel();
$danhSachVaiTro = $vaiTroModel->getAllVaiTro();

// Include header
include_once __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <?php include 'includes/sidebar_lanh_dao.php'; ?>

        <!-- Main content -->
        <div class="col-md-10 p-4">
            <!-- Welcome Card -->
            <div class="card mb-4 fade-in-up border-0 shadow-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h3 class="mb-2 text-dark">
                                Quản lý <strong>tài khoản người dùng</strong>
                            </h3>
                            <p class="mb-0 text-muted">
                                Quản lý danh sách tài khoản, tạo mới người dùng và import dữ liệu cho hệ thống.
                            </p>
                        </div>
                        <div class="col-md-4 text-md-end mt-3 mt-md-0">
                            <a href="../auth/import_sinh_vien.php" class="btn btn-outline-primary btn-sm me-2">
                                <i class="bi bi-upload"></i> Import sinh viên
                            </a>
                            <a href="../auth/import_giang_vien.php" class="btn btn-outline-success btn-sm">
                                <i class="bi bi-upload"></i> Import giảng viên
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Flash Messages -->
            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> <?= $success ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= $error ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($warning): ?>
                <div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert">
                    <i class="bi bi-exclamation-circle-fill me-2"></i> <?= $warning ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Card Thống kê nhanh -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card border border-dark shadow-sm bg-white">
                        <div class="card-body p-3 d-flex justify-content-between align-items-center">
                            <div>
                                <div class="small fw-semibold text-dark text-opacity-75">TỔNG TÀI KHOẢN</div>
                                <div class="fs-3 fw-bold text-dark"><?= $tongTaiKhoan ?></div>
                            </div>
                            <i class="bi bi-people fs-1 text-dark opacity-50"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border border-dark shadow-sm bg-white">
                        <div class="card-body p-3 d-flex justify-content-between align-items-center">
                            <div>
                                <div class="small fw-semibold text-dark text-opacity-75">LÃNH ĐẠO</div>
                                <div class="fs-3 fw-bold text-dark"><?= $tongLanhDao ?></div>
                            </div>
                            <i class="bi bi-person-workspace fs-1 text-dark opacity-50"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border border-dark shadow-sm bg-white">
                        <div class="card-body p-3 d-flex justify-content-between align-items-center">
                            <div>
                                <div class="small fw-semibold text-dark text-opacity-75">GIẢNG VIÊN</div>
                                <div class="fs-3 fw-bold text-dark"><?= $tongGiangVien ?></div>
                            </div>
                            <i class="bi bi-person-badge fs-1 text-dark opacity-50"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border border-dark shadow-sm bg-white">
                        <div class="card-body p-3 d-flex justify-content-between align-items-center">
                            <div>
                                <div class="small fw-semibold text-dark text-opacity-75">SINH VIÊN</div>
                                <div class="fs-3 fw-bold text-dark"><?= $tongSinhVien ?></div>
                            </div>
                            <i class="bi bi-mortarboard fs-1 text-dark opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card Quản lý tài khoản (Tabs & Content) -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom">
                    <ul class="nav nav-tabs card-header-tabs mb-0" id="userManagementTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active fw-semibold" id="tab-danh-sach" data-bs-toggle="tab"
                                data-bs-target="#content-danh-sach" type="button" role="tab">
                                Danh sách tài khoản
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-semibold" id="tab-tao-moi" data-bs-toggle="tab"
                                data-bs-target="#content-tao-moi" type="button" role="tab">
                                Tạo tài khoản mới
                            </button>
                        </li>
                    </ul>
                </div>

                <div class="card-body p-0">
                    <div class="tab-content">
                        <!-- TAB 1: DANH SÁCH TÀI KHOẢN -->
                        <div class="tab-pane fade show active" id="content-danh-sach" role="tabpanel">
                            <div
                                class="p-3 border-bottom bg-light d-flex flex-wrap gap-2 justify-content-between align-items-center">
                                <!-- Filter theo vai trò -->
                                <div class="btn-group btn-group-sm" role="group" id="roleFilterGroup">
                                    <button type="button" class="btn btn-outline-dark active" data-role="all">Tất cả
                                        (<?= $tongTaiKhoan ?>)</button>
                                    <button type="button" class="btn btn-outline-warning text-dark"
                                        data-role="lanh_dao">Lãnh
                                        đạo (<?= $tongLanhDao ?>)</button>
                                    <button type="button" class="btn btn-outline-info" data-role="giang_vien">Giảng viên
                                        (<?= $tongGiangVien ?>)</button>
                                    <button type="button" class="btn btn-outline-success" data-role="sinh_vien">Sinh
                                        viên (<?= $tongSinhVien ?>)</button>
                                </div>

                                <!-- Tìm kiếm -->
                                <div style="min-width: 280px;">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-white border-end-0">
                                            <i class="bi bi-search text-muted"></i>
                                        </span>
                                        <input type="text" id="searchUser" class="form-control border-start-0"
                                            placeholder="Tìm theo tên, email...">
                                    </div>
                                </div>
                            </div>

                            <!-- Bảng dữ liệu -->
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered mb-0 align-middle" id="userTable">
                                    <thead class="table-light text-center">
                                        <tr>
                                            <th style="width: 50px;">STT</th>
                                            <th>Họ và tên</th>
                                            <th>Email</th>
                                            <th class="col-mssv" style="width: 130px; display: none;">MSSV</th>
                                            <th style="width: 140px;">Vai trò</th>
                                            <th style="width: 120px;">Trạng thái</th>
                                            <th style="width: 150px;">Ngày tạo</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($danhSachNguoiDung)): ?>
                                            <tr>
                                                <td colspan="7" class="text-center py-4 text-muted">
                                                    <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                                    Chưa có tài khoản nào trong hệ thống
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($danhSachNguoiDung as $index => $u): ?>
                                                <?php 
                                                    $mssv = ($u['ma_vai_tro'] === ROLE_SINH_VIEN) ? ($u['ma_sinh_vien'] ?? '') : '';
                                                ?>
                                                <tr class="user-row" data-role="<?= $u['ma_vai_tro'] ?>"
                                                    data-search="<?= strtolower(htmlspecialchars($u['ho_ten'] . ' ' . $u['email'] . ' ' . $mssv)) ?>">
                                                    <td class="text-center fw-bold text-muted"><?= $index + 1 ?></td>
                                                    <td>
                                                        <div class="fw-semibold text-dark"><?= htmlspecialchars($u['ho_ten']) ?>
                                                        </div>
                                                    </td>
                                                    <td><?= htmlspecialchars($u['email']) ?></td>
                                                    <td class="col-mssv text-center font-monospace small text-dark fw-medium" style="display: none;"><?= htmlspecialchars($mssv ?: '-') ?></td>
                                                    <td class="text-center">
                                                        <?php if ($u['ma_vai_tro'] === ROLE_GIANG_VIEN): ?>
                                                            <span class="badge bg-info text-white px-2 py-1">Giảng viên</span>
                                                        <?php elseif ($u['ma_vai_tro'] === ROLE_SINH_VIEN): ?>
                                                            <span class="badge bg-success text-white px-2 py-1">Sinh viên</span>
                                                        <?php elseif ($u['ma_vai_tro'] === ROLE_LANH_DAO): ?>
                                                            <span class="badge bg-secondary text-white px-2 py-1">Lãnh đạo</span>
                                                        <?php else: ?>
                                                            <span
                                                                class="badge bg-dark text-white px-2 py-1"><?= htmlspecialchars($u['ten_vai_tro']) ?></span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <?php if ($u['trang_thai'] === 'active'): ?>
                                                            <span
                                                                class="badge bg-success-subtle text-success border border-success px-2 py-1">Hoạt
                                                                động</span>
                                                        <?php else: ?>
                                                            <span
                                                                class="badge bg-danger-subtle text-danger border border-danger px-2 py-1">Bị
                                                                khóa</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-center small text-muted">
                                                        <?= !empty($u['created_at']) ? date('d/m/Y H:i', strtotime($u['created_at'])) : 'N/A' ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- TAB 2: TẠO TÀI KHOẢN MỚI -->
                        <div class="tab-pane fade" id="content-tao-moi" role="tabpanel">
                            <div class="p-4" style="max-width: 800px; margin: 0 auto;">
                                <form method="POST" action="xu_ly_tao_nguoi_dung.php" id="formTaoNguoiDung">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Vai trò <span
                                                    class="text-danger">*</span></label>
                                            <select name="vai_tro" id="selectVaiTro"
                                                class="form-select form-select-modern" required>
                                                <option value="">-- Chọn vai trò --</option>
                                                <option value="lanh_dao">Lãnh đạo</option>
                                                <option value="giang_vien">Giảng viên</option>
                                                <option value="sinh_vien">Sinh viên</option>
                                            </select>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Họ tên <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" name="ho_ten" class="form-control form-control-modern"
                                                placeholder="Nhập họ và tên đầy đủ" required>
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label fw-semibold">Email <span
                                                    class="text-danger">*</span></label>
                                            <input type="email" name="email" class="form-control form-control-modern"
                                                placeholder="example@tvu.edu.vn" required>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Mật khẩu <span
                                                    class="text-danger">*</span></label>
                                            <input type="password" name="mat_khau"
                                                class="form-control form-control-modern" minlength="6"
                                                placeholder="Mật khẩu tối thiểu 6 ký tự" required>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Xác nhận mật khẩu <span
                                                    class="text-danger">*</span></label>
                                            <input type="password" name="xac_nhan_mat_khau"
                                                class="form-control form-control-modern" placeholder="Nhập lại mật khẩu"
                                                required>
                                        </div>
                                    </div>

                                    <!-- Giảng viên fields -->
                                    <div id="fieldsGiangVien"
                                        class="role-specific-fields mt-4 p-3 bg-light rounded border"
                                        style="display: none;">
                                        <h6 class="fw-bold text-primary mb-3"><i class="bi bi-person-badge me-1"></i>
                                            Thông tin bổ sung Giảng viên</h6>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold">Mã giảng viên <span
                                                        class="text-danger">*</span></label>
                                                <input type="text" name="ma_giang_vien"
                                                    class="form-control form-control-modern" placeholder="VD: GV001">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold">Khoa</label>
                                                <input type="text" name="khoa" class="form-control form-control-modern"
                                                    placeholder="VD: Công nghệ thông tin">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold">Chuyên môn</label>
                                                <input type="text" name="chuyen_mon"
                                                    class="form-control form-control-modern"
                                                    placeholder="VD: Kỹ thuật phần mềm">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold">Số điện thoại</label>
                                                <input type="text" name="so_dien_thoai"
                                                    class="form-control form-control-modern">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Sinh viên fields -->
                                    <div id="fieldsSinhVien"
                                        class="role-specific-fields mt-4 p-3 bg-light rounded border"
                                        style="display: none;">
                                        <h6 class="fw-bold text-success mb-3"><i class="bi bi-mortarboard me-1"></i>
                                            Thông tin bổ sung Sinh viên</h6>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold">Mã số sinh viên <span
                                                        class="text-danger">*</span></label>
                                                <input type="text" name="ma_sinh_vien"
                                                    class="form-control form-control-modern"
                                                    placeholder="VD: 110121001">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold">Lớp</label>
                                                <input type="text" name="lop" class="form-control form-control-modern"
                                                    placeholder="VD: DA21TTA">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold">Khóa học</label>
                                                <input type="text" name="khoa_hoc"
                                                    class="form-control form-control-modern"
                                                    placeholder="VD: 2021 - 2025">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold">Chuyên ngành</label>
                                                <input type="text" name="chuyen_nganh"
                                                    class="form-control form-control-modern"
                                                    placeholder="VD: Công nghệ thông tin">
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label fw-semibold">Số điện thoại</label>
                                                <input type="text" name="so_dien_thoai_sv"
                                                    class="form-control form-control-modern">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Lãnh đạo fields -->
                                    <div id="fieldsLanhDao"
                                        class="role-specific-fields mt-4 p-3 bg-light rounded border"
                                        style="display: none;">
                                        <h6 class="fw-bold mb-3" style="color: #fd7e14;"><i
                                                class="bi bi-person-workspace me-1"></i> Thông tin bổ sung Lãnh đạo</h6>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold">Mã lãnh đạo <span
                                                        class="text-danger">*</span></label>
                                                <input type="text" name="ma_lanh_dao"
                                                    class="form-control form-control-modern" placeholder="VD: LD001">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold">Chức vụ</label>
                                                <input type="text" name="chuc_vu"
                                                    class="form-control form-control-modern"
                                                    placeholder="VD: Trưởng khoa">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold">Khoa</label>
                                                <input type="text" name="khoa_ld"
                                                    class="form-control form-control-modern"
                                                    placeholder="VD: Công nghệ thông tin">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold">Số điện thoại</label>
                                                <input type="text" name="so_dien_thoai_ld"
                                                    class="form-control form-control-modern">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-4 text-end">
                                        <button type="submit" class="btn btn-primary px-4 py-2">
                                            Tạo tài khoản
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .form-control-modern,
    .form-select-modern {
        border: 1.5px solid #212529;
        border-radius: 8px;
        padding: 10px 14px;
        transition: all 0.2s ease;
    }

    .form-control-modern:focus,
    .form-select-modern:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.15);
        outline: none;
    }

    .user-row {
        transition: background-color 0.15s ease;
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Xử lý chuyển vai trò trong Form Tạo Tài Khoản
        const selectVaiTro = document.getElementById('selectVaiTro');
        const roleFields = document.querySelectorAll('.role-specific-fields');

        selectVaiTro.addEventListener('change', function () {
            const val = this.value;

            roleFields.forEach(el => {
                el.style.display = 'none';
                el.querySelectorAll('input').forEach(inp => inp.removeAttribute('required'));
            });

            if (val === 'giang_vien') {
                const f = document.getElementById('fieldsGiangVien');
                f.style.display = 'block';
                f.querySelector('[name="ma_giang_vien"]').setAttribute('required', 'required');
            } else if (val === 'sinh_vien') {
                const f = document.getElementById('fieldsSinhVien');
                f.style.display = 'block';
                f.querySelector('[name="ma_sinh_vien"]').setAttribute('required', 'required');
            } else if (val === 'lanh_dao') {
                const f = document.getElementById('fieldsLanhDao');
                f.style.display = 'block';
                f.querySelector('[name="ma_lanh_dao"]').setAttribute('required', 'required');
            }
        });

        // Xử lý Lọc & Tìm kiếm ở Tab Danh Sách
        const searchInput = document.getElementById('searchUser');
        const roleButtons = document.querySelectorAll('#roleFilterGroup button');
        const userRows = document.querySelectorAll('.user-row');

        let currentRole = 'all';

        roleButtons.forEach(btn => {
            btn.addEventListener('click', function () {
                roleButtons.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                currentRole = this.getAttribute('data-role');
                filterTable();
            });
        });

        searchInput.addEventListener('input', function () {
            filterTable();
        });

        function filterTable() {
            const query = searchInput.value.toLowerCase().trim();
            const showMssv = (currentRole === 'sinh_vien');

            document.querySelectorAll('.col-mssv').forEach(el => {
                el.style.display = showMssv ? '' : 'none';
            });

            userRows.forEach(row => {
                const rowRole = row.getAttribute('data-role');
                const rowSearch = row.getAttribute('data-search');

                const matchesRole = (currentRole === 'all' || rowRole === currentRole);
                const matchesQuery = (!query || rowSearch.includes(query));

                if (matchesRole && matchesQuery) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
    });
</script>