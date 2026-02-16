<h1 align="center">Seeder : Insert Sample Data</h1>

<table align="center" border="0">
  <td>

  ## Topics  

  01.&nbsp;&nbsp;[Source Code Files of this Chapter](#source-code-files-of-this-chapter)   
  02.&nbsp;&nbsp;[Database seeding](#database-seeding)   
  03.&nbsp;&nbsp;[Create Abstract Seeder Class](#create-abstract-seeder-class)  
  04.&nbsp;&nbsp;[Create Seeder File](#create-seeder-file)  
  05.&nbsp;&nbsp;[Command: Seeder Script Runner](#command-seeder-script-runner)  
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
│           └── Seeder.php                      # new file
├── database/
│   └── Seeders/
│       └── UserSeeder.php                      # new file
└── seeder.php                                  # new file
```

## Database Seeding
Database seeder is class used to populate the database by initial sample bulk data automatically.

## Create Abstract Seeder Class
Create a abstract seeder class `app/Core/Abstract/Seeder.php` to define a common interface for all seeders:

```php
<?php
declare(strict_types=1);

namespace App\Core\Abstract;

abstract class Seeder
{
    abstract public function run(): void;
}

```

**Code Explain**
> Every seeder must have data insertion query in the `run()` method

## Create Seeder File

Let's create a simple concrete database seeder `database/Seeders/UserSeeder.php` file to populate `users` table.

```php
<?php
declare(strict_types=1);

namespace Database\Seeders;

use App\Core\Abstract\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::truncate();

        User::create([
            'name'     => 'John Doe',
            'email'    => 'john@example.com',
        ]);

        User::create([
            'name'     => 'Jane Doe',
            'email'    => 'jane@example.com',
        ]);
    }
}

```

**Code Explain**
> ```php
> User::truncate();
> ```
> remove all existing user
> 
> ```php
> User::create([
>     'name'     => 'John Doe',
>     'email'    => 'john@example.com',
> ]);
>
> User::create([
>     'name'     => 'Jane Doe',
>     'email'    => 'jane@example.com',
> ]);
> ```
> Create two new users in `users` table.

## Command: Seeder Script Runner
To execute seeder file, create command-line database seeder runner `seeder.php` on the project-root directory:

```php
<?php
declare(strict_types=1);

/**
 * Seeder Runner
 *
 * Usage command: `php seeder.php seed`
 */

require __DIR__ . '/app/Core/Bootstrap.php';

$command = $argv[1] ?? null;

if ($command !== 'seed') {
    echo "Usage:\n";
    echo "  php seeder.php seed\n";
    exit(1);
}

// load all seeder files
$seederPath = __DIR__ . '/database/Seeders/*.php';

foreach (glob($seederPath) as $file) {
    require_once $file;
}

// process each seeder files
foreach (get_declared_classes() as $class) {
    if (is_subclass_of($class, App\Core\Abstract\Seeder::class)) {
        echo "Seeding: {$class}\n";
        (new $class())->run();
    }
}

echo "Seeding completed.\n";

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
> if ($command !== 'seed') {
>     echo "Usage:\n";
>     echo "  php seeder.php seed\n";
>     exit(1);
> }
> ```
> If argument is not `seed` then display help instruction and exits.
>
> ```php
> $seederPath = __DIR__ . '/database/Seeders/*.php';   
> 
> foreach (glob($seederPath) as $file) {
>     require_once $file;
> }
> ```
> Load all `.php` (seeders) files of `database/Seeders` directory into memory.
> 
> ```php
> foreach (get_declared_classes() as $class) {
>     if (is_subclass_of($class, App\Core\Abstract\Seeder::class)) {
>         echo "Seeding: {$class}\n";
>        (new $class())->run();
>     }
> }
> 
> echo "Seeding completed.\n";
> ```
> get all loaded class, even loaded by Bootstrap are also.    
> then filter only seeder classes by checking whether a class extends the abstract Seeder base class or not.
> instantiate dynamically only the seeder class and execute by calling its run() method.
> At the end display success message.

## Result

Make sure server is running or go to the project-root directory and run command `php -S localhost:8000 -t public server.php` on the terminal.  
```
# go to project-root directory
cd <PROJECT-ROOT>

# execute seeder
php seeder.php seed
```
> Above command will create user of the seeder file to the `users` table.

- Browse: `http://localhost:8000/adminer.php` -> Login to Adminer and check the new created users in the `users` table.

If the testing results are satisfactory, commit and push your changes to the remote repository.   

## Learn More
- [get_declared_classes()](https://www.php.net/manual/en/function.get-declared-classes.php)  
- [is_subclass_of()](https://www.php.net/manual/en/function.is-subclass-of.php)  

**[⬇SOURCE CODE: Chapter 15](source_code/15)**    

<table align="center">
  <td>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <a href="14-model.md"> ◄ Previous: 14. Model - Database Table Representer </a>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <a href="16-template.md"> Next: 16. Template - Frontend Structure and Resources ► </a>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
  </td>
</table>
