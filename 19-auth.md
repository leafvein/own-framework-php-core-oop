<h1 align="center">User Authentication</h1>

<table align="center" border="0">
  <td>

  ## Topics  

  01.&nbsp;&nbsp;[Source Code Files of this Chapter](#source-code-files-of-this-chapter)   
  02.&nbsp;&nbsp;[User Registration and Login](#user-registration-and-login)   
  03.&nbsp;&nbsp;[Add `columnExists()` shared function](#add-columnExists-shared-function)  
  03.&nbsp;&nbsp;[Migration to Alter User Table](#migration-to-alter-user-table)  
  04.&nbsp;&nbsp;[Update Core Model](#update-core-model)  
  05.&nbsp;&nbsp;[User Registration](#user-registration)  
  06.&nbsp;&nbsp;[User Login](#user-login)  
  07.&nbsp;&nbsp;[Create Authentication Menu](#create-authentication-menu)  
  08.&nbsp;&nbsp;[Create Dashboard Page](#create-dashboard-page)  
  09.&nbsp;&nbsp;[Create Profile Page](#create-profile-page)  
  10.&nbsp;&nbsp;[User Logout](#user-logout)  
  11.&nbsp;&nbsp;[Result](#result)    
  12.&nbsp;&nbsp;[Learn More](#learn-more)   
  </td>
</table>

## Source Code Files of this Chapter
```
project-root/
├── app/
│   ├── Controllers/
│   │   ├── AuthController.php                                    # modified file
│   │   └── UserController.php                                    # modified file
│   ├── Core/
│   │   └── Model.php                                             # modified file
│   └── Models/
│       └── User.php                                              # modified file
├── database/
│   └── Migrations/
│       └── 2026_01_19_add_password_column_to_users_table.php     # new file
├── routes/
│   └── web.php                                                   # modified file
└── views/
    ├── layout.php                                                # modified file
    └── user/
        ├── edit.php                                              # new file
        └── show.php                                              # new file
```

## User Registration and Login  
PHP authentication is the process of registering users in the application database and checking user's login credentials to allow access to the application.
We have already developed the user registration and login forms. CSRF token and validation also implemented, so we can proceed with adding the authentication feature to our framework.    

## Add `columnExists()` shared function

## Migration to Alter User Table
To register and login a user using the `users` database table, it is required to add a `password` field to the `users` table. So, create a migration to add `password` field in the `users` table. 

Before starting the migration, I want to add a shared helper function to the `app/Core/Abstract/Migration.php` file that will check whether the table already has a column with this name. Please add the function to the `app/Core/Abstract/Migration.php` file:

```php
> protected function columnExists(string $table, string $column): bool
> {
>      $stmt = $this->db->prepare("
>          SELECT COUNT(*)
>          FROM information_schema.columns
>          WHERE table_schema = DATABASE()
>          AND table_name = ?
>          AND column_name = ?
>      ");
> 
>      $stmt->execute([$table, $column]);
> 
>      return (bool) $stmt->fetchColumn();
> }
```

**Code Explain**
The helper function `columnExists` will run a query to the `information_schema.columns` (internal metadata table of the MySQL) to check whether a column exists in the current database table and it will return a boolean value.

<details>
<summary>See full code: <code>app/Core/Abstract/Migration.php</code></summary>

```php
<?php
declare(strict_types=1);

namespace App\Core\Abstract;

use App\Core\Database;
use PDO;

abstract class Migration
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    /**
     * Shared helper: check if table exists
     */
    protected function tableExists(string $table): bool
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*)
            FROM information_schema.tables
            WHERE table_schema = DATABASE()
            AND table_name = ?
        ");

        $stmt->execute([$table]);

        return (bool) $stmt->fetchColumn();
    }

    /**
     * Check if column exists in a table
     */
    protected function columnExists(string $table, string $column): bool
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*)
            FROM information_schema.columns
            WHERE table_schema = DATABASE()
              AND table_name = ?
              AND column_name = ?
        ");

        $stmt->execute([$table, $column]);

        return (bool) $stmt->fetchColumn();
    }

    abstract public function up(): void;
    abstract public function down(): void;
}

```
</details>

Create the migration file `database/Migrations/2026_01_19_add_password_to_users_table.php` to add the `password` field to the `users` column:

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

        if ($this->columnExists('users', 'password')) {
            echo "Column password already exists, skipping...\n";
            return;
        }

        $this->db->exec("
            ALTER TABLE users
            ADD COLUMN password VARCHAR(255) NOT NULL AFTER email
        ");

        echo "Password column added to users table.\n";
    }

    public function down(): void
    {
        if (! $this->tableExists('users')) {
            echo "Table users does not exist, skipping...\n";
            return;
        }

        if (! $this->columnExists('users', 'password')) {
            echo "Column password does not exist, skipping...\n";
            return;
        }

        $this->db->exec("
            ALTER TABLE users
            DROP COLUMN password
        ");

        echo "Password column removed from users table.\n";
    }
};


```

**Code Explain**
> `up()`: If the `users` table exist and the `password` column is not exist in the `users` table then add the `password` column after the `email` column.    
> 
> `down()`: Revert the changes made by the `up()` method.    

## Update Core Model

The model represents a database table. To complete user registration and login, it is necessary to find and update user. Let's add these shared model functions to the abstract Model class (`app/Core/Abstract/Model.php`) so that all table-specific models can use them:

```php
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

```

**Code Explain**
> ```php
> public static function find(int $id): ?array
> {
>     $stmt = static::db()->prepare(
>         "SELECT * FROM " . static::$table . " WHERE id = ? LIMIT 1"
>     );
>     $stmt->execute([$id]);
> 
>     return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
> }
> ```   
> Prepare a SQL query statement to select all columns from the table and execute the query by binding the `$id` value to safely retrive the user.
> 
> `findBy()`: Retrieve record by applying condition of a specifying column, not just only ID.   
>  
> `update()`: Update a record by specifying the ID as a condition and an associative array of column-value pairs.
Loop through each key-value pair in $attributes; if the key is password, it saves the hashed value, otherwise it saves the value as is.
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
}

```
</details>

## User Registration

Update register form submission handler `store` in the `app/Controllers/UserController.php` file to register user in the database table:

```php
use App\Models\User;

if (isset($_SESSION['user_id'])) {
    header('Location: /dashboard');
    exit;
}

User::create([
    'name'     => $request->input('name'),
    'email'    => $request->input('email'),
    'password' => password_hash($request->input('password'), PASSWORD_BCRYPT)
]);

$_SESSION['flash_message'] = 'User created successfully.';
```

**Code Explain**  
> If the `user_id` is set in the $_SESSION variable that means user already logged-in and redirect to the `/dashboard` page.    
> 
> If not set then create the User (register to the database table) and show a success message.    

Additionally, update the `create` method in the `app/Controllers/UserController.php` so that logged-in user cannot access the registration form. If a logged-in user try to access the registration form, redirect to the `/dashboard` page.

```diff
public function create()
{
+     if (isset($_SESSION['user_id'])) {
+         header('Location: /dashboard');
+         exit;
+     }

    return Response::view('user/register');
}
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

        $_SESSION['flash_message'] = 'User created successfully.';

        header('Location: /login');
        exit;
    }
}

```
</details>

To display the flash message on login page please add a flash message section. Update file `views/auth/login.php`:
```php
<?php if (isset($_SESSION['flash_message'])) : ?>
    <div class="flash_message"><?= $_SESSION['flash_message'] ?></div>
    <?php unset($_SESSION['flash_message']); ?>
<?php endif; ?>
```

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

## User Login

Update login form submission handler `process` in the `app/Controllers/AuthController.php` file to login user in the database table:

```php
use App\Models\User;

$user = User::findBy('email', $request->input('email'));

if (!$user || !password_verify($request->input('password'), $user['password'])) {
    return Response::view('auth/login', [
        'error' => 'Invalid credentials'
    ]);
}

$_SESSION['user_id'] = $user['id'];

header('Location: /dashboard');
exit;
```

**Code Explain**

> Find the user in the `users` database table using the `email` field.  
> 
> If the user does not exist or `password` does not matched with the `password` field, authentication will fail and redirect to the login page.  
> 
> Otherwise, the user is authenticated, set the `user_id` variable in $_SESSION and redirect to the Dashboard page.  

<details>
<summary>See full code: <code>app/Controllers/AuthController.php</code></summary>

```php
<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;
use App\Models\User;

class AuthController
{
    public function login()
    {
        if (isset($_SESSION['user_id'])) {
            header('Location: /dashboard');
            exit;
        }
        return Response::view('auth/login');
    }

    public function process(Request $request)
    {
        if (isset($_SESSION['user_id'])) {
            header('Location: /dashboard');
            exit;
        }
        csrf_verify($request->input('csrf'));

        $validator = new Validator();

        $validator->required('email', $request->input('email'));
        $validator->email('email', $request->input('email'));
        $validator->required('password', $request->input('password'));
        $validator->min('password', $request->input('password'), 6);

        if ($validator->fails()) {
            return Response::view('auth/login', [
                'errors' => $validator->errors()
            ]);
        }

        $user = User::findBy('email', $request->input('email'));

        if (!$user || !password_verify($request->input('password'), $user['password'])) {
            return Response::view('auth/login', [
                'error' => 'Invalid credentials'
            ]);
        }

        $_SESSION['user_id'] = $user['id'];

        header('Location: /dashboard');
        exit;
    }
}

```
</details>

## Create Authentication Menu

Add the authentication menu in the `views/layout.php` file to display authentication menu.

```php
<nav>
    <?php if (isset($_SESSION['user_id'])): ?>
        <a href="/dashboard">Dashboard</a> |
        <a href="/profile">Profile</a> |
        <form action="/logout" method="POST" class="logout-form">
            <button type="submit" class="logout-link">Logout</button>
        </form>
    <?php else: ?>
        <a href="/login">Login</a> |
        <a href="/register">Register</a> |
    <?php endif; ?>
</nav>
```

<details>
<summary>See full code: <code>views/layout.php</code></summary>

```php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?= $title ?? 'My OOP App' ?></title>
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
</head>
<body>
    <div class="content-wrapper">
        <header><h1>My OOP App</h1></header>
        <nav>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="/dashboard">Dashboard</a> |
                <a href="/profile">Profile</a> |
                <form action="/logout" method="POST" class="logout-form">
                    <button type="submit" class="logout-link">Logout</button>
                </form>
            <?php else: ?>
                <a href="/login">Login</a> |
                <a href="/register">Register</a> |
            <?php endif; ?>
        </nav>
        <main>
        <?= $content ?>
        </main>
        <footer>Copyright &copy; <?= date('Y') ?></footer>
    </div>
<script src="<?= asset('scripts/app.js') ?>"></script>
</body>
</html>

```
</details>

Apply style of the menu block in the `public/css/style.css` file:

```css
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

```

<details>
<summary>See full code: <code>public/css/style.css</code></summary>

```css
html, body {
    height: 100%;
    margin: 0;
    background: #eee;
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

## Create Dashboard Page

We have implemented authentication, now we will create dashboard and profile pages only for authenticated user. Create dashboard page in the `routes/web.php` file:

```php
$router->get('/dashboard', [UserController::class, 'show']);
```

<details>
<summary>See full code: <code>routes/web.php</code></summary>

```php
<?php
declare(strict_types=1);

use App\Controllers\HomeController;
use App\Controllers\AuthController;
use App\Controllers\UserController;

$router->get('/', [HomeController::class, 'index']);

# user auth
$router->get('/login', [AuthController::class, 'login']);
$router->post('/login', [AuthController::class, 'process']);

$router->get('/register', [UserController::class, 'create']);
$router->post('/register', [UserController::class, 'store']);

$router->get('/dashboard', [UserController::class, 'show']);

```

</details>

Create dashboard controller `show()` method in the app/Controllers/UserController.php` file:

```php
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
```

**Code Explain**
> If the `user_id` is not set in the $_SESSION variable that means user is not logged-in and redirect to the `/login` page.  
> 
> Otherwise, get the logged in user from the `users` table and render `user\show` view with `$user` data.

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

        $_SESSION['flash_message'] = 'User created successfully.';

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
}

```
</details>

Create the dashboard view `views/user/show.php` file

```php
<h2>Welcome, <?= htmlspecialchars($user['name']) ?></h2>
<p>You have successfully logged in</p>
```

**Code Explain**
> Display the `$user` name safely.    

## Create Profile Page

Profile page will render profile information in a form. Authenticated user could be update some specific field. Create profile routes in the `routes/web.php` file:

```php
$router->get('/profile', [UserController::class, 'edit']);
$router->post('/profile', [UserController::class, 'update']);
```

**Code Explain**

> GET `/profile`: get `profile` route will render the profile information in a form.
> 
> POST `/profile`: post `profile` route is a form submission handler of the profile update form.

<details>
<summary>See full code: <code>routes/web.php</code></summary>

```php
<?php
declare(strict_types=1);

use App\Controllers\HomeController;
use App\Controllers\AuthController;
use App\Controllers\UserController;

$router->get('/', [HomeController::class, 'index']);

# user auth
$router->get('/login', [AuthController::class, 'login']);
$router->post('/login', [AuthController::class, 'process']);

$router->get('/register', [UserController::class, 'create']);
$router->post('/register', [UserController::class, 'store']);

$router->get('/dashboard', [UserController::class, 'show']);

$router->get('/profile', [UserController::class, 'edit']);
$router->post('/profile', [UserController::class, 'update']);

```
</details>

Now create the controller method of the profile `edit` and `update` in the `app/Controllers/UserController.php` file:

```php
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

```

**Code Explain**
> `edit()`: If the `user_id` is not set in the $_SESSION variable that means user is not logged-in and redirect to the `/login` page.    
> - if the `users` exist in the `users` database table, render the profile edit page.    
> 
> `update()`: profile page form submission handler.   
> - Receive the CSRF token from the form submission and verify.    
> - If the `user_id` is not set in the $_SESSION variable that means user is not logged-in and redirect to the `/login` page.
> - On the profile edit form `name` fields are required (should not be empty).    
> - `password` field value should contain minimum 6 characters and also match with the `confirm password` field.    
> ```php
> if ($validator->fails()) {
>     return Response::view('user/register', [
>         'errors' => $validator->errors()
>     ]);
> }
> ```
> - If validation fail, return back to the registration form with all validation error messages. 
> - Otherwise, find the user by the `$_SESSION['user_id']` and update the user's name and password. 
> - Redirect to the dashboard page after a successful update.  

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

        $_SESSION['flash_message'] = 'User created successfully.';

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

Create the profile view page `views/user/edit.php` with profile edit option:

```php
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

```

**Code Explain**

> To protect from the CSRF attack, a CSRF token field is added to the form as a hidden field. When the form is submitted, the token will verify on the server-side.    
>
> The form's method is `POST` and submit action is `/profile` endpoint.    
>
> The profile edit form is included `name` (text), `email`, and `password` fields.    
>  
> The `name` (text), `email` fields display the user's full name and email as field's default value.    
> 
> The email field is `readonly`, that means user will be able to see the email but cannot modify it.    
>
> At the bottom, there is a submit button.    

Apply CSS style to the `readonly` field. File the `public/css/style.css`:

```css
input[readonly] {
    background: #eee;
    border: none;
}
```

**Code Explain**
> ```css
> input[readonly]
> ```
> This is a CSS attribute selector. It will target any <input> element that has the `readonly` attribute. 
>  
> Background color will be light gray and, and the border will be removed.

<details>
<summary>See full code: <code>public/css/style.css</code></summary>

```css
html, body {
    height: 100%;
    margin: 0;
    background: #eee;
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

## User Logout

User login is implemented. Now add the logout route in the `routes/web.php` file:

```php
$router->post('/logout', [AuthController::class, 'logout']);
```

**Code Explain**
> Added the `logout` route as a `POST` method.    

<details>
<summary>See full code: <code>routes/web.php</code></summary>

```php
<?php
declare(strict_types=1);

use App\Controllers\HomeController;
use App\Controllers\AuthController;
use App\Controllers\UserController;

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

```
</details>

Create the logout handler in the `app/Controllers/AuthController.php` file:

```php
public function logout()
{
    session_destroy();
    header('Location: /login');
    exit;
}
```

**Code Explain**

> Delete all data associated with the current session on the server.
> 
> Redirect to the `/login` page.

<details>
<summary>See full code: <code>app/Controllers/AuthController.php</code></summary>

```php
<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;
use App\Models\User;

class AuthController
{
    public function login()
    {
        if (isset($_SESSION['user_id'])) {
            header('Location: /dashboard');
            exit;
        }
        return Response::view('auth/login');
    }

    public function process(Request $request)
    {
        if (isset($_SESSION['user_id'])) {
            header('Location: /dashboard');
            exit;
        }
        csrf_verify($request->input('csrf'));

        $validator = new Validator();

        $validator->required('email', $request->input('email'));
        $validator->email('email', $request->input('email'));
        $validator->required('password', $request->input('password'));
        $validator->min('password', $request->input('password'), 6);

        if ($validator->fails()) {
            return Response::view('auth/login', [
                'errors' => $validator->errors()
            ]);
        }

        $user = User::findBy('email', $request->input('email'));

        if (!$user || !password_verify($request->input('password'), $user['password'])) {
            return Response::view('auth/login', [
                'error' => 'Invalid credentials'
            ]);
        }

        $_SESSION['user_id'] = $user['id'];

        header('Location: /dashboard');
        exit;
    }

    public function logout()
    {
        session_destroy();
        header('Location: /login');
        exit;
    }
}

```
</details>

## Result
Make sure server is running or go to the project-root directory and run command `php -S localhost:8000 -t public server.php` on the terminal.

- Browse: `http://localhost:8000/register` -> Submit form to register.
- Browse: `http://localhost:8000/login` -> Submit form to login.
- Browse: `http://localhost:8000/dashboard` -> See the content of the Dashboard page.
- Browse: `http://localhost:8000/profile` -> See user's information on the profile page and try to update the profile information.
- Browse: `http://localhost:8000/logout` -> Logout from the application.
- Check the Authentication menu link.
- Also try accessing public pages and authentication-required pages as both logged-in and not logged-in users.

If the testing results are satisfactory, commit and push your changes to the remote repository.    

## Learn More
- [password_hash()](https://www.php.net/manual/en/function.password-hash.php)
- [password_verify()](https://www.php.net/manual/en/function.password-verify.php)
- [session_destroy()](https://www.php.net/manual/en/function.session-destroy.php)

**[⬇SOURCE CODE: Chapter 19](source_code/19)**    

<table align="center">
  <td>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <a href="18-form-security-and-validation.md"> ◄ Previous: 18. Form - Security and Validation </a>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <a href="20-user-verification.md"> Next: 20. Mail - Verify User Email ► </a>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
  </td>
</table>
