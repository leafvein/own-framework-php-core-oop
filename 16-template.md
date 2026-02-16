<h1 align="center">Template : Frontend Structure and Resources</h1>

<table align="center" border="0">
  <td>

  ## Topics  

  01.&nbsp;&nbsp;[Source Code Files of this Chapter](#source-code-files-of-this-chapter)   
  02.&nbsp;&nbsp;[Integrate Frontend Resources](#integrate-frontend-resources)   
  03.&nbsp;&nbsp;[Define `assets()` Helper Function](#define-assets-helper-function)  
  04.&nbsp;&nbsp;[Create `style.css` File](#create-stylecss-file)  
  05.&nbsp;&nbsp;[Create `script.js` File](#create-scriptjs-file)  
  06.&nbsp;&nbsp;[Add Image File](#add-image-file)  
  07.&nbsp;&nbsp;[Use `assets()` to the Template](#use-assets-to-the-template)  
  08.&nbsp;&nbsp;[Result](#result)  
  09.&nbsp;&nbsp;[Learn More](#learn-more)    
  </td>
</table>

## Source Code Files of this Chapter
```
project-root/
├── app/
│   └── Helpers/
│       └── helpers.php                         # modified file
├── public/
│   ├── css/
│   │   └── style.css                           # new file
│   └── scripts/
│       └── app.js                              # new file
└── views/
    ├── home.php                                # modified file 
    └── layout.php                              # modified file
```

## Integrate Frontend Resources
Until now, our application has been displaying information using simple plain HTML without formatting and design. To make our application a little bit fancy, we will configure it to add css style, and image content. We will also configure an option to integrate javascript.

## Define `assets()` Helper Function
Our framework uses front-controller architecture. So, we cannot directly integrate resources in template files. Let's explain why.
In front-controller architecture, all requests go through a single entry point. This feature breaks relative path to file because routes/URLs cannot map to the file system. Like the relative path of this `<link rel="stylesheet" href="css/style.css">` HTML tag on this application depends on the current route or URL (e.g., `example.com/user/profile`), not the actual file system path. So, the link will break because browser doesn't know about our project's directory structure.    
To solve this issue, we will create a custom `asset()` helper function that loads assets from web root. The `asset()` function will ensure static files are always loaded from the correct absolute path. 

Create the `asset()` helper function in the `app/Helpers/helpers.php` file:

```php
if (!function_exists('asset')) {
    function asset(string $path)
    {
        return '/' . ltrim($path, '/');
    }
}
```
**Code Explain**
> `if (!function_exists('asset'))`: check to avoid multiple definition of a function. Helper functions are typically placed in simple PHP script files, not inside a class or namespace. If you write many helper functions in a single file, there is a possibility of redefining a function. In PHP, if you try to redefine a function that already exists, a fatal error will occur.  
>   
> `function asset(string $path)` : The function takes one string type argument.    
> 
> `return '/' . ltrim($path, '/');` : after removing any leading slashes (`/`) from $path, it appends a single slash to ensure that all asset URLs start from the web root (absolute path).    
> `
<details>
<summary>See full code: <code>app/Helpers/helpers.php</code></summary>

```php
<?php
declare(strict_types=1);

use App\Core\Config;

if (!function_exists('config')) {
    function config(string $key, mixed $default = null)
    {
        return Config::get($key, $default);
    }
}

if (!function_exists('asset')) {
    function asset(string $path)
    {
        return '/' . ltrim($path, '/');
    }
}

```
</details>


## Create `style.css` File
Create simple css `public/css/style.css` file:    
```css
body {
    width: 60%;
    margin: 0 auto;
}
```

## Create `script.js` File
Create simple css `public/scripts/app.js` file:

```js
console.log('Global JS loaded');
```


## Add Image File
Put the `welcome.png` image file in the `public/images/` directory.

##  Use `assets()` to the Template
Open the layout `views/layout.php` template file. Add CSS, and JavaScript files:  

```php
<link rel="stylesheet" href="<?= asset('css/style.css') ?>">

<script src="<?= asset('scripts/app.js') ?>"></script>
```

<details>
<summary>See full code: <code>views/layout.php</code></summary>

```php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?= $title ?? 'My OOP App' ?></title>
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
</head>
<body>
<header><h1>My OOP App</h1></header>
<main>
<?= $content ?>
</main>
<footer>Copyright &copy; <?= date('Y') ?></footer>
<script src="<?= asset('scripts/app.js') ?>"></script>
</body>
</html>

```
</details>

Add the banner image `images/welcome.png` to the `views/home.php` file:

```php
<img class="banner" src="<?= asset('images/welcome.png') ?>" alt="banner">
```

<details>
<summary>See full code: <code>views/home.php</code></summary>

```php
<img class="banner" src="<?= asset('images/welcome.png') ?>" alt="banner">
<p><?= htmlspecialchars($message) ?></p>
```
</details>

Apply CSS style for the banner image on `public/css/style.css` file:

```css
.banner {
    border: solid 1px #ccc;
}
```

<details>
<summary>See full code: <code>public/css/style.css</code></summary>

```css
body {
    width: 60%;
    margin: 0 auto;
}

.banner {
    border: solid 1px #ccc;
}

```
</details>

## Result

Make sure server is running or go to the project-root directory and run command `php -S localhost:8000 -t public server.php` on the terminal.  

- Browse: `http://localhost:8000` -> Page content will display on center of the page.
- Browse: `http://localhost:8000` -> Check the browser's console dev tool and confirm a javascript log message. 

If the testing results are satisfactory, commit and push your changes to the remote repository.   

## Learn More
- [function_exists()](https://www.php.net/manual/en/function.function-exists.php)
- [Google Chrome : Console](https://developer.chrome.com/docs/devtools/console)

**[⬇SOURCE CODE: Chapter 16](#)**    

<table align="center">
  <td>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <a href="15-seeder.md"> ◄ Previous: 15. Seeder - Insert Sample Data </a>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <a href="17-form.md"> Next: 17. Form - User Data Input ► </a>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
  </td>
</table>
