<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\EmailVerification;
use App\Models\User;
use App\Core\Mailer;

class EmailVerificationService
{
    public function send(string $email): void
    {
        $user    = User::findBy('email', $email);
        $token   = bin2hex(random_bytes(32));
        $expires = (new \DateTimeImmutable('+24 hours'))->format('Y-m-d H:i:s');
        
        EmailVerification::deleteBy('user_id', $user['id']);
        
        EmailVerification::create([
            'user_id'    => $user['id'],
            'token'      => $token,
            'expires_at' => $expires
        ]);
        
        $url = config('app.url') . '/verification-email/verify?token=' . $token;
        
        // mail send
        $mail    = new Mailer();
        $subject = 'Verify your email';
        $body    = "
            <h3>Email Verification</h3>
            <p>Hi {$user['name']},
            <br>
            <p>Click the link below to verify your email:</p>
            <a href='{$url}'>{$url}</a>
            <p>Thank You</p>
        ";
            
        $mail::send($user['email'], $subject, $body);
    }
}
