<h1 align="center">Migration : Database Schema Operation</h1>

<table align="center" border="0">
  <td>

  ## Topics  

  01.&nbsp;&nbsp;[Source Code Files of this Chapter](#source-code-files-of-this-chapter)   
  02.&nbsp;&nbsp;[Migration : Design Database Structure](#migration--design-database-structure)   
  03.&nbsp;&nbsp;[Create Migration Abstract Class](#create-migration-abstract-class)  
  04.&nbsp;&nbsp;[Create Migration File](#create-migration-file)  
  05.&nbsp;&nbsp;[Command: Migration Script Runner](#command-migration-script-runner)  
  06.&nbsp;&nbsp;[Result](#result)  
  07.&nbsp;&nbsp;[Learn More](#learn-more)  
  </td>
</table>

## Source Code Files of this Chapter
```
project-root/
├── app/
│   └── Core/
│       └── Abstract/
│           └── Migration.php                       # new file
├── database/
│   └── Migrations/
│       └── 2026_01_10_create_users_table.php       # new file
└── migration.php                                   # new file
```

## Migration : Design Database Structure
On this framework, we will use a migration script as a layer to create and manage the database schema instead of direct SQL operations. 
Migration is a object oriented way to manage database schema operation.  
Migration has some advantages such as:
- Easy synchronization of the database schema across server environments like dev, stage, production.
- By checking migration files, you will be able to track changes of the database schema and roll back to previous state.

## Create Migration Abstract Class
In an application, there can be many migration files. So, it will be difficult to maintain a common standard for each file. The abstract migration class solves this problem by enforcing a consistent structure and standard. 
The abstract class will ensure the same (shared) database connection and a uniform structure for all migration files. As like the shared database connection, Abstract class allow shared helper functions to use on all extending (child) migration classes.
Create the Abstract` migration `app/Core/Abstract/Migration.php` file.   

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

    abstract public function up(): void;
    abstract public function down(): void;
}

```

**Code Explain**
> ```php
> abstract class Migration
> ```
> Declaring the class as `abstract` that means cannot instantiated it directly. Because it is a base template for all migrations. So, just extend by concrete migration classes. 
>
> ```php
> public function __construct()
> {
>     $this->db = Database::connect();
> }
> ```
> Instead of creating a connection in every migration file constructor is initializing a shared database connection.
>
> ```php
> protected function tableExists(string $table): bool
> {
>     $stmt = $this->db->prepare("
>         SELECT COUNT(*)
>         FROM information_schema.tables
>         WHERE table_schema = DATABASE()
>         AND table_name = ?
>     ");
> 
>     $stmt->execute([$table]);
> 
>     return (bool) $stmt->fetchColumn();
> }
> ```
> A Shared helper method to check whether a table exists in the current database.
>
> ```php
> abstract public function up(): void;
> abstract public function down(): void;
> ```
> Abstract methods. Every migration class have to implement these abstract methods.

## Create Migration File

Now create a concrete migration `database/Migrations/2026_01_10_create_users_table.php` file that will extend the abstract migration class and execute the database schema script. 

```php
<?php
declare(strict_types=1);

use App\Core\Abstract\Migration;

return new class extends Migration {

    public function up(): void
    {
        if ($this->tableExists('users')) {
            echo "Table users already exists, skipping...\n";
            return;
        }

        $this->db->exec("
            CREATE TABLE users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100),
                email VARCHAR(150),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
    }

    public function down(): void
    {
        if (! $this->tableExists('users')) {
            echo "Table users does not exist, skipping...\n";
            return;
        }

        $this->db->exec("DROP TABLE users");
    }
};

```

**Code Explain**

> This class creates an anonymous class that extends Migration and returned immediately.
>
> In up() method, first check, if the `user` table exists using shared helper function then echo a message and return early. 
>
> If user table is not exist then proceed to create the `users` table.
>
> In down() method delete the `users` database table as rollback operation.

## Command: Migration Script Runner  
To execute migration file, create command-line database migration runner `migration.php` on the project-root directory:

```php
<?php
declare(strict_types=1);

require __DIR__ . '/app/Core/Bootstrap.php';

$command = $argv[1] ?? null;

if (!in_array($command, ['up', 'down'], true)) {
    echo "Usage:\n";
    echo "  php migration.php up\n";
    echo "  php migration.php down\n";
    exit(1);
}

$files = glob(__DIR__ . '/database/Migrations/*.php');

sort($files); // important for ordered migrations

foreach ($files as $file) {
    echo "Running: " . basename($file) . "\n";

    $migration = require $file;

    if ($command === 'up') {
        $migration->up();
    } else {
        $migration->down();
    }
}

echo "Migration {$command} completed.\n";

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
> if (!in_array($command, ['up', 'down'], true)) {
>     echo "Usage:\n";
>     echo "  php migration.php up\n";
>     echo "  php migration.php down\n";
>     exit(1);
> }
> ```
> If argument is not `up` or `down` then display help instruction and exits.
>
> ```php
> $files = glob(__DIR__ . '/database/Migrations/*.php');
> sort($files);
> ```
> `glob()` will find all `.php` files in `database/Migrations` directory and return an array of file paths.
> then sort files alphabetically. This sorting is important because migrations should to run in a periodical order.
> 
> ```php
> foreach ($files as $file) {
>     echo "Running: " . basename($file) . "\n";
>
>     $migration = require $file;
>
>     if ($command === 'up') {
>         $migration->up();
>     } else {
>         $migration->down();
>     }
> }
> 
> echo "Migration {$command} completed.\n";
> ```
> Loop through each file and load object of the migration class using `require` statement.    
> `up()` will apply the migration operation.
> `down()` will rollback the migration operation.
> At the end display success message.

## Result

Make sure server is running or go to the project-root directory and run command `php -S localhost:8000 -t public server.php` on the terminal.  
```
# go to project-root directory
cd <PROJECT-ROOT>

# execute migration
php migration.php up
```
> Above command will create `users` table

- Browse: `http://localhost:8000/adminer.php` -> Login to Adminer and check the structure of the `users` table.

```
# ⚠️ Important: This command will drop the database table
> php migration.php down
```

If the testing results are satisfactory, commit and push your changes to the remote repository.   

## Learn More
- [anonymous class](https://www.phptutorial.net/php-oop/php-anonymous-class/)  
- [abstract class](https://mohasin-dev.medium.com/abstract-class-in-php-with-example-cc53b1428e20)  
- [glob()](https://www.php.net/manual/en/function.glob.php)  
- [argv](https://www.php.net/manual/en/reserved.variables.argv.php)  
- [exec()](https://www.php.net/manual/en/function.exec.php)  

**[⬇SOURCE CODE: Chapter 13](#)**  

<table align="center">
  <td>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <a href="12-database.md"> ◄ Previous: 12. Database Connection </a>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <a href="14-model.md"> Next: 14. Model - Database Table Representer ► </a>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
  </td>
</table>
