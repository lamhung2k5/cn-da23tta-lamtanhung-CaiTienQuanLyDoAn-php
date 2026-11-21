<?php

class DeTaiQuanTamModel extends BaseModel {
    protected $table = 'de_tai_quan_tam';

    public function limit() {
        $row = (new CaiDatModel())->getByKey('gioi_han_de_tai_quan_tam');
        $value = $row ? filter_var($row['value'], FILTER_VALIDATE_INT) : false;
        return $value !== false && $value >= 1 && $value <= 50 ? $value : 6;
    }

    public function savedMap($studentId) {
        $map = [];
        foreach ($this->query('SELECT id,de_tai_id FROM de_tai_quan_tam WHERE sinh_vien_id=?', [$studentId]) as $row) {
            if ($row['de_tai_id'] !== null) { $map[(int)$row['de_tai_id']] = (int)$row['id']; }
        }
        return $map;
    }

    public function savedCount($studentId) { return (int)$this->count(['sinh_vien_id'=>$studentId]); }

    public function registrationContext($studentId) {
        $context = ['topics'=>[], 'types'=>[]];
        foreach ($this->query('SELECT dk.de_tai_id,dk.trang_thai,dt.he_dao_tao FROM dang_ky_de_tai dk JOIN de_tai dt ON dt.id=dk.de_tai_id WHERE dk.sinh_vien_id=?', [$studentId]) as $row) {
            $context['topics'][(int)$row['de_tai_id']] = $row['trang_thai'];
            if ($row['trang_thai'] === STATUS_DA_DUYET) { $context['types'][$row['he_dao_tao']] = true; }
        }
        return $context;
    }

    public function listForStudent($studentId, $type = null) {
        $sql = "SELECT qt.id AS quan_tam_id,qt.de_tai_id,qt.created_at AS ngay_quan_tam,
            CASE WHEN dt.trang_thai='da_duyet' THEN dt.tieu_de ELSE qt.tieu_de_da_luu END AS tieu_de,
            CASE WHEN dt.trang_thai='da_duyet' THEN dt.he_dao_tao ELSE qt.he_dao_tao_da_luu END AS he_dao_tao,dt.trang_thai AS trang_thai_de_tai,
            CASE WHEN dt.trang_thai='da_duyet' THEN nd.ho_ten ELSE NULL END AS ten_giang_vien,
            dt.so_luong_sv,gv.gioi_han_sv_csn,gv.gioi_han_sv_cn,
            (dt.so_luong_sv-(SELECT COUNT(*) FROM dang_ky_de_tai dk WHERE dk.de_tai_id=dt.id AND dk.trang_thai='da_duyet')) AS con_lai,
            (SELECT COUNT(DISTINCT dk.sinh_vien_id) FROM dang_ky_de_tai dk JOIN de_tai x ON x.id=dk.de_tai_id
             WHERE x.giang_vien_id=dt.giang_vien_id AND x.he_dao_tao=dt.he_dao_tao AND dk.trang_thai='da_duyet') AS so_sv_gv
            FROM de_tai_quan_tam qt LEFT JOIN de_tai dt ON dt.id=qt.de_tai_id
            LEFT JOIN giang_vien gv ON gv.id=dt.giang_vien_id LEFT JOIN nguoi_dung nd ON nd.id=gv.nguoi_dung_id
            WHERE qt.sinh_vien_id=?";
        $params = [$studentId];
        if ($type !== null) { $sql .= " AND (CASE WHEN dt.trang_thai='da_duyet' THEN dt.he_dao_tao ELSE qt.he_dao_tao_da_luu END)=?"; $params[] = $type; }
        return $this->query($sql . ' ORDER BY qt.created_at DESC,qt.id DESC', $params);
    }

    public function addForCurrentStudent($studentId, $topicId) {
        $this->db->exec('SET TRANSACTION ISOLATION LEVEL READ COMMITTED');
        $this->db->beginTransaction();
        try {
            // Same lock order for every add; synchronizes with leadership changing the limit.
            $setting = $this->queryOne("SELECT value FROM cai_dat WHERE key_name='gioi_han_de_tai_quan_tam' FOR UPDATE");
            if (!$setting) { throw new DomainException('Chưa cập nhật cấu hình Đề tài quan tâm.'); }
            $limit = filter_var($setting['value'], FILTER_VALIDATE_INT);
            if ($limit === false || $limit < 1 || $limit > 50) { throw new DomainException('Giới hạn quan tâm chưa hợp lệ.'); }
            $student = $this->queryOne('SELECT nguoi_dung_id FROM sinh_vien WHERE id=? FOR UPDATE', [$studentId]);
            if (!$student || !hasRole(ROLE_SINH_VIEN) || (int)$student['nguoi_dung_id'] !== (int)($_SESSION['user_id'] ?? 0)) {
                throw new DomainException('Bạn không có quyền sửa danh sách này.');
            }
            $topic = $this->queryOne('SELECT id,tieu_de,he_dao_tao,trang_thai FROM de_tai WHERE id=? FOR UPDATE', [$topicId]);
            if (!$topic || $topic['trang_thai'] !== STATUS_DA_DUYET) { throw new DomainException('Chỉ có thể quan tâm đề tài đang được công khai.'); }
            // Registration decisions lock this same student row before writing.
            $registered = $this->queryOne("SELECT dk.id FROM dang_ky_de_tai dk JOIN de_tai dt ON dt.id=dk.de_tai_id WHERE dk.sinh_vien_id=? AND dt.he_dao_tao=? AND dk.trang_thai='da_duyet' LIMIT 1", [$studentId,$topic['he_dao_tao']]);
            if ($registered) { throw new DomainException('Bạn đã đăng ký đề tài ' . getHeDaoTaoLabel($topic['he_dao_tao']) . '.'); }
            $existing = $this->findOneBy(['sinh_vien_id'=>$studentId,'de_tai_id'=>$topicId]);
            if ($existing) { $this->db->commit(); return 'Đề tài đã có trong danh sách quan tâm.'; }
            if ($this->savedCount($studentId) >= $limit) { throw new DomainException('Bạn đã lưu đủ ' . $limit . ' đề tài. Hãy bỏ một đề tài trước khi thêm đề tài khác.'); }
            $this->insert(['sinh_vien_id'=>$studentId,'de_tai_id'=>$topicId,'tieu_de_da_luu'=>$topic['tieu_de'],'he_dao_tao_da_luu'=>$topic['he_dao_tao']]);
            $this->db->commit();
            return 'Đã thêm vào Đề tài quan tâm. Việc quan tâm không giữ chỗ đăng ký.';
        } catch (Throwable $error) {
            if ($this->db->inTransaction()) { $this->db->rollBack(); }
            throw $error;
        }
    }

    public function removeForCurrentStudent($studentId, $savedId) {
        $student = (new SinhVienModel())->getByNguoiDungId($_SESSION['user_id'] ?? 0);
        if (!hasRole(ROLE_SINH_VIEN) || !$student || (int)$student['id'] !== (int)$studentId) { throw new DomainException('Bạn không có quyền sửa danh sách này.'); }
        $stmt = $this->db->prepare('DELETE FROM de_tai_quan_tam WHERE id=? AND sinh_vien_id=?');
        $stmt->execute([$savedId,$studentId]);
        if (!$stmt->rowCount()) { throw new DomainException('Không tìm thấy đề tài trong danh sách của bạn.'); }
        return 'Đã bỏ đề tài khỏi danh sách quan tâm.';
    }
}
