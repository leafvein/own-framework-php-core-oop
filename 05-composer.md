<h1 align="center">Composer : Load Class</h1>

<table align="center" border="0">
  <td>

  ## Topics  

  01.&nbsp;&nbsp;[Source Code Files of this Chapter](#source-code-files-of-this-chapter)   
  02.&nbsp;&nbsp;[Using Composer](#using-composer)   
  03.&nbsp;&nbsp;[Configure Composer](#configure-composer)  
  04.&nbsp;&nbsp;[Install Dependency and Generate Autoload File](#install-dependency-and-generate-autoload-file)  
  05.&nbsp;&nbsp;[Integrate the `autoload.php` File into the Application](#integrate-the-autoloadphp-file-into-the-application)  
  06.&nbsp;&nbsp;[Load a Controller Class Using Composer Autoload](#load-a-controller-class-using-composer-autoload)  
  07.&nbsp;&nbsp;[Unified Error and Exception Handler](#unified-error-and-exception-handler)  
  08.&nbsp;&nbsp;[Result](#result)    
  09.&nbsp;&nbsp;[Git : Add `.gitignore` File and Push Changes to Remote](#git--add-gitignore-file-and-push-changes-to-remote)  
  10.&nbsp;&nbsp;[Learn More](#learn-more)  
  </td>
</table>

## Source Code Files of this Chapter
```
project-root/
├── app/
│   ├── Controllers/
│   │   └── HomeController.php      # new file
│   └── Core/
│       └── Bootstrap.php           # modified file
├── public/
│    └── index.php                  # modified file
│
├── vendor/                         # new directory
│
├── composer.json                   # new file
├── composer.lock                   # new file
└── .gitignore                      # new file
```

## Using Composer
This project is using core php that means no php package will be used. So someone may be surprised that why we are configuring composer? 

The answer in a single word is **autoload**. Autoload will load required PHP classes based on their namespace and directory structure without using the `require()` or `include()` statement. 

## Configure Composer
Go to the project root directory and run following command:  
`composer init`  
Above command will create a `composer.json` file in the project root directory.

**Edit `composer.json` file:**  
```json
{
    "name": "leafvein/own-framework-php-core",
    "description": "Create Own Framework Using Core PHP OOP",
    "type": "project",
    "require": {
        "php": ">=8.0"
    },
    "autoload": {
        "psr-4": {
            "App\\": "app/"
        }
    },
    "minimum-stability": "dev",
    "license": "MIT"
}
```

**Code Explain:**  
> [name](https://getcomposer.org/doc/04-schema.md#name) : a required attribute that is used as a unique identifier of the application within the composer ecosystem. composer.    
>[description](https://getcomposer.org/doc/04-schema.md#description): a short description of the package.    
>[require](https://getcomposer.org/doc/04-schema.md#require): another required attribute used to specify your project's  dependencies. Current now our project dependency is php 8.    
>[autoload](https://getcomposer.org/doc/04-schema.md#autoload): configure the method to load PHP classes and files of a project automatically. Here [PSR-4](https://getcomposer.org/doc/04-schema.md#psr-4) is used to map namespace `App` to `app` directory.

## Install Dependency and Generate Autoload File

From your project-root directory run this command:
```
composer install
```

After running `composer install` command composer will read project dependencies from the `composer.json` file then create `vendor` directory and `composer.lock` file to the project root directory.  
> `vendor`: directory stores dependencies and [autoload.php](https://getcomposer.org/doc/01-basic-usage.md#autoloading) file.   
> `composer.lock`: file locks version of all dependencies to make an option so that all team members can use the same versions of dependencies.

## Integrate the `autoload.php` File into the Application
We are integrating `vendor/autoload.php` file through the Bootstrap. So, open the `app/Core/Bootstrap.php` file and add this line:
```php
require __DIR__ . '/../../vendor/autoload.php';
```

<details>
<summary>See full code: <code>app/Core/Bootstrap.php</code></summary>

```php
<?php
declare(strict_types = 1);

require __DIR__ . '/../../vendor/autoload.php';

// load environment
$config = [
    'app_name'=> 'PHP core OOP project'
];

// route define
$route = ltrim($_SERVER['REQUEST_URI'], '/');

echo($route);

if ($route == "get-error") {
    // trigger a fatal error
    trigger_error("An Intentional Error Occurred.", E_USER_ERROR);
}

if ($route == 'get-exception') {
    // throw an exception
    throw new Exception("An Intentional Exception Occurred.");
}

echo 'Welcome to the ' . $config['app_name'];

```
</details>

## Load a Controller Class Using Composer Autoload

Create a new `app/Controllers/HomeController.php` file and add following code:
```php
<?php

namespace App\Controllers;

class HomeController
{
    private $appName;

    public function __construct($appName) {
        $this->appName = $appName;
    }

    public function getGreeting()
    {
        return 'Welcome to the ' . $this->appName;
    }
}

```

**Code Explain**

> A new Controller Class file is created, now it is require to update classmap of composer autoload. So, run following command: 
> ```
> composer dump-autoload
> ```

> This command will update classmap of composer autoload to ensure all latest classes are available to use.

Now you will be able to call the controller's method from the bootstrap. Open the `app/Core/Bootstrap.php` file and add the following code:

```php
use App\Controllers\HomeController;

$homeController = new HomeController($config['app_name']);
echo $homeController->getGreeting();
```

**Code Explain**
> ```php
> use App\Controllers\HomeController;
> ```
> Loading HomeController class using fully qualified class names (FQCNs)
>
> ```php
> $homeController = new HomeController($config['app_name']);
> echo $homeController->getGreeting();
> ```
> Instantiation of HomeController class and Displaying content of the getGreeting()

<details>
<summary>See full code: <code>app/Core/Bootstrap.php</code></summary>

```php
<?php
declare(strict_types = 1);

require __DIR__ . '/../../vendor/autoload.php';

use App\Controllers\HomeController;

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
    throw new Exception("An Intentional Exception Occurred.");
}

$homeController = new HomeController($config['app_name']);
echo $homeController->getGreeting();
```
</details>

## Unified Error and Exception Handler
On chapter 03 we've used `set_error_handler()` and `set_exception_handler()` to handle error and exception respectively. In this chapter we will use try-catch statement to simplify the Error and Exception Handling.
Open the `public/index.php` file and modify as below:

```diff
- // error handler
- set_error_handler(function ($errno, $errstr, $errfile, $errline) {
-    echo 'Message: ' . $errno   . ' - ' . $errstr . '<br>';
-    echo 'File: '    . $errfile . '<br>';
-    echo 'Line: '    . $errline . '<br>';
- });
-
- // exception handler
- set_exception_handler(function (Exception $e) {
-     echo 'Message: ' . $e->getMessage() . '<br>';
-     echo 'File: '    . $e->getFile()    . '<br>';
-     echo 'Line: '    . $e->getLine()    . '<br>';
-});
-
+ try {
    require __DIR__ . '/../app/Core/Bootstrap.php';
+ } catch (Throwable $e) {
+    echo 'Message: ' . $e->getMessage() . '<br>';
+    echo 'File: '    . $e->getFile()    . '<br>';
+    echo 'Line: '    . $e->getLine()    . '<br>';
+}
```

**Code Explain**
> Instead of handling Error and Exception separately we are using unified handler `Throwable`. In PHP 7 and later, a single try-catch block can catch both traditional exceptions and critical internal PHP errors.

<details>
<summary>See full code: <code>public/index.php</code></summary>

```php
<?php
declare(strict_types = 1);

try {
    require __DIR__ . '/../app/Core/Bootstrap.php';
} catch (Throwable $e) {
    echo 'Message: ' . $e->getMessage() . '<br>';
    echo 'File: '    . $e->getFile()    . '<br>';
    echo 'Line: '    . $e->getLine()    . '<br>';
}
```
</details>

## Result

Make sure server is running or go to the project-root directory and run command `php -S localhost:8000 -t public/` on the terminal.    

- Browse `http://localhost:8000` -> Returns a welcome greeting message
- Browse: `http://localhost:8000/get-error` -> Returns error information
- Browse: `http://localhost:8000/get-exception` -> Returns exception information 

## Git : Add `.gitignore` File and Push Changes to Remote    
If testing result is satisfactory then it is time to push changes to the remote repository.    

Run Command:
```
> git status
```
As expected you will see the `vendor` directory in untracked stage but it is [not recommended to push the `vendor` directory to the remote repository](https://getcomposer.org/doc/faqs/should-i-commit-the-dependencies-in-my-vendor-directory.md). In Git terminology, this is called 'ignore or not track'. Git uses `.gitignore` file to ignore directory and files.

Create `.gitignore` file on project-root directory with following content:

```
/vendor
```

Add the files that are newly created in this chapter.

```
> git add HomeController.php composer.json composer.lock .gitignore
```
> `git add <file_path>` command add specific file content to the staging area for the next commit. We are adding `HomeController.php`, `composer.json`, `.gitignore`, and `composer.lock` files to the staging area for next commit.
 
⚠️ `composer.lock`! :thinking:. Yes, [commit your composer.lock file to git repository](https://getcomposer.org/doc/01-basic-usage.md#installing-from-composer-lock).  
⚠️ `.gitignore`! :thinking: :thinking:. Yes, yes, Commit the `.gitignore` file to ensure everyone using the repository ignores the same files.

Add the files that are modified in this chapter.

```
> git add -p Bootstrap.php
> git add -p index.php
```
> 💡 `git add -p <file_path>` command provides an review option that steps through each hunk (section of changes) in the specified filename and asks you whether you want to stage that specific hunk or not. To add specific section of changes just type `y` otherwise `n`. It will prevent you to add any unintentional testing code and it your code will clean.

Commit your changes and push to the remote repository.

```
> git commit -m "Configured composer"
> git push origin <branch_name>
```

## Learn More
- [Install Composer](https://getcomposer.org/download/)
- [Composer JSON schema (properties)](https://getcomposer.org/doc/04-schema.md)  
- [PSR-4](https://www.php-fig.org/psr/psr-4/)
- [.gitignore](https://www.w3schools.com/git/git_ignore.asp)

**[⬇SOURCE CODE: Chapter 05](#)**

<table align="center">
  <td>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <a href="04-git.md"> ◄ Previous: 04. Git </a>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <a href="06-error-exception-handler.md"> Next: 06. Error and Exception Handler ► </a>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
  </td>
</table>
