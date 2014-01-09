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



## Auto-refresh



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

## Timezone selector

<a name="timezone"></a>

You can easily change all time values according to your current timezone. By default, all time values are displayed as they are stored in log files, assuming they correspond to the server timezone. The server timezone is normally set in your `php.ini` file.

If you are currently in an other timezone location and want to display logs according to your current timezone, you can specify your timezone in the GET parameter like this:

`http://.../PimpMyLog/?tz=America/Los_Angeles`

Or:

`http://.../PimpMyLog/?tz=UTC`

All available timezone strings are available [here](http://php.net/manual/en/timezones.php).

## Language selector

<a name="languageselector"></a>
Users can override the default  by setting a GET parameter named `l`.

`http://.../PimpMyLog/?l=fr_FR` will load *Pimp My Log* in French for example.

Supported languages are :

* `en_GB` or empty for English
* `fr_FR` for French



