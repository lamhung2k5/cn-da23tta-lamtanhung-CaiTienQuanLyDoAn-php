<?php
/**
 * GIẢNG VIÊN MODEL
 */

require_once 'BaseModel.php';

class GiangVienModel extends BaseModel {
    protected $table = 'giang_vien';

    public function getDirectory($publicOnly = true, $id = null) {
        $sql = "SELECT gv.id,gv.nguoi_dung_id,gv.ma_giang_vien,gv.hoc_ham_hoc_vi,gv.khoa,gv.chuyen_mon,
            gv.so_dien_thoai,gv.chuc_vu_cong_khai,gv.so_noi_bo,gv.hien_thi_cong_khai,
            nd.ho_ten,nd.email,nd.trang_thai,vt.ma_vai_tro
            FROM giang_vien gv JOIN nguoi_dung nd ON nd.id=gv.nguoi_dung_id
            JOIN vai_tro vt ON vt.id=nd.vai_tro_id
            WHERE vt.ma_vai_tro IN ('giang_vien','lanh_dao')";
        if ($publicOnly) { $sql .= " AND nd.trang_thai='active' AND gv.hien_thi_cong_khai=1"; }
        $params = [];
        if ($id !== null) { $sql .= ' AND gv.id=?'; $params[] = $id; }
        return $this->query($sql . ' ORDER BY nd.ho_ten,gv.id', $params);
    }

    public function getPublicProfile($id) {
        return $this->getDirectory(true, $id)[0] ?? null;
    }

    public function updateDirectoryProfile($id, array $input) {
        $fields = ['chuc_vu_cong_khai'=>100, 'so_noi_bo'=>30, 'hoc_ham_hoc_vi'=>50,
            'khoa'=>255, 'chuyen_mon'=>255, 'so_dien_thoai'=>20];
        $data = [];
        foreach ($fields as $field=>$limit) {
            $value = $input[$field] ?? '';
            if (!is_string($value) || mb_strlen(trim($value), 'UTF-8') > $limit) {
                throw new DomainException('Thông tin hồ sơ không hợp lệ hoặc vượt độ dài cho phép.');
            }
            $data[$field] = trim($value);
        }
        if (!in_array($data['chuc_vu_cong_khai'], ['Giảng viên','Trưởng khoa','Phó Trưởng khoa'], true)) {
            throw new DomainException('Chức vụ công khai không hợp lệ.');
        }
        $visible = $input['hien_thi_cong_khai'] ?? '0';
        if (!in_array($visible, ['0','1'], true)) { throw new DomainException('Trạng thái công khai không hợp lệ.'); }
        $data['hien_thi_cong_khai'] = (int)$visible;
        $profile = $this->queryOne("SELECT gv.id FROM giang_vien gv JOIN nguoi_dung nd ON nd.id=gv.nguoi_dung_id
            JOIN vai_tro vt ON vt.id=nd.vai_tro_id WHERE gv.id=? AND vt.ma_vai_tro IN ('giang_vien','lanh_dao')", [$id]);
        if (!$profile) { throw new DomainException('Không tìm thấy hồ sơ giảng viên hợp lệ.'); }
        return $this->update($id, $data);
    }

    public function getPublicTopics($lecturerId, $type = null) {
        $sql = "SELECT * FROM de_tai WHERE giang_vien_id=? AND trang_thai='da_duyet'";
        $params = [$lecturerId];
        if (in_array($type, ['co_so_nganh','chuyen_nganh'], true)) { $sql .= ' AND he_dao_tao=?'; $params[] = $type; }
        return $this->query($sql . ' ORDER BY created_at DESC,id DESC', $params);
    }
    
    /**
     * Tạo profile giảng viên
     */
    public function createProfile($nguoiDungId, $data) {
        return $this->insert([
            'nguoi_dung_id' => $nguoiDungId,
            'ma_giang_vien' => $data['ma_giang_vien'],
            'khoa' => $data['khoa'] ?? null,
            'chuyen_mon' => $data['chuyen_mon'] ?? null,
            'so_dien_thoai' => $data['so_dien_thoai'] ?? null
        ]);
    }
    
    /**
     * Lấy thông tin giảng viên theo nguoi_dung_id
     */
    public function getByNguoiDungId($nguoiDungId) {
        return $this->findOneBy(['nguoi_dung_id' => $nguoiDungId]);
    }
    
    /**
     * Kiểm tra mã giảng viên đã tồn tại
     */
    public function maExists($maGiangVien) {
        return $this->findOneBy(['ma_giang_vien' => $maGiangVien]) !== null;
    }
    
    /**
     * Thống kê đề tài của giảng viên
     */
    public function getThongKeDeTai($giangVienId) {
        $sql = "SELECT 
                    COUNT(*) as tong_de_tai,
                    SUM(CASE WHEN he_dao_tao = 'co_so_nganh' THEN 1 ELSE 0 END) as de_tai_csn,
                    SUM(CASE WHEN he_dao_tao = 'chuyen_nganh' THEN 1 ELSE 0 END) as de_tai_cn,
                    SUM(CASE WHEN trang_thai = 'cho_duyet' THEN 1 ELSE 0 END) as cho_duyet,
                    SUM(CASE WHEN trang_thai = 'da_duyet' THEN 1 ELSE 0 END) as da_duyet,
                    SUM(CASE WHEN trang_thai = 'tu_choi' THEN 1 ELSE 0 END) as tu_choi,
                    SUM(CASE WHEN trang_thai = 'nhap' THEN 1 ELSE 0 END) as nhap
                FROM de_tai 
                WHERE giang_vien_id = :giang_vien_id";
        
        return $this->queryOne($sql, ['giang_vien_id' => $giangVienId]);
    }
    
    /**
     * Kiểm tra giảng viên đã đủ đề tài chưa
     */
    public function isDuDeTai($giangVienId) {
        $thongKe = $this->getThongKeDeTai($giangVienId);
        
        return $thongKe['de_tai_csn'] >= SO_DE_TAI_CO_SO_NGANH && 
               $thongKe['de_tai_cn'] >= SO_DE_TAI_CHUYEN_NGANH;
    }
    
    /**
     * Lấy danh sách giảng viên với thống kê
     */
    public function getAllWithStats() {
        $sql = "SELECT 
                    gv.*,
                    nd.ho_ten,
                    nd.email,
                    COUNT(dt.id) as tong_de_tai,
                    SUM(CASE WHEN dt.trang_thai = 'da_duyet' THEN 1 ELSE 0 END) as de_tai_duyet,
                    SUM(dt.so_luong_da_dang_ky) as tong_sinh_vien
                FROM giang_vien gv
                JOIN nguoi_dung nd ON gv.nguoi_dung_id = nd.id
                LEFT JOIN de_tai dt ON gv.id = dt.giang_vien_id
                GROUP BY gv.id
                ORDER BY nd.ho_ten ASC";
        
        return $this->query($sql);
    }
}
