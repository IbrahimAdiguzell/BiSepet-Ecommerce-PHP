<?php
/**
 * Mail Notification Service
 * * A wrapper class/function set for PHPMailer to handle transactional emails.
 * Used primarily for OTP (One-Time Password) verification and system alerts.
 * * @package BiSepet
 * @subpackage Notification
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/*
|--------------------------------------------------------------------------
| Dependency Loading
|--------------------------------------------------------------------------
| Manually requiring PHPMailer source files.
| TODO: Migrate to Composer autoloading in the future versions.
*/
require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';

/**
 * Sends a verification email containing the OTP code.
 *
 * @param string $userEmail The recipient's email address.
 * @param string $code The 6-digit verification code.
 * @return bool Returns true on success, false on failure.
 */
function sendVerificationEmail(string $userEmail, string $code): bool {
    // Instantiation and passing `true` enables exceptions
    $mail = new PHPMailer(true);

    try {
        /*
        |--------------------------------------------------------------------------
        | Server Configuration (SMTP)
        |--------------------------------------------------------------------------
        | Configured for Gmail SMTP.
        | NOTE: Credentials should be loaded from Environment Variables (.env) in production.
        */
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com'; 
        $mail->SMTPAuth   = true;
        
        // Use Environment Variables or fallback to placeholder for security
        // NEVER commit real passwords to version control!
        $mail->Username   = getenv('SMTP_USER') ?: 'your-email@gmail.com'; 
        $mail->Password   = getenv('SMTP_PASS') ?: 'your-app-password'; 
        
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        
        // Charset settings for Turkish character support
        $mail->CharSet    = 'UTF-8';
        $mail->Encoding   = 'base64';

        /*
        |--------------------------------------------------------------------------
        | Recipients & Content
        |--------------------------------------------------------------------------
        */
        $mail->setFrom('noreply@bisepet.com', 'BiSepet Security');
        $mail->addAddress($userEmail);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'BiSepet - Account Verification Code';
        
        // Professional Email Template
        $mail->Body    = "
            <div style='font-family: Arial, sans-serif; padding: 20px; color: #333;'>
                <h2 style='color: #02A676;'>BiSepet</h2>
                <p>Hello,</p>
                <p>Please use the verification code below to activate your account:</p>
                <div style='background: #f4f4f4; padding: 15px; text-align: center; border-radius: 5px; margin: 20px 0;'>
                    <b style='font-size: 24px; letter-spacing: 5px; color: #333;'>$code</b>
                </div>
                <p style='font-size: 12px; color: #777;'>If you did not request this code, please ignore this email.</p>
            </div>
        ";
        
        $mail->AltBody = "Your verification code is: $code";

        $mail->send();
        return true;

    } catch (Exception $e) {
        // Log error details for internal debugging (do not expose to user)
        // error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}
?>