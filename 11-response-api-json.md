<h1 align="center">Response API : JSON</h1>

<table align="center" border="0">
  <td>

  ## Topics  

  01.&nbsp;&nbsp;[Source Code Files of this Chapter](#source-code-files-of-this-chapter)   
  02.&nbsp;&nbsp;[API Design](#api-design)   
  03.&nbsp;&nbsp;[Define API Response](#define-api-response)  
  04.&nbsp;&nbsp;[Register API Route](#register-api-route)  
  05.&nbsp;&nbsp;[Add API Controller Method](#add-api-controller-method)   
  06.&nbsp;&nbsp;[Result](#result)  
  07.&nbsp;&nbsp;[Learn More](#learn-more)  
  </td>
</table>

## Source Code Files of this Chapter
```
project-root/
├── app/
│   ├── Controllers/
│   │   └── Api/                                # new directory
│   │       └── UserController.php              # new file
│   └── Core/
│       ├── App.php                             # modified file
│       ├── Bootstrap.php                       # modified file
│       └── Response.php                        # modified file
└── routes/
    └── api.php                                 # new file
```

## API Design
In API design, a JSON response is not just a data format. Rather, it is a structured, standardized communication between the server and the API client. The required components of an API response are the status code, the Content-Type header, and the JSON data.

## Define API Response
Update the Response class `app/Core/Response.php` to add `json()` method, it will be responsible to provide API response.
```php
public static function json($data, int $status = 200)
{
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}
```

**Code Explain**
> Set the HTTP status code of the response.    
> Set a flag to the client to be prepare for receiving the upcoming JSON response data.    
> Converts array $data into a JSON string.    
> After sending the response, `exit` will confirm that the request has been completed.    

<details>
<summary>See full code: <code>app/Core/Response.php</code></summary>

```php
<?php
declare(strict_types = 1);

namespace App\Core;

class Response
{
    public static function view(string $view, array $data = [])
    {
        extract($data);
        $content = self::render($view, $data);
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

API design does not handle only success response but error response also. So we handle a simple error response from the App (`app/Core/App.php`):
```diff
if (!$action) {
      http_response_code(404);
+     if (strpos($uri, '/api/') === 0) {
+         header('Content-Type: application/json');
+         echo json_encode(['error' => 'Route not found']);
+     } else {
          echo '404 Not Found';
+     }
      return;
}

```
**Code Explain**
> When route map is empty
> The route is api route (request URI starts with `/api/`)   
> Set a flag to the client to be prepare for receiving the upcoming JSON response data.
> Send error message into a JSON string.
<details>
<summary>See full code: <code>app/Core/App.php</code></summary>

```php
<?php
declare(strict_types = 1);

namespace App\Core;

class App
{
    private Router $router;
    private Request $request;

    public function __construct(Router $router, Request $request)
    {
        $this->router  = $router;
        $this->request = $request;
    }

    public function run()
    {
        $method = $this->request->method();
        $uri    = $this->request->uri();

        $routes = $this->router->routes();

        $action = $routes[$method][$uri] ?? null;

        if (!$action) {
            http_response_code(404);
            if (strpos($uri, '/api/') === 0) {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Route not found']);
            } else {
                echo '404 Not Found';
            }
            return;
        }

        if (is_array($action)) {
            [$class, $method] = $action;
            $controller       = new $class();

            return $controller->$method($this->request);
        }

        return $action($this->request);
    }
}

```
</details>


## Register API Route
We will use separate route file for registering API route.  
Create `routes/api.php` file as shown below:
```php
<?php
use App\Controllers\Api\UserController;

$router->get('/api/users', [UserController::class, 'index']);

$router->post('/api/users', [UserController::class, 'store']);

```

**Code Explain**
> Mapping API route to associate method of the controller class

As like web route register file `routes/web.php` we will inject api route register file on Bootstrap (`app/Core/Bootstrap.php`).
Update the `app/Core/Bootstrap.php` file: 

```php
require __DIR__ . '/../../routes/api.php';
```
<details>
<summary>See full code: <code>app/Core/Bootstrap.php</code></summary>

```php
<?php
declare(strict_types = 1);

use App\Core\Env;
use App\Core\Config;
use App\Core\Request;
use App\Core\Router;
use App\Core\App;

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

$request  = new Request();
$router   = new Router($request);

$app = new App($router, $request);

require __DIR__ . '/../../routes/web.php';
require __DIR__ . '/../../routes/api.php';

return $app;

```
</details>

## Add API Controller Method

To implement response of the API route create `app/Controllers/Api/UserController.php` file. For demonstration purposes, we will use an in-memory array of users instead of a database (we will [configure Database on Chapter 12]()).
```php
<?php
declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Request;
use App\Core\Response;

class UserController
{
    private Request $request;
    private array $users;
    
    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->users = [
            [
                'id'        => 1,
                'username'  => 'jhondoe',
                'email'     => 'jhondoe@example.com',
                'firstName' => 'Jhon',
                'lastName'  => 'Doe',
            ],
            [
                'id'        => 2,
                'username'  => 'adamsmith',
                'email'     => 'adamsmith@example.com',
                'firstName' => 'Adam',
                'lastName'  => 'Smith',
            ],
        ];
    }

    public function index()
    {
        return Response::json($this->users);
    }

    public function store()
    {
        $userData = $this->request->all();

        $this->users[] = [
            'id' => count($this->users) + 1,
            ...$userData
        ];

        return Response::json($this->users, 201);
    }
}

```
**Code Explain**
> `__construct()` creating users array for demonstration, because our database connection is not ready yet.     
> `index()` will return `users` data as a JSON response using our newly created `json()` method.    
> `store()` will just append a new `user` entry to the users array.    

<details>
<summary>See full code: <code>app/Controllers/Api/UserController.php</code></summary>

```php
<?php
declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Request;
use App\Core\Response;

class UserController
{
    private Request $request;
    private array $users;
    
    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->users = [
            [
                'id'        => 1,
                'username'  => 'jhondoe',
                'email'     => 'jhondoe@example.com',
                'firstName' => 'Jhon',
                'lastName'  => 'Doe',
            ],
            [
                'id'        => 2,
                'username'  => 'adamsmith',
                'email'     => 'adamsmith@example.com',
                'firstName' => 'Adam',
                'lastName'  => 'Smith',
            ],
        ];
    }

    public function index()
    {
        return Response::json($this->users);
    }

    public function store()
    {
        $userData = $this->request->all();

        $this->users[] = [
            'id' => count($this->users) + 1,
            ...$userData
        ];

        return Response::json($this->users, 201);
    }
}

```
</details>

## Result

Make sure server is running or go to the project-root directory and run command `php -S localhost:8000 -t public/` on the terminal.    
**Check the new feature:**
- Open an API client like `Postman`, `REST Client` (VS Code) or browser tools like `Requestly`, `API Tester`.
- Send a `GET` API request to `http://localhost:8000/api/users` -> return a JSON response containing the users data..
- Send a `POST` API request with new user info to `http://localhost:8000/api/users` -> return a JSON response containing the users data including the newly added user.
**Negative Testing or Route Fallback Test
- Send a `GET` API request to `http://localhost:8000/api/users-no-route` -> verify that the API fallback route return a JSON error response

If the testing results are satisfactory, commit and push your changes to the remote repository.   

## Learn More
- [REST APIs](https://aws.amazon.com/what-is/api/#what-are-rest-apis--1lv9tsd)    
- [json_encode](php.net/manual/en/function.json-encode.php), [json_decode](https://www.php.net/manual/en/function.json-decode.php)    
- [HTTP headers](https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Headers)
- [HTTP response status codes](https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Status)    

**[⬇SOURCE CODE: Chapter 11](#)**

<table align="center">
  <td>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <a href="10-response-view-template.md"> ◄ Previous: 10. Response View - Template </a>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <a href="12-database.md"> Next: 12. Database Connection ► </a>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
  </td>
</table>
