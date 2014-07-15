---
layout: default
title: Usage
---

# 1 - Main features

## Introduction

Here is the main window of *Pimp My Log* :

{% image /assets/ss/interface.png class="img-responsive" alink="" atarget="" %}

We are going to take a tour of all features in the menu bar from left to right.

## Logo

Click on it and the page will be fully reloaded. The configuration file will be reloaded. Hey what a feature!

## Refresh

Click on the refresh button to check for new logs for the selected file.

You can manually refresh logs :

- by clicking on this icon
- by pulling down the page
- by stroking the `R` key on a qwerty keyboard
- by calling `javascript:get_logs()` in your browser if available. It is handy in *Sublime Text* with the *Build on Save* to refresh *Safari* with your dev code and one second later *Chrome* with *Pimp My Log*

## Switch between log files

You can display as many files as you want in the same *Pimp My Log* instance.

Simply choose the one you want to view.

You can set the default loaded file in the configuration file by setting its `file` object as the first one in the dictionary `files`.

## Search

The search input lets you type some words to search in the log file. Stroke key `F` to focus on the search input.

The search is done line by line in every full line. The search is not performed on each separated token from a line.

> **Warning**  
> 
> The search is done in a full line of logs. Take this log:
>
> `127.0.0.1 - - [21/Dec/2013:22:19:29 +0100] "GET /img/psk.png HTTP/1.1"`
>
> The line above will be returned if you search for text `+0100] "GET /img/` for example

### Text search

Simply type something and the text will be searched and displayed.

### RegEx search

If the search is a valid regular expression, it will be searched with the *PHP* `preg_match` version. The documentation about patterns is [here](http://www.php.net/manual/en/reference.pcre.pattern.syntax.php).

When a RegEx is valid, the search input box becomes <span style="color:#f0f">pink</span>.

The great thing in *PCRE* functions is that a RegEx delimiter can be any non-alphanumeric, non-backslash, non-whitespace character.

To get all images from the root /img/ directory for example, you can use these RegEx:

- `@ /img/@`
- `+ /img/+`
- `/ \/img\//`

I put a space before the first slash because I don't forget that the RegEx is applied on full lines of logs and a line is something like this `127.0.0.1 - - [21/Dec/2013:22:19:29 +0100] "GET /img/psk.png HTTP/1.1"` so to match the `/img` directory at root, I know there is a space before. I could write this too:

- `@GET /img/@`
- `+GET /img/+`
- `/GET \/img\//`

### Search timer

The search is done from the bottom to the top of log file and *Pimp My Log* collects matched lines until the number of lines you want to display is reached. The problem is that if you try to find something rare, *Pimp My Log* will scan the full log file and I am quite sure you have tons of megabytes!

So there is a limitation timer and when *Pimp My Log* has reach this timer, it returns what it has found. 2 consequences :

- *Pimp My Log* will not display the number of lines that you want
- when refreshing the view, *Pimp My Log* will still add new logs which are matched by the search

You can modify the default timer as explained [here](/documentation/configuration.html#max_search_log_time).

You can see in the footer 2 interesting things :

- *7 new logs found in `2870ms` with 331K of logs* for example. *Ouch!* almost 3 seconds to search the expression in my log file. But on the next refresh, duration comes back to `1ms`.
- `1079` skipped line(s) for example. To perform the search, *Pimp My Log* has rejected 1079 unmatched lines of logs.

If these values are too high and you cannot display what you want to find, change your search expression...

## Auto-refresh

You can ask *Pimp My Log* to refresh the display automatically. It is really useful with desktop notification. You can leave *Pimp My Log* in a hidden tab in a window back to all others and you will receive desktop notification when a new log is available. Just click on the desktop notification and *Pimp My Log* will come on top!

I use these settings when I develop a website:

- `error.log` : auto refresh every second because I want to be alerted as soon as an error is thrown
- `access.log` : no auto-refresh because I don't want to be alerted when *apache* serves somebody

> **Note**  
> 
> Don't be afraid to use the 1 second auto-refresh even on production servers. *Pimp My Log* only performs incremental log scans (except for the first launch).

<!-- -->


## Displayed lines

You can change the number of lines you want to display. Just select in the list the number or log lines you want. Displaying a lot of lines has performance consequences on:
- first load
- search

On normal usage, *Pimp My Log* only check for new logs in the log file.

## Desktop notifications

The desktop notification icon is displayed when your browser supports this feature. If browser does not support desktop notification, the notification icon is not displayed.

The notification icon on top right is:

- <span class="label label-default">grey</span> when desktop notifications are disabled
- <span class="label label-success">green</span> when desktop notifications are enabled and user has allowed them in its browser
- <span class="label label-warning">orange</span> when desktop notifications are enabled but user has not taken a decision about allowing or denying them in its browser
- <span class="label label-danger">red</span>  when desktop notifications are enabled and user has denied them in its browser

## Footer

You can see something like this :

> 13 logs displayed, 2 new logs found in `11ms` with `947B` of logs, `1` skipped line(s), `0` unreadable line(s).  
> 
> File `/opt/local/apache2/logs/access_log` was last modified on `2014/01/09 23:12:39`, size is `7M`

What does it mean?

- *13 logs displayed* : the number of displayed logs, ok it is easy!

- *2 new logs found* : since the previous refresh, *Pimp My Log* has found 2 new logs according to the current search

- *in `11ms`* : the time *Pimp My Log* has spent to do its job in the last refresh

- *with `947B` of logs* : *Pimp My Log* has parsed this quantity of bytes of logs from the end of the file before stopping and returning results

- *`1` skipped line(s)* : the number of skipped lines because they do not match the search

- *`0` unreadable line(s)* : the number of lines which do not match the regular expression. **It should always be zero!**

- *File `/opt/local/apache2/logs/access_log`* : the path of the displayed file

- *was last modified on `2014/01/09 23:12:39`* : its last modification date. Don't be brutal with the refresh button if this date does not change... perhaps you do not display the right file!

- *size is `7M`* : the log file size. If you see some gigabytes, perhaps you should take a look on this [huge piece](https://fedorahosted.org/logrotate/) of software :-)

## Log rotation

Don't be afraid to see a these messages in top of the window:

- <div class="alert alert-info">2014/01/09 23:24:08 > Log file has been rotated</div>  
*Pimp My Log* cannot find the last line of the log file from the previous scan. So it can be because the log file has been rotated and the new refresh process is done on a bigger file than the previous refresh.  
It can happen too when you write manually some data in the log file.


- <div class="alert alert-info">2014/01/09 23:24:08 > Log file has been rotated (previous size was 7M and new one is 174B)</div>  
*Pimp My Log* cannot find the last line of the log file from the previous scan and the file size has decreased.


# 2 - Advanced features

As a user, each time you change a parameter in the *Pimp My Log* interface, the URL is refreshed in your browser.

You can save the current view by bookmarking *Pimp My Log*.

If you click on the *Pimp My Log* logo at top left, all default settings from the `config.user.json` will be used.

Here is the list of supported GET parameters.

## Default file (`i`)

<a name="defaultfile"></a>

The first file defined in your configuration file is displayed by default.

You can change this behaviour by adding a *GET* parameter in the *Pimp My Log* url. The *GET* parameter name is `i` and its value is the `fileid` of the log file as defined in the configuration file.

eg: `http://.../PimpMyLog/?i=apache2`


## Language selector (`l`)

<a name="languageselector"></a>
Users can override the default  by setting a GET parameter named `l`.

`http://.../PimpMyLog/?l=fr_FR` will load *Pimp My Log* in French for example.

Supported languages are :

* `en_GB` or empty for English
* `fr_FR` for French
* `pt_BR` for portugues do Brasil


## Maximum count of logs (`m`)

Users can override the maximum count of logs by setting a GET parameter named `m`.

`http://.../PimpMyLog/?w=100` will display a maximum count of 100 logs.

Supported values are pre-defined values ( configuration file value, 5, 10, 20, 50, ...)


## Notification (`n`)

Users can override the default notification preference by setting a GET parameter named `n`.

`http://.../PimpMyLog/?n=false` will disable the notification.

Supported views are :

* `true` to enable notifications
* `false` to disable notifications


## Refresh timer (`r`)

Users can override the auto-refresh value by setting a GET parameter named `r`.

`http://.../PimpMyLog/?r=1` will refresh every second.

Supported values are pre-defined values ( configuration file value, 1, 2, 5, ...)


## Search (`s`)

Users can launch a search by default by setting a GET parameter named `s`.

`http://.../PimpMyLog/?s=what+am+I+searching+for+%3F`


## Timezone selector (`tz`)

<a name="timezone"></a>

You can easily change all time values according to your current timezone. By default, all time values are displayed as they are stored in log files, assuming they correspond to the server timezone. The server timezone is normally set in your `php.ini` file.

If you are currently in an other timezone location and want to display logs according to your current timezone, you can specify your timezone in the GET parameter like this:

`http://.../PimpMyLog/?tz=America/Los_Angeles`

Or:

`http://.../PimpMyLog/?tz=UTC`

All available timezone strings are available [here](http://php.net/manual/en/timezones.php).


## Wide view (`w`)

Users can override the default view by setting a GET parameter named `w`.

`http://.../PimpMyLog/?w=false` will disable the wide view for example.

Supported views are :

* `true`, `on`, `1` or `` for a wide view
* `false`, `off`, `0` for a narrow view

