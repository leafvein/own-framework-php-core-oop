<h1 align="center">Environment and Configuration Variable</h1>

<table align="center" border="0">
  <td>

  ## Topics  
 
  01.&nbsp;&nbsp;[Source Code Files of this Chapter](#source-code-files-of-this-chapter)   
  02.&nbsp;&nbsp;[Access Environment and Configuration Variable](#access-environment-and-configuration-variable)  
  03.&nbsp;&nbsp;[Create .env File](#create-env-file)  
  04.&nbsp;&nbsp;[Create .env File Parser](#create-env-file-parser)  
  05.&nbsp;&nbsp;[Create Config File](#create-config-file)  
  06.&nbsp;&nbsp;[Create Config File Parser](#create-config-file-parser)  
  07.&nbsp;&nbsp;[Using Environment and Configuration Variable](#using-environment-and-configuration-variable)  
  08.&nbsp;&nbsp;[Display Error Message Only on Dev Server](#display-error-message-only-on-dev-server)  
  09.&nbsp;&nbsp;[Result](#result)  
  10.&nbsp;&nbsp;[Learn More](#learn-more)  
  </td>
</table>

## Source Code Files of this Chapter
```
project-root/
├── app/
│   ├── Controllers/
│   │   └── HomeController.php            # modified file
│   ├── Core/
│   │   ├── App.php                       # modified file
│   │   ├── Bootstrap.php                 # modified file
│   │   ├── Config.php                    # new file
│   │   ├── Env.php                       # new file
│   │   └── ExceptionHandler.php          # modified file
│   └── Helpers/
│       └── helpers.php                   # new file
├── config/
│   └── app.php                           # modified file
└── public/
    └── index.php                         # modified file

```

## Access Environment and Configuration Variable
We have also simplified the task of fixing errors by implementing error handling, but this error debug information cannot be displayed in the production environment, only in dev mode.

But how will the program detect the environment? We will try to detect the environment so that the program can work according to the environment information and various configurations.

So let's start by making sure that error information is only displayed in the dev environment. In addition, you can configure integrations with databases and various third party sites using environment variables.

## Create .env File
Create an `.env` file on the root (/) directory of the project.
```
APP_NAME=PHP core OOP project
APP_ENV=dev
APP_DEBUG=true
```

**Code Explain**
> `APP_NAME`: Name of the application   
> `APP_ENV`: Server environment of the application   
> `APP_DEBUG`: Application debug mode

## Create .env File Parser
Create `app/Core/Env.php` file to parse the `.env` file 

```php
<?php
declare(strict_types=1);

namespace App\Core;

class Env
{
    public static function load(string $path)
    {
        if (!file_exists($path)) {
            throw new \RuntimeException(".env file not found at {$path}");
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            [$name, $value] = array_map('trim', explode('=', $line, 2));
            $value          = trim($value, "\"'");

            putenv("$name=$value");
            $_ENV[$name]    = $value;
            $_SERVER[$name] = $value;
        }
    }
}

```

**Code Explain**
> ```php
> public static function load(string $path)
> ```
> This parser class contains only the single static action method named `load`. This method's access modifier is `public`so that we can call it from anywhere without creating an object. 
> 
> ```php
> if (!file_exists($path)) {
>    throw new \RuntimeException(".env file not found at {$path}");
> }
> ```
> If the file path does not exist, return an exception. This is `fail-fast design`. It confirms that configuration is available for the application.
> 
> ```php
> $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
> 
> foreach ($lines as $line) {
>   $line = trim($line);
>   if ($line === '' || str_starts_with($line, '#')) {
>       continue;
>   }
> ```
> Read each line of the file except comment line (start with #) and store it in an array.
> 
> ```php
> [$name, $value] = array_map('trim', explode('=', $line, 2));
> $value          = trim($value, "\"'");
> ```
> the array destructuring operator has used to define left portion of the `=` (assignment operator) to $name variable and define right portion of the `=` (assignment operator) to the $value variable by using array destructuring. 
>
> ```php
> putenv("$name=$value");
> $_ENV[$name]    = $value;
> $_SERVER[$name] = $value;
> ```
> Created environment variable using `putenv()`, `$_ENV['KEY']`, `$_SERVER['KEY']`.

## Create Config File
Environment variables are not standard OOP structured data. So we will use Config class to use environment variable as OOP way throughout the application.

Create `config/app.php` file:

```php
<?php
return [
    'name'  => $_ENV['APP_NAME'] ?? 'My OOP App',
    'url' => ($_ENV['APP_URL'] ?? 'http://localhost:8000'),
    'env'   => $_ENV['APP_ENV'] ?? 'local',
    'debug' => ($_ENV['APP_DEBUG'] ?? 'false') === 'true',
];
```

**Code Explain**
> Reads configuration values from environment variables, applies safe default values when they are not set, and returns them as an associative array.

## Create Config File Parser

Create `app/Core/Config.php` file to parse the config file: 

```php
<?php
declare(strict_types=1);

namespace App\Core;

class Config
{
    protected static array $items = [];

    public static function load(string $configPath)
    {
        foreach (glob($configPath . '/*.php') as $file) {
            $key               = basename($file, '.php');
            self::$items[$key] = require $file;
        }
    }

    public static function get(string $key, mixed $default = null)
    {
        $keys  = explode('.', $key);
        $value = self::$items;

        foreach ($keys as $k) {
            if (!is_array($value) || !array_key_exists($k, $value)) {
                return $default;
            }
            $value = $value[$k];
        }

        return $value;
    }

    public static function all()
    {
        return self::$items;
    }
}

```

**Code Explain**
> As same the .env parser, each method is `static` and visibility is `public`. So, we will be able to call method directly using the scope resolution operator (::) without create an instance (object) of that class.
>
> `load()` method uses the `glob()` to scan all files of the `config` directory and load all file’s content to the $items[] array by using the `require` statement.
>
> `get()` method will provide config value by using `(.)` dot notation.
>
>`all()` method will provide all config keys and values as an array.

## Using Environment and Configuration Variable

Create a helper function `config` to the `app/Helpers/helpers.php`file:
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

```

Load .env file parser, config file parser and helper files to the `Core/Bootstrap.php` file:

```php
use App\Core\Env;
use App\Core\Config;

Env::load(__DIR__ . '/../../.env');

Config::load(__DIR__ . '/../../config');

require __DIR__ . '/../Helpers/helpers.php';

```

<details>
<summary>See full code: <code>Core/Bootstrap.php</code></summary>

```php
<?php
declare(strict_types = 1);

use App\Core\Env;
use App\Core\Config;

require __DIR__ . '/../../vendor/autoload.php';

// Create an instance of the ExceptionHandler and register it
$exceptionHandler = new \App\Core\ExceptionHandler();
$exceptionHandler->register();

// 1. Load .env
Env::load(__DIR__ . '/../../.env');

// 2. Load configs
Config::load(__DIR__ . '/../../config');

// 3. Load helpers
require __DIR__ . '/../Helpers/helpers.php';

return new \App\Core\App();

```
</details>

The `config()` helper function is available in the application. So, replace previous hardcoded configuration value with `config()`.
Open the `app/Controllers/HomeController.php` and update as like below:

```diff
- private $appName;

- public function __construct($appName) {
-     $this->appName = $appName;
- }

  public function getGreeting()
  {
-       return 'Welcome to the ' . $this->appName;
+       return 'Welcome to the ' . config('app.name');
  }
```

**Code Explain**
> We are using config() method directly from the controller that’s why the  `__construct()` method is not required..

<details>
<summary>See full code: <code>app/Controllers/HomeController.php</code></summary>

```php
<?php
declare(strict_types=1);

namespace App\Controllers;

class HomeController
{
    public function getGreeting()
    {
        return 'Welcome to the ' . config('app.name');
    }
}

```
</details>


`__construct()` method is removed from the `app/Controllers/HomeController.php`. So, update the `app/Core/App.php` file as like below:

```diff
- // load environment
-     $config = [
-         'app_name'=> 'PHP core OOP project'
-     ];

      // route define
      $route = $_SERVER['REQUEST_URI'];

      if ($route == "/") {
-         $homeController = new HomeController($config['app_name']);
-         echo $homeController->getGreeting();
+         $homeController = new HomeController();
+         echo $homeController->getGreeting();
 
      }

```

<details>
<summary>See full code: <code>app/Core/App.php</code></summary>

```php
<?php

declare(strict_types = 1);

namespace App\Core;

use App\Controllers\HomeController;

class App
{
    public function run()
    {
        // route define
        $route = $_SERVER['REQUEST_URI'];

        if ($route == "/") {
            $homeController = new HomeController();
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
</details>


## Display Error Message Only on Dev Server
Use the `config()` function to show error message conditionally only in the dev server. Update the `handleException()` method of the `app/Core/ExceptionHandler.php` file:

```diff
// This method will now handle both exceptions and errors
// that have been converted to ErrorExceptions.

http_response_code(500);
+ $appEnv = Config::get('app.env');

+ if ($appEnv == 'dev') {
    echo 'Message: ' . $e->getMessage() . '<br>';
    echo 'File: '    . $e->getFile()    . '<br>';
    echo 'Line: '    . $e->getLine()    . '<br>';
+ } else {
+     echo 'An Error Occurred';
+ }

/* 
 * Optional: 
 * You can log the error message to a log file
 * or perform other log related actions
 */
+ 
+ error_log($e->getMessage());
```

## Result

Make sure server is running or go to the project-root directory and run command `php -S localhost:8000 -t public/` on the terminal.    

- Browse `http://localhost:8000` -> Returns a welcome greeting message. See the app name in the message. It should be same as the Environment variable.
- Browse error and exception pages in both server environment modes `dev` and `production`. 
    - Browse: `http://localhost:8000/get-error` -> Returns error information only in `dev` environment.
    - Browse: `http://localhost:8000/get-exception` -> Returns exception information only in `dev` environment.

If the testing results are satisfactory, commit and push your changes to the remote repository. 
### ⚠️ Important
The `.env` file will contain sensitive environment variables, so it should not be added to the Git repository. Please update the `.gitignore` file as shown below:
```diff
/vendor
+.env
```

## Learn More
- [putenv()](https://www.php.net/manual/en/function.putenv.php), [getenv()](https://www.php.net/manual/en/function.getenv.php)
- [$_ENV](https://www.php.net/manual/en/reserved.variables.environment.php)
- [getenv() vs $_ENV](https://medium.com/@julien_schmitt/difference-between-php-getenv-and-env-beware-of-the-subtleties-50b5f17fc90b)
- [glob](https://www.php.net/manual/en/function.glob.php)
- [Array destructuring](https://www.php.net/manual/en/language.types.array.php#language.types.array.syntax.destructuring)
- [Static methods](https://www.php.net/manual/en/language.oop5.static.php#language.oop5.static.methods)

**[⬇SOURCE CODE: Chapter 07](source_code/07)**

<table align="center">
  <td>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <a href="06-error-exception-handler.md"> ◄ Previous: 06. Error and Exception Handler </a>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <a href="08-http-request.md"> Next: 08. HTTP Request Handler ► </a>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
  </td>
</table>
