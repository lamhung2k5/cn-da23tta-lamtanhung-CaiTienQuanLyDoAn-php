<?php
/**
 * PHPMailer Helper
 * Gửi email qua SMTP (Gmail)
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Gửi email qua PHPMailer với debug chi tiết
 */
function sendEmailSMTP($to, $subject, $body, $toName = '') {
    $mail = new PHPMailer(true);
    
    try {
        // Enable debug output (chỉ cho development)
        $mail->SMTPDebug = 0; // 0 = off, 1 = client messages, 2 = client and server messages
        $mail->Debugoutput = 'error_log';
        
        // Cấu hình SMTP
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;
        $mail->CharSet    = 'UTF-8';
        
        // Tắt SSL verification để tránh lỗi OpenSSL
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        
        // Nếu OpenSSL không có, thử không dùng encryption
        if (!extension_loaded('openssl')) {
            $mail->SMTPSecure = false;
            $mail->SMTPAutoTLS = false;
            error_log("OpenSSL not available, using non-encrypted SMTP");
        }
        
        // Log cấu hình để debug
        error_log("SMTP Config - Host: " . SMTP_HOST . ", Port: " . SMTP_PORT . ", User: " . SMTP_USER);
        
        // Người gửi
        $mail->setFrom(SMTP_FROM, SMTP_FROM_NAME);
        
        // Người nhận
        $mail->addAddress($to, $toName);
        
        // Nội dung email
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        
        // Gửi email
        $result = $mail->send();
        
        if ($result) {
            error_log("Email sent successfully to: $to");
            return true;
        } else {
            error_log("Email send failed to: $to - " . $mail->ErrorInfo);
            return false;
        }
        
    } catch (Exception $e) {
        error_log("PHPMailer Exception: " . $e->getMessage());
        error_log("PHPMailer ErrorInfo: " . $mail->ErrorInfo);
        return false;
    }
}

/**
 * Gửi email reset password
 */
function sendResetPasswordEmail($email, $hoTen, $resetLink) {
    $subject = "Khôi phục mật khẩu - Hệ thống QLĐT";
    
    $body = "
    <html>
    <head>
        <style>
            body { 
                font-family: Arial, sans-serif; 
                line-height: 1.6; 
                color: #333;
            }
            .container { 
                max-width: 600px; 
                margin: 0 auto; 
                padding: 20px; 
                background: #f8f9fa;
            }
            .header { 
                background: #4285f4; 
                color: white; 
                padding: 30px 20px; 
                text-align: center;
                border-radius: 8px 8px 0 0;
            }
            .header h2 {
                margin: 0;
                font-size: 24px;
            }
            .content { 
                background: white; 
                padding: 30px; 
                border-radius: 0 0 8px 8px;
            }
            .button { 
                display: inline-block;
                background: #4285f4; 
                color: white !important; 
                padding: 15px 30px; 
                text-decoration: none; 
                border-radius: 8px;
                margin: 20px 0;
                font-weight: bold;
            }
            .button:hover {
                background: #3367d6;
            }
            .warning {
                background: #fff3cd;
                border-left: 4px solid #ffc107;
                padding: 15px;
                margin: 20px 0;
            }
            .footer { 
                text-align: center; 
                padding: 20px; 
                color: #666; 
                font-size: 12px; 
            }
            .link-text {
                word-break: break-all;
                background: #f8f9fa;
                padding: 10px;
                border-radius: 4px;
                font-family: monospace;
                font-size: 12px;
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>🔐 Khôi phục mật khẩu</h2>
            </div>
            <div class='content'>
                <p>Xin chào <strong>" . htmlspecialchars($hoTen) . "</strong>,</p>
                
                <p>Chúng tôi nhận được yêu cầu khôi phục mật khẩu cho tài khoản của bạn tại <strong>Hệ thống Quản lý Đề tài</strong>.</p>
                
                <p style='text-align: center;'>
                    <a href='{$resetLink}' class='button'>Đặt lại mật khẩu</a>
                </p>
                
                <p>Hoặc copy link sau vào trình duyệt:</p>
                <div class='link-text'>{$resetLink}</div>
                
                <div class='warning'>
                    <strong>⚠️ Lưu ý quan trọng:</strong>
                    <ul style='margin: 10px 0 0 0; padding-left: 20px;'>
                        <li>Link này chỉ có hiệu lực trong <strong>1 giờ</strong></li>
                        <li>Link chỉ sử dụng được <strong>1 lần duy nhất</strong></li>
                        <li>Không chia sẻ link này với bất kỳ ai</li>
                    </ul>
                </div>
                
                <p style='color: #666; font-size: 14px; margin-top: 20px;'>
                    Nếu bạn không yêu cầu khôi phục mật khẩu, vui lòng bỏ qua email này. 
                    Tài khoản của bạn vẫn an toàn và không có thay đổi nào được thực hiện.
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
    
    return sendEmailSMTP($email, $subject, $body, $hoTen);
}
