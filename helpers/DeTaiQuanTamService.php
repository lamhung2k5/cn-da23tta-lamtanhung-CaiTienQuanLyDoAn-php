<?php

class DeTaiQuanTamService {
    public static function followRestriction($type, $context) {
        return !empty($context['types'][$type]) ? 'Bạn đã đăng ký đề tài ' . getHeDaoTaoLabel($type) . '.' : '';
    }

    public static function registrationState($topic, $context, $window) {
        $topicId = (int)($topic['de_tai_id'] ?? $topic['id'] ?? 0);
        $available = ($topic['trang_thai_de_tai'] ?? $topic['trang_thai'] ?? '') === STATUS_DA_DUYET;
        $result = ['can_register'=>false,'state'=>'unavailable','label'=>'Đề tài hiện không khả dụng','description'=>'Bạn có thể bỏ quan tâm hoặc chọn đề tài khác.'];
        if (!$available || !$topicId) { return $result; }
        $registration = $context['topics'][$topicId] ?? null;
        if ($registration === STATUS_DA_DUYET) { return array_merge($result, ['state'=>'registered','label'=>'Đã đăng ký','description'=>'','reason'=>'Bạn đã đăng ký đề tài này.']); }
        if ((int)$topic['con_lai'] <= 0) { return array_merge($result, ['state'=>'full','label'=>'Đã có sinh viên đăng ký','description'=>'']); }
        if (!empty($context['types'][$topic['he_dao_tao']])) {
            return array_merge($result, ['state'=>'ineligible','label'=>'Bạn đã có đề tài cùng hệ','description'=>'',
                'reason'=>'Bạn đã có đề tài ' . getHeDaoTaoLabel($topic['he_dao_tao']) . ' được duyệt, không thể đăng ký đề tài này']);
        }
        if ($registration !== null) { return array_merge($result, ['state'=>'existing','label'=>'Đã có đăng ký','description'=>'Xem trạng thái và xử lý trong Đề tài của tôi trước khi đăng ký lại.']); }
        $limitKey = $topic['he_dao_tao'] === 'co_so_nganh' ? 'gioi_han_sv_csn' : 'gioi_han_sv_cn';
        if (isset($topic['so_sv_gv'], $topic[$limitKey]) && (int)$topic['so_sv_gv'] >= (int)$topic[$limitKey]) {
            return array_merge($result, ['state'=>'lecturer_full','label'=>'Giảng viên đã đủ số lượng','description'=>'Chọn đề tài của giảng viên khác.']);
        }
        return $window->status($topic['he_dao_tao']);
    }
}
