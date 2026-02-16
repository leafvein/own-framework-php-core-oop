<h1 align="center">PHPUnit Test</h1>

<table align="center" border="0">
  <td>

  ## Topics  

  01.&nbsp;&nbsp;[Source Code Files of this Chapter](#source-code-files-of-this-chapter)   
  02.&nbsp;&nbsp;[Unit Testing on PHP](#unit-testing-on-PHP)   
  03.&nbsp;&nbsp;[Install and Configure PHPUnit Testing](#install-and-configure-phpunit-testing)  
  04.&nbsp;&nbsp;[Configure Test Environment](#configure-test-environment)  
  05.&nbsp;&nbsp;[Sample Test Cases](#sample-test-cases)     
  06.&nbsp;&nbsp;[Result](#result)  
  07.&nbsp;&nbsp;[Learn More](#learn-more)   
  </td>
</table>

## Source Code Files of this Chapter

```
project-root
├── app/
│   └── Core/
│       └── Response.php                       # modified file
├── composer.json                              # modified file
├── .env.testing                               # new file
├── .gitignore                                 # modified file
├── .phpunit.result.cache                      # new file
├── phpunit.xml                                # new file
└── tests/                                     # new directory
    ├── Bootstrap.php                          # new file
    ├── Database/                              # new directory
    │   ├── DatabaseConnectionTest.php         # new file
    │   ├── MigrationTest.php                  # new file
    │   ├── Seeders/                           # new directory
    │   │   └── UserSeeder.php                 # new file
    │   ├── SeederTest.php                     # new file
    │   └── UserModelTest.php                  # new file
    ├── DatabaseTestCase.php                   # new file
    ├── Feature/                               # new directory
    │   └── HomePageTest.php                   # new file
    └── Unit/                                  # new directory
        ├── ConfigTest.php                     # new file
        ├── EnvTest.php                        # new file
        ├── RequestTest.php                    # new file
        ├── RouterTest.php                     # new file
        └── ValidatorTest.php                  # new file
 
```

## Unit Testing on PHP

[PHPUnit](https://phpunit.de/index.html) is a testing framework for PHP. It allows you to write automated tests for your PHP code to ensure it works as expected. We will use PHPUnit to test the functionalities of components in our framework. If we add new features or components later, we can be assured that the changes do not break any existing functionality and that the entire framework  is working properly.  

## Install and Configure PHPUnit Testing

Run command to install `PHPUnit`:

```
> composer require --dev phpunit/phpunit
```

Create PHPUnit configuration `phpunit.xml` file at the project root:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit
    bootstrap="tests/Bootstrap.php"
    colors="true"
>
    <testsuites>
        <testsuite name="PHP OOP Own Framework PHPUnit Test">
            <directory>tests</directory>
        </testsuite>
    </testsuites>

    <php>
        <env name="APP_ENV" value="testing"/>
        <env name="DB_NAME" value="test_example_db"/>
    </php>
</phpunit>
```

**Code Explain**
> Use a test bootstrap to load the autoloader and environment variables for testing.
> 
> ```xml
> <directory>tests</directory>
> ```
> Specifies test direcotry. 
>
> ```xml
> <php>
>     <env name="APP_ENV" value="testing"/>
>     <env name="DB_NAME" value="test_example_db"/>
> </php>
> ```
> Defines environment variables for tests.

First of all create the `tests` directory file at the project root.  
Then create the **Test Bootstrap** (`tests/bootstrap.php`) file:

```php
<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Core\Env;
use App\Core\Config;
use App\Core\Response;

// Load test env safely
Env::load(__DIR__ . '/../.env.testing');

// Load configs
Config::load(__DIR__ . '/../config');

// Load helpers
require __DIR__ . '/../app/Helpers/helpers.php';

// Disable view html rendering in tests
Response::$testing = true;

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
```

**Code Explain**
> Load the test environment file to use a separate test database
> 
> ```php
> Response::$testing = true;
> ```
> Disable view to disallow direct HTML rendering from the `view()` method during testing
>
> ```php
> if (session_status() === PHP_SESSION_NONE) {
>     session_start();
> }
> ```
> Start a PHP session only during testing.

Update `composer.json` file to autoload test classes:

```diff
  "autoload": {
      "psr-4": {
          "App\\": "app/",
+         "Tests\\": "tests/"
      }
  },
```
<details>
<summary>See full code: <code>composer.json</code></summary>

```json
{
    "name": "leafvein/own-framework-php-core-oop",
    "description": "Create Own Framework Using PHP Core OOP",
    "type": "project",
    "require": {
        "php": ">=8.0",
        "phpmailer/phpmailer": "dev-master"
    },
    "require-dev": {
        "phpunit/phpunit": "11.5.x-dev"
    },
    "autoload": {
        "psr-4": {
            "App\\": "app/",
            "Tests\\": "tests/"
        }
    },
    "minimum-stability": "dev",
    "license": "MIT"
}

```
</details>

Regenerate the autoloader files to include test classes:
```
> composer dump-autoload
```

## Configure Test Environment

We will use `.env.testing` to prevent tests from affecting the production database and other services. Create the `.env.testing` file:

```
APP_NAME=PHP core OOP project
APP_URL=http://localhost:8000
APP_ENV=testing
APP_DEBUG=true

# Database
DB_HOST=127.0.0.1
DB_NAME=test_example_db
DB_USER=root
DB_PASSWORD=<YOUR_ROOT_PASSPORT>
```

Update the `app/Core/Response.php`file to return early so that direct HTML is not rendered when testing is in progress.  

```php
public static bool $testing = false;

if (self::$testing === true) {
    echo $content;
    return;
}
```

<details>
<summary>See full code: <code>app/Core/Response.php</code></summary>

```php
<?php
declare(strict_types = 1);

namespace App\Core;

class Response
{
    public static bool $testing = false;

    public static function view(string $view, array $data = [])
    {
        extract($data);
        $content = self::render($view, $data);

        if (self::$testing === true) {
            echo $content;
            return;
        }
        
        include __DIR__ . '/../../views/layout.php';
        exit;
    }

    protected static function render(string $view, array $data = [])
    {
        ob_start();
        extract($data);
        include __DIR__ . '/../../views/' . $view . '.php';
        return ob_get_clean();
    }

    public static function json($data, int $status = 200)
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}

```
</details>

## Sample Test Cases
We've implemented simple test cases as examples. For all test cases, see the tests directory. Let's explain some of the test cases that will help you get started.

Test config value `tests/Unit/ConfigTest.php`:

```php
<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\Core\Config;

final class ConfigTest extends TestCase
{
    public function test_config_value_can_be_retrieved()
    {
        $this->assertNotNull(Config::get('app.name'));
    }

    public function test_config_default_value()
    {
        $this->assertEquals('default', Config::get('app.unknown', 'default'));
    }
}
```

Test request properties `tests/Unit/RequestTest.php`:
```php
<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\Core\Request;

final class RequestTest extends TestCase
{
    protected function setUp(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI']    = '/test';
        $_POST['name'] = 'JohnDoe';
    }

    public function test_request_method()
    {
        $request = new Request();
        $this->assertEquals('POST', $request->method());
    }

    public function test_request_uri()
    {
        $request = new Request();
        $this->assertEquals('/test', $request->uri());
    }

    public function test_request_input()
    {
        $request = new Request();
        $this->assertEquals('JohnDoe', $request->input('name'));
    }
}
```

Test route path `tests/Unit/RouterTest.php`: 
```php
<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\Core\Router;
use App\Core\Request;

final class RouterTest extends TestCase
{
    public function test_route_registration()
    {
        $router = new Router(new Request());

        $router->get('/home', fn () => 'ok');

        $routes = $router->routes();

        $this->assertArrayHasKey('/home', $routes['GET']);
    }
}
```

Test database connection `tests/Database/DatabaseConnectionTest.php` file:
```php
<?php
declare(strict_types=1);

namespace Tests\Database;

use Tests\DatabaseTestCase;
use App\Core\Database;
use PDO;

final class DatabaseConnectionTest extends DatabaseTestCase
{
    public function test_database_connection()
    {
        $pdo = Database::connect();

        $this->assertInstanceOf(PDO::class, $pdo);
    }
}
```

## Result
Make sure server is running or go to the project-root directory and run command `php -S localhost:8000 -t public server.php` on the terminal.   

Add the test database:  
- Browse: `http://localhost:8000/adminer.php    
- Login as a root user:
```
host: `127.0.0.1`
root-user: `root`
root-password: `<YOUR_PASSWORD>`
```
- After login create the `test_example_db` database.

Run command to check result:  
```
# Run all tests
> vendor/bin/phpunit --testdox
>
# Run a specific test
> vendor/bin/phpunit tests/Unit/ConfigTest.php
```
 
<details>
<summary>The results of running all tests (as similar)</summary>

```
PHPUnit 11.5.53 by Sebastian Bergmann and contributors.

Runtime:       PHP 8.2.28
Configuration: `<PROJECT-ROOT>`/phpunit.xml

...................                                               19 / 19 (100%)

Time: 00:00.720, Memory: 8.00 MB

Config
 ✔ Config value can be retrieved
 ✔ Config default value

Database Connection (Tests\Database\DatabaseConnection)
 ✔ Database connection

Env (Tests\Unit\Env)
 ✔ Testing env loaded

Home Page
 ✔ Home page returns view

Migration (Tests\Database\Migration)
 ✔ Users table exists

Request
 ✔ Request method
 ✔ Request uri
 ✔ Request input

Router
 ✔ Route registration

Seeder (Tests\Database\Seeder)
 ✔ User seeder runs

User Model (Tests\Database\UserModel)
 ✔ Create user
 ✔ Find user by id
 ✔ Find by column
 ✔ Update user
 ✔ Delete user

Validator
 ✔ Required validation fails
 ✔ Email validation passes
 ✔ Min length validation

OK (19 tests, 22 assertions)
```
</details>

If the testing results are satisfactory, commit and push your changes to the remote repository.   

**Note:**
For testing, we've created a `.env.testing` file that should not be added to the Git repository. We should also ignore the `.phpunit.result.cache` file.
Therefore, update the `.gitignore` file as shown below:
`.gitignore` file    

```
.env.testing
.phpunit.result.cache
```

<details>
<summary>See full code: <code>.gitignore</code></summary>

```
/vendor
.env
.env.testing
.phpunit.result.cache

```
</details>

## Learn More
- [PHPUnit](https://phpunit.de/index.html)
- [Database For Testing](https://laraveldaily.com/lesson/testing-laravel/db-configuration-refreshdatabase-phpunit-xml-env-testing)

**[⬇SOURCE CODE: Chapter 22](source_code/22)**    

<table align="center">
  <td>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <a href="21-queue-verify-email.md"> ◄ Previous: 21. Queue (Asynchronous) Process - User Verification Email </a>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
  </td>
</table>
