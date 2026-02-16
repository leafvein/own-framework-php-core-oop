<h1 align="center">Route - Mapping Request with Action</h1>

<table align="center" border="0">
  <td>
  
  ## Topics  

  01.&nbsp;&nbsp;[Source Code Files of this Chapter](#source-code-files-of-this-chapter)  
  02.&nbsp;&nbsp;[Exploring the Router Concept](#exploring-the-router-concept)   
  03.&nbsp;&nbsp;[Route Definition Container : Create Router Class](#route-definition-container--create-router-class)  
  04.&nbsp;&nbsp;[Injecting Router Class to the Application](#injecting-router-class-to-the-Application)  
  05.&nbsp;&nbsp;[Create Route Register](#create-route-register)  
  06.&nbsp;&nbsp;[Injecting Router Class to the Application](#injecting-router-class-to-the-application)   
  07.&nbsp;&nbsp;[Make the Controller MVC and RESTful Standard](#make-the-controller-mvc-and-restful-standard)   
  08.&nbsp;&nbsp;[Result](#result)  
  09.&nbsp;&nbsp;[Learn More](#learn-more)  
  </td>
</table>

## Source Code Files of this Chapter
```
project-root/
├── app/
│   ├── Controllers/
│   │   └── HomeController.php              # modified file
│   └── Core/
│       ├── App.php                         # modified file
│       ├── Bootstrap.php                   # modified file
│       └── Router.php                      # new file
└── routes/
    └── web.php                             # new file
```

## Exploring the Router Concept
Currently our application directly receives requests and generates content based on the request URI. But in addition to the URI, a request has more properties such as the request method (GET, POST) and the request type (web, API). 

We want to update our framework so that it can generate content based on more properties of the request. We will implement this by using a Router class. A Router class helps the application to determine which action or controller method should be performed based on the properties of the request.

## Route Definition Container : Create Router Class
The Router class will store only route definitions in a instance property as a two dimensional associative array. The array key will be a combination of route method (’GET’, ‘POST’ etc) and route URI. Corresponding value of this array will be either a callable function or an array where the first element is a controller class and the second element is a method of that controller.

Let's create the Router class in the `app/Core/Router.php` file:
```php
<?php
declare(strict_types = 1);

namespace App\Core;

class Router
{
    protected Request $request;
    protected array $routes = [];

    public function __construct(Request $request)
    {
        $this->request  = $request;
    }

    public function get(string $path, callable|array $callback)
    {
        $this->routes['GET'][$path] = $callback;
    }

    public function post(string $path, callable|array $callback)
    {
        $this->routes['POST'][$path] = $callback;
    }

    public function routes()
    {
        return $this->routes;
    }
}

```
**Code Explain**
> ```php
> public function __construct(Request $request)
> {
>     $this->request = $request;
> }
> ```
> The constructor will inject `Request` class and store an instance of a Request class to the `$this->request` class property.
> `$request` (instance of a Request class) containing route method (GET, POST), URI like ‘/’, and Query parameters, body data, headers, etc.
>
> get() and post() method will register a route that HTTP Request method are GET and POST respectively.
>
> routes() method will return all registered route definitions.

## Injecting Router Class to the Application
We will use `app/Core/Bootstrap.php` file to inject Request Object in our framework. Open the `app/Core/Bootstrap.php` file and add following code:   
`app/Core/Bootstrap.php`

```php
use App\Core\Router;

$router   = new Router($request);

$app = new App($router, $request);
```
**Code Explain**
> Create `Request` object to define a route based on the current HTTP request, and then pass to the App (app/Core/App.php) so it can be used throughout the application components. 

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

require __DIR__ . '/../../routes/web.php';

$app = new App($router, $request);

return $app;

```
</details>

Since the `Bootstrap` will send the Router object to the App (`app/Core/App.php`), the App class will use Class constructor to capture and use the data. Open the `app/Core/App.php` file and update as shown below:
`app/Core/App.php`
```php
private Router $router;
private Request $request;

public function __construct(Router $router, Request $request)
{
    $this->router  = $router;
    $this->request = $request;
}
```
**Code Explain**
> Receive the `Router` object in the class constructor.

```php
$routes = $this->router->routes();

$action = $routes[$method][$uri] ?? null;

if (!$action) {
    http_response_code(404);
    echo '404 Not Found';
    
    return;
}

if (is_array($action)) {
    [$class, $method] = $action;
    $controller       = new $class();

    return $controller->$method($this->request);
}

return $action($this->request);

```
**Code Explain**
> In the `Run()` method now check 
> - Now check if the route registered or not in the $routes (definition) array.
> - If not available set response code 404 and return api or http 404 response. 
> - If available then get the callback (controller and controller action method).
> - Finally call the controller method to complete the request.
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
            echo '404 Not Found';

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

## Create Route Register
We have created a `Router` class to contain registered routes. Now we will register route by mapping the request method, uri to its associated controller method. 

Let's create the `routes/web.php`
```php
<?php
declare(strict_types=1);

use App\Controllers\HomeController;

$router->get('/', [HomeController::class, 'index']);
```
**Code Explain**
> Mapping Route to associate method of the controller class
> The Router class will be used by the `web.php` file to store the route definitions.

## Injecting Router Register to the Application
On Bootstrap (`app/Core/Bootstrap.php`) we will inject Router Register `routes/web.php` above the `App` (`App\Core\App`) initialization so route can be used throughout the application.
```php
require __DIR__ . '/../../routes/web.php';

$app = new App($router, $request);
```


## Make the Controller MVC and RESTful Standard

Open the `app/Controllers/HomeController.php` and update code as shown below:

```php
class HomeController
{
    public function index()
    {
        echo 'Welcome to the ' . config('app.name');
    }
}
```
**Code Explain**
> We renamed the controller method from getGreeting() to index() to follow MVC and RESTful standards, where index() represents the default action.
> Displaying a simple Welcome message

## Result

Make sure server is running or go to the project-root directory and run command `php -S localhost:8000 -t public/` on the terminal.    
- Browse: `http://localhost:8000` -> Returns a welcome greeting message.
- Browse: `http://localhost:8000/get-error` -> Returns error information.
- Browse: `http://localhost:8000/get-exception` -> Returns exception information.   

If the testing results are satisfactory, commit and push your changes to the remote repository. 

## Learn More
- [Controller Methods](https://laracasts.com/series/php-for-beginners/episodes/23)

**[⬇SOURCE CODE: Chapter 09](#)**

<table align="center">
  <td>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <a href="08-http-request.md"> ◄ Previous: 08. HTTP Request Handler </a>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <a href="10-response-view-template.md"> Next: 10. Response View - Template ► </a>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
  </td>
</table>
