<h1 align="center">Form : Security and Validation</h1>

<table align="center" border="0">
  <td>

  ## Topics  

  01.&nbsp;&nbsp;[Source Code Files of this Chapter](#source-code-files-of-this-chapter)   
  02.&nbsp;&nbsp;[Form Security in PHP](#form-security-in-php)   
  03.&nbsp;&nbsp;[Add Form Submission Handler](#add-form-submission-handler)   
  04.&nbsp;&nbsp;[Configure CSRF Verification](#configure-csrf-verification)   
  05.&nbsp;&nbsp;[Enable PHP Session](#enable-php-session)  
  06.&nbsp;&nbsp;[Request Validation](#request-validation)  
  07.&nbsp;&nbsp;[Result](#result)    
  08.&nbsp;&nbsp;[Learn More](#learn-more)    
  </td>
</table>

## Source Code Files of this Chapter
```
project-root/
├── app/
│   ├── Controllers/
│   │   ├── AuthController.php                  # modified file
│   │   └── UserController.php                  # modified file
│   ├── Core/
│   │   ├── App.php                             # modified file
│   │   └── Validator.php                       # new file
│   └── Helpers/
│       └── helpers.php                         # modified file
├── public/
│   └── index.php                               # modified file
├── routes/
│   └── web.php                                 # modified file
└── views/
    ├── auth/
    │   └── login.php                           # modified file
    └── user/
        └── register.php                        # modified file
```

## Form Security in PHP
While forms allow for user interaction, they also introduce security issues. To ensure form security, all user input should be properly validated. Passwords must be securely stored using hashing functions like `password_hash()`. Using prepared statements helps protect the application from SQL injection attacks. Additionally, implementing measures like CSRF tokens helps protect form data from unauthorized access.

## Add Form Submission Handler

Create form submission handler routes in the `routes/web.php` file:

```php
<?php
$router->post('/login', [AuthController::class, 'process']);

$router->post('/register', [UserController::class, 'store']);

```
**Code Explain**
> The form submission method should be POST. Both POST routes connect form submissions (`/login` and `/register`) to controller methods that handle user authentication and registration.

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

```
</details>

Create empty `store` method in the `app/Controllers/UserController.php` file:

```php
public function store()
{
    
}

```

<details>
<summary>See full code: <code>app/Controllers/UserController.php</code></summary>

```php
<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Response;

class UserController
{

    public function create()
    {
        return Response::view('user/register');
    }

    public function store()
    {
        
    }
}

```
</details>

Create empty `process` method in the `app/Controllers/AuthController.php` file:

```php
public function process()
{

}

```

<details>
<summary>See full code: <code>app/Controllers/AuthController.php</code></summary>

```php
<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Response;

class AuthController
{
    public function login()
    {
        return Response::view('auth/login');
    }
}

public function process()
{

}

```
</details>

Add the Form Submission handler in the register form `views/user/register.php`:

```php
<form method="POST" action="/register">
```

<details>
<summary>See full code: <code>views/user/register.php</code></summary>

```php
<form method="POST" action="/register">
    <input type="text" name="name" placeholder="Name" size="40"><br>
    <input type="email" name="email" placeholder="Email" size="40"><br>
    <input type="password" name="password" placeholder="Password"><br>

    <button>Register</button>
</form>

```
</details>

Add the Form Submission handler in the login form `views/auth/login.php`

```php
<form method="POST" action="/login">
```

<details>
<summary>See full code: <code>views/auth/login.php</code></summary>

```php
<form method="POST" action="/login">
    <input type="email" name="email" placeholder="Email"><br>
    <input type="password" name="password" placeholder="Password">

    <button>Login</button>
</form>

```
</details>

## Configure CSRF Verification
CSRF is an attack that makes a user unknowingly submit a form on a trusted website from another site. To prevent our forms from CSRF attack we will configure CSRF verification. Create CSRF verification in `app/Helpers/helpers.php` file:

```php
if (!function_exists('csrf_token')) {
    function csrf_token()
    {
        if (empty($_SESSION['csrf'])) {
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf'];
    }
}

if (!function_exists('csrf_verify')) {
    function csrf_verify($token)
    {
        if (
            empty($_SESSION['csrf']) ||
            !hash_equals($_SESSION['csrf'], $token ?? '')
        ) {
            http_response_code(419);
            die('CSRF token mismatch');
        }
    }
```

**Code Explain**
> `if (!function_exists())`: check to avoid multiple definition of a function. Helper functions are typically placed in simple PHP script files, not inside a class or namespace. If you write many helper functions in a single file, there is a possibility of redefining a function. In PHP, if you try to redefine a function that already exists, a fatal error will occur.   
>    
> `csrf_token()`: if a CSRF token already exist in the session then return otherwise generate a 64-character hexadecimal CSRF token string and stores it in the user’s session.   
>    
> `csrf_verify()`: Verify CSRF token validity with `hash_equals()`.    

<details>
<summary>See full code: <code>app/Helpers/helpers.php</code></summary>

```php
<?php
declare(strict_types=1);

use App\Core\Config;

if (!function_exists('config')) {
    function config(string $key, mixed $default = null)
    {
        return Config::get($key, $default);
    }
}

if (!function_exists('asset')) {
    function asset(string $path)
    {
        return '/' . ltrim($path, '/');
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token()
    {
        if (empty($_SESSION['csrf'])) {
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf'];
    }
}

if (!function_exists('csrf_verify')) {
    function csrf_verify($token)
    {
        if (
            empty($_SESSION['csrf']) ||
            !hash_equals($_SESSION['csrf'], $token ?? '')
        ) {
            http_response_code(419);
            die('CSRF token mismatch');
        }
    }
}

```
</details>

Add CSRF token to the register form `views/user/register.php`:

```php
<input type="hidden" name="csrf" value="<?= csrf_token() ?>">
```

**Code Explain**    
> A CSRF token is added to the form as a hidden field. When the form is submitted, the form submission handler will verify the token server-side.

<details>
<summary>See full code: <code>views/user/register.php</code></summary>

```php
<form method="POST" action="/register">
    <input type="hidden" name="csrf" value="<?= csrf_token() ?>">

    <input type="text" name="name" placeholder="Name" size="40"><br>
    <input type="email" name="email" placeholder="Email" size="40"><br>
    <input type="password" name="password" placeholder="Password"><br>

    <button>Register</button>
</form>
```
</details>

Add CSRF token to the login form `views/auth/login.php`

```php
<input type="hidden" name="csrf" value="<?= csrf_token() ?>">
```

**Code Explain**
A CSRF token is added to the form as a hidden field. When the form is submitted, the form submission handler will verify the token server-side.

<details>
<summary>See full code: <code>views/auth/login.php</code></summary>

```php
<form method="POST" action="/login">
    <input type="hidden" name="csrf" value="<?= csrf_token() ?>">

    <input type="email" name="email" placeholder="Email"><br>
    <input type="password" name="password" placeholder="Password">

    <button>Login</button>
</form>
```
</details>

Verify the CSRF token in the form submission handler `store` (`app/Controllers/UserController.php`):

```php
use App\Core\Request;

public function store(Request $request)
{
    csrf_verify($request->input('csrf'));

    header('Location: /login');
    exit;
}

```

**Code Explain**  

> Receive the CSRF token from the form submission and verify.    
> 
> Check whether this token matches the token stored in the user’s session.    
>  
> If the token is invalid, stop execution and return an error and redirect the user to the login page

<details>
<summary>See full code: <code>app/Controllers/UserController.php</code></summary>

```php
<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;

class UserController
{

    public function create()
    {
        return Response::view('user/register');
    }

    public function store(Request $request)
    {
        csrf_verify($request->input('csrf'));

        header('Location: /login');
        exit;
    }
}
```
</details>

Verify the CSRF token in the form submission handler `process` (`app/Controllers/AuthController.php`):

```php
use App\Core\Request;

public function process(Request $request)
{
    csrf_verify($request->input('csrf'));

    header('Location: /');
    exit;
}

```

**Code Explain**
> Receive the CSRF token from the form submission.  
> Check whether this token matches the token stored in the user’s session.    
> If the token is invalid, stop execution and return an error and redirect the user to the home page

<details>
<summary>See full code: <code>app/Controllers/AuthController.php</code></summary>

```php
<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;

class AuthController
{
    public function login()
    {
        return Response::view('auth/login');
    }
}

public function process(Request $request)
{
    csrf_verify($request->input('csrf'));

    header('Location: /');
    exit;
}
```
</details>

## Enable PHP Session
PHP is stateless, which means each request is independent and has no relation to previous requests. To pass data from one request to another, we use PHP’s built-in session feature. Let’s configure sessions in our framework. Open the `public/index.php` file and add following code:

```php
session_start();
```

### ⚠️ Important
The session should be started at the beginning of the request lifecycle so that no headers or output have been sent yet. That is why it is added in our application's entry point (index.php). Otherwise, a `headers already sent` error will occur.

<details>
<summary>See full code: <code>public/index.php</code></summary>

```php
<?php
declare(strict_types = 1);

$app = require __DIR__ . '/../app/Core/Bootstrap.php';

session_start();

$app->run();

```
</details>

## Request Validation
Validation is the core part of a PHP security. Processing user input without validation is a dangerous approach that can make your application vulnerable. To handle form submissions safely, validate user input at first. 
Let’s start by creating a validator file `app/Core/Validator.php`:
```php
<?php
declare(strict_types=1);

namespace App\Core;

class Validator
{
    private array $errors = [];

    public function required(string $field, $value)
    {
        if (empty(trim((string)$value))) {
            $this->errors[$field][] = 'This field is required';
        }
    }

    public function email(string $field, $value)
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field][] = 'Invalid email address';
        }
    }

    public function min(string $field, $value, int $length)
    {
        if (strlen((string)$value) < $length) {
            $this->errors[$field][] = "Minimum {$length} characters required";
        }
    }

    public function confirmed(string $field, $value, $confirmation)
    {
        if ($value !== $confirmation) {
            $this->errors[$field][] = 'Password confirmation does not match';
        }
    }

    public function fails()
    {
        return !empty($this->errors);
    }

    public function errors()
    {
        return $this->errors;
    }

    public function all()
    {
        $messages = [];

        foreach ($this->errors as $fieldErrors) {
            $messages[] = $fieldErrors[0]; // first error only
        }

        return $messages;
    }
}

```

**Code Explain**
> `required()`: Check whether a field's value is empty. If empty, add an error message.    
> 
> `email()`: Check whether a field's value is a valid email using `filter_var()`. If not a valid email, add an error message.    
> 
> `min()`: Check whether a field's value is containing a minimum number of characters. If less then minimum number of characters, add an error message.    
> 
> `confirmed()`: Check whether a `password` and `confirm password` field's value are same. If not same add an error message.    
> 
> `fails()`: If any validation errors exist, return boolean value `true`.    
> 
> `errors()`: Return full error array, grouped by field. This function is useful to display field-specific error messages.    
> 
> `fails()`: Return all error messages. Useful to display a list of messages at the top of a form.    



Now configure the application `app/Core/App.php` to make request object available in the controller:

```php
if (is_array($action)) {
    [$class, $method] = $action;
    $controller = new $class();

    return $controller->$method($this->request);
}

return $action($this->request);
```

**Code Explain**

<details>
<summary>See full code: <code>app/Core/App.php</code></summary>

```php
<?php
declare(strict_types = 1);

namespace App\Core;

class App
{
    private Router $router;
    private Request $request;

    public function __construct(Router $router, Request $request)
    {
        $this->router  = $router;
        $this->request = $request;
    }

    public function run()
    {
        $method = $this->request->method();
        $uri    = $this->request->uri();

        $routes = $this->router->routes();

        $action = $routes[$method][$uri] ?? null;

        if (!$action) {
            http_response_code(404);
            if (strpos($uri, '/api/') === 0) {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Route not found']);
            } else {
                echo '404 Not Found';
            }
            return;
        }

        if (is_array($action)) {
            [$class, $method] = $action;
            $controller       = new $class();

            return $controller->$method($this->request);
        }

        return $action($this->request);
    }
}

```
</details>

To validate the `store` form submission handler, add following code in the `store` method of the `app/Controllers/UserController.php` file:

```php
use App\Core\Validator;

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

```

**Code Explain**
> On the registration form `name` and `email` fields are required (should not be empty).    
> `email` field value should be valid email address.    
> `password` field value should contain minimum 6 characters and also match with the `confirm password` field.    
> ```php
> if ($validator->fails()) {
>     return Response::view('user/register', [
>         'errors' => $validator->errors()
>     ]);
> }
> ```
> If validation fails return back to the registration form with all validation error messages.  

<details>
<summary>See full code: <code>app/Controllers/UserController.php</code></summary>

```php
<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;

class UserController
{

    public function create()
    {
        return Response::view('user/register');
    }

    public function store(Request $request)
    {
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

        header('Location: /login');
        exit;
    }
}

```
</details>

To validate the `process` form submission handler, add following code in the `process` method of the `app/Controllers/AuthController.php`

```php
use App\Core\Validator;

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

```

**Code Explain**  
> `email` field value should be valid email address.    
> `password` field value should contain minimum 6 characters and also match with the `confirm password` field.
> ```php
> if ($validator->fails()) {
>     return Response::view('auth/login', [
>         'errors' => $validator->errors()
>     ]);
> }
> ```
> If validation fails return back to the login form with all validation error messages. 

<details>
<summary>See full code: <code>app/Controllers/AuthController.php</code></summary>

```php
<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;

class AuthController
{
    public function login()
    {
        return Response::view('auth/login');
    }

    public function process(Request $request)
    {
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

        header('Location: /');
        exit;
    }
}

```
</details>

Update the registration form (`views/user/register.php`) to display error message:  

```php
<?php if (!empty($errors)): ?>
    <div class="error">
        <?php foreach ($errors as $error): ?>
            <p><?= htmlspecialchars($error[0]) ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
```

**Code Explain**
> If any error messages are available in the `$errors` array, display each error message safely using htmlspecialchars() inside a red-styled box with a foreach() loop. If no error messages exist, nothing is displayed.

<details>
<summary>See full code: <code>views/user/register.php</code></summary>

```php
<form method="POST" action="/register">
    <input type="hidden" name="csrf" value="<?= csrf_token() ?>">

    <input type="text" name="name" placeholder="Name" size="40"><br>
    <input type="email" name="email" placeholder="Email" size="40"><br>
    <input type="password" name="password" placeholder="Password"><br>
    <input type="password" name="password_confirmation" placeholder="Confirm Password"><br>

    <button>Register</button>

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

Update the login form `views/auth/login.php` to display error message:  

```php
<?php if (!empty($errors)): ?>
    <div class="error">
        <?php foreach ($errors as $error): ?>
            <p><?= htmlspecialchars($error[0]) ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
```

**Code Explain**
> If any error messages are available in the `$errors` array, display each error message safely using htmlspecialchars() inside a red-styled box with a foreach() loop. If no error messages exist, nothing is displayed.

<details>
<summary>See full code: <code>views/auth/login.php</code></summary>

```php
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

To add a red color style to the error message, update the `public/css/style.css` file: 

```css
.error {
    color:red; 
    margin-top:10px
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

**Check the new feature:**
- Browse: `http://localhost:8000/register` 
    - Display a registration page containing a registration form.  
    - Inspect the form and verify the CSRF value.  
    - Test form validation by submitting the form with invalid data.  
- Browse: `http://localhost:8000/login` -> Display the register form
    - Display a login page containing a login form.    
    - Inspect the form and verify the CSRF value.    
    - Test form validation by submitting the form with invalid data.      

**Regression Test**
- Browse: `http://localhost:8000` -> Returns a welcome greeting message.
- Browse: `http://localhost:8000/get-error` -> Returns error information.
- Browse: `http://localhost:8000/get-exception` -> Returns exception information.

If the testing results are satisfactory, commit and push your changes to the remote repository.    

## Learn More
- [csrf](https://www.phptutorial.net/php-tutorial/php-csrf/)    
- [$_SESSION](https://www.w3schools.com/php/php_sessions.asp)    
- [bin2hex()](https://www.php.net/manual/en/function.bin2hex.php)    
- [filter_var()](https://www.php.net/manual/en/function.filter-var.php)    
- [hash_equals()](https://www.php.net/manual/en/function.hash-equals.php)    

**[⬇SOURCE CODE: Chapter 18](#)**    

<table align="center">
  <td>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <a href="17-form.md"> ◄ Previous: 17. Form - User Data Input </a>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <a href="19-auth.md"> Next: 19. User Authentication ► </a>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
  </td>
</table>
