<h1 align="center">HTTP Request Handler</h1>

<table align="center" border="0">
  <td>

  ## Topics  

  01.&nbsp;&nbsp;[Source Code Files of this Chapter](#source-code-files-of-this-chapter)   
  02.&nbsp;&nbsp;[Request Property in Object Oriented Way](#request-property-in-object-oriented-way)   
  03.&nbsp;&nbsp;[Create Request Class](#create-request-class)  
  04.&nbsp;&nbsp;[Inject the Request Object in the Application](#inject-the-request-object-in-the-application)  
  05.&nbsp;&nbsp;[Result](#result)  
  06.&nbsp;&nbsp;[Learn More](#learn-more)  
  </td>
</table>

## Source Code Files of this Chapter
```
project-root
└── app
    ├── Controllers
    │   └── HomeController.php              # modified file
    └── Core
        ├── App.php                         # modified file
        ├── Bootstrap.php                   # modified file
        └── Request.php                     # new file

```

## Request Property in Object Oriented Way
We have been using the magic variable `$_SERVER` to detect server requests for a long time. It identifies the request path of the request and supplies content accordingly; it works like a procedural system. Now we will create an object oriented `Request` class that will be responsible for handling all request related tasks.

## Create Request Class

Create a new file `Request.php` on the `app/core/` directory:
```php

<?php
declare(strict_types = 1);

namespace App\Core;

class Request
{
    protected array $data = [];

    public function __construct()
    {
        $body       = file_get_contents('php://input');
        $json       = json_decode($body, true);
        $this->data = array_merge($_GET, $_POST, is_array($json) ? $json : []);
    }

    public function method()
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    public function uri()
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $uri = strtok($uri, '?');
        $uri = rtrim($uri, '/') ?: '/';
        
        return $uri;
    }

    public function input(string $key, $default = null)
    {
        return $this->data[$key] ?? $default;
    }

    public function all()
    {
        return $this->data;
    }
}

```

**Code Explain**
> ```php
> public function __construct()
> {
>     $body       = file_get_contents('php://input');
>     $json       = json_decode($body, true);
>     $this->data = array_merge($_GET, $_POST, is_array($json) ? $json : []);
> }
> ```
>In the constructor of the Request class, we capture raw HTTP request body and also store data from the `$_GET` and `$_POST` variables. To access all types of request input like query string, form data, and API JSON payloads we merge and store all data in the `$this->data` variable. 
>
> `method()` will return HTTP method.
>
> `uri()` will return requested URL path.
>
> `input()` will use to retrive  a specific input data
>
> `all(`) will returns all request data.


## Inject the Request Object in the Application
We will use `app/Core/Bootstrap.php` file to inject Request Object in our framework. Open the `app/Core/Bootstrap.php` file and add following code:     

```php
use App\Core\Request;

$request  = new Request();

$app = new App($request);

return $app;

```
**Code Explain**
> Create `Request` object to capture the current HTTP request, and then pass to the App (app/Core/App.php) so it can be used throughout the application components. 

<details>
<summary>See full code: <code>app/Core/Bootstrap.php</code></summary>

```php
<?php
declare(strict_types = 1);

use App\Core\Env;
use App\Core\Config;
use App\Core\Request;
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
$app = new App($request);

return $app;

```
</details>

Since the `Bootstrap` will send the Request object to the App (`app/Core/App.php`), the App class will use a Class constructor to capture and use the data. Open the `app/Core/App.php` file and update as shown below:

```php
private Request $request;

public function __construct(Request $request)
{
    $this->request = $request;
}
```
**Code Explain**
> Receive the `Request` object in the class constructor.

```php
$uri = $this->request->uri();

if ($uri == "/") {
    $homeController = new HomeController();
    echo $homeController->getGreeting($this->request);
}
```
**Code Explain**
> In the `Run()` method, we use the Request object and based on the request uri pass the Request object to the HomeController.

<details>
<summary>See full code: <code>app/Core/App.php</code></summary>

```php
<?php

declare(strict_types = 1);

namespace App\Core;

use App\Controllers\HomeController;

class App
{
    private Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function run()
    {
        // route define
        $method = $this->request->method();
        $uri    = $this->request->uri();

        if ($uri == "/") {
            $homeController = new HomeController();
            echo $homeController->getGreeting($this->request);
        }
        
        if ($uri == "/get-error") {
            // trigger a fatal error
            trigger_error("An Intentional Error Occurred.", E_USER_ERROR);
        }

        if ($uri == '/get-exception') {
            // throw an exception
            throw new \Exception("An Intentional Exception Occurred.");
        }
    }
}

```
</details>

Request object is now available in the Controller (app/Controllers/HomeController.php). We can use it for a simple example.

```php
    public function getGreeting($request)
    {
        echo '<strong>Route Method:</strong> ' . $request->method() . '<br>';
        echo '&nbsp;&nbsp;&nbsp;&nbsp;<em>For POST method you have to <a href="18-form-security-and-validation.md">Submit Form</a> or use <a href="18-form-security-and-validation.md">API client</a>, Later we will implement this respectively on Chapter <a href="11-response-api-json.md">11. Response API - JSON</a> and Chapter <a href="18-form-security-and-validation.md">18. Form - Security and Validation</a>.</em>' . '<br><br>';
        echo '<strong>Route URI:</strong> ' . $request->uri() . '<br><br>';
        echo '<strong>Route Query String:</strong> <br>' . str_replace('&', '<br>', http_build_query($request->all())) . '<br><br>';
        
        return 'Welcome to the ' . config('app.name');
    }
```

**Code Explain**
> Display Request method, uri and Query String (if available). 

<details>
<summary>See full code: <code>app/Controllers/HomeController.php</code></summary>

```php
<?php
declare(strict_types=1);

namespace App\Controllers;

class HomeController
{
    public function getGreeting($request)
    {
        echo '<strong>Route Method:</strong> ' . $request->method() . '<br>';
        echo '&nbsp;&nbsp;&nbsp;&nbsp;<em>For POST method you have to <a href="18-form-security-and-validation.md">Submit Form</a> or use <a href="18-form-security-and-validation.md">API client</a>, Later we will implement this respectively on Chapter <a href="11-response-api-json.md">11. Response API - JSON</a> and Chapter <a href="18-form-security-and-validation.md">18. Form - Security and Validation</a>.</em>' . '<br><br>';
        echo '<strong>Route URI:</strong> ' . $request->uri() . '<br><br>';
        echo '<strong>Route Query String:</strong> <br>' . str_replace('&', '<br>', http_build_query($request->all())) . '<br><br>';
        
        return 'Welcome to the ' . config('app.name');
    }
}

```
</details>

## Result

Make sure server is running or go to the project-root directory and run command `php -S localhost:8000 -t public/` on the terminal.   
**Check the new feature:**
- Browse: `http://localhost:8000?name=jhon&email=test@example.com` -> Check query string.    

**Regression Test**
- Browse: `http://localhost:8000` -> Returns a welcome greeting message.
- Browse: `http://localhost:8000/get-error` -> Returns error information.
- Browse: `http://localhost:8000/get-exception` -> Returns exception information.

If the testing results are satisfactory, commit and push your changes to the remote repository.    

## Learn More
- [$_SERVER](https://www.php.net/manual/en/reserved.variables.server.php),[$_REQUEST](https://www.php.net/manual/en/reserved.variables.request.php)
- [php://input](https://www.php.net/manual/en/wrappers.php.php#wrappers.php.input)
- [file_get_contents](https://www.php.net/manual/en/function.file-get-contents.php), [request_parse_body](https://www.php.net/manual/en/function.request-parse-body.php)
- [http_build_query](https://reintech.io/blog/mastering-php-http-build-query-function)

**[⬇SOURCE CODE: Chapter 08](#)**

<table align="center">
  <td>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <a href="07-env-config.md"> ◄ Previous: 07. Environment and Configuration Variable </a>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <a href="09-route.md"> Next: 09. Route - Mapping Request with Action ► </a>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
  </td>
</table>
