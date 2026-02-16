<h1 align="center">Queue (Asynchronous) Process : User Verification Email</h1>

<table align="center" border="0">
  <td>

  ## Topics  

  01.&nbsp;&nbsp;[Source Code Files of this Chapter](#source-code-files-of-this-chapter)   
  02.&nbsp;&nbsp;[Queue Operation](#queue-operation)   
  03.&nbsp;&nbsp;[Migration : Create `email_queue` Table](#migration--create-email_queue-table)  
  04.&nbsp;&nbsp;[Create `EmailQueue` Model](#create-emailqueue-model)  
  05.&nbsp;&nbsp;[Create Queue Class : MailQueue](#create-queue-class--mailqueue)  
  06.&nbsp;&nbsp;[Push/Trigger an Email to Queue](#pushtrigger-an-email-to-queue)  
  07.&nbsp;&nbsp;[Process Queue to Send Verification Email](#process-queue-to-send-verification-email)  
  08.&nbsp;&nbsp;[Command: Queue Process Runner](#command-queue-process-runner)  
  09.&nbsp;&nbsp;[Result](#result)    
  10.&nbsp;&nbsp;[Learn More](#learn-more)   
  </td>
</table>

## Source Code Files of this Chapter
```
project-root/
├── app/
│   ├── Controllers/
│   │   ├── EmailVerificationController.php                 # modified file
│   │   └── UserController.php                              # modified file
│   ├── Core/
│   │   └── Model.php                                       # modified file
│   ├── Models/
│   │   └── EmailQueue.php                                  # new file
│   └── Queue/
│       └── MailQueue.php                                   # new file
├── database/
│   └── Migrations/
│       └── 2026_01_18_create_email_queue_table.php         # new file
└── queue.php                                               # new file
```

## Queue Operation

We have integrated email verification into the `user registration` process and also allow authenticated users to `trigger verification` by clicking a button. However, if the SMTP server is busy or the mail service is unavailable, the registration and email verification process may experience delays, long response times, request timeouts, or even error responses.

To overcome this issue, we use a queue process. When a user will register or click the “Verify Email” button and all validation requirements are satisfied, the system will return a success  immediately and dispatch (push) the email verification task to a queue. A queue worker processes this task in the background, ensuring that the registration flow remains fast, and independent of the email service.

## Migration : Create `email_queue` Table

To store queue data, create `email_queue` table migration `database/Migrations/2026_01_18_create_email_queue_table.php` file:

```php
<?php
declare(strict_types=1);

use App\Core\Abstract\Migration;

return new class extends Migration {

    public function up(): void
    {
        if ($this->tableExists('email_queue')) {
            return;
        }

        $this->db->exec("
            CREATE TABLE email_queue (
                id INT AUTO_INCREMENT PRIMARY KEY,
                to_email VARCHAR(150) NOT NULL,
                type VARCHAR(255) NOT NULL,
                attempts TINYINT DEFAULT 0,
                status ENUM('pending','sent','failed') DEFAULT 'pending',
                last_error TEXT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
    }

    public function down(): void
    {
        $this->db->exec("DROP TABLE IF EXISTS email_queue");
    }
};

```

**Code Explain**

> In up() method, first check, if the `email_queue` table exists using shared helper function then echo a message and return early. 
>
> If user table is not exist then proceed to create the `email_queue` table.
>
> In down() method delete the `email_queue` database table as rollback operation.

## Create `EmailQueue` Model

Create `EmailQueue` model in the file (`app/Models/EmailQueue.php`) for the newly created database table `email_queue`:    

```php
<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Abstract\Model;

class EmailQueue extends Model
{
    protected static string $table = 'email_queue';
    
}

```

## Create Queue Class : MailQueue

Create a MailQueue class in `app/Queue/MailQueue.php` to handle email queue tasks:   

```php
<?php
declare(strict_types=1);

namespace App\Queue;

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
}

```

**Code Explain**  

> The `MailQueue` class has defined a single static method `push()`. Which accepts a email address and email type.    
> 
> The method `push()` will create a new record in the `email_queue` table by using the `EmailQueue` model.    

## Push/Trigger an Email to Queue

Update `app/Controllers/UserController.php` to push a `MailQueue` task in the user registration process for email verification.    

```diff
- use App\Services\EmailVerificationService;
+ use App\Queue\MailQueue;

- // send verification email
- $emailVerification = new EmailVerificationService();
- $emailVerification->send($request->input('email'));
+ // queue trigger to send verification email
+ MailQueue::push($request->input('email'), 'verify');
+ $_SESSION['flash_message'] = 'User Created. Verification Email has been Sent';

```

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
use App\Queue\MailQueue;

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

        // queue trigger to send verification email
        MailQueue::push($request->input('email'), 'verify');
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

Update `app/Controllers/EmailVerificationController.php` to push a `MailQueue` task when an authenticated user requests email verification.

```diff
- use App\Services\EmailVerificationService;
+ use App\Queue\MailQueue;

- // send verification email
- $emailVerificationService = new EmailVerificationService();
- $emailVerificationService->send($user['email']);
+ // queue trigger to send verification email
+ MailQueue::push($user['email'], 'verify');
+ $_SESSION['flash_message'] = 'Verification Email Sent';
```

<details>
<summary>See full code: <code>app/Controllers/EmailVerificationController</code></summary>

```php
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

        // queue trigger to send verification email
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

```
</details>

## Process Queue to Send Verification Email

To process pending queue we need to get queue records form the `email_queue` table. Our Core Model class (`app/Core/Abstract/Model.php`) does not currently have a method to fetch multiple records. The existing `find()` and `findBy()` methods return only a single record. 
So, please add a `findAllBy()` method to the `app/Core/Abstract/Model.php` file as like shown below:

```php
    public static function findAllBy(string $column, mixed $value, int $limit = 10): ?array
    {
        $stmt = static::db()->prepare(
            "SELECT * FROM " . static::$table . " WHERE {$column} = ? LIMIT {$limit}"
        );
        $stmt->execute([$value]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: null;
    }
```

**Code Explain**

> ```php
> $stmt = static::db()->prepare(
>     "SELECT * FROM " . static::$table . " WHERE {$column} = ? LIMIT {$limit}"
> );
> Create a prepared SQL query to select all rows from the model’s table where a specific column matches a value, and limit the number of results. Default value of the limit is 10 rows.
> 
> ```php
> $stmt->execute([$value]);
> ```
> Replace placeholder of the prepared statement with the given value in a safe way, and then execute the query on the database.
> 
> ```php
> return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: null;
> ```
> Fetch all rows as associative arrays. If the array is not empty return the array. Otherwise, return null.

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

    public static function findAllBy(string $column, mixed $value, int $limit = 10): ?array
    {
        $stmt = static::db()->prepare(
            "SELECT * FROM " . static::$table . " WHERE {$column} = ? LIMIT {$limit}"
        );
        $stmt->execute([$value]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: null;
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

Create the `work()` method in the MailQueue class (`app/Queue/MailQueue.php`) to handle email queue processing:

```php
use App\Services\EmailVerificationService;

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

            echo "Failed to sent email to {$job['to_email']}\n";
        }
    }
}

```

**Code Explain**

> Get pending queue records.
> 
> Return early if no pending queue records are found.
>
> If pending queue record available and queue type is `verify` then try to send the verification email.
> 
> If the verification email is sent successfully, update the queue record status to `sent`.
> 
> ```php
> $currentAttempt = (int) $job['attempts'] + 1;
> EmailQueue::update($job['id'], [
>     'status'     => $currentAttempt > 2 ? 'failed' : 'pending',
>     'attempts'   => $currentAttempt,
>     'last_error' => $e->getMessage(),
> ]);
> ```
> If failed to process the queue, update the queue record's status to `pending`.   
> If the number of failed attempts is 3 times, update the queue record's status to `failed`.

<details>
<summary>See full code: <code>app/Queue/MailQueue.php</code></summary>

```php
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

                echo "Failed to sent email to {$job['to_email']}\n";
            }
        }
    }
}

```
</details>

## Command: Queue Process Runner

To process pending queue, create command-line queue work runner `queue.php` on the project-root directory:

```php
<?php
declare(strict_types=1);

/**
 * Queue Runner
 *
 * Usage command: `php queue.php work`
 */

require __DIR__ . '/app/Core/Bootstrap.php';

use App\Queue\MailQueue;

$command = $argv[1] ?? null;

if ($command !== 'work') {
    echo "Usage:\n";
    echo "  php queue.php work\n";
    exit(1);
}

$worker = new MailQueue();

echo "Processing email queue...\n";
$worker->work();
echo "Queue processing finished.\n";

```

**Code Explain**

> ```php
> require __DIR__ . '/app/Core/Bootstrap.php';
> ```
> Load application configuration to access database
> 
> ```php
> $command = $argv[1] ?? null;
> ```
> Read first argument of the command
> 
> ```php
> if ($command !== 'work') {
>     echo "Usage:\n";
>     echo "  php queue.php work\n";
>     exit(1);
> }
> ```
> If argument is not `work` then display help instruction and exits.
>
> ```php
> $worker = new MailQueue();
>
> echo "Processing email queue...\n";
> $worker->work();
> echo "Queue processing finished.\n";
> ```
> Create a new instance of the `MailQueue` class.
> Call the work() method to process the queue.

## Result

Make sure server is running or go to the project-root directory and run command `php -S localhost:8000 -t public server.php` on the terminal.   

**Case 1:**  
- Browse: `http://localhost:8000/register` -> Submit the form to register.
- Browse: `http://localhost:8000/login` -> Submit form to login.
- After login browse `http://localhost:8000/dashboard` and `http://localhost:8000/profile` page
    - On both pages you will get `Verify Email` button.

**Case 2:**
```
# go to project-root directory
cd <PROJECT-ROOT>

# process queue
php queue.php work
```
> Above command will process queue and send verification email
- Check Mailtrap or your configured email client to get the verification email.
- Use the verification token URL to verify your email.
- After verify browse `http://localhost:8000/dashboard` and `http://localhost:8000/profile` page 
    - On Dashboard page, you will get welcome message, and on the Profile page, you will find your profile information.

**Case 3:**  
- Browse: `http://localhost:8000/register` -> Submit the form to register as a new user .
- Browse: `http://localhost:8000/login` -> Submit form to login as a new user.
- After login browse `http://localhost:8000/dashboard` or `http://localhost:8000/profile` page
    - On both pages you will get `Verify Email` button.
    - Click on the `Verify Email` button.  
    ```
    > # go to project-root directory
    cd <PROJECT-ROOT>

    # process queue
    php queue.php work
    ```
    > Above command will process queue and send verification email
    - Check Mailtrap or your configured email client to get the verification email.
    - First, try to use the verification link of First email (of the User Registration).
        - This verification link will not verified.
    - Now try to use the verification link of Second email (of the `Verify Email` button).
        - This verification link will verify the user.

**Case 4:**  
- Browse: `http://localhost:8000/logout` -> Logout from the application.

### 💡 Tip : Check Queue on Database
You can also check queue record information from the database table.  
- Browse: `http://localhost:8000/adminer.php    
- Login as a root user:
```
host: `127.0.0.1`
root-user: `root`
root-password: `<YOUR_PASSWORD>`
```
- After login go to the `email_queue` table of the `example_db` database

If the testing results are satisfactory, commit and push your changes to the remote repository.   

## Learn More
- [MySQL ENUM Type](https://dev.mysql.com/doc/refman/9.5/en/enum.html)
- [PDOStatement::fetchAll](https://www.php.net/manual/en/pdostatement.fetchall.php)
- [Async PHP](https://www.techosquare.com/blog/async-php-modern-web-apps-queues-workers-long-running-processes)

**[⬇SOURCE CODE: Chapter 21](#)**    

<table align="center">
  <td>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <a href="20-user-verification.md"> ◄ Previous: 20. Mail - Verify User Email </a>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <a href="22-phpunit-test.md"> Next: 22. PHPUnit Test ► </a>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
  </td>
</table>
