<?php
declare(strict_types=1);

namespace App\Queue;

use App\Services\EmailVerificationService;
use App\Models\EmailQueue;

class MailQueue
{
	public static function push(string $to_email, string $type): void
    {
        EmailQueue::create([
            'to_email' => $to_email,
            'type'     => $type,
        ]);
    }
    
    public function work(int $limit = 10): void
    {
        $jobs = EmailQueue::findAllBy('status', 'pending', $limit);

        if (!$jobs) {
            echo "[" . date('H:i:s') . "] No jobs\n";
            return;
        }

        foreach ($jobs as $job) {
            try {
                
                if ($job['type'] == 'verify') {
		                $emailVerification = new EmailVerificationService();
		                $emailVerification->send($job['to_email']);
                }

                EmailQueue::update($job['id'], [
		            'status' => 'sent',
                ]);
                
                echo "Email sent to {$job['to_email']}\n";
            } catch (\Throwable $e) {
                $currentAttempt = (int) $job['attempts'] + 1;
                EmailQueue::update($job['id'], [
                    'status'     => $currentAttempt > 2 ? 'failed' : 'pending',
                    'attempts'   => $currentAttempt,
                    'last_error' => $e->getMessage(),
                ]);
                die($e);

                echo "Failed to sent email to {$job['to_email']}\n";
            }
        }
    }
}
