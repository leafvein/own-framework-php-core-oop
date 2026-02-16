<h1 align="center">Mail: Verify User Email</h1>

<table align="center" border="0">
  <td>

  ## Topics  

  01.&nbsp;&nbsp;[Source Code Files of this Chapter](#source-code-files-of-this-chapter)   
  02.&nbsp;&nbsp;[User Verification](#user-verification)   
  03.&nbsp;&nbsp;[Migration : Create `email_Verifications` Table](#migration--create-email_verifications-table)  
  04.&nbsp;&nbsp;[Migration : Alter `users` Table](#migration--alter-users-table)   
  05.&nbsp;&nbsp;[Create `EmailVerification` Model](#create-emailverification-model)    
  06.&nbsp;&nbsp;[Update Core Model](#update-core-model)    
  07.&nbsp;&nbsp;[Configure PHP Mailer](#configure-php-mailer)  
  08.&nbsp;&nbsp;[Create Email Service](#create-email-service)    
  09.&nbsp;&nbsp;[Integrate Email Verification : on User Registration](#integrate-email-verification--on-user-registration)    
  10.&nbsp;&nbsp;[Integrate Email Verification : for Authenticated User](#integrate-email-verification--for-authenticated-user)  
  11.&nbsp;&nbsp;[Verify User](#verify-user)  
  12.&nbsp;&nbsp;[Result](#result)  
  13.&nbsp;&nbsp;[Learn More](#learn-more)   
  </td>
</table>


## Source Code Files of this Chapter
```
project-root/
├── app/
│   ├── Controllers/
│   │   ├── EmailVerificationController.php                           # new file
│   │   └── UserController.php                                        # modified file
│   ├── Core/
│   │   ├── Mailer.php                                                # new file
│   │   └── Model.php                                                 # modified file
│   ├── Models/
│   │   └── EmailVerification.php                                     # new file
│   └── Services/
│       └── EmailVerificationService.php                              # new file
├── config/
│   └── mail.php                                                      # new file
├── database/
│   └── Migrations/
│       ├── 2026_01_18_create_email_verifications_table.php           # new file
│       └── 2026_01_19_add_verified_column_to_users_table.php         # new file
├──.env                                                               # modified file
├── public/
│   └── css/
│       └── style.css                                                 # modified file
├── routes/
│   └── web.php                                                       # modified file
└── views/
    ├── auth/
    │   └── login.php                                                 # modified file
    └── user/
        ├── edit.php                                                  # modified file
        └── show.php                                                  # modified file
```

## User Verification

User verification by email is a process used to confirm that a person who registered in the application actually owns the email address they used. This process is important for the application's security and accuracy. In our framework, we will configure a mail service to implement the email verification feature.    

## Migration : Create `email_Verifications` Table

To store `user_id`, `token`, and token expiration time (`expires_at`), we will create an `email_Verifications` table. Create the migration file `database/Migrations/2026_01_18_create_email_verifications_table.php`:

```php
<?php
declare(strict_types=1);

use App\Core\Abstract\Migration;

return new class extends Migration {

    public function up(): void
    {
        if ($this->tableExists('email_verifications')) {
            echo "Table email_verifications already exists\n";
            return;
        }

        $this->db->exec("
            CREATE TABLE email_verifications (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                token VARCHAR(64) NOT NULL UNIQUE,
                expires_at DATETIME NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )
        ");
    }

    public function down(): void
    {
        if (! $this->tableExists('email_verifications')) {
            return;
        }

        $this->db->exec("DROP TABLE email_verifications");
    }
};

```

**Code Explain**

> This Migration will create `email_verifications` table.
> 
> In the up() method, first check, if the `email_verifications` table exists using shared helper function then echo a message and return early. 
> 
> If user table is not exist then proceed to create the `email_verifications` table.
> 
> The main columns of the `email_verifications` table are `user_id`, `token` and `expires_at`.
>
> In the down() method delete the `email_verifications` database table as rollback operation.

## Migration : Alter `users` Table

To identify verified and non-verified user, we will use a `verified` boolean field in the `users` table. Create a migration file `database/Migrations/2026_01_19_add_verified_column_to_users_table.php` to alter the `users` table.

```php
<?php
declare(strict_types=1);

use App\Core\Abstract\Migration;

return new class extends Migration {

    public function up(): void
    {
        if (! $this->tableExists('users')) {
            echo "Table users does not exist, skipping...\n";
            return;
        }

        if ($this->columnExists('users', 'verified')) {
            echo "Column verified already exists, skipping...\n";
            return;
        }

        $this->db->exec("
            ALTER TABLE users
            ADD COLUMN verified BOOLEAN DEFAULT FALSE AFTER password
        ");

        echo "verified column added to users table.\n";
    }

    public function down(): void
    {
        if (! $this->tableExists('users')) {
            echo "Table users does not exist, skipping...\n";
            return;
        }

        if (! $this->columnExists('users', 'verified')) {
            echo "Column verified does not exist, skipping...\n";
            return;
        }

        $this->db->exec("
            ALTER TABLE users
            DROP COLUMN verified
        ");

        echo "verified column removed from users table.\n";
    }
};

```

**Code Explain**

> `up()`: If the `users` table exist and the `verified` column is not exist in the `users` table then add the `verified` column after the `password` column.. 
> 
> `down()`: Revert the changes made by the `up()` method.    



## Create `EmailVerification` Model

Create `EmailVerification` model in the file (`app/Models/EmailVerification.php`) for the newly created database table `email_Verifications`:    

```php
<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Abstract\Model;

class EmailVerification extends Model
{
    protected static string $table = 'email_verifications';
}

```

## Update Core Model

`email_verifications` table should contain only one verification token per user at a time. So, before generating a new verification token, all existing tokens for the user should be deleted. Since our model does not have a delete option yet, let's create `delete()` and `deleteBy()` methods in `app/Core/Abstract/Model.php`.

```php
public static function delete(int $id): bool
{
    $stmt = static::db()->prepare(
        "DELETE FROM " . static::$table . " WHERE id = ?"
    );

    return $stmt->execute([$id]);
}

public static function deleteBy(string $column, mixed $value): int
{
    $stmt = static::db()->prepare(
        "DELETE FROM " . static::$table . " WHERE {$column} = ?"
    );

    $stmt->execute([$value]);

    // return number of affected rows
    return $stmt->rowCount();
}
```

**Code Explain**

> `delete()`: Delete a single record specified by the `id`.
>
> `deleteBy()`: Delete rows from a table based column specific value.

<details>
<summary>See full code: <code>app/Core/Abstract/Model.php</code></summary>

```php
<?php
declare(strict_types=1);

namespace App\Core\Abstract;

use PDO;
use App\Core\Database;

abstract class Model
{
    protected static string $table;
    protected static ?PDO $db = null;

    protected static function db(): PDO
    {
        if (!static::$db) {
            static::$db = Database::connect();
        }
        return static::$db;
    }

    public static function tableExists(): bool
    {
        $stmt = static::db()->prepare(
            "SELECT COUNT(*) FROM information_schema.tables
             WHERE table_schema = DATABASE()
             AND table_name = ?"
        );

        $stmt->execute([static::$table]);

        return (bool) $stmt->fetchColumn();
    }

    public static function all(): array
    {
        return static::db()
            ->query("SELECT * FROM " . static::$table)
            ->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function truncate(): void
    {
        static::db()->exec("TRUNCATE TABLE " . static::$table);
    }

    public static function create(array $attributes): bool
    {
        $keys = array_keys($attributes);

        $sql = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)",
            static::$table,
            implode(',', $keys),
            rtrim(str_repeat('?,', count($keys)), ',')
        );

        return static::db()
            ->prepare($sql)
            ->execute(array_values($attributes));
    }

    public static function find(int $id): ?array
    {
        $stmt = static::db()->prepare(
            "SELECT * FROM " . static::$table . " WHERE id = ? LIMIT 1"
        );
        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public static function findBy(string $column, mixed $value): ?array
    {
        $stmt = static::db()->prepare(
            "SELECT * FROM " . static::$table . " WHERE {$column} = ? LIMIT 1"
        );
        $stmt->execute([$value]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public static function update(int $id, array $attributes): bool
    {
        $fields = [];
        $values = [];

        foreach ($attributes as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            if ($key === 'password') {
                $value = password_hash($value, PASSWORD_BCRYPT);
            }

            $fields[] = "{$key} = ?";
            $values[] = $value;
        }

        if (!$fields) {
            return false;
        }

        $values[] = $id;

        $sql = "UPDATE " . static::$table .
               " SET " . implode(', ', $fields) .
               " WHERE id = ?";

        return static::db()->prepare($sql)->execute($values);
    }

    public static function delete(int $id): bool
    {
        $stmt = static::db()->prepare(
            "DELETE FROM " . static::$table . " WHERE id = ?"
        );
    
        return $stmt->execute([$id]);
    }
    
    public static function deleteBy(string $column, mixed $value): int
    {
        $stmt = static::db()->prepare(
            "DELETE FROM " . static::$table . " WHERE {$column} = ?"
        );
    
        $stmt->execute([$value]);
    
        // return number of affected rows
        return $stmt->rowCount();
    }
}

```
</details>

## Configure PHP Mailer

PHP has a built-in `mail()` function to send emails without any library, but it does not supports SMTP authentication, attachments, and email often go to spam or get rejected. To make our framework more production friendly, we will integrate SMTP-supported email configuration. We have chosen the PHPMailer library for the Mail service of our framework. PHPMailer simplifies SMTP configuration, supports authentication, (TLS/SSL) encryption, HTML email, and attachments. 
For SMTP service, we will use Mailtrap, as a testing SMTP service used during development.

To install PHPMailer run command:   
```
> composer require phpmailer/phpmailer
```

Create an account to any SMTP service or even you can use gmail as a SMTP service then add your SMTP configuration to the `.env` file.    
**Note:** we are using Mailtrap as a test SMTP service

`.env` file:

```
# SMTP Mail
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=<YOUR_SMTP_USERNAME>
MAIL_PASSWORD=<YOUR_SMTP_PASSWORD>
MAIL_FROM=your@gmail.com
MAIL_FROM_NAME=My App
```

Create the mail configuration file `config/mail.php` as like shown:

```php
<?php
return [
    'mail_host'      => $_ENV['MAIL_HOST'] ?? 'smtp.gmail.com',
    'mail_port'      => $_ENV['MAIL_PORT'] ?? '2525',
    'mail_username'  => $_ENV['MAIL_USERNAME'],
    'mail_password'  => $_ENV['MAIL_PASSWORD'],
    'mail_from'      => $_ENV['MAIL_FROM'] ?? 'your@gmail.com',
    'mail_from_name' => $_ENV['MAIL_FROM_NAME'] ?? 'App Name',
];

```

Create the email `send()` function to the `app/Core/Mailer.php` file:

```php
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

```

**Code Explain**

> The `send()` function supports setting the email subject, HTML email body, and can be extended to send attachments.

## Create Email Service

Our email configuration has been completed. Now, we will create a service to generate email content, including the verification link using a token and sending the email. Create the `EmailVerificationService` in the `app/Services/EmailVerificationService.php` file:

```php
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
        
        $body = "
                <h3>Email Verification</h3>
                <p>Hi {$user['name']},
                <br>
                <p>Click the link below to verify your email:</p>
                <a href='{$url}'>{$url}</a>
                <p>Thank You</p>
            ";
        //$body = "<p>Verify your email: <a href='$url'>$url</a></p>";
        $mail::send($user['email'], $subject, $body);
    }
}

```

**Code Explain**
> Get the user from the `users` table by using user's email.
> 
> Generate a 64-character hexadecimal token string.  
> 
> ```php
> $expires = (new \DateTimeImmutable('+24 hours'))->format('Y-m-d H:i:s');
> ```
> Set the token expiration time to 24 hours
>
> ```php
> EmailVerification::deleteBy('user_id', $user['id']);
> ```
> Delete all existing verification tokens for this user ID from the `email_verifications` table..  
>
> Insert the newly generated verification token into the `email_verifications` table.
> 
> ```php
> $url = config('app.url') . '/verification-email/verify?token=' . $token;
> ```
> Generate verification link using the token
> 
> ```php
> $subject = 'Verify your email';
>
> $body = "
>     <h3>Email Verification</h3>
>     <p>Hi {$user['name']},
>     <br>
>     <p>Click the link below to verify your email:</p>
>     <a href='{$url}'>{$url}</a>
>     <p>Thank You</p>
> ";
> ```
> Set the email subject, and generate HTML email content using verification link 
>
> ```php
> $mail::send($user['email'], $subject, $body);
> ```
> Send the verification email to the user.

## Integrate Email Verification : on User Registration

To integrate the email verification into the user registration process, add the following code to the `app/Controllers/UserController.php` file:

```php
use App\Services\EmailVerificationService;

// send verification email
$emailVerification = new EmailVerificationService();
$emailVerification->send($request->input('email'));
$_SESSION['flash_message'] = 'User Created. Verification Email has been Sent';
```

**Code Explain**

> In the user registration process, use the email provided by the user for email verification. 

<details>
<summary>See full code: <code>app/Controllers/UserController.php</code></summary>

```php
<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;
use App\Models\User;
use App\Services\EmailVerificationService;

class UserController
{

    public function create()
    {
        if (isset($_SESSION['user_id'])) {
            header('Location: /dashboard');
            exit;
        }

        return Response::view('user/register');
    }

    public function store(Request $request)
    {
        if (isset($_SESSION['user_id'])) {
            header('Location: /dashboard');
            exit;
        }
        
        csrf_verify($request->input('csrf'));

        $validator = new Validator();

        $validator->required('name', $request->input('name'));
        $validator->required('email', $request->input('email'));
        $validator->email('email', $request->input('email'));
        $validator->min('password', $request->input('password'), 6);
        $validator->confirmed(
            'password',
            $request->input('password'),
            $request->input('password_confirmation')
        );

        if ($validator->fails()) {
            return Response::view('user/register', [
                'errors' => $validator->errors()
            ]);
        }

        User::create([
            'name'     => $request->input('name'),
            'email'    => $request->input('email'),
            'password' => password_hash($request->input('password'), PASSWORD_BCRYPT)
        ]);

        // send verification email
        $emailVerification = new EmailVerificationService();
        $emailVerification->send($request->input('email'));
        $_SESSION['flash_message'] = 'User Created. Verification Email has been Sent';

        header('Location: /login');
        exit;
    }

    // dashboard
    public function show()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $user = User::find($_SESSION['user_id']);
        
        return Response::view('user/show', [
            'user' => $user
        ]);
    }
    
    // profile
    public function edit()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $user = User::find($_SESSION['user_id']);
        return Response::view('user/edit', [
            'user' => $user
        ]);
    }

    public function update(Request $request)
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        csrf_verify($request->input('csrf'));
        
        $validator = new Validator();

        $validator->required('name', $request->input('name'));
        $validator->min('password', $request->input('password'), 6);
        $validator->confirmed(
            'password',
            $request->input('password'),
            $request->input('password_confirmation')
        );

        if ($validator->fails()) {
            return Response::view('user/register', [
                'errors' => $validator->errors()
            ]);
        }

        $user = User::find($_SESSION['user_id']);
        User::update($user['id'], [
            'name'     => $request->input('name'),
            'password' => $request->input('password')
        ]);

        header('Location: /dashboard');
        exit;
    }
}

```
</details>

## Integrate Email Verification : for Authenticated User

In the user registration process, we integrated email verification. Keep in mind that the token validation expire time is only 24 hours.
There is a possibility that the user may miss the verification email or that the token may expire. So, authenticated but non-verified users should have an option to verify their email later.

Create email verification route in the `routes/web.php`

```php
use App\Controllers\EmailVerificationController;

$router->post('/verification-email/send', [EmailVerificationController::class, 'send']);
```

<details>
<summary>See full code: <code>routes/web.php</code></summary>

```php
<?php
declare(strict_types=1);

use App\Controllers\HomeController;
use App\Controllers\AuthController;
use App\Controllers\UserController;
use App\Controllers\EmailVerificationController;

$router->get('/', [HomeController::class, 'index']);

# user auth
$router->get('/login', [AuthController::class, 'login']);
$router->post('/login', [AuthController::class, 'process']);

$router->get('/register', [UserController::class, 'create']);
$router->post('/register', [UserController::class, 'store']);

$router->post('/logout', [AuthController::class, 'logout']);

$router->get('/dashboard', [UserController::class, 'show']);

$router->get('/profile', [UserController::class, 'edit']);
$router->post('/profile', [UserController::class, 'update']);

$router->post('/verification-email/send', [EmailVerificationController::class, 'send']);

```
</details>

Add the `send()` controller method to `app/Controllers/EmailVerificationController.php` file to trigger email verification for authenticated user:

```php
<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Services\EmailVerificationService;
use App\Models\EmailVerification;
use App\Models\User;
use App\Core\Request;

class EmailVerificationController
{
    public function send(Request $request) { 
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        csrf_verify($request->input('csrf'));

        $user = User::find($_SESSION['user_id']);

        $emailVerificationService = new EmailVerificationService();
	    $emailVerificationService->send($user['email']);
        $_SESSION['flash_message'] = 'Verification Email Sent';
        
        header('Location: /dashboard');
        exit;
	}

```

**Code Explain**

> If the `user_id` is not set in the $_SESSION variable that means user is not logged-in and redirect to the `/login` page.
> 
> Receive the CSRF token from the form submission and verify.   
> 
> Get the logged in user from the `users` table.
>
> Call the `send()` function to send verification email to the user's email and show a success message.    

Add the `Verify Email` button to the Dashboard page (`views/user/show.php`):

```php
<?php if (isset($_SESSION['flash_message'])) : ?>
    <div class="flash_message"><?= $_SESSION['flash_message'] ?></div>
    <?php unset($_SESSION['flash_message']); ?>
<?php endif; ?>
<?php if (!$user['verified']): ?>
    <form method="POST" action="verification-email/send">
        <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
        <button>Verify Email</button>
    </form>
<?php else: ?>
    <h2>Welcome, <?= htmlspecialchars($user['name']) ?></h2>
    <p>Your are a verified user!</p>
<?php endif; ?>

```

**Code Explain**
> Added a container to display the flash message.
> 
> If the user is not verified, display the `Verify Email` button.
>
> Added the `verification-email/send` endpoint as a Form Submission handler.
> 
> A CSRF token is added to the form as a hidden field. When the form is submitted, the form submission handler will verify the token server-side.

<details>
<summary>See full code: <code>views/user/show.php</code></summary>

```php
<?php if (isset($_SESSION['flash_message'])) : ?>
    <div class="flash_message"><?= $_SESSION['flash_message'] ?></div>
    <?php unset($_SESSION['flash_message']); ?>
<?php endif; ?>
<?php if (! $user['verified']): ?>
    <form method="POST" action="verification-email/send">
        <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
        <button>Verify Email</button>
    </form>
<?php else: ?>
    <h2>Welcome, <?= htmlspecialchars($user['name']) ?></h2>
    <p>Your are a verified user!</p>
<?php endif; ?>

```
</details>

Add the `Verify Email` button to the `Profile` page `views/user/edit.php`

```php
<?php if (! $user['verified']): ?>
    <form method="POST" action="verification-email/send">
        <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
        <button>Verify Email</button>
    </form>
<?php else: ?>
<form method="POST" action="/profile">
    <input type="hidden" name="csrf" value="<?= csrf_token() ?>"><br>
    <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>"><br>
    <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" readonly><br>
    <input type="password" name="password" placeholder="Password"><br>
    <input type="password" name="password_confirmation" placeholder="Confirm Password"><br>
    
    <?php if (!empty($errors)): ?>
        <div class="error">
            <?php foreach ($errors as $error): ?>
                <p><?= htmlspecialchars($error[0]) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <button>Update Profile</button>
</form>
<?php endif; ?>

```

**Code Explain**

> If the user is not verified, display the `Verify Email` button.
>
> Added the `verification-email/send` endpoint as a Form Submission handler.
> 
> A CSRF token is added to the form as a hidden field. When the form is submitted, the form submission handler will verify the token server-side.

<details>
<summary>See full code: <code>views/user/edit.php</code></summary>

```php
<?php if (!$user['verified']): ?>
    <form method="POST" action="verification-email/send">
        <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
        <button>Verify Email</button>
    </form>
<?php else: ?>
<form method="POST" action="/profile">
    <input type="hidden" name="csrf" value="<?= csrf_token() ?>"><br>
    <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>"><br>
    <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" readonly><br>
    <input type="password" name="password" placeholder="Password"><br>
    <input type="password" name="password_confirmation" placeholder="Confirm Password"><br>
    
    <?php if (!empty($errors)): ?>
        <div class="error">
            <?php foreach ($errors as $error): ?>
                <p><?= htmlspecialchars($error[0]) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <button>Update Profile</button>
</form>
<?php endif; ?>

```
</details>

## Verify User

When a user receives the verification email, it will contain a verification link. By clicking on the link, a valid user can be set as verified. 

Let's create the `verify` route endpoint to the `routes/web.php` file:

```php
$router->get('/verification-email/verify', [EmailVerificationController::class, 'verify']);
```

<details>
<summary>See full code: <code>routes/web.php</code></summary>

```php
<?php
declare(strict_types=1);

use App\Controllers\HomeController;
use App\Controllers\AuthController;
use App\Controllers\UserController;
use App\Controllers\EmailVerificationController;

$router->get('/', [HomeController::class, 'index']);

# user auth
$router->get('/login', [AuthController::class, 'login']);
$router->post('/login', [AuthController::class, 'process']);

$router->get('/register', [UserController::class, 'create']);
$router->post('/register', [UserController::class, 'store']);

$router->post('/logout', [AuthController::class, 'logout']);

$router->get('/dashboard', [UserController::class, 'show']);

$router->get('/profile', [UserController::class, 'edit']);
$router->post('/profile', [UserController::class, 'update']);

$router->post('/verification-email/send', [EmailVerificationController::class, 'send']);
$router->get('/verification-email/verify', [EmailVerificationController::class, 'verify']);

```
</details>

Create the `verify()` method in the `app/Controllers/EmailVerificationController.php` file to handle email verification process:

```php
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
```

**Code Explain**

> ```php
> $token = $request->input('token') ?? '';
> ```
> Get the token from the route query string.
>
> Redirect to the login page if token is empty.
> 
> ```php
> $unverifiedUser = EmailVerification::findBy('token', $token);
> ```
> Otherwise, get the user's verification details using the token.
> 
> ```php
> if (!empty($unverifiedUser) && strtotime($unverifiedUser['expires_at']) > time()) {
>     User::update($unverifiedUser['user_id'], [
>         'verified' => 1,
>     ]);
> }
> ```
> If the token is valid, update user as a verified .
> ```php
> EmailVerification::deleteBy('user_id', $unverifiedUser['user_id']);
> $_SESSION['flash_message'] = 'Email verified successfully';
> ```
> Delete the used verification token and display a success message.

<details>
<summary>See full code: <code>app/Controllers/EmailVerificationController.php</code></summary>

```php
<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Services\EmailVerificationService;
use App\Models\EmailVerification;
use App\Models\User;
use App\Core\Request;

class EmailVerificationController
{
    public function send(Request $request) { 
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        csrf_verify($request->input('csrf'));

        $user = User::find($_SESSION['user_id']);

        // send verification email
        $emailVerificationService = new EmailVerificationService();
	    $emailVerificationService->send($user['email']);
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

```
</details>

Update the login view `views/auth/login.php` file to display the verification success or failed message as a flash (one-time) message :

```php
<?php if (isset($_SESSION['flash_message'])) : ?>
	<div class="flash_message"><?= $_SESSION['flash_message'] ?></div>
<?php endif; ?>
```

**Code Explain**

> Display a message using `$_SESSION` variable 

<details>
<summary>See full code: <code>views/auth/login.php</code></summary>

```php
<?php if (isset($_SESSION['flash_message'])) : ?>
    <div class="flash_message"><?= $_SESSION['flash_message'] ?></div>
    <?php unset($_SESSION['flash_message']); ?>
<?php endif; ?>
<form method="POST" action="/login">
    <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
    <input type="email" name="email" placeholder="Email"><br>
    <input type="password" name="password" placeholder="Password"><br>
    
    <button>Login</button>

    <?php if (!empty($errors)): ?>
        <div class="error">
            <?php foreach ($errors as $error): ?>
                <p><?= htmlspecialchars($error[0]) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</form>

```
</details>

Add CSS style for the flash message in the `public/css/style.css` file:

```css
.flash_message {
    padding: 10px;
    background: #ee9;
}
```

<details>
<summary>See full code: <code>public/css/style.css</code></summary>

```css
html, body {
    height: 100%;
    margin: 0;
    background: #eee;
}

.flash_message {
    padding: 10px;
    background: #ee9;
}

.banner {
    border: solid 1px #ccc;
}

.content-wrapper {
    width: 60%;
    margin: 0 auto;
    padding: 0 60px;
    min-height: 100vh;
    background: #bee;

    display: flex;
    flex-direction: column;
}

input[readonly] {
    background: #eee;
    border: none;
}

nav {
    margin-bottom: 10px;
    text-align: right;
    padding: 10px;
    border: 1px solid #aaa;
}

.logout-form {
    display: inline;
}

.logout-link {
    background: none;
    border: none;
    padding: 0;
    color: blue;
    text-decoration: underline;
    font-family: serif;
    font-size: 16px;
    cursor: pointer;
}

main {
    flex: 1;
}

input, button {
    padding: 5px;
    margin: 5px 0;
}

footer {
    padding-bottom: 20px;
}

.error {
    color:red; 
    margin-top:10px
}

```
</details>


## Result

Make sure server is running or go to the project-root directory and run command `php -S localhost:8000 -t public server.php` on the terminal.    

**Case 1:**  
- Browse: `http://localhost:8000/register` -> Submit the form to register.
- Browse: `http://localhost:8000/login` -> Submit form to login.
- After login browse `http://localhost:8000/dashboard` and `http://localhost:8000/profile` page
    - On both pages you will get `Verify Email` button.

**Case 2:**
- Check Mailtrap or your configured email client to get the verification email.
- Use the verification token URL to verify your email.
- After verify browse `http://localhost:8000/dashboard` and `http://localhost:8000/profile` page 
    - On Dashboard page, you will get welcome message, and on the Profile page, you will find your profile information.

**Case 3:**  
- Browse: `http://localhost:8000/register` -> Submit the form to register.
- Browse: `http://localhost:8000/login` -> Submit form to login.
- After login browse `http://localhost:8000/dashboard` or `http://localhost:8000/profile` page
    - On both pages you will get `Verify Email` button.
    - Click on the `Verify Email` button.
    - Check Mailtrap or your configured email client to get the verification email.
    - First, try to use the verification link of First email (of the User Registration).
        - This verification link will not verified.
    - Now try to use the verification link of Second email (of the `Verify Email` button).
        - This verification link will verify the user.

**Case 4:**  
- Browse: `http://localhost:8000/logout` -> Logout from the application.

If the testing results are satisfactory, commit and push your changes to the remote repository.   

## Learn More
- [SMTP Service](https://aws.amazon.com/what-is/smtp/)    
- [SSL and TLS](https://aws.amazon.com/compare/the-difference-between-ssl-and-tls/)    
- [PHPMailer](https://github.com/PHPMailer/PHPMailer)    
- [Mailtrap SMTP Integration](https://docs.mailtrap.io/email-api-smtp/setup/smtp-integration)    
- [DateTimeImmutable](https://www.php.net/manual/en/class.datetimeimmutable.php)    

**[⬇SOURCE CODE: Chapter 20](#)**    

<table align="center">
  <td>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <a href="19-auth.md"> ◄ Previous: 19. User Authentication </a>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <a href="21-queue-verify-email.md"> Next: 21. Queue (Asynchronous) Process - User Verification Email ► </a>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
  </td>
</table>
