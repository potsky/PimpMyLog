---
layout: default
title: Software format
---

# 1 - Introduction

You can add new softwares in *Pimp My Log* for all *Pimp My Log* users.

The first default included software is *apache*.

When we will add some softwares, users will have to choose which softwares they want during the configuration part at the first launch.

# 2 - Configuration process

At first launch, *Pimp My Log* will launch the configurator and will :

- check if there is several softwares to propose to the user. If there is only *apache*, the next step will be skipped. The list is defined in `cfg/softwares.inc.php` and then in your custom file `cfg/softwares.inc.user.php`.
- user choose which software he wants
- for each wanted software, *Pimp My Log*:
    - searches for log files in all pre-defined files in all pre-defined paths. Pre-defined files and paths are defined in file `cfg/SOFTWARE.paths.php`.
    - proposes found files to the user
    - lets user fill custom paths
    - check if user custom paths are reachable
- when all softwares are parsed, *Pimp My Log* will generate the configuration file:
    - with the default skeleton `cfg/pimpmylog.config.json`
    - with all files but for each file, *Pimp My Log* will try to guess the structure of a line by delegating the check to all `cfg/SOFTWARE.config.php` files.

# 3 - Build a configurator

## 3.1 - Write your configuration file

Before all, you should manually create your configuration file to support the new software.

Once you have written the RegEx, `match` and `types` arrays, save your configuration file somewhere on your hard-drive, we will need it later. Read the documentation menu and all other developer menus to do this.

## 3.2 - Enhance your software list

To add a new software, create your own enhanced software list. It will define your new software. Don't modify directly 
`cfg/softwares.inc.php`, you will not be able to update *Pimp My Log* during you development phase and you could lose your work!

Copy `cfg/softwares.inc.php` to `cfg/softwares.inc.user.php`.

> **Note**  
>
> File `cfg/softwares.inc.user.php` is not overwritten while updating *Pimp My Log* via a `git pull`.

<!-- -->

Edit `cfg/softwares.inc.user.php` :

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

Change wanted values:

- `my_software` is the software *ID* like *nginx* or *apache24* if there is a specific configuration for *apache 2.4*,...
- `name` is the common name of the software
- `desc` is a short description to let users understand what is this software
- `home` is the link of the homepage of the software to give users more informations
- `notes` is your personal notes about your software support in *Pimp My Log* (what is provided, covered,...)
- `load` is a boolean which tells to *Pimp My Log* if this software should be checked by default when the software list is provided to users

Now, if you remove your configuration file `config.user.json` to start a new *Pimp My Log* instance, *Pimp My Log* will ask you which softwares to install. Nice!

## 3.3 - Add your software common paths

Now that you have declared a new software, you have to explain to *Pimp My Log* where it can check for new log files for your software.

Theses paths have to be declared in a file named `cfg/ID.paths.user.php` where ID is the value of `my_software` in the paragraph above.

> **Note**  
>
> File `ID.paths.user.php` is not overwritten while updating *Pimp My Log* via a `git pull`.

<!-- -->

Take this example:

```php
<?php

$paths = array(
    '/var/log/',
    '/var/log/apache/',
    'C:/wamp/logs/',
);

$files = array(
    'error' => array(
        'error.log',
        'error_log',
    ),
    'access' => array(
        'access.log',
        'access_log',
    ),
);
?>
```

You must define both arrays:

- `$paths` is a flat array which defines all possible paths where log files can be stored in all major linux distributions, Windows versions, MAC OS X versions, ...
- `$files` is an array of `file` arrays. Each file defines a type of log. Indeed a software can have several types of log files. So for each type, you define an array which defines itself all possible file names in all major linux distributions, Windows versions, MAC OS X versions, ...

In the example above, *Pimp My Log* will check for 2 types of log files :

- for *error* log files in the following locations:
    - `/var/log/error.log`
    - `/var/log/apache/error.log`
    - `C:/wamp/logs/error.log`
    - `/var/log/error_log`
    - `/var/log/apache/error_log`
    - `C:/wamp/logs/error_log`
- for *access* log files in the following locations:
    - `/var/log/access.log`
    - `/var/log/apache/access.log`
    - `C:/wamp/logs/access.log`
    - `/var/log/access_log`
    - `/var/log/apache/access_log`
    - `C:/wamp/logs/access_log`

> **Note**  
> Even if your software just uses a single type of log file, `$files` has to be an array of array.

<!-- -->

## 3.4 - Add your software configurator

Finally, you have to create a function which will return the JSON configuration part for a single log file.

This generator function has to be created in a file name named `cfg/ID.config.user.php`.

This function has to be named `ID_get_config( $type , $file , $software , $counter )`

With the same example:

{% highlight php linenos %}
<?php
function ID_get_config( $type , $file , $software , $counter ) {
    if ( $type == 'error' ) {
        return<<<EOF
            "$software$counter": {
                "display" : "Apache Error #$counter",
                "path"    : "$file",
                ...
                "format"  : {
                    "regex": "|^\\\\[(.*) (.*) (.*) (.*):(.*):(.*)\\\\.(.*) (.*)\\\\] \\\\[(.*):(.*)\\\\] \\\\[pid (.*)\\\\] .*\\\\[client (.*):(.*)\\\\] (.*)(, referer: (.*))*\$|U",
                    "match": { ... },
                    "types": { ... },
                    "exclude": { ... }
                }
            }
EOF;
    }
    else if ( $type == 'access' ) {
        return ...;
    }
?>
{% endhighlight %}

Here are some explanations:

- *line 3* : `$type` if the type of log defined in your `ID.paths.user.php` file.
- *line 5* : `$software$counter` is the concatenation of the software name and a software counter during the configuration process. Defined like this, this *ID* is unique and **must** be unique.
- *line 6* : `Apache Error #$counter` is the displayed name for final users. `$counter` is added to let users understand which file they are displaying in *Pimp My Log*.


# 4 - Delivery

You can make a [GitHub Pull Request](pullrequest) on the *dev* branch only or send us your files via a discussion in the [support](http://pimpmylog.com) website.

Then we will:

- include your `cfg/softwares.inc.user.php` to the original `cfg/softwares.inc.php` file
- copy your `cfg/ID.paths.user.php` file to `cfg/ID.paths.php`
- copy your `cfg/ID.config.user.php` file to `cfg/ID.config.php`
- refresh translation files and prepare `.po` templates
- try it!




