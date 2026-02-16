<h1 align="center">Bootstrap</h1>

<table align="center" border="0">
  <td>

  ## Topics  

  01.&nbsp;&nbsp;[Source Code Files of this Chapter](#source-code-files-of-this-chapter)   
  02.&nbsp;&nbsp;[Bootstrap : Prepare Runnable Environment by Loading Common Dependencies](#bootstrap--prepare-runnable-environment-by-loading-common-dependencies)   
  03.&nbsp;&nbsp;[Create `Bootstrap.php` File](#create-bootstrapphp-file)  
  04.&nbsp;&nbsp;[Inject Bootstrap into Front Controller](#inject-bootstrap-into-front-controller)  
  05.&nbsp;&nbsp;[Result](#result)  
  06.&nbsp;&nbsp;[Learn More](#learn-more)  
  </td>
</table>

## Bootstrap : Prepare Runnable Environment by Loading Common Dependencies
When a client’s request comes to the front controller, we want to pass the request through a middle layers instead of directly starting the process. That’s why we need the Bootstrap that will load configuration data, request information, (autoload) classes and if required it could be response for exception.

## Source Code Files of this Chapter
```
project-root/
├── app/
│   └── Core/
│       └── Bootstrap.php                   # new file
└── public/
    └── index.php                           # modified file
```

## Create `Bootstrap.php` File
Create `app/Core/Bootstrap.php` file and write following code:

```php
<?php
declare(strict_types = 1);

// load environment
$config = [
    'app_name'=> 'PHP core OOP project'
];

// route define
$route = ltrim($_SERVER['REQUEST_URI'], '/');

if ($route == "get-error") {
    // trigger a fatal error
    trigger_error("An Intentional Error Occurred.", E_USER_ERROR);
}

if ($route == 'get-exception') {
    // throw an exception
    throw new Exception("An intentional Exception Occurred.");
}

echo 'Welcome to the ' . $config['app_name'];

```

**Code Explain:**
>```php
>declare(strict_types = 1)
>```
> As we explained in [Front Controller index.php file](02-front-controller.md) chapter, the `declare(strict_types=1)` cannot be set globally for the whole project It only works per file and should be placed at the top of every PHP file.
> 
> ```php
> $route = ltrim($_SERVER['REQUEST_URI'], '/');
>
> if ($route == "get-error") {
>    // trigger a fatal error
>    trigger_error("An Intentional Error Occurred.", E_USER_ERROR);
>}
>
> if ($route == 'get-exception') {
>    // throw an exception
>    throw new Exception("An intentional Exception Occurred.> ");
> }
> ```
> Get the route path using Global `$_SERVER` variable.
> 
> Based on the route triggered error and thrown exception intentionally using a hard-coded condition
>
> ```php
> $config = [
>    'app_name'=> 'PHP core OOP project'
> ];
>
> echo 'Welcome to the ' . $config['app_name'];
> ```
> Using static configuration variable, displaying a welcome greeting message.  

## Inject Bootstrap into Front Controller

The front controller will only receive the request and will not contain any application logic or data. Open the `public/index.php` file in editor and update the code:

```php
require __DIR__ . '/../app/Core/Bootstrap.php';
```

**Code Explain:**  
> First, load and execute the bootstrap file into the front-controller.

Bootstrap injecting in the front-controller is done, now we will add the following code to the front-controller file to handle the errors and exceptions that we conditionally triggered in the bootstrap file.

```php
// error handler
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    echo 'Message: ' . $errno   . ' - ' . $errstr . '<br>';
    echo 'File: '    . $errfile . '<br>';
    echo 'Line: '    . $errline . '<br>';
});

// exception handler
set_exception_handler(function (Exception $e) {
    echo 'Message: ' . $e->getMessage() . '<br>';
    echo 'File: '    . $e->getFile()    . '<br>';
    echo 'Line: '    . $e->getLine()    . '<br>';
});
```

**Code Explain:**  
> When an error occurs in the application, `set_error_handler()` will be called and it will display information about the error.
> 
> When an exception occurs in the application, `set_exception_handler()` will be called and it will display information about the exception.

<details>
<summary>See full code: <code>public/index.php</code></summary>

```php
<?php
declare(strict_types = 1);

// error handler
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    echo 'Message: ' . $errno   . ' - ' . $errstr . '<br>';
    echo 'File: '    . $errfile . '<br>';
    echo 'Line: '    . $errline . '<br>';
});

// exception handler
set_exception_handler(function (Exception $e) {
    echo 'Message: ' . $e->getMessage() . '<br>';
    echo 'File: '    . $e->getFile()    . '<br>';
    echo 'Line: '    . $e->getLine()    . '<br>';
});

require __DIR__ . '/../app/Core/Bootstrap.php';

```
</details>

## Result

Make sure server is running or go to the project-root directory and run command `php -S localhost:8000 -t public/` on the terminal.
- Browse `http://localhost:8000` -> Returns a welcome greeting message
- Browse: `http://localhost:8000/get-error` -> Returns error information
- Browse: `http://localhost:8000/get-exception` -> Returns exception information 


## Learn More

[PHP's error are not exception](https://dev.to/swyx/errors-are-not-exceptional-1g0b)

[trigger_error()](https://www.php.net/manual/en/function.trigger-error.php):

[Throwing exception](https://www.w3schools.com/php/php_exception.asp):

**[⬇SOURCE CODE: Chapter 03](source_code/03)**

<table align="center">
  <td>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <a href="02-front-controller.md"> ◄ Previous: 02. Front Controller `index.php` File </a>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <a href="04-git.md"> Next: 04. Git ► </a>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
  </td>
</table>
