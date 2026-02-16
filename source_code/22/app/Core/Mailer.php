<?php
declare(strict_types=1);

namespace App\Core;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer
{
    public static function send(string $to, string $subject, string $htmlBody): void
    {
		$config = Config::get('mail');
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = $config['mail_host'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $config['mail_username'];
            $mail->Password   = $config['mail_password'];
            $mail->Port       = $config['mail_port'] ?? 2525;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

            $mail->setFrom($config['mail_from'], $config['mail_from_name']);
            $mail->addAddress($to);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $htmlBody;

            $mail->send();
            //return $mail->send();
        } catch (Exception $e) {
            error_log($mail->ErrorInfo);
        }
    }
}
