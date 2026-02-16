<h1 align = "center">Model : Database Table Representer</h1>

<table align="center" border="0">
  <td>

  ## Topics  

  01.&nbsp;&nbsp;[Source Code Files of this Chapter](#source-code-files-of-this-chapter)   
  02.&nbsp;&nbsp;[Model : Database Table Representer](#model--database-table-representer)   
  03.&nbsp;&nbsp;[Create Model Abstract Class](#create-model-abstract-class)  
  04.&nbsp;&nbsp;[Create Model Class](#create-model-class)  
  05.&nbsp;&nbsp;[Use Model Class](#use-model-class)  
  06.&nbsp;&nbsp;[Result](#result)  
  07.&nbsp;&nbsp;[Learn More](#learn-more)  
  </td>
</table>

## Source Code Files of this Chapter
```
project-root/
└── app/
    ├── Controllers/
    │   └── HomeController.php                  # modified file
    ├── Core/
    │   └── Model.php                           # new file
    └── Models/
        └── User.php                            # new file
```


## Model : Database Table Representer
A `Model` is the layer responsible for managing the data of a database table. A `Model Class` represents the table’s entities in an object-oriented way and provides common execution method like query, insert, update, or delete data from the database table.

## Create Model Abstract Class
To create Model we will use an abstract model class in PHP to share common logic (method and property), and enforce common structure. 

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
    
    /**
     * Check whether the model's table exists
     */
    public static function tableExists(): bool
    {
        $stmt = static::db()->prepare("
            SELECT COUNT(*)
            FROM information_schema.tables
            WHERE table_schema = DATABASE()
              AND table_name = :table
        ");

        $stmt->execute([
            'table' => static::$table
        ]);

        return (bool) $stmt->fetchColumn();
    }

    public static function create(array $attributes): bool
    {
        $columns      = array_keys($attributes);
        $placeholders = array_fill(0, count($columns), '?');

        $sql = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)",
            static::$table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        $stmt = static::db()->prepare($sql);
        return $stmt->execute(array_values($attributes));
    }

    public static function truncate(): void
    {
        static::db()->exec("TRUNCATE TABLE " . static::$table);
    }

    public static function all(): array
    {
        $stmt = static::db()->query(
            "SELECT * FROM " . static::$table
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

```

**Code Explain**
> In the abstract Model class, common shared functions are db(), tableExists(), create(), truncate(), all().
> 
> `db()`: it will return only one PDO connection.
>
> `tableExists()`: return boolean value based on the table existence.
>
> ```php
> public static function create(array $attributes): bool
> {
>     $columns      = array_keys($attributes);
>     $placeholders = array_fill(0, count($columns), '?');
>
>     $sql = sprintf(
>         "INSERT INTO %s (%s) VALUES (%s)",
>         static::$table,
>         implode(', ', $columns),
>         implode(', ', $placeholders)
>     );
>
>     $stmt = static::db()->prepare($sql);
>     return $stmt->execute(array_values($attributes));
> }
> ```
> Inserts a new row into the table.
> Extract column names then create SQL placeholders
> Build sql queries.
> At the end prepare and execute sql statement.
> 
> `truncate()`: Deletes all rows from the table
> 
> `all()`: Fetch every row from the table Returns an array of rows.

## Create Model Class

Create `User` model `app/Models/User.php`:

```php
<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Abstract\Model;

class User extends Model
{
    protected static string $table = 'users';
}

```

**Code Explain**
> This simple model defines specific database table to use and inheriting all CRUD (shared methods) from the base Model.

## Use Model Class
Use user model in the `app/Controllers/HomeController.php` file:
```php
use App\Models\User;

$greetMessage = User::tableExists()
    ? "Users table exists in {$appName}"
    : "Users table does NOT exist in {$appName}";
```

**Code Explain**
> Import `User` model and check for the existence of the `users` table, then stores a table existence status message in a variable.

<details>
<summary>See full code: <code>app/Controllers/HomeController.php</code></summary>

```php
<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Response;
use App\Models\User;
use Throwable;

class HomeController
{

    public function index()
    {
        $appName = config('app.name');

        try {
            $greetMessage = User::tableExists()
                ? "Users table exists in {$appName}"
                : "Users table does NOT exist in {$appName}";
        } catch (Throwable $e) {
            $greetMessage = "Database error: " . $e->getMessage();
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

> - Browse: `http://localhost:8000` -> Returns success message with table name.

If the testing results are satisfactory, commit and push your changes to the remote repository.   

## Learn More
- [sprintf()](https://www.php.net/manual/en/function.sprintf.php)
- [PDO::prepare()](https://www.php.net/manual/en/function.sprintf.php)
- [PDO::query()](https://www.php.net/manual/en/pdo.query.php)
- [PDO::exec()](https://www.php.net/manual/en/pdo.exec.php)
- [PDOStatement::fetch()](https://www.php.net/manual/en/pdostatement.fetch.php)
- [PDOStatement::execute()](https://www.php.net/manual/en/pdostatement.execute.php)

**[⬇SOURCE CODE: Chapter 14](#)**  

<table align="center">
  <td>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <a href="13-migration.md"> ◄ Previous: 13. Migration - Database Schema Operation </a>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <a href="15-seeder.md"> Next: 15. Seeder - Insert Sample Data ► </a>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
  </td>
</table>
