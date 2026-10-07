<!-- Modal Quản lý tài khoản -->
<div class="modal fade" id="quanLyNguoiDungModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-person-plus"></i> Quản lý tài khoản</h5>
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
                        <i class="bi bi-person-plus"></i> Tạo tài khoản
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
}

#quanLyNguoiDungModal .modal-header {
    background: linear-gradient(135deg, #0d6efd 0%, #0052a8 100%);
    color: white;
    border-radius: 16px 16px 0 0;
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
    font-weight: 600;
}

#quanLyNguoiDungModal .btn-primary:hover {
    background: linear-gradient(135deg, #0052a8 0%, #003d82 100%);
    transform: translateY(-2px);
}

#quanLyNguoiDungModal .text-center p {
    color: #64748b;
    font-size: 14px;
}
</style>

<script>
document.getElementById('vaiTroModal').addEventListener('change', function() {
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
