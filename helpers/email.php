<?php
/**
 * EMAIL HELPER
 * Xử lý gửi email (OTP, thông báo)
 */

/**
 * Send email using PHP mail() function
 * Trong production nên dùng PHPMailer hoặc SMTP
 */
function sendEmail($to, $subject, $body) {
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: " . SMTP_FROM_NAME . " <" . SMTP_FROM . ">" . "\r\n";
    
    return mail($to, $subject, $body, $headers);
}

/**
 * Send OTP email using PHPMailer - LUÔN GỬI THẬT
 */
function sendOTPEmail($to, $otp, $hoTen = '') {
    $subject = "Mã OTP xác thực tài khoản - Hệ thống QLĐT";
    
    $body = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #007bff; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
            .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 8px 8px; }
            .otp-code { font-size: 32px; font-weight: bold; color: #007bff; text-align: center; 
                        padding: 20px; background: white; border: 2px dashed #007bff; margin: 20px 0; border-radius: 8px; }
            .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
            .warning { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>🔐 Xác thực tài khoản</h2>
            </div>
            <div class='content'>
                <p>Xin chào <strong>" . htmlspecialchars($hoTen) . "</strong>,</p>
                <p>Bạn đã đăng ký tài khoản tại <strong>Hệ thống Quản lý Đề tài</strong>.</p>
                <p>Mã OTP của bạn là:</p>
                <div class='otp-code'>" . $otp . "</div>
                <div class='warning'>
                    <p><strong>⚠️ Lưu ý quan trọng:</strong></p>
                    <ul>
                        <li>Mã OTP có hiệu lực trong <strong>" . OTP_EXPIRE_MINUTES . " phút</strong></li>
                        <li>Không chia sẻ mã này với bất kỳ ai</li>
                        <li>Nếu bạn không thực hiện đăng ký, vui lòng bỏ qua email này</li>
                    </ul>
                </div>
                <p style='color: #666; margin-top: 20px;'>
                    Cảm ơn bạn đã sử dụng hệ thống của chúng tôi!
                </p>
            </div>
            <div class='footer'>
                <p>Email này được gửi tự động từ Hệ thống Quản lý Đề tài</p>
                <p>Vui lòng không trả lời email này</p>
                <p>&copy; 2026 Khoa Công nghệ Thông tin - Trường Đại học Trà Vinh</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    // LUÔN GỬI EMAIL THẬT - chỉ dùng PHPMailer SMTP
    error_log("Sending OTP email to: $to, OTP: $otp");
    
    // CHỈ sử dụng PHPMailer SMTP - không fallback về mail()
    if (function_exists('sendEmailSMTP')) {
        $result = sendEmailSMTP($to, $subject, $body, $hoTen);
        if ($result) {
            error_log("OTP email sent successfully via PHPMailer to: $to");
            return true;
        } else {
            error_log("PHPMailer SMTP failed - check SMTP configuration");
            return false;
        }
    } else {
        error_log("PHPMailer function not available - check bootstrap.php");
        return false;
    }
}

/**
 * Send notification email
 */
function sendNotificationEmail($email, $subject, $message) {
    $body = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #28a745; color: white; padding: 20px; text-align: center; }
            .content { background: #f8f9fa; padding: 30px; }
            .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>Thông báo từ hệ thống</h2>
            </div>
            <div class='content'>
                " . $message . "
            </div>
            <div class='footer'>
                <p>Email này được gửi tự động, vui lòng không trả lời.</p>
                <p>&copy; 2024 Hệ thống Quản lý Đề tài</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    return sendEmail($email, $subject, $body);
}
