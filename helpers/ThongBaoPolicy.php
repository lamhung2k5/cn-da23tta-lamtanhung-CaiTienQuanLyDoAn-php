<?php
class ThongBaoPolicy {
    public const LABELS = ['hidden'=>'Đã ẩn', 'scheduled'=>'Chưa đến ngày', 'active'=>'Đang hiển thị', 'expired'=>'Hết hạn'];

    public static function text($value, $label, $max, $required = false) {
        if (!is_string($value)) { throw new DomainException($label . ' không hợp lệ.'); }
        $value = trim($value);
        if (($required && $value === '') || mb_strlen($value, 'UTF-8') > $max) {
            throw new DomainException($label . ' bắt buộc hoặc vượt độ dài cho phép (' . $max . ' ký tự).');
        }
        return $value;
    }

    public static function dates(array $data) {
        $dates = [];
        foreach (['ngay_bat_dau','ngay_ket_thuc'] as $field) {
            $value = $data[$field] ?? '';
            if ($value === '' || $value === null) { $dates[$field] = null; continue; }
            $date = is_string($value) ? DateTimeImmutable::createFromFormat('!Y-m-d', $value) : false;
            if (!$date || $date->format('Y-m-d') !== $value || (int)$date->format('Y') < 1000) {
                throw new DomainException('Ngày hiển thị không hợp lệ.');
            }
            $dates[$field] = $value;
        }
        if ($dates['ngay_bat_dau'] && $dates['ngay_ket_thuc'] && $dates['ngay_bat_dau'] > $dates['ngay_ket_thuc']) {
            throw new DomainException('Ngày kết thúc không được trước ngày bắt đầu.');
        }
        return $dates;
    }

    public static function visibility($value) {
        if (!in_array($value, ['mo','khoa'], true)) { throw new DomainException('Trạng thái không hợp lệ.'); }
        return $value;
    }

    public static function state(array $row, $today = null) {
        $today = $today ?? date('Y-m-d');
        if (($row['trang_thai'] ?? '') !== 'mo') { return 'hidden'; }
        if (!empty($row['ngay_bat_dau']) && $row['ngay_bat_dau'] > $today) { return 'scheduled'; }
        if (!empty($row['ngay_ket_thuc']) && $row['ngay_ket_thuc'] < $today) { return 'expired'; }
        return 'active';
    }

    public static function positiveId($value) {
        $id = filter_var($value, FILTER_VALIDATE_INT);
        if (!$id || $id < 1) { throw new DomainException('Mã dữ liệu không hợp lệ.'); }
        return $id;
    }
}
