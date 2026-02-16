<h1 align="center">Error and Exception Handler</h1>

<table align="center" border="0">
  <td>

  ## Topics  

  01.&nbsp;&nbsp;[Source Code Files of this Chapter](#source-code-files-of-this-chapter)   
  02.&nbsp;&nbsp;[Error and Exception in PHP](#error-and-exception-in-php)   
  03.&nbsp;&nbsp;[PHP Error Types and Behavior](#php-error-types-and-behavior)  
  04.&nbsp;&nbsp;[Handling Error and Exception](#handling-error-and-exception)  
  05.&nbsp;&nbsp;[Create a Simple Error/Exception Handler Class](#create-a-simple-errorexception-handler-class)  
  06.&nbsp;&nbsp;[Update Architectural Bootstrapping Patterns to OOP-first Design](#update-architectural-bootstrapping-patterns-to-oop-first-design)  
  07.&nbsp;&nbsp;[Implemented the Exception Handler in the Application](#implemented-the-exception-handler-in-the-application)  
  08.&nbsp;&nbsp;[Result](#result)  
  09.&nbsp;&nbsp;[Learn More](#learn-more)  
  </td>
</table>

## Source Code Files of this Chapter
```
├── app
│   └── Core
│       ├── App.php                   # new file
│       ├── Bootstrap.php             # modified file
│       └── ExceptionHandler.php      # new file
└── public
    └── index.php                     # modified file
 
```

## Error and Exception in PHP   
`Error` are unexpected failure of the execution that are unrecoverable. On the other hand `Exception` are expected failures that are thrown intentionally to catch, so that execution will continue.   

The try block will throw a fatal error (instance of Error object) and catch block is declared to catch only Error objects also. Therefore, the catch statement will  execute.
```php
try {
	callUndefineFn();
} catch (Error $e) {
  // catch statement will execute
  echo 'Message: ' . $e->getMessage();
}
```

The try block will throw an instance of Exception object and catch block is declared to catch only instance of Exception object also. Therefore, the catch statement will  execute.
```php
try {
	throw new Exception("An Intentional Exception Occurred.");
} catch (Exception $e) {
  // catch statement will execute
    echo 'Message: ' . $e->getMessage();
}
```

The try block will throw an Error `on-catchable legacy error`, but the catch block is declared to catch only Exception objects. Therefore, the catch statement will not execute.
```php
try {
	trigger_error("An Intentional Error Occurred.", E_USER_ERROR);
} catch (Exception $e) {
  // catch statement will not execute
  echo 'Message: ' . $e->getMessage();
}
```

The try block will throw an instance of Error object or Exception object and catch block is declared to catch instance of Throwable object. Throwable is the base interface of both Error and Exception objects. So, the catch statement will execute.
```php
try {
  callUndefineFn();
  // OR
	throw new Exception("An Intentional Exception Occurred.");
} catch (Throwable $e) {
  catch statement will execute
  echo 'Message: ' . $e->getMessage();
}
```

## PHP Error Types and Behavior

By default, PHP does not display errors in the browser for security. To display PHP errors you can choose any option from below:

A. Modifying `php.ini` (only for development):

```
display_errors = On
error_reporting = E_ALL
```

B. Using `ini_set()` in PHP scripts

```php
<?php
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
```
*ⓘ* Note:  
On the PHP built in server, no configuration is required to display error.


There are different types of errors in PHP, some of which stop the process execution when an error occurs, while the rest simply generate an error notice and continue the next process execution.

| Error types | Example | Terminate execution |
|------------|---------|---------------------|
| E_ERROR (Fatal Errors) | undefined function, non-existent class | yes |
| E_PARSE (Parse Errors/Syntax Errors) | missing semicolon, unclosed quotes, mismatched braces | yes |
| E_WARNING | Calling a function with incorrect arguments | no |
| E_NOTICE | undefined variable | no |

## Handling Error and Exception
We have already seen the php error, exception, throwable, types of errors, and the behavior of PHP error.

That is why error handling in PHP is a crucial topic. So we will create a simple error handler for our framework that can handle most common PHP error exceptions.

Keep in mind that if a mistake is made during development, simply knowing that an error has occurred does not really help in resolving the error. Rather, the error message should be one that helps the developer by providing debugging information.


### ⚠️ Important
Error debugging information should only be displayed on the development server, never on the production server. We will implement it on [Chapter 07](07-env-config.md#display-error-message-only-on-dev-server).


## Create a Simple Error/Exception Handler Class
Create `app/Core/ExceptionHandler.php` file:

```php
<?php
declare(strict_types=1);

namespace App\Core;

class ExceptionHandler
{
    public function register()
    {
        // Convert all PHP errors to ErrorExceptions
        set_error_handler([$this, 'handleError']);

        // Handle uncaught exceptions
        set_exception_handler([$this, 'handleException']);
    }

    public function handleError($severity, $message, $file, $line)
    {
        if (!(error_reporting() & $severity)) {
            // This error code is not included in error_reporting, so we ignore it.
            return;
        }

        // Convert the error into an ErrorException
        throw new \ErrorException($message, 0, $severity, $file, $line);
    }

    public function handleException(\Throwable $e)
    {
        // This method will now handle both exceptions and errors
        // that have been converted to ErrorExceptions.

        http_response_code(500);
        echo 'Message: ' . $e->getMessage() . '<br>';
        echo 'File: '    . $e->getFile()    . '<br>';
        echo 'Line: '    . $e->getLine()    . '<br>';

        /* 
         * Optional: 
         * You can log the error message to a log file
         * or perform other log related actions
         */
    }
}

```

## Update Architectural Bootstrapping Patterns to OOP-first Design

Our exception handler is an OOP Class. Later we will add request, response, templates and other core components in object-oriented way. So, this is the time to change  architectural styles or bootstrapping pattern of the Application from Script-based Front Controller to OOP Front Controller (Application Kernel). 
On OOP Front Controller (Application Kernel), all logic lives inside a central App object that controls the application lifecycle.

Let's start implementing this by moving code from `app/Core/Bootstrap.php` to `app/Core/App.php` file. 

Create a new `app/Core/App.php` file and, except for the `require` statement in `app/Core/Bootstrap.php`, move rest of the code into the newly created `app/Core/App.php` file as shown below:

```php
<?php

declare(strict_types = 1);

namespace App\Core;

use App\Controllers\HomeController;

class App
{
    public function run()
    {
        // load environment
        $config = [
            'app_name'=> 'PHP core OOP project'
        ];

        // route define
        $route = $_SERVER['REQUEST_URI'];

        if ($route == "/") {
            $homeController = new HomeController($config['app_name']);
            echo $homeController->getGreeting();
        }
        
        if ($route == "/get-error") {
            // trigger a fatal error
            trigger_error("An Intentional Error Occurred.", E_USER_ERROR);
        }

        if ($route == '/get-exception') {
            // throw an exception
            throw new \Exception("An Intentional Exception Occurred.");
        }
    }
}

```

**Code Explain**
> This code was moved from the `app/Core/Bootstrap.php` with a slight change to the Home (/) page condition so that the content is displayed only on the Home page and not on the error pages. 

Next, in the `app/Core/Bootstrap.php` file simply create the App object and return it:

```php
<?php
declare(strict_types = 1);

require __DIR__ . '/../../vendor/autoload.php';

return new \App\Core\App();

```

Finally, we will remove exception handling from the `public/index.php` file because a dedicated Exception handler class has already been created. Update the `public/index.php` file as shown below:

```php
<?php
declare(strict_types = 1);

$app = require __DIR__ . '/../app/Core/Bootstrap.php';
$app->run();

```

## Implemented the Exception Handler in the Application
Open the `app/Core/Bootstrap.php` file and update as following:

```diff
<?php
declare(strict_types = 1);

require __DIR__ . '/../../vendor/autoload.php';

+ $exceptionHandler = new \App\Core\ExceptionHandler();
+ $exceptionHandler->register();

return new \App\Core\App();
```

**Code Explain**
> Create an instance of the ExceptionHandler and register it

## Result

Make sure server is running or go to the project-root directory and run command `php -S localhost:8000 -t public/` on the terminal.    

- Browse `http://localhost:8000` -> Returns a welcome greeting message.
- Browse: `http://localhost:8000/get-error` -> Returns error information.
- Browse: `http://localhost:8000/get-exception` -> Returns exception information.

If the testing results are satisfactory, commit and push your changes to the remote repository. 

## Learn More
- [PHP Error, Exception and Throwable](http://heera.it/throwable-vs-exception-php-error-handling)
- [PHP Error Types](https://www.qodo.ai/blog/what-are-common-php-error-types-warnings-notices-fatal-errors/)
- [Front Controller Pattern](https://stackoverflow.com/questions/20277102)

**[⬇SOURCE CODE: Chapter 06](#)**

<table align="center">
  <td>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <a href="05-composer.md"> ◄ Previous: 05. Composer - Autoload Dependency </a>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <a href="07-env-config.md"> Next: 07. Environment and Configuration Variable ► </a>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
  </td>
</table>
