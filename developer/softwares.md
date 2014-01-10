---
layout: default
title: Software format
---

# Introduction

You can add new softwares in *Pimp My Log* for all *Pimp My Log* users.

The first default included software is *apache*.

When we will add some softwares, users will have to choose which softwares they want during the configuration part at the first launch.

# Configuration process

At first launch, *Pimp My Log* will launch the configurator and will :

- check if there is several softwares to propose to the user. If there is only *apache*, the next step will be skipped. The list is defined in `cfg/softwares.inc.php` and then in your custom file `cfg/softwares.user.inc.php`.
- user choose which software he wants
- for each wanted software, *Pimp My Log*:
    - searches for log files in all pre-defined files in all pre-defined paths. Pre-defined files and paths are defined in file `cfg/SOFTWARE.paths.php`.
    - proposes found files to the user
    - lets user fill custom paths
    - check if user custom paths are reachable
- when all softwares are parsed, *Pimp My Log* will generate the configuration file:
    - with the default skeleton `cfg/pimpmylog.config.json`
    - with all files but for each file, *Pimp My Log* will try to guess the structure of a line by delegating the check to all `cfg/SOFTWARE.config.php` files.

# Build a configurator

## Write your configuration file

Before all, you should manually create your configuration file to support the new software.

Once you have written the RegEx, `match` and `types` arrays, save your configuration file somewhere on your hard-drive, we will need it later. Read the documentation menu and all other developer menus to do this.

## Enhance your software list

To add a new software, create your own enhanced software list. It will define your new software. Don't modify directly 
`cfg/softwares.inc.php`, you will not be able to update *Pimp My Log* during you development phase and you could lose your work!

Copy `cfg/softwares.inc.php` to `cfg/softwares.user.inc.php`.

> **Note**  
>
> File `cfg/softwares.user.inc.php` is not overwritten while updating pml via a `git pull`.

<!-- -->

Edit `cfg/softwares.user.inc.php` :

- remove all uncomment lines
- uncomment comments
- remove all non PHP lines

You should have something like this:

```php
<?php
$softwares_all[ 'my_software' ] = array(
    'name'  => __('My Super Software'),
    'desc'  => __('My Super Software build with love for you users which are installing Pimp my Log !'),
    'home'  => __('http://www.example.com'),
    'notes' => __('All versions 2.x are supported but 1.x too in fact.'),
    'load'  => true,
);
?>
```

Now, if you remove your configuration file `config.user.json` to start a new *Pimp My Log* instance, *Pimp My Log* will ask you which softwares to install. Nice!

## Add your software common paths

## Add your software configurator

# Delivery
