---
layout: default
title: Home
---

# Getting started

## What is *Pimp my Log* ?

*Pimp my Log* is a web app written in *PHP* which displays server logs friendly. Formerly named *PHP Apache Log Viewer*, we renamed it because you can in fact display logs from *Apache*, but *nginx*, *Ruby on Rails*, *Tomcat*, *sshd*, ... too !

I am a *ssh/vi/tail/grep -f* guy but tailing log files is finished now. I want a web app tool to view my logs and I want it to be better, prettier and faster.

*Pimp my Log* is useful too for mutualized web servers. *Chrooting* *SSH* accesses and web servers is a really boring task. While you often just want to give a *SFTP* access to your developers for code and logs. But have you really try to code with logs only available via *SFTP* or via simple *HTTP* ? :-)

## Key features

###  Configuration

* Auto-configuration for web server access and error logs
* User-configuration and profiling in one PHP file
* App customization (wording and design) to embed it in your own products
* Easily extendable for any type of log. You can display what you want !

###  Usage

* Auto-refresh
* Count of lines to display
* Search in log file with plain text or with PHP PECL RegEx
* Desktop notification as soon as new logs are available

Coming soon in v0.3 :

* Column sorting
* Column selector when running
* Word colorizing in logs
* Log viewer Server / Agent for multiple servers (ideal for Round Robin servers without log sharing

## Screenshots

# Requirements

## Supported web servers

## Supported browsers

# Install

{% highlight php linenos %}
{% include file.php %}
{% endhighlight %}

# Configuration

# Usage

# Language

# Support

Do you have a question ? Do you have found a hugly bug ? Do you need more assistance ? Please read our support page at [support.pimpmylog.com](http://support.pimpmylog.com) provided gracefully by Tender !

# Contribute

*Pimp my Log* is open sourced and ready to grow. Do you want to contribute to add some amazing features, new supported web servers, ... ? Get the code on [GitHub](https://github.com/potsky/PimpMyLog) and make some pull requests !

# Licensing & Terms of use

This plugin is licensed under the GNU General Public License and is copyright 2012 potsky.

# Special thanks

A special thanks goes out to :

* [ENTP](http://entp.com/) for their amazing web support tool app [Tender](http://tenderapp.com/)
* [GitHub](http://github.com) for code hosting
* [Jekyll](http://jekyllrb.com)
* [JetBrains](http://www.jetbrains.com/) and their fabulous [PHPStorm](http://www.jetbrains.com/phpstorm/) IDE free for open source project
* [Steve Smith](https://github.com/orderedlist) for this *gh-pages* template

*Pimp my Log* uses these tools, so really thank you to :

* [Bootstrap Team](https://github.com/twbs?tab=members) for their front-end framework [Bootstrap](http://getbootstrap.com/) which includes marvelous icons done by [Glyphicons](http://glyphicons.com/)
* [hook.js](http://usehook.com/) by [Jordan Singer](https://github.com/jordansinger)
* [Initializer](http://www.initializr.com/)
* [jQuery](http://jquery.com/) of course
* [jQuery Cookie](https://github.com/carhartl/jquery-cookie)
* [Modernizr](http://modernizr.com/)
* [Numeral.js](http://numeraljs.com/) by [Adam Draper](https://github.com/adamwdraper)
* [PHP](http://www.php.net) of course
* [ua-parser.js](http://faisalman.github.io/ua-parser-js/) by [Faisal Salman](https://github.com/faisalman)

And finally, thank you *Buzz* ([Ledouze](http://www.ledouze.fr) agency) for the logo!

