<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Abstract\Model;

class EmailQueue extends Model
{
    protected static string $table = 'email_queue';
    
}
