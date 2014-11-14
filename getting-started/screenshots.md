---
layout: default
title: Screenshots
---

## Main window

{% image /assets/ss/getstarted_desktopnotifications2.png class="img-responsive" alink="" atarget="" %}

You can select several log files with the selector on top left.

You can sort your logs by clicking on the column headers.

## Desktop notifications

Reduce the *Pimp My Log* window and when a new log is available, click on the notification to open *Pimp My Log*

{% image /assets/ss/getstarted_desktopnotifications1.png class="img-responsive" alink="" atarget="" %}

*New logs have a violet border on the row left, you can of course [customize](/documentation/customization.html#newlogs) this*



## Export

Choose a format to export the current view

{% image /assets/ss/getstarted_export1.png class="img-responsive" alink="" atarget="" %}

You can export your logs in several formats :

- ATOM and RSS for your feed reader
- CSV for your boss
- XML
- JSON
- JSON Pretty Print for your human eyes
- JSONP for jQuery for example

Exporting your logs in the CSV format for example will just launch the download on your computer.

Exporting your logs in ATOM and RSS is a little bit more interesting because you can put these both URLS in your RSS reader and then be alerted as soon as a certain type of log appears!

Exporting your logs in XML, JSON and JSONP is awesome because you can use your generated URLs in your own app to do exactly what you want ! *Pimp My Log* becomes a secure gateway between your protected log files and your external apps.

Just 3 more things !

1. Security rules are applied in all feeds. So if a log file is only accessible by some users, these users will generate a feed URL with their Access Token and with a security hash embedded. As an admin, if you revoke a user for a log file, its feed will not work anymore for this user only.

2. If an private URL (with access token) is now public, as a user you can reset your access token and your salt key and then regenerate all wanted feed URL. Previous URL will not work anymore.

3. You can use some parameters in the URL. Try to export in JSON format for example, parameters are explained in the popup. The most important parameter is `search` : you can specify a search expression (text or regex) to retrieve only important logs in a feed.

{% image /assets/ss/getstarted_export2.png class="img-responsive" alink="" atarget="" %}


## Authentication

There are 3 modes :

- no authentication : people can view all configured log files
- authentication enabled : only users can view log files according to their rights
- authentication enabled with anonymous access : only users can view log files according to their rights and visitors can view some log files

{% image /assets/ss/getstarted_authentication1.png class="img-responsive" alink="" atarget="" %}

## User management

{% image /assets/ss/getstarted_authentication_admin1.png class="img-responsive" alink="" atarget="" %}

{% image /assets/ss/getstarted_authentication_admin2.png class="img-responsive" alink="" atarget="" %}

{% image /assets/ss/getstarted_authentication_admin3.png class="img-responsive" alink="" atarget="" %}

{% image /assets/ss/getstarted_authentication_admin4.png class="img-responsive" alink="" atarget="" %}

{% image /assets/ss/getstarted_authentication_admin5.png class="img-responsive" alink="" atarget="" %}


## Settings

Select which column to display at run time

{% image /assets/ss/getstarted_menus1.png class="img-responsive" alink="" atarget="" %}

Change language, timezone and more

{% image /assets/ss/getstarted_menus2.png class="img-responsive" alink="" atarget="" %}

Manage your account

{% image /assets/ss/getstarted_menus3.png class="img-responsive" alink="" atarget="" %}

Change your password

{% image /assets/ss/getstarted_changepassword.png class="img-responsive" alink="" atarget="" %}

Change your profile

{% image /assets/ss/getstarted_profile.png class="img-responsive" alink="" atarget="" %}


## Pull to refresh

{% image /assets/ss/getstarted_pulltorefresh.png class="img-responsive" alink="" atarget="" %}

You can stroke key `R` too or click on the refresh button on top left.

## Search

Text plain expression to search

{% image /assets/ss/getstarted_search1.png class="img-responsive" alink="" atarget="" %}

RegEx expression to search

{% image /assets/ss/getstarted_search2.png class="img-responsive" alink="" atarget="" %}

*If search expression is detected as a regular expression, the search input is pink, because pink is a coder color*

## Installation

{% image /assets/ss/getstarted_install1.png class="img-responsive" alink="" atarget="" %}

{% image /assets/ss/getstarted_install2.png class="img-responsive" alink="" atarget="" %}

{% image /assets/ss/getstarted_install3.png class="img-responsive" alink="" atarget="" %}

{% image /assets/ss/getstarted_install4.png class="img-responsive" alink="" atarget="" %}

{% image /assets/ss/getstarted_install5.png class="img-responsive" alink="" atarget="" %}

{% image /assets/ss/getstarted_install6.png class="img-responsive" alink="" atarget="" %}


## Upgrade

{% image /assets/ss/getstarted_upgrade1.png class="img-responsive" alink="" atarget="" %}

{% image /assets/ss/getstarted_upgrade2.png class="img-responsive" alink="" atarget="" %}

*You are alerted when an upgrade is available, you can [disable this feature](/documentation/configuration.html#checkupgrade)*
