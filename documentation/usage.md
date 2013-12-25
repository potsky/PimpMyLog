---
layout: default
title: Usage
---

# Main features

##  Introduction

## Switch between log files

## Search

## Auto-refresh

## Displayed lines

## Desktop notifications

# Advanced features

## Timezone selector

You can easily change all time values according to your current timezone. By default, all time values are displayed as they are stored in log files, assuming they correspond to the server timezone. The server timezone is normally set in your `php.ini` file.

If you are currently in an other timezone location and want to display logs according to your current timezone, you can specify your timezone in the GET parameter like this:

`http://.../PimpMyLog/?tz=America/Los_Angeles`

Or:

`http://.../PimpMyLog/?tz=UTC`

All available timezone strings are available [here](http://php.net/manual/en/timezones.php).

##  Language selector

<a name="languageselector"></a>
Users can override the default  by setting a GET parameter named `l`.

`http://.../PimpMyLog/?l=fr_FR` will load *Pimp My Log* in French for example.

Supported languages are :

* `en_GB` or empty for English
* `fr_FR` for French



