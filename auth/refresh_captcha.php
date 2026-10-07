<?php
/**
 * REFRESH CAPTCHA
 * Tạo mã captcha mới
 */

session_start();

function generateCaptcha() {
    $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $captcha = '';
    for ($i = 0; $i < 6; $i++) {
        $captcha .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $captcha;
}

$captcha = generateCaptcha();
$_SESSION['captcha'] = $captcha;

echo $captcha;
