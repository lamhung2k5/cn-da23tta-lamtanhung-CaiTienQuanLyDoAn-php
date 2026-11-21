<?php

trait RegistrationWorkflow {
    private function beginRegistrationWorkflow() {
        // Counts after waiting for a lock must observe the preceding commit.
        $this->db->exec('SET TRANSACTION ISOLATION LEVEL READ COMMITTED');
        $this->db->beginTransaction();
    }
    private function lockRegistrationScope($studentId, $topicId) {
        $topic = $this->queryOne('SELECT giang_vien_id FROM de_tai WHERE id=?', [$topicId]);
        if (!$topic) { throw new DomainException('Đề tài không tồn tại.'); }
        // Every registration/decision uses the same lock order.
        $lecturer = $this->queryOne('SELECT * FROM giang_vien WHERE id=? FOR UPDATE', [$topic['giang_vien_id']]);
        $student = $this->queryOne('SELECT * FROM sinh_vien WHERE id=? FOR UPDATE', [$studentId]);
        $topic = $this->queryOne('SELECT * FROM de_tai WHERE id=? FOR UPDATE', [$topicId]);
        if (!$lecturer || !$student || !$topic) { throw new DomainException('Hồ sơ không hợp lệ.'); }
        return [$topic, $lecturer, $student];
    }

    private function checkRegistrationCapacity($studentId, $topic, $lecturer) {
        if ($topic['trang_thai'] !== STATUS_DA_DUYET) { throw new DomainException('Đề tài chưa được duyệt.'); }
        $sameType = $this->queryOne("SELECT COUNT(*) AS total FROM dang_ky_de_tai dk JOIN de_tai dt ON dt.id=dk.de_tai_id WHERE dk.sinh_vien_id=? AND dt.he_dao_tao=? AND dk.trang_thai='da_duyet'", [$studentId, $topic['he_dao_tao']]);
        if ($sameType['total'] > 0) { throw new DomainException('Sinh viên đã có đề tài được duyệt cùng hệ đào tạo.'); }
        $occupied = $this->queryOne("SELECT COUNT(*) AS total FROM dang_ky_de_tai WHERE de_tai_id=? AND trang_thai='da_duyet'", [$topic['id']]);
        if ($occupied['total'] >= $topic['so_luong_sv']) { throw new DomainException('Đề tài đã đủ số lượng sinh viên.'); }
        $used = $this->queryOne("SELECT COUNT(DISTINCT dk.sinh_vien_id) AS total FROM dang_ky_de_tai dk JOIN de_tai dt ON dt.id=dk.de_tai_id WHERE dt.giang_vien_id=? AND dt.he_dao_tao=? AND dk.trang_thai='da_duyet'", [$lecturer['id'], $topic['he_dao_tao']]);
        $column = $topic['he_dao_tao'] === 'co_so_nganh' ? 'gioi_han_sv_csn' : 'gioi_han_sv_cn';
        if ($used['total'] >= (int)$lecturer[$column]) { throw new DomainException('Giảng viên đã đủ số lượng sinh viên hướng dẫn.'); }
    }

    private function synchronizeRegistrationCount($topicId) {
        $stmt = $this->db->prepare("UPDATE de_tai SET so_luong_da_dang_ky=(SELECT COUNT(*) FROM dang_ky_de_tai WHERE de_tai_id=? AND trang_thai='da_duyet') WHERE id=?");
        $stmt->execute([$topicId, $topicId]);
    }

    private function registrationError($error) {
        if ($this->db->inTransaction()) { $this->db->rollBack(); }
        return ['success' => false, 'message' => $error instanceof DomainException ? $error->getMessage() : safeError($error)];
    }

    public function dangKyDeTai($sinhVienId, $deTaiId) {
        try {
            $this->beginRegistrationWorkflow();
            [$topic, $lecturer, $student] = $this->lockRegistrationScope($sinhVienId, $deTaiId);
            if (!hasRole(ROLE_SINH_VIEN) || (int)$student['nguoi_dung_id'] !== (int)($_SESSION['user_id'] ?? 0)) {
                throw new DomainException('Bạn không có quyền đăng ký cho sinh viên này.');
            }
            $registrationSetting = (new CaiDatModel())->getByKey('cho_phep_dang_ky');
            if ($registrationSetting && $registrationSetting['value'] === '0') { throw new DomainException('Đợt đăng ký đang đóng.'); }
            $window = (new DangKyThoiGian())->status($topic['he_dao_tao']);
            if (!$window['can_register']) { throw new DomainException($window['label'] . '. ' . $window['description']); }
            $this->checkRegistrationCapacity($sinhVienId, $topic, $lecturer);
            if ($this->findOneBy(['sinh_vien_id' => $sinhVienId, 'de_tai_id' => $deTaiId])) {
                throw new DomainException('Bạn đã đăng ký đề tài này.');
            }
            $this->insert(['sinh_vien_id' => $sinhVienId, 'de_tai_id' => $deTaiId,
                'trang_thai' => STATUS_DA_DUYET, 'ngay_duyet' => date('Y-m-d H:i:s')]);
            $this->synchronizeRegistrationCount($deTaiId);
            $this->db->commit();
            return ['success' => true, 'message' => 'Đăng ký đề tài thành công.'];
        } catch (Throwable $error) { return $this->registrationError($error); }
    }

    private function decideRegistration($id, $status, $reason = null) {
        try {
            $this->beginRegistrationWorkflow();
            $registration = $this->findById($id);
            if (!$registration) { throw new DomainException('Đăng ký không tồn tại.'); }
            [$topic, $lecturer] = $this->lockRegistrationScope($registration['sinh_vien_id'], $registration['de_tai_id']);
            if (!hasRole(ROLE_LANH_DAO) && (!hasRole(ROLE_GIANG_VIEN) || (int)$lecturer['nguoi_dung_id'] !== (int)($_SESSION['user_id'] ?? 0))) {
                throw new DomainException('Bạn không có quyền xử lý đăng ký này.');
            }
            $registration = $this->queryOne('SELECT * FROM dang_ky_de_tai WHERE id=? FOR UPDATE', [$id]);
            if (!$registration) { throw new DomainException('Đăng ký không tồn tại.'); }
            if ($registration['trang_thai'] === $status) {
                $this->synchronizeRegistrationCount($topic['id']);
                $this->db->commit();
                return ['success' => true, 'changed' => false, 'message' => 'Đăng ký đã ở trạng thái này.'];
            }
            if ($registration['trang_thai'] !== STATUS_CHO_DUYET) { throw new DomainException('Chỉ xử lý đăng ký đang chờ duyệt.'); }
            if ($status === STATUS_DA_DUYET) {
                $this->checkRegistrationCapacity($registration['sinh_vien_id'], $topic, $lecturer);
            } elseif (!is_string($reason) || trim($reason) === '') { throw new DomainException('Vui lòng nhập lý do từ chối.'); }
            $this->update($id, ['trang_thai' => $status, 'ly_do_tu_choi' => $reason,
                'nguoi_duyet_id' => $_SESSION['user_id'], 'ngay_duyet' => date('Y-m-d H:i:s')]);
            $this->synchronizeRegistrationCount($topic['id']);
            auditSecurity('registration.' . $status, (int)$id);
            $this->db->commit();
            return ['success' => true, 'changed' => true, 'message' => 'Đã cập nhật đăng ký.'];
        } catch (Throwable $error) { return $this->registrationError($error); }
    }

    public function duyetSinhVien($dangKyId, $nguoiDuyetId) {
        if ((int)$nguoiDuyetId !== (int)($_SESSION['user_id'] ?? 0)) { return ['success' => false, 'message' => 'Người duyệt không hợp lệ.']; }
        return $this->decideRegistration($dangKyId, STATUS_DA_DUYET);
    }

    public function tuChoiSinhVien($dangKyId, $lyDo) {
        return $this->decideRegistration($dangKyId, STATUS_TU_CHOI, $lyDo);
    }

    public function xoaDangKyAnToan($id, $student = false, $redundantOnly = false) {
        try {
            $this->beginRegistrationWorkflow();
            $registration = $this->findById($id);
            if (!$registration) { throw new DomainException('Đăng ký không tồn tại.'); }
            [$topic, $lecturer, $profile] = $this->lockRegistrationScope($registration['sinh_vien_id'], $registration['de_tai_id']);
            $registration = $this->queryOne('SELECT * FROM dang_ky_de_tai WHERE id=? FOR UPDATE', [$id]);
            if (!$registration) { throw new DomainException('Đăng ký không tồn tại.'); }
            if ($student) {
                if (!hasRole(ROLE_SINH_VIEN) || (int)$profile['nguoi_dung_id'] !== (int)($_SESSION['user_id'] ?? 0)) { throw new DomainException('Bạn không có quyền xóa đăng ký này.'); }
            } elseif (!hasRole(ROLE_LANH_DAO) && (!hasRole(ROLE_GIANG_VIEN) || (int)$lecturer['nguoi_dung_id'] !== (int)($_SESSION['user_id'] ?? 0))) { throw new DomainException('Bạn không có quyền xóa đăng ký này.'); }
            if ($registration['trang_thai'] !== STATUS_TU_CHOI) {
                $other = $this->queryOne("SELECT COUNT(*) AS total FROM dang_ky_de_tai dk JOIN de_tai dt ON dt.id=dk.de_tai_id WHERE dk.sinh_vien_id=? AND dt.he_dao_tao=? AND dk.trang_thai='da_duyet' AND dk.id<>?", [$registration['sinh_vien_id'], $topic['he_dao_tao'], $id]);
                if (!$redundantOnly || $student || $registration['trang_thai'] === STATUS_DA_DUYET || !$other['total']) { throw new DomainException('Chỉ được xóa đăng ký bị từ chối hoặc đăng ký chờ đã dư.'); }
            }
            $this->delete($id);
            $this->synchronizeRegistrationCount($topic['id']);
            $this->db->commit();
            return ['success' => true, 'message' => 'Xóa đăng ký thành công.'];
        } catch (Throwable $error) { return $this->registrationError($error); }
    }

    public function duyetHangLoat($ids, $actorId) {
        return $this->decideMany($ids, null, $actorId);
    }

    public function tuChoiHangLoat($ids, $reason) {
        return $this->decideMany($ids, $reason, $_SESSION['user_id'] ?? 0);
    }

    private function decideMany($ids, $reason, $actorId) {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if (!$ids || count($ids) > 500) { return ['success' => false, 'message' => 'Danh sách không hợp lệ.']; }
        // Preflight the whole set before changing any record.
        foreach ($ids as $id) { requireRegistrationOwner($id); }
        $changed = 0;
        $failed = 0;
        foreach ($ids as $id) {
            $result = $reason === null ? $this->duyetSinhVien($id, $actorId) : $this->tuChoiSinhVien($id, $reason);
            if (!$result['success']) { $failed++; }
            elseif (!empty($result['changed'])) { $changed++; }
        }
        return ['success' => $failed === 0, 'so_duyet' => $changed, 'so_tu_choi' => $changed,
            'message' => "Đã cập nhật $changed đăng ký; $failed đăng ký không đủ điều kiện."];
    }

    public function duyetTuDongThongMinh($lecturerId, $csn, $cn) {
        $lecturer = $this->queryOne('SELECT nguoi_dung_id FROM giang_vien WHERE id=?', [$lecturerId]);
        if (!$lecturer || (!hasRole(ROLE_LANH_DAO) && (int)$lecturer['nguoi_dung_id'] !== (int)($_SESSION['user_id'] ?? 0))) { return ['success' => false, 'message' => 'Không có quyền duyệt.']; }
        $rows = $this->query("SELECT dk.id, dt.he_dao_tao FROM dang_ky_de_tai dk JOIN de_tai dt ON dt.id=dk.de_tai_id WHERE dt.giang_vien_id=? AND dk.trang_thai='cho_duyet' ORDER BY dk.ngay_dang_ky, dk.id", [$lecturerId]);
        $count = 0;
        foreach ($rows as $row) {
            if (($row['he_dao_tao'] === 'co_so_nganh' && !$csn) || ($row['he_dao_tao'] === 'chuyen_nganh' && !$cn)) { continue; }
            $result = $this->duyetSinhVien($row['id'], $_SESSION['user_id']);
            if ($result['success'] && !empty($result['changed'])) { $count++; }
        }
        return ['success' => true, 'so_duyet' => $count, 'so_tu_choi' => 0, 'message' => 'Giữ chờ các đăng ký chưa đủ điều kiện.'];
    }
}
