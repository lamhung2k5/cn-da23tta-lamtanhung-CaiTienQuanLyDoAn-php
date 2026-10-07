<?php
/**
 * XỬ LÝ TẠO NGƯỜI DÙNG
 */

require_once '../bootstrap.php';
requireRole(ROLE_LANH_DAO);

$redirectUrl = 'lanh_dao/quan_ly_nguoi_dung.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $vaiTro = sanitize($_POST['vai_tro'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $hoTen = sanitize($_POST['ho_ten'] ?? '');
    $matKhau = $_POST['mat_khau'] ?? '';
    $xacNhanMatKhau = $_POST['xac_nhan_mat_khau'] ?? '';

    // Validate
    if (empty($vaiTro) || empty($email) || empty($hoTen) || empty($matKhau)) {
        setFlashMessage('error', 'Vui lòng nhập đầy đủ thông tin');
        redirect($redirectUrl);
    }

    if (!isValidEmail($email)) {
        setFlashMessage('error', 'Email không hợp lệ');
        redirect($redirectUrl);
    }

    if (strlen($matKhau) < PASSWORD_MIN_LENGTH) {
        setFlashMessage('error', 'Mật khẩu phải có ít nhất ' . PASSWORD_MIN_LENGTH . ' ký tự');
        redirect($redirectUrl);
    }

    if ($matKhau !== $xacNhanMatKhau) {
        setFlashMessage('error', 'Mật khẩu xác nhận không khớp');
        redirect($redirectUrl);
    }

    // Kiểm tra email đã tồn tại
    $nguoiDungModel = new NguoiDungModel();
    if ($nguoiDungModel->emailExists($email)) {
        setFlashMessage('error', 'Email đã được sử dụng');
        redirect($redirectUrl);
    }

    // Lấy thông tin bổ sung theo vai trò
    $additionalData = [];

    switch ($vaiTro) {
        case ROLE_GIANG_VIEN:
            $additionalData = [
                'ma_giang_vien' => sanitize($_POST['ma_giang_vien'] ?? ''),
                'khoa' => sanitize($_POST['khoa'] ?? ''),
                'chuyen_mon' => sanitize($_POST['chuyen_mon'] ?? ''),
                'so_dien_thoai' => sanitize($_POST['so_dien_thoai'] ?? '')
            ];

            if (empty($additionalData['ma_giang_vien'])) {
                setFlashMessage('error', 'Vui lòng nhập mã giảng viên');
                redirect($redirectUrl);
            }

            $gvModel = new GiangVienModel();
            if ($gvModel->maExists($additionalData['ma_giang_vien'])) {
                setFlashMessage('error', 'Mã giảng viên đã tồn tại');
                redirect($redirectUrl);
            }
            break;

        case ROLE_SINH_VIEN:
            $additionalData = [
                'ma_sinh_vien' => sanitize($_POST['ma_sinh_vien'] ?? ''),
                'lop' => sanitize($_POST['lop'] ?? ''),
                'khoa_hoc' => sanitize($_POST['khoa_hoc'] ?? ''),
                'chuyen_nganh' => sanitize($_POST['chuyen_nganh'] ?? ''),
                'so_dien_thoai' => sanitize($_POST['so_dien_thoai_sv'] ?? '')
            ];

            if (empty($additionalData['ma_sinh_vien'])) {
                setFlashMessage('error', 'Vui lòng nhập mã sinh viên');
                redirect($redirectUrl);
            }

            $svModel = new SinhVienModel();
            if ($svModel->mssvExists($additionalData['ma_sinh_vien'])) {
                setFlashMessage('error', 'Mã sinh viên đã tồn tại');
                redirect($redirectUrl);
            }
            break;

        case ROLE_LANH_DAO:
            $additionalData = [
                'ma_lanh_dao' => sanitize($_POST['ma_lanh_dao'] ?? ''),
                'chuc_vu' => sanitize($_POST['chuc_vu'] ?? ''),
                'khoa' => sanitize($_POST['khoa_ld'] ?? ''),
                'so_dien_thoai' => sanitize($_POST['so_dien_thoai_ld'] ?? '')
            ];

            if (empty($additionalData['ma_lanh_dao'])) {
                setFlashMessage('error', 'Vui lòng nhập mã lãnh đạo');
                redirect($redirectUrl);
            }

            $ldModel = new LanhDaoModel();
            if ($ldModel->maExists($additionalData['ma_lanh_dao'])) {
                setFlashMessage('error', 'Mã lãnh đạo đã tồn tại');
                redirect($redirectUrl);
            }
            break;
    }

    try {
        // Lấy vai_tro_id từ mã vai trò
        $vaiTroModel = new VaiTroModel();
        $vaiTroInfo = $vaiTroModel->findByMa($vaiTro);

        if (!$vaiTroInfo) {
            setFlashMessage('error', 'Vai trò không hợp lệ');
            redirect($redirectUrl);
        }

        // Tạo tài khoản trực tiếp (không cần OTP vì lãnh đạo tạo)
        $nguoiDungId = $nguoiDungModel->insert([
            'email' => $email,
            'mat_khau' => hashPassword($matKhau),
            'ho_ten' => $hoTen,
            'vai_tro_id' => $vaiTroInfo['id'],
            'trang_thai' => 'active'
        ]);

        if ($nguoiDungId) {
            // Tạo profile tương ứng
            switch ($vaiTro) {
                case ROLE_GIANG_VIEN:
                    $gvModel = new GiangVienModel();
                    $gvModel->insert(array_merge($additionalData, ['nguoi_dung_id' => $nguoiDungId]));
                    break;

                case ROLE_SINH_VIEN:
                    $svModel = new SinhVienModel();
                    $svModel->insert(array_merge($additionalData, ['nguoi_dung_id' => $nguoiDungId]));
                    break;

                case ROLE_LANH_DAO:
                    $ldModel = new LanhDaoModel();
                    $ldModel->insert(array_merge($additionalData, ['nguoi_dung_id' => $nguoiDungId]));
                    break;
            }

            setFlashMessage('success', 'Tạo tài khoản thành công');
        } else {
            setFlashMessage('error', 'Không thể tạo tài khoản');
        }

    } catch (Exception $e) {
        setFlashMessage('error', 'Lỗi hệ thống: ' . $e->getMessage());
    }
}

redirect($redirectUrl);
