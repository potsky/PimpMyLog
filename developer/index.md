---
layout: default
title: Developer
---

There is essentially three ways to contribute to *Pimp My Log* :

1. Add new type of logs for new softwares (want to display logs from *sshd*, *nginx*, ...)
1. Add some new features, fix bugs, etc... in the main source code
1. Add a new language

Current dev build status is [![Build Status](https://travis-ci.org/potsky/PimpMyLog.svg)](https://travis-ci.org/potsky/PimpMyLog)

# 1 - New logs

## 1.1 - For your own usage

Basically, you just have to modify the configuration file `config.user.json` at root and it will work. To understand how the configuration file works, just read the [documentation about configuration](/documentation/configuration.html), it is easy.

The only difficulty should be the regular expression. There is a tool to help you to find the right expression. This tool is explained in the [debug](/developer/debug.html) page.

## 1.2 - For the community

First of all, you have to make it work for you! This is the most difficult part.

## 1.2.1 - Simple way

To share your configuration file with other users, create a new discussion on the [support](http://support.pimpmylog.com) website and provide these informations :

- the configuration file of course :-)
- several lines of log file sample
- additional informations about how to configure the software to make it generate the wanted log format ( *apache* `LogFormat` directive for example)

Here is a discussion template ready to modify and post:

Subject : `Submission: Config for ___SOFTWARE___`

    Hi!

    Here is a configuration excerpt for software *---SOFTWARE---*:

    - **name**   : ---SOFTWARE_NAME---
    - **url**    : ---SOFTWARE_URL IF AVAILABLE---
    - **os**     : ---ON WHICH OPERATING SYSTEM IS THIS SOFTWARE AVAILABLE---
    - **format** : ---SOME ADVICE TO CONFIGURE THE SOFTWARE ON THE SERVER TO HAVE SAME LOG FORMAT AS YOU---
    - **config** :  
    ```
    "---SOFTWARE_ID---": {
        "display" : "---SOFTWARE_NAME---",
        "path"    : "---SOFTWARE__PATH---",
        "refresh" : 20,
        "max"     : 20,
        "notify"  : false,
        "format"  : {
            ---YOUR_CONFIGURATION---
        }
    }
    ```
    - **sample** :  
    ```
    Jan 12 09:25:12 VNLAdapterStatus - ioctl: SIOCGIFFLAGS failed, error: Device not configured
    Jan 12 09:25:12 VNL_EnableNetworkAdapter - Successfully enabled hostonly adapter on vnet: vmnet1
    Jan 12 09:25:12 VNL_StartService - Started "DHCP" service for vnet: vmnet1
    Jan 12 09:25:12 VNL_StartService - Started "NAT" service for vnet: vmnet8
    ```



## 1.2.2 - Add it in the configurator

You can share it with the community by adding some stuffs so that users will enable this new software during the auto-configuration process at first launch.

You must read the [softwares](/developer/softwares.html) page to understand how you can build your software own configurator.

# 2 - Active development

<a name="pullrequest"></a>

If you want to add new features, fix some bugs or something else, it is easy :

- Fork the repository on [GitHub](https://github.com/potsky/PimpMyLog/fork)
- Work in the `dev` or `jekyll` branch (see below)
- Commit
- Push
- Click on the *New pull request* on your forked repository in your account 
- Add some comments to your modifications if they are not trivial to understand

Got it!

## Structure

You have to understand how the repository is organized at first. *Pimp My Log* has 5 branches:

- `master`: the production code
- `beta`: the code in preproduction to test on live before going to prod
- `dev`: the development branch, **you code here**
- `gh-pages`: hey this is the static website you are reading now!
- `jekyll`: the development branch for the `gh-pages`, **you write documentation here**

### Code

To code in *Pimp My Log*, checkout the `dev` branch:

```sh
$ git clone https://github.com/potsky/PimpMyLog.git -b dev PimpMyLog-dev
$ cd PimpMyLog-dev
```

You will need these tools on your computer:

- [Ruby](http://www.ruby-lang.org/)
- [SASS](http://sass-lang.com) (`gem install sass`)
- [Node.js](http://nodejs.org/)
- [Composer](https://getcomposer.org) (`curl -sS https://getcomposer.org/installer | php; mv composer.phar /usr/local/bin/composer`)
- [Grunt](http://gruntjs.com/) (`npm install -g grunt-cli`)
- [Bower](http://bower.io/) (`npm install -g bower`)

Install some tools:

```sh
$ npm install
$ bower install
$ composer install
```

And launch `grunt` to watch your modifications and rebuild what is necessary:

```sh
$ grunt
```

Your *Pimp My Log* development instance is now available at <http://localhost/PimpMyLog-dev/_site/>

You need of course *PHP* on your local server!

When you are ready, test your code in production. Stop `grunt` and launch the production server now:

```sh
$ grunt build
```

Your *Pimp My Log* development instance ready for production is now available at <http://localhost/PimpMyLog-dev/_build/>.

This code will be the same as in the `master` branch :

- sass files converted to a single css minified file
- javascripts in a single file minified
- licences added
- version checked
- ...

### Documentation

To work on the documentation is really funny in my private opinion. The website that you are reading now is a static website without any database or script language. It is based on [jekyll](http://jekyllrb.com). It is served by *Github* servers but **statically** because plugins are great and *Github* does not allow them.

So the website in the *gh-pages* is already generated by `grunt` in the `jekyll` branch.

You will need these tools on your computer:

- [Ruby](http://www.ruby-lang.org/)
- [Node.js](http://nodejs.org/)
- [Grunt](http://gruntjs.com/) (`npm install -g grunt-cli`)
- [Bower](http://bower.io/) (`npm install -g bower`)
- [Jekyll](http://jekyllrb.com) (`gem install jekyll`)
- [GraphicsMagick](http://www.graphicsmagick.org) (`port install GraphicsMagick`)

To write documentation or fix my poor english words on *pimpmylog.com*, checkout the `jekyll` branch:

```sh
$ git clone https://github.com/potsky/PimpMyLog.git -b jekyll PimpMyLog-jekyll
$ cd PimpMyLog-jekyll
```

Install some tools:

```sh
$ bundle install
$ npm install
$ bower install
```

In 2 terminals, run at the same time :

- `grunt` to watch for file modification and live rebuild
- `grunt server` to launch Jekyll in dev mode with watch for file modification and live rebuild

Now you can watch the documentation website at <http://localhost:4000>.

Here is the file structure :

- `_js` : our javascripts
- `_css` : our css and less files
- `assets` : the static assets directory
- `upload` : assets for posts (thumbnails are computed on grunt processes)

Install new css and js tools with `bower install ... -S` and do not modify original files in `bower-components`!

When you have finished and are ready, test your modification in production. Stop both grunt processes and launch:

```sh
$ grunt build server-prod
```

Now you can watch the production documentation website ready to deploy at <http://localhost:4000>.

As in the `dev` branch, this will minify css, js, html, build thumbnails, etc...

# 3 - Language

## Existing languages

To contribute to an existing language in *Pimp My Log* (not the documentation website):

- select a language and register on *Pimp My Log* [PoEditor](https://poeditor.com/join/project?hash=b767ddcd3dcd545253717a12d3fabfa1) project
- wait for us to validate your account
- translate some terms

We will update *Pimp My Log* to embed your definitions.

## New languages

There are 2 ways to add a new language in *Pimp My Log* (not the documentation website):

- Ask us the wanted locale (*eg*: `en_CA` for english in Canada) on the [support](http://support.pimpmylog.com) website.  
We will create this file in the project and in [PoEditor](https://poeditor.com/join/project?hash=b767ddcd3dcd545253717a12d3fabfa1). Then you will be able to translates terms in [PoEditor](https://poeditor.com/join/project?hash=b767ddcd3dcd545253717a12d3fabfa1).
- If you are familiar with *PO* files, download the latest [po template](https://raw.github.com/potsky/PimpMyLog/master/lang/messages.po) file and load it with [PoEdit](http://www.poedit.net/download.php) for example. Translate it and send it via the [support site](http://support.pimpmylog.com), we will include it in a next release and we will create this file in [PoEditor](https://poeditor.com/join/project?hash=b767ddcd3dcd545253717a12d3fabfa1).




