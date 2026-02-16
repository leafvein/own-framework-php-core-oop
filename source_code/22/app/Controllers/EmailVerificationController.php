<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\EmailVerification;
use App\Models\User;
use App\Core\Request;
use App\Queue\MailQueue;

class EmailVerificationController
{
    public function send(Request $request) { 
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        csrf_verify($request->input('csrf'));

        $user = User::find($_SESSION['user_id']);
        
        MailQueue::push($user['email'], 'verify');
        $_SESSION['flash_message'] = 'Verification Email Sent';
        
        header('Location: /dashboard');
        exit;
	}

    public function verify(Request $request): void
    {
		$token = $request->input('token') ?? '';
		    
		if (!$token) {
			$_SESSION['flash_message'] = 'Invalid verification link';
            header('Location: /login');
            exit;
        }
        
        $unverifiedUser = EmailVerification::findBy('token', $token);
        if (!empty($unverifiedUser) && strtotime($unverifiedUser['expires_at']) > time()) {
            User::update($unverifiedUser['user_id'], [
                'verified' => 1,
            ]);
        }
        
        EmailVerification::deleteBy('user_id', $unverifiedUser['user_id']);
        $_SESSION['flash_message'] = 'Email verified successfully';
        header('Location: /login');
        exit;
    }
}
