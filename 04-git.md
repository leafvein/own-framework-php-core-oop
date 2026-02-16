<h1 align="center">Git</h1>

<table align="center" border="0">
  <td>

  ## Topics  

  01.&nbsp;&nbsp;[Source Code Files of this Chapter](#source-code-files-of-this-chapter)   
  02.&nbsp;&nbsp;[Using Git](#using-git)   
  03.&nbsp;&nbsp;[Configure Git on Local](#configure-git-on-local)  
  04.&nbsp;&nbsp;[Configure Git on Remote](#configure-git-on-remote)  
  05.&nbsp;&nbsp;[Push Changes to GitHub](#push-changes-to-github)  
  06.&nbsp;&nbsp;[Result](#result)  
  07.&nbsp;&nbsp;[Learn More](#learn-more)  
  </td>
</table>

## Source Code Files of this Chapter
```
project-root/
├── app/
│   └── Core/
│       └── Bootstrap.php
├── .git/                                   # new directory
└── public/
    └── index.php
```

## Using Git
We are in the very initial stages of a framework creation. Now it is time to configure git.

You are creating a framework, right! So Git will be a handful tool for collaborating with colleagues, teams, or friends, or for sharing code by embedding it in blog posts.

Do you have any account of git cloud repository hosting like [GitHub](https://github.com/) or any other? If not, just create an account and it is free.

## Configure Git on Local
```
# Go to the project root
> cd <project_root> 

> git init

> git add .

> git commit -m "Initial commit"
```

## Configure Git on Remote
> [Create a new repository on the github and configure](https://docs.github.com/en/repositories/creating-and-managing-repositories/creating-a-new-repository)
> 
> Copy the remote repository url

## Push Changes to GitHub
To make a connection between your local and remote repository first of all you have to set the remote origin. So, set the remote repository url as a remote origin and push changes.

```
# Go to the project root
> git remote add origin <remote_repository_URL>
>
> git push -u origin <branch_name>
```

### ⚠️ Important
> Github uses SSH Key to establish a connection between your local machine and `github.com`. You have to [generate SSH key to your local machine](https://docs.github.com/en/authentication/connecting-to-github-with-ssh/generating-a-new-ssh-key-and-adding-it-to-the-ssh-agent) if it is not exist and [add to your github account](https://docs.github.com/en/authentication/connecting-to-github-with-ssh/adding-a-new-ssh-key-to-your-github-account). 


## Result

> Go to your remote repository and you will see your source code is uploaded.

## Learn More
- [Install Git](https://git-scm.com/book/en/v2/Getting-Started-Installing-Git)
- [git add](https://github.com/git-guides/git-add)
- [git commit](https://www.w3schools.com/git/git_commit.asp)
- [git branch](https://www.codecademy.com/learn/fscp-git-and-github-part-ii/modules/fscp-git-branching/cheatsheet)

**[⬇SOURCE CODE: Chapter 04](#)**

<table align="center">
  <td>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <a href="03-bootstrap.md"> ◄ Previous: 03. Bootstrap </a>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <a href="05-composer.md"> Next: 05. Composer - Autoload Dependency ► </a>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
  </td>
</table>
