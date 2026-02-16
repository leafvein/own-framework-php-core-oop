<h1 align="center">Create your own framework using PHP core features</h1>

<p align="center">
  <a href="#"><img src="https://img.shields.io/badge/%E2%AC%87_DOWNLOAD-SOURCE_CODE-blue" alt="Download source code"></a> &nbsp;&nbsp; 
  <img src="https://img.shields.io/badge/core-8-787CB5?logo=php&logoColor=ffffff" alt="PHP Core 8"> &nbsp;&nbsp; 
  <img src="https://img.shields.io/badge/tests-phpunit-ffff00?logo=cachet&logoColor=ffff00" alt="PHPUnit Test Available"> &nbsp;&nbsp; 
</p>

<table align="center" border="0">
  <td>

  ## Table of Contents    

  [Introduction](#introduction)  
  01.&nbsp;&nbsp;[Directory Structure of Application](01-directory-structure.md)   
  02.&nbsp;&nbsp;[Front Controller `index.php` File](02-front-controller.md)   
  03.&nbsp;&nbsp;[Bootstrap](03-bootstrap.md)  
  04.&nbsp;&nbsp;[Git](04-git.md)  
  05.&nbsp;&nbsp;[Composer : Autoload Dependency](05-composer.md)  
  06.&nbsp;&nbsp;[Error and Exception Handler](06-error-exception-handler.md)  
  07.&nbsp;&nbsp;[Environment and Configuration Variable](07-env-config.md)  
  08.&nbsp;&nbsp;[HTTP Request Handler](08-http-request.md)  
  09.&nbsp;&nbsp;[Route : Mapping Request with Action](09-route.md)  
  10.&nbsp;&nbsp;[Response View : Template](10-response-view-template.md)  
  11.&nbsp;&nbsp;[Response API : JSON](11-response-api-json.md)  
  <hr width="400">  

  12.&nbsp;&nbsp;[Database Connection](12-database.md)  
  13.&nbsp;&nbsp;[Migration : Database Schema Operation](13-migration.md)  
  14.&nbsp;&nbsp;[Model : Database Table Representer](14-model.md)  
  15.&nbsp;&nbsp;[Seeder : Insert Sample Data](15-seeder.md)  
  <hr width="400">    

  16.&nbsp;&nbsp;[Template : Frontend Structure and Resources](16-template.md)  
  17.&nbsp;&nbsp;[Form : User Data Input](17-form.md)  
  <hr width="400">  

  18.&nbsp;&nbsp;[Form : Security and Validation](18-form-security-and-validation.md)  
  19.&nbsp;&nbsp;[User Authentication](19-auth.md)  
  20.&nbsp;&nbsp;[Mail : Verify User Email](20-user-verification.md)  
  21.&nbsp;&nbsp;[Queue (Asynchronous) Process : User Verification Email](21-queue-verify-email.md)  
  <hr width="400">  

  22.&nbsp;&nbsp;[PHPUnit Test](22-phpunit-test.md)  
  </td>
</table>

## Introduction

Over the time, PHP and its various tools have become more advanced and easier to use. As a result, you can create your own framework even using core PHP. Although there are many reputable, well-known, secure, updated and well-documented open source PHP frameworks that can be easily installed and used with Git or Composer commands. But as a PHP learner, if you want to understand how things like function classes, namespace objects, and so on work together within a framework, there is no substitute for hands-on learning. 

So let's start by building a simple framework from scratch.

Please keep in mind that we are not inventing anything new. We will just use various functions and features of PHP.

**Disclaimer:**
> - This framework is for learning purposes only. It is not intended for professional use or use on production server.
>
> - This tutorial is not for novice in PHP. You should have at least an understanding of object-oriented programming concepts. It is also recommended to have a grasp of MVC and Solid Design Patterns.
>
> - Highly recommend to get clear knowledge about git and composer.

<table align="center">
  <td>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <a href="01-directory-structure.md"> Next: 01. Directory Structure of Application ► </a>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
  </td>
</table>
