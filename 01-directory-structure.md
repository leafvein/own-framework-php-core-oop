<h1 align="center">Directory Structure of the Application</h2>

<table align="center" border="0">
  <td>

  ## Topics  

  01.&nbsp;&nbsp;[Source Code Files of this Chapter](#source-code-files-of-this-chapter)   
  02.&nbsp;&nbsp;[MVC-inspired Directory Structure](#mvc-inspired-directory-structure)     
  03.&nbsp;&nbsp;[Init Coding](#init-coding)  
  </td>
</table>

## Source Code Files of this Chapter
```
project-root/
│── public/
│
└── app/
```

<details>
<summary>See the final project directory structure</summary>

```
project-root
├── app/
│   ├── Controllers/
│   │   ├── Api/
│   │   │   └── UserController.php
│   │   ├── AuthController.php
│   │   ├── EmailVerificationController.php
│   │   ├── HomeController.php
│   │   └── UserController.php
│   ├── Core/
│   │   ├── Abstract/
│   │   │   ├── Migration.php
│   │   │   ├── Model.php
│   │   │   └── Seeder.php
│   │   ├── App.php
│   │   ├── Bootstrap.php
│   │   ├── Config.php
│   │   ├── Database.php
│   │   ├── Env.php
│   │   ├── ExceptionHandler.php
│   │   ├── Mailer.php
│   │   ├── Request.php
│   │   ├── Response.php
│   │   ├── Router.php
│   │   └── Validator.php
│   ├── Helpers/
│   │   └── helpers.php
│   ├── Models/
│   │   ├── EmailQueue.php
│   │   ├── EmailVerification.php
│   │   └── User.php
│   ├── Queue/
│   │   └── MailQueue.php
│   └── Services/
│       └── EmailVerificationService.php
├── composer.json
├── composer.lock
├── config/
│   ├── app.php
│   ├── database.php
│   └── mail.php
├── database/
│   ├── Migrations/
│   │   ├── 2026_01_10_create_users_table.php
│   │   ├── 2026_01_18_create_email_queue_table.php
│   │   ├── 2026_01_18_create_email_verifications_table.php
│   │   ├── 2026_01_19_add_password_to_users_table.php
│   │   └── 2026_01_19_add_verified_column_to_users_table.php
│   └── Seeders/
│       └── UserSeeder.php
├── .env
├── .env.testing
├── .gitignore
├── migration.php
├── .phpunit.result.cache
├── phpunit.xml
├── public/
│   ├── adminer.php
│   ├── css/
│   │   └── style.css
│   ├── images/
│   │   └── welcome.png
│   ├── index.php
│   └── scripts/
│       └── app.js
├── queue.php
├── routes/
│   ├── api.php
│   └── web.php
├── seeder.php
├── server.php
├── tests/
│   ├── Bootstrap.php
│   ├── Database/
│   │   ├── DatabaseConnectionTest.php
│   │   ├── MigrationTest.php
│   │   ├── Seeders/
│   │   │   └── UserSeeder.php
│   │   ├── SeederTest.php
│   │   └── UserModelTest.php
│   ├── DatabaseTestCase.php
│   ├── Feature/
│   │   └── HomePageTest.php
│   └── Unit/
│       ├── ConfigTest.php
│       ├── EnvTest.php
│       ├── RequestTest.php
│       ├── RouterTest.php
│       └── ValidatorTest.php
├── vendor/
└── views/
    ├── auth/
    │   └── login.php
    ├── home.php
    ├── layout.php
    └── user/
        ├── edit.php
        ├── register.php
        └── show.php

```
</details>    
<br>

The directory structure is the skeleton of the project. So the more standardized and understandable it can be,  the project will be in terms of extensible, maintainable, and testable.

## MVC-inspired Directory Structure  

On this project, the directory structure is flexible and conventional. Which means you can create it the way you want. But since the PHP community has been using different directory structures in web development for a long time, some conventions have become well-known regarding the names and patterns of files and directories. For example, `src`, `config`, `.env`, `public`, `index.php` etc. 

Our project will be like MVC and we will use `PSR-4` through Composer for class autoloader. 

## Init Coding
So, let’s start to build the `project-root` directory first, then create `public` and `app` directory inside the `project-root` directory.
```
> mkdir <PROJECT_NAME>
> cd <PROJECT_NAME>
> mkdir public app
```

> `<PROJECT_NAME>`: your project name, it will be the `project-root` directory.
> 
> `public`: the default accessible file of the project
> 
> `app`: all project related class will go here

**[⬇SOURCE CODE: Chapter 01](source_code/01)**

<table align="center">
  <td>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <a href="README.md"> ◄ Previous: Introduction </a>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <a href="02-front-controller.md"> Next: 02. Front Controller `index.php` File ► </a>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
  </td>
</table>
