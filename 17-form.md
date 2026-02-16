<h1 align="center">Form : User Data Input</h1>

<table align="center" border="0">
  <td>

  ## Topics  

  01.&nbsp;&nbsp;[Source Code Files of this Chapter](#source-code-files-of-this-chapter)   
  02.&nbsp;&nbsp;[User Input](#user-input)   
  03.&nbsp;&nbsp;[Create Register and Login Forms](#create-register-and-login-forms)  
  04.&nbsp;&nbsp;[Render Form in Route](#render-form-in-route)  
  05.&nbsp;&nbsp;[Update Global Style](#update-global-style)  
  06.&nbsp;&nbsp;[Result](#result)  
  07.&nbsp;&nbsp;[Learn More](#learn-more)  
  </td>
</table>

 
## Source Code Files of this Chapter
```
project-root/
├── app/
│   └── Controllers/
│       ├── AuthController.php                  # new file
│       └── UserController.php                  # new file
├── public/
│   └── css/
│       └── style.css                           # modified file
├── routes/
│   └── web.php                                 # modified file
└── views/
    ├── auth/
    │   └── login.php                           # new file
    ├── layout.php                              # modified file
    └── user/
        └── register.php                        # new file
```

## User Input
In an application, a form is the main component for interacting with user input. In our framework, we can’t leave this feature, so let’s start by building a form.

## Create Register and Login Forms

Create a simple user register form in `views/user/register.php` file:

```php
<form method="POST" action="">
    <input type="text" name="name" placeholder="Name" size="40"><br>
    <input type="email" name="email" placeholder="Email" size="40"><br>
    <input type="password" name="password" placeholder="Password"><br>

    <button>Register</button>
</form>

```

**Code Explain**
> This simple user registration form consists of name (text), email, and password fields that collect basic user information.    
> At the bottom, there is a submit button.    
> The `action` attribute is empty because in this chapter we are creating only the form and the submission handler will be added in the next chapter.    

Create another form for user login in `views/auth/login.php` file:

```php
<form method="POST" action="">
    <input type="email" name="email" placeholder="Email"><br>
    <input type="password" name="password" placeholder="Password">

    <button>Login</button>
</form>

```

**Code Explain**
> The user login form consists of email, and password fields.    
> At the bottom, there is a submit button.    
> The `action` attribute is empty because in this chapter we are creating only the form and the submission handler will be added in the next chapter.    

## Render Form in Route
Create two new routes in the `routes/web.php` to render Register and Login forms:

```php
use App\Controllers\AuthController;
use App\Controllers\UserController;

# user auth
$router->get('/login', [AuthController::class, 'login']);

$router->get('/register', [UserController::class, 'create']);
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

$router->get('/register', [UserController::class, 'create']);

```
</details>


Create `UserController` (`app/Controllers/UserController.php`) and display the Register form:

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
}

```

Create `AuthController` (`app/Controllers/AuthController.php`) and display the Login form:

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

```

## Update Global Style

To render form on a structured layout, update the `views/layout.php` as like following:

```php
<div class="content-wrapper">
    <header><h1>My OOP App</h1></header>
    <main>
    <?= $content ?>
    </main>
    <footer>Copyright &copy; <?= date('Y') ?></footer>
</div>
```

**Code Explain**
> Added a new wrapper class to render the form in the center of the page.

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

Applied simple CSS styles in the `public/css/style.css` file to make the layout more consistent.

```css
html, body {
    height: 100%;
    margin: 0;
    background: #eee;
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
```

## Result

Make sure server is running or go to the project-root directory and run command `php -S localhost:8000 -t public server.php` on the terminal.  

- Browse: `http://localhost:8000` -> Returns a welcome greeting message in new styled layout.
- Browse: `http://localhost:8000/register` -> Display the register form
- Browse: `http://localhost:8000/login` -> Display the login form

If the testing results are satisfactory, commit and push your changes to the remote repository.   

## Learn More
- [Form element](https://developer.mozilla.org/en-US/docs/Web/HTML/Reference/Elements/form)
- [HTML Form Attributes](https://www.w3schools.com/html/html_forms_attributes.asp)

**[⬇SOURCE CODE: Chapter 17](source_code/17)**    

<table align="center">
  <td>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <a href="16-template.md"> ◄ Previous: 16. Template - Frontend Structure and Resources </a>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <a href="18-form-security-and-validation.md"> Next: 18. Form - Security and Validation ► </a>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
  </td>
</table>
