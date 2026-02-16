<h1 align="center">Front Controller <code>index.php</code> File</h1>

<table align="center" border="0">
  <td>

  ## Topics  

  01.&nbsp;&nbsp;[Source Code Files of this Chapter](#source-code-files-of-this-chapter)   
  02.&nbsp;&nbsp;[Front Controller Design Pattern](#front-controller-design-pattern)   
  03.&nbsp;&nbsp;[Create Front Controller](#create-front-controller)  
  04.&nbsp;&nbsp;[Result](#result)  
  05.&nbsp;&nbsp;[Tip : Local Server Setup](#-tip--local-server-setup)  
  06.&nbsp;&nbsp;[Learn More](#learn-more)  
  </td>
</table>

## Source Code Files of this Chapter
```
project-root/
└── public/
   └──index.php                             # new file
```

## Front Controller Design Pattern
Front controller is a central (obviously a single) entry point to handle incoming client request.

To implement front controller design pattern in this project, our central entry point will be an `index.php` file. So let's create an `index.php` file in the public directory. We have chosen to create the `index.php` file in the `public` directory instead of the project root directory because we want to protect our project’s confidential and sensitive code from publicly access. This access restriction could be implemented by setting file permission, and off course we will do this but also separate public resources so that no one can access the rest of the project except the public resources.

## Create Front Controller

Now open the `public/index.php` file, write the following code.

```php
<?php
declare(strict_types = 1);

echo 'Welcome to the PHP core OOP Project!';
```

**Code Explain:**
> ```php
> declare(strict_types = 1)
> ```
>
> `declare(strict_types=1)` enforces strict type checking for function arguments and return values within a specific PHP file. Only exact type of the type declaration will be accepted, or a TypeError will be thrown. 
>
>Unfortunately, in a projects, `declare(strict_types=1)` cannot be set globally for the whole project It only works per file and should be placed at the top of every PHP file.

>```php
> echo 'Welcome to the PHP core OOP Project!':
>```
>
> Show this string text on the browser screen.

## Result

If the local server and domain hosting configured properly, then it is time to check the result of your first code. Let’s say your local domain is `http://localhost:8000`. So, browse this URL, expected result is `Welcome to the PHP core OOP Project!`. If there is an exception, look at the error code number and debug accordingly. And if necessary, you can view the server log.

### 💡 Tip : Local Server Setup
You can use any of LAMP, MAMP, WAMP or XAMPP Stack as a local server. I've used PHP's built in server to develop this project.   

Open terminal/shell then run commands:
```
> cd <project-root>
 
> php -S localhost:8000 -t public/
```
Now browse: `http://localhost:8000`

## Learn More  
- Page controller:   
While the Front Controller pattern provides a single, centralized entry point for all requests in an application, the Page Controller is an architectural approach where each individual page request has its own controller and this controller is primary handler for its request. It is responsible for handling the logic associated with that particular page or request.
Advantages of front controller against page controller

- In addition to the PHP application, the [front controller needs to be configured on the server end](https://dev.to/xxzeroxx/php-design-patterns-front-controller-jjo) as well.

**[⬇SOURCE CODE: Chapter 02](#)**

<table align="center">
  <td>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <a href="01-directory-structure.md"> ◄ Previous: 01. Directory Structure of Application </a>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <a href="03-bootstrap.md"> Next: 03. Bootstrap ► </a>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
  </td>
</table>
