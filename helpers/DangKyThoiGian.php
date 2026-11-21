<?php

class DangKyThoiGian {
    public const DEFAULTS = [
        'cho_phep_dang_ky' => '1',
        'dang_ky_csn_bat_dau' => '2026-09-22 07:00:00',
        'dang_ky_csn_ket_thuc' => '2026-12-23 23:59:59',
        'dang_ky_cn_bat_dau' => '2026-09-24 07:00:00',
        'dang_ky_cn_ket_thuc' => '2026-12-25 23:59:59',
    ];
    private $settings;

    public function __construct() {
        $this->settings = self::DEFAULTS;
        foreach ((new CaiDatModel())->getAllSettings() as $key => $setting) {
            if (array_key_exists($key, $this->settings)) { $this->settings[$key] = $setting['value']; }
        }
    }

    public function settings() { return $this->settings; }

    public function status($type, $now = null) {
        $state = ['can_register' => false, 'state' => 'unconfigured', 'label' => 'Chưa thiết lập lịch đăng ký', 'description' => 'Vui lòng liên hệ Lãnh đạo.'];
        if (!in_array($type, ['co_so_nganh','chuyen_nganh'], true)) { return $state; }
        $prefix = $type === 'co_so_nganh' ? 'dang_ky_csn_' : 'dang_ky_cn_';
        $start = self::timestamp($this->settings[$prefix . 'bat_dau']);
        $end = self::timestamp($this->settings[$prefix . 'ket_thuc']);
        if ($start === null || $end === null || $end < $start) { return $state; }
        $state['starts_at'] = $this->settings[$prefix . 'bat_dau'];
        $state['ends_at'] = $this->settings[$prefix . 'ket_thuc'];
        if ($this->settings['cho_phep_dang_ky'] !== '1') {
            return array_merge($state, ['state'=>'paused','label'=>'Đăng ký đang tạm đóng','description'=>'Bạn vẫn có thể thêm hoặc bỏ đề tài quan tâm.']);
        }
        $now = $now ?? time();
        if ($now < $start) {
            return array_merge($state, ['state'=>'before','label'=>'Chưa mở đăng ký','description'=>'Mở đăng ký lúc ' . date('H:i d/m/Y', $start) . '.']);
        }
        if ($now > $end) {
            return array_merge($state, ['state'=>'closed','label'=>'Đã hết hạn đăng ký','description'=>'Đợt đăng ký kết thúc lúc ' . date('H:i d/m/Y', $end) . '.']);
        }
        return array_merge($state, ['can_register'=>true,'state'=>'open','label'=>'Đang mở đăng ký','description'=>'Đến ' . date('H:i d/m/Y', $end) . '.']);
    }

    public static function timestamp($value) {
        if (!is_string($value)) { return null; }
        $date = DateTimeImmutable::createFromFormat('!Y-m-d H:i:s', $value);
        return $date && $date->format('Y-m-d H:i:s') === $value ? $date->getTimestamp() : null;
    }
}
