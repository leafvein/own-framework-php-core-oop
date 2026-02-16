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
<summary>See final project directory structure</summary>

```
CODE!
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

**[⬇SOURCE CODE: Chapter 01](#)**

<table align="center">
  <td>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <a href="README.md"> ◄ Previous: Introduction </a>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <a href="02-front-controller.md"> Next: 02. Front Controller `index.php` File ► </a>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
  </td>
</table>
