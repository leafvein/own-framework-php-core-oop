<h1 align="center">Database Connection</h1>

<table align="center" border="0">
  <td>

  ## Topics  

  01.&nbsp;&nbsp;[Source Code Files of this Chapter](#source-code-files-of-this-chapter)   
  02.&nbsp;&nbsp;[MySQL Database](#mysql-database)   
  03.&nbsp;&nbsp;[Install MySQL Database Server](#install-mysql-database-server)  
  04.&nbsp;&nbsp;[Install Database Client `adminer.php`](#install-database-client-adminerphp)  
  05.&nbsp;&nbsp;[Create `server.php`](#create-serverphp)  
  06.&nbsp;&nbsp;[Create a New Database](#create-a-new-database)  
  07.&nbsp;&nbsp;[Configure Database Connection](#configure-database-connection)  
  08.&nbsp;&nbsp;[Use Database in PHP](#use-database-in-php)  
  09.&nbsp;&nbsp;[Result](#result)   
  07.&nbsp;&nbsp;[Learn More](#learn-more)   
  </td>
</table>

## Source Code Files of this Chapter
```
project-root/
├── app/
│   ├── Controllers/
│   │   └── HomeController.php                  # modified file
│   ├── Core/
│   │   └── Database.php                        # new file
│   └── database.php                            # new file
├── .env                                        # modified file
└── server.php                                  # new file
```

## MySQL Database
To store and manage data, we will use a Database. In this application, we perform relational CRUD operations, so a MySQL database is sufficient. PHP has built-in support for integrating with MySQL database. So, let's get started.

## Install MySQL Database Server
```
# install the mysql-server
> sudo apt install mysql-server

# check MySQL is running
> sudo systemctl status mysql

# Access MySQL
> sudo mysql
```
### ⚠️ Security    
> MySQL root user uses the auth_socket plugin.   
> That means you cannot log in as `root` user using a password, just logged into system as a sudo user and access.   
> `sudo` ensure that you are the system root user and MySQL trusts that using the `auth_socket` and grants access.
> **PHP cannot log in as MySQL root using auth_socket**. So set root password
```
# change mysql authentication plugin and set password
> ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY 'YOUR_ROOT_PASSWORD';

# Alternatively use
> sudo mysql_secure_installation

> FLUSH PRIVILEGES;
> EXIT;

# Check access by root password
> mysql -u root -p
```

Alternatively, you can [install MySQL using Docker]((https://gist.github.com/johirpro/132639a3580033c14354e307beade6e8)).

## Install Database Client `adminer.php`
To access and manage the MySQL server using a web-based GUI, we can use Adminer, which is a single-file PHP database client.    
```
# Go to the project-root directory
> cd <PROJECT_ROOT>

# download the adminer file
> curl -L -o adminer.php https://www.adminer.org/latest.php
```

## Create `server.php`
We installed `adminer.php` in the public directory, but our application uses index.php as a front controller, so direct access to Adminer is blocked by the routing logic. Therefore, we use `server.php` as a URL rewriter / front controller override for PHP’s built-in server, which serves Adminer correctly as a static file.
create `server.php` file in the project root directory:
```php
<?php
declare(strict_types=1);

/**
 * PHP built-in server router configuration
 */

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = rtrim($path, '/');

$requestedFile = __DIR__ . '/public' . $path;

// Serve static file directly if it exists
if (is_file($requestedFile)) {
    return false;
}

// Otherwise, forward to front controller
require __DIR__ . '/public/index.php';

```

**Code Explain**
> ```php
> $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
> $path = rtrim($path, '/');
> ```
> get the request path from the url. For example, extracts `about` from http://localhost:8000/about.
>
> ```php
> $requestedFile = __DIR__ . '/public' . $path;
> ```
> converted request path to a full filesystem path of the public directory. 
>
> ```php
> if (is_file($requestedFile)) {
>    return false;
> }
> 
> require __DIR__ . '/public/index.php';
> ```
> if public file is exist physically in the public directory, it is served directly.    
> otherwise, the request is forwarded to the front-controller (public/index.php) file.   

## Create a New Database
- To access to the MySQL database using the database client `adminer.php`, First run the application using following command:
```
# using url rewrite script along side with document root
> `php -S localhost:8000 -t public server.php`
```
- Browse: `http://localhost:8000/adminer.php    
- Login as a root user:
```
host: `127.0.0.1`
root-user: `root`
root-password: `<YOUR_PASSWORD>`
```
- After login create a new database named `example_db`

## Configure Database Connection
Add MySQL database connection information to the `.env` file:
```
# Database
DB_HOST=127.0.0.1
DB_NAME=example_db
DB_USER=root
DB_PASSWORD=<YOUR_PASSWORD>
```
<details>
<summary>See full code: <code>.env</code></summary>

```
APP_NAME=PHP core OOP project
APP_ENV=dev
APP_DEBUG=true

# Database
DB_HOST=127.0.0.1
DB_NAME=example_db
DB_USER=root
DB_PASSWORD=<YOUR_PASSWORD>
```
</details>

We have created a new database and now we will create a php class to establish a connection with MySQL database. 
The class will implement `singleton pattern` to ensure that only one database connection will be created and used as a central access point throughout the application.    

Create the `Database` connection on `app/core/Database.php` file:

```php
<?php
declare(strict_types=1);

namespace App\Core;

use PDO;

class Database
{
    private static ?PDO $pdo = null;

    public static function connect(): PDO
    {
        if (!self::$pdo) {
            $config = Config::get('database');

            self::$pdo = new PDO(
                "mysql:host={$config['host']};dbname={$config['database']};charset=utf8mb4",
                $config['username'],
                $config['password'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                ]
            );
        }

        return self::$pdo;
    }
}

```
**Code Explain**
> If no database PDO connection exists yet,
> it creates one connection to MySQL database using PDO.
> Otherwise simply return the exists connection.

## Use Database in PHP
Our mysql database and php database connection class are prepared. So, to check the connection let's update the `app/Controllers/HomeController.php` file:

```php
use App\Core\Database;

try {
    Database::connect();

    $greetMessage = "Database connection to {$appName} implemented successfully";
} catch (Throwable) {
    $greetMessage = "Database connection to {$appName} failed";
}

```
**Code Explain**
> Try to connect to the database and display a success message if the connection is successful or display a failure message.

<details>
<summary>See full code: <code>app/Controllers/HomeController.php</code></summary>

```php
<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Core\Response;
use Throwable;

class HomeController
{

    public function index()
    {
        $appName = config('app.name');

        try {
            Database::connect();

            $greetMessage = "Database connection to {$appName} implemented successfully";
        } catch (Throwable) {
            $greetMessage = "Database connection to {$appName} failed";
        }
        
        return Response::view('home', [
            'message' => $greetMessage
        ]);
    }
}

```
</details>

## Result

Make sure server is running or go to the project-root directory and run command `php -S localhost:8000 -t public server.php` on the terminal.    
⚠️ Important    
From the beginning up to Chapter 11, our App run command was `php -S localhost:8000 -t public`.    
From now on, until the last chapter, our App run command will be `php -S localhost:8000 -t public server.php`.

- Browse: `http://localhost:8000/adminer.php` -> Loads Adminer properly.
- Browse: `http://localhost:8000` -> Returns success message.

If the testing results are satisfactory, commit and push your changes to the remote repository.   

## Learn More
- [PDO](https://www.phptutorial.net/php-pdo/)    
- [Singleton](https://refactoring.guru/design-patterns/singleton/php/example)    

**[⬇SOURCE CODE: Chapter 12](source_code/12)**

<table align="center">
  <td>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <a href="11-response-api-json.md"> ◄ Previous: 11. Response API - JSON </a>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <a href="13-migration.md"> Next: 13. Migration - Database Schema Operation ► </a>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
  </td>
</table>
