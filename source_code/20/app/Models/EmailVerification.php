<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Abstract\Model;

class EmailVerification extends Model
{
    protected static string $table = 'email_verifications';
}
