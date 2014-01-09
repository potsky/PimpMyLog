---
layout: default
title: Configuration
---

You can configure a standard profile for each *Pimp My Log* instance. A profile is stored in  the `config.user.json` file at root. If you have done the automatic configuration at first launch, this file has been automatically created and set up for you. You can now adjust settings to fit your daily usage.

This file is not updated and is ignored when you perform a `git pull` so you can easily update *Pimp My Log*, your settings will be kept.

The file is composed by these 3 objects:

* `globals` : global constants for *Pimp My Log* that you can overwrite
* `files` : the log files description with path, format, column definition, ...
* `badges` : a matching array between some kind of values and CSS classes



# 1 - Globals

#### CHECK\_UPGRADE

<a name="checkupgrade"></a>

Whether *Pimp My Log* should check for a new available version on launch or not. You should let this value to `true` given that the check is done in background and only downloads a few bytes.

> **Note**
>  
> Turn it to `false` if your *Pimp My Log* instance is installed on an embedded device without internet connection (*box*, *player*, ...)

<!-- -->

Default:

```json
"CHECK_UPGRADE" : true
```

---

#### FOOTER

This constant is the footer text in *HTML*.

Default:

```json
"FOOTER" : "&copy; <a href=\"http:\/\/www.potsky.com\" target=\"doc\">Potsky<\/a> 2013 - <a href=\"http:\/\/pimpmylog.com\" target=\"doc\">Pimp my Log<\/a>"
```

---

#### GEOIP\_URL

This constant is used to generate links for fields with type *ip* (see below). The url is used and the IP address will replace all `%p` instance.

Default:

```json
"GEOIP_URL" : "http:\/\/www.geoiptool.com\/en\/?IP=%p"
```

---

#### GOOGLE\_ANALYTICS

Your *Google Analytics tracking ID*. If you let the default value or empty, *Google Analytics* will not be loaded.

> **Note**  
> 
> Do not set a *Google Analytics tracking ID* if your *Pimp My Log* instance runs offline.

<!-- -->

Default:

```json
"GOOGLE_ANALYTICS" : "UA-XXXXX-X"
```

---

#### LOCALE

You can set a default locale for all users. By default, this value is not set and the displayed language is the user browser one. 

If you set `"LOCALE" : "fr_FR"` for example, *Pimp My Log* will be in French by default whatever is the browser language for all users.

Users can personally overwrite this value with a GET parameter when launching *Pimp My Log*. More informations are available [here](/documentation/index.html#languageselector).

---

#### LOGS\_MAX

This is the number of logs to display by default for all log files. This value is overwritten by the `max` value of the `files` object.

Default:

```json
"LOGS_MAX" : 10
```

--- 

#### LOGS_REFRESH

This is the frequency in seconds to refresh the logs for all log files. This value is overwritten by the `refresh` value of the `files` object. If set to `0`, auto-refresh is disabled and users will have to pull to refresh, click on the refresh button or stroke the key `R`.

Default: 

```json
"LOGS_REFRESH" : 7
```

---

#### MAX\_SEARCH\_LOG\_TIME

This is the maximum duration in seconds to search for logs in log files. *Pimp My Log* parses logs from the end to the beginning of log files and stop parsing log files when :

- the last line of the previous parsing process is reached (incremental parsing)
- the maximum count of logs is reached
- the beginning of file is reached

So the first parsing can be long and search parsing too. When this duration is reached, parsed logs are returned even if the maximum logs count is not reached.

Searching for an unexisting word in a several gigabytes log file will not retain a web server process for a long time.

Default:

```json
"MAX_SEARCH_LOG_TIME" : 3
```

---

#### NAV\_TITLE

This is the text displayed in the navigation bar on top left. This text is hidden on devices which have a less than 768 pixels width.

Default:

```json
"NAV_TITLE" : ""
```

---

#### NOTIFICATION

Whether desktop notifications are enabled or not on supported browsers for all log files. This value is overwritten by the `notify` value of the `files` object.

If set to `true`, desktop notifications are enabled on supported browser. On launch, users will be prompt whether they allow or not desktop notifications. The notification icon on top right is:
- green when user allows
- orange when user do not take a decision
- red when user denies

If set to `false`, desktop notifications are disabled and the notification icon is grey.

If browser does not support desktop notification, the notification icon is not displayed.

Default:

```json
"NOTIFICATION" : true
```

--- 

#### NOTIFICATION_TITLE

The title of the desktop notification if enabled.

Default: 

```json
"NOTIFICATION_TITLE" : "New logs [%f]"
```

---

#### PIMPMYLOG\_VERSION\_URL

<a name="pimpmylog_version_url"></a>

This is the url of the current version file in production. If you have installed the *beta* branch instead of the *master* one, install this constant and replace word *master* by *beta* in the url. *Pimp my Log* will then check for beta upgrades instead of production upgrades.

Default:

```json
"PIMPMYLOG_VERSION_URL" : "http:\/\/raw.github.com\/potsky\/PimpMyLog\/master\/version.js"
```

---

#### PULL\_TO\_REFRESH

Whether a *pull to refresh* system is loaded or not to refresh logs. You can disable this feature if your browser always refresh logs as soon as you scroll in the window.

Default:

```json
"PULL_TO_REFRESH" : true
```

---

#### TITLE

The HTML title of the *Pimp My Log* page.

Default:

```json
"TITLE" : "Pimp my Log"
```

---

#### USER\_TIME\_ZONE

You can easily change all time values of log files. By default, all time values are displayed as they are stored in log files, assuming they correspond to the server timezone. The server timezone is normally set in the `php.ini` configuration file on the server running your *Pimp My Log* instance. If all *Pimp My Log* users are in an other timezone, you can set this value to shift all time values in their time zone.

Each user can shift its timezone by [setting a GET parameter](/documentation/index.html#timezone).

Default:

```json
"USER_TIME_ZONE" : "Europe\/Paris"
```

---

# 2 - Badges

<a name="badges"></a>

The `badges` object defines which CSS class should be used when applying the type `badges` on a log token (types are explained [below](#typesformat)). This CSS is applied on a [bootstrap *label* object](http://getbootstrap.com/components/#labels).

```json
"badges": {
        "severity": {
                "debug" : "success",
                "info" : "success",
                "notice" : "",
                "warn" : "warning",
                "error" : "danger",
                "crit" : "danger",
                "alert" : "danger",
                "emerg" : "danger"
        },
        "http": {
                "1" : "info",
                "2" : "success",
                "3" : "default",
                "4" : "warning",
                "5" : "danger"
        }
}
```

# 3 - Files

The file object is the main item in the configuration file and it defines:

- which log files to display (file path on the server)
- how to parse the log file lines (the regular expression)
- what to extract (which are the tokens to take in consideration)
- how to display (how have these tokens to be formatted)
- what to exclude
- other parameters which can overwrite the globals

## 3.1 - Files structure

The `files` object is a dictionary of files (a `file` object). The key `fileid` is not really important (eg: `apacheaccess`, `nginx`, ...).

```json
"files": {
    "apacheaccess": {
    ...
    },
    ...
    "nginx": {
    ...
    }
}
```

> **Note**  
> 
> The first file defined in this object will be the selected file when *Pimp My Log* is loaded.

<!-- -->


### 3.1.1 - File structure

The `file` object structure is something like this:

```json
"display" : "Apache Error #1",
"path" : "\/opt\/local\/apache2\/logs\/error_log",
"refresh" : 5,
"max" : 10,
"notify" : true,
"format" : {
    "regex": "|^\\[(.*)\\] \\[(.*)\\] (\\[client (.*)\\] )*((?!\\[client ).*)(, referer: (.*))*$|U",
    "match": {
        "Date" : 1,
        "IP" : 4,
        "Log" : 5,
        "Severity" : 2,
        "Referer" : 7
    },
    "types": {
        "Date" : "date:H:i:s",
        "IP" : "ip:http",
        "Log" : "pre",
        "Severity" : "badge:severity",
        "Referer" : "link"
    },
    "exclude": {
            "Log": ["\/PHP Stack trace:\/", "\/PHP *[0-9]*\\. \/"]
    }
}
```

### 3.1.2 - Keys

#### display

This string is the name displayed in the *Pimp My Log* interface in the file selector on top left.

Example:

```json
"display" : "Apache Error #1"
```

#### path

This string is the full path of the log file on the server.

Example:

```json
"path" : "\/opt\/local\/apache2\/logs\/error_log"
```

#### refresh [optional]

This integer overwrite the global `LOGS_REFRESH` constant defined above only for the current file. This is the frequency in seconds to refresh the logs for the current file. If set to `0`, auto-refresh is disabled and users will have to pull to refresh, click on the refresh button or stroke the key `R`.

Example:

```json
"refresh" : 5
```

#### max

This integer overwrite the global `LOGS_MAX` constant defined above only for the current file. This is the count of displayed log lines.

Example:

```json
"max" : 10
```

#### notify

This boolean overwrite the global `NOTIFICATION` constant defined above only for the current file. If set to `true`, desktop notifications are enabled on supported browser. On launch, users will be prompt whether they allow or not desktop notifications.

Example:

```json
"notify" : true
```

#### format

The `format` key is an JSON array which describes how a line of log text is parsed and how it is understood by *Pimp My Log*. This array is explained in the next following paragraph.


## 3.2 - Log parsing

### 3.2.1 regex

The regular expression is used in PHP with the `preg_match` function. This is a PCRE: *Perl Compatible Regular Expression*. The syntax is available [here](http://www.php.net/manual/en/reference.pcre.pattern.syntax.php).

The regular expression must:

- catch all wanted nuclear tokens
- try to protect unknown formats

> **Note**  
> The date can be considered as a nuclear token if it respects the PHP supported date and time formats as described [here](https://php.net/manual/en/datetime.formats.php). If your date format is not recognized, get the day as a token, month as a token, ... and you will see in the next paragraph how to rebuild a valid format.

<!-- -->

> **Note**  
> I want to say by *unknown formats protection* that it is a good practice to think about others users who can have some variations in their logs. For example, this text is caught via these both regular expressions:
>
> - text : `[client 192.168.12.34]`
> - RE1 : `\\[client (.*)\\]`
> - RE2 : `([client (.*)\\] +)*`
>
> but the second one will allow logs without `[client 192.168.12.34] ` inside. You cannot imagine all cases but obvious cases should be considered.

<!-- -->

> **Advice**  
> Write regular expressions with the [debugger](/developer/debug.html) and then json escape them!

<!-- -->

Example:

```json
"regex": "|^\\[(.*)\\] \\[(.*)\\] (\\[client (.*)\\] )*((?!\\[client ).*)(, referer: (.*))*$|U"
```


### 3.2.2 match

The match object links *human fields* to *token rank* in the regular expression (back references to capturing subpatterns).

##### Simple link

Example:

```json
"match": {
    "Date"        : 1,
    "IP"          : 4,
    "Log"         : 5,
    "Severity"    : 2,
    "WhatYouWant" : 3,
    "Referer"     : 7
}
```

In the example above :

- `WhatYouWant` is the third captured token in the regular expression
- `WhatYouWant` will be the header of the 5th column in the displayed log table in *Pimp My Log* because it has rank 5 in the definition object. If you want `WhatYouWant` to be the first column, move it in the first rank.

##### Unrecognised date formats

You can deal with odd date format as in the following example by capturing :

- `D` : the day
- `M` : the month
- `Y` : the year
- `H` : the hour value
- `I` : the minute value
- `S` : the second value

Example:

```json
"match": {
    "Date"    : {
        "M" : 2,
        "D" : 3,
        "H" : 4,
        "I" : 5,
        "S" : 6,
        "Y" : 8
    },
    "IP"          : 12,
    "Log"         : 14,
    "Severity"    : 10,
    "WhatYouWant" : 11,
    "Referer"     : 16,
},
```


##### Concatenation

You can concatenate several tokens in a single field. This is useful when you want to build a field value with several tokens which do not stand side by side in a line of log.

```json
"match": {
    "IP"          : 12,
    "Log"         : { " : " , 10 , 14 },
    "Severity"    : 10,
    "WhatYouWant" : 11,
    "Referer"     : 16,
},
```

2nd column values will look like this `token10 : token14`.


### 3.2.3 types

Each log token is typed in order to be displayed with the right format and the right style.

Typing a token is easy. You just have to assign a type to a *human field* name. Here is an example:

```json
"types": {
    "Date"     : "date:H:i:s",
    "IP"       : "ip:http",
    "Log"      : "pre/100",
    "Severity" : "badge:severity",
    "Referer"  : "link"
}
```

You can find all supported types in the following paragraph


### 3.2.4 exclude

You can exclude some logs if some regular expressions match a token value.

Example:

```json
"exclude": {
    "Log"  : ["\/PHP Stack trace:\/", "\/PHP *[0-9]*\\. \/"],
    "Date" : ["\/2013\/"],
    "Url " : ["\/favicon.ico\/"],
}
```

In the example below, we don't want to retrieve:

- logs which have a `Date` value which contains `2013`
- logs which have a `Log` value which contains `PHP Stack trace:`
- logs which have a `Log` value which looks like `PHP NUMBER. `
- logs which have a `Url` value which contains `favicon.ico`


## 3.3 Types format

<a name="typesformat"></a>

The type format is:

```json
"field" : "type[:value][/cut_char_count]"
```

Example:

* `txt/100` : display the first **100 chars** of the value without any formatting
* `link:http/50` : display the **50 first chars** of the field formatted as an url which redirect to <http://value>
* `ip:geo/-7` : display the **7 last chars** of the field formatted as an url which redirect to <http://www.geoiptool.com/en/?IP=value>

> **Note**  
>
> When moving your mouse on a value, a tooltip with the raw value is always displayed except for human field named `Date` which always displays the full log line in the tooltip.

<!-- -->

> **Warning**  
>
> Spaces between delimiters are not allowed!  
>
> - `link:http/50` is correct
> - `date:Y/m/d H:i:s/100` is correct (space is in the type value)
> - `link :http/50` is invalid
> - `link: http/50` is invalid
> - `link:http /50` is invalid
> - `link:http/ 50` is invalid

<!-- -->


### type *badge*

When you specify this type, a colored label will be displayed according to the `badges` object as explained [above](#badges). If the value is not defined as a key in the `badges` object, the bootstrap `label-default` class is applied.

- **value *http***  
The badge will be colored according to the first char of the value.  
*eg*: type `badge:http` on value `404` will produce <span class="label label-danger">404</span>  
*eg*: type `badge:http` on value `200` will produce <span class="label label-success">200</span>  

- **value *severity***  
The badge will be colored according to the value.  
*eg*: type `badge:severity` on value `error` will produce <span class="label label-danger">error</span>  
*eg*: type `badge:severity` on value `debug` will produce <span class="label label-success">debug</span>  

- No value will produce a default label.  
*eg*: type `badge` on value `what you want` will produce <span class="label label-default">what you want</span>  


### type *date*

The value of this type is dynamic. Date format is a PHP date format and is available [here](http://php.net/manual/function.date.php).

- *eg*: type `date:H:i:s` will produce `23:33:23`  
- *eg*: type `date` will produce `2014/01/09 23:33:23` as `date:Y/m/d H:i:s/100`

> **Note**  
>
> `date:Y/m/d H:i:s` will produce a odd behaviour because *Pimp My Log* will think that you want to cut the value at `d H:i:s` chars... In this case, use a large amount of chars so that the value will never be cut: `date:Y/m/d H:i:s/100`

<!-- -->


### type *numeral*

Numeral uses the awesome library *Numeral.js* available [here](http://numeraljs.com).

- *eg:* `numeral:0b` will produce an whole number of bytes
- *eg:* `numeral:0.0` will produce an whole number formated with a thousand separator


### type *ip*

The *IP* type assumes that the value is a valid IP address.

- **value *geo***  
The global constant `GEOIP_URL` will be used to generate a link for the IP address.  
*eg*: type `ip:geo` on value `93.184.216.119` will produce text `93.184.216.119` linked to <http://www.geoiptool.com/en/?IP=93.184.216.119>  
*eg*: type `ip:geo` on value `192.168.19.16` will produce text `192.168.19.16` linked to <http://www.geoiptool.com/en/?IP=192.168.19.16> which is not really useful...  

- **value *WhatYouWant***  
The value will be linked to address `WhatYouWant://value`  
*eg*: type `ip:http` on value `93.184.216.119` will produce text `93.184.216.119` linked to <http://93.184.216.119>  
*eg*: type `ip:ftp` on value `192.168.19.16` will produce text `192.168.19.16` linked to <ftp://192.168.19.16>  

### type *link*

The *link* type simply add a link to the value.

- *eg*: type `link` on value `http://93.184.216.119` will produce text `http://93.184.216.119` linked to <http://93.184.216.119>  
- *eg*: type `link` on value `http://www.google.fr` will produce text `http://www.google.fr` linked to <http://www.google.fr>  


### type *ua*

Numeral uses the awesome library *UAParser.js* available [here](https://github.com/faisalman/ua-parser-js).

- *eg:* `ua:{os.name} {os.version} | {browser.name} {browser.version}` will produce `Mac OS X 10.9.1 | Safari 7.0.1` for my computer and my favourite browser or `Windows 7 | Opera 12.15` for odd guys...

> **Note**  
> 
> - If any token cannot be analysed, it will return the full user agent like this: `Mozilla/5.0 (compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm)`
> - If some token can be analysed but not all, it will return something like this: `{os.name} {os.version} | Firefox 3.6.28`



<!-- -->

> **Warning**  
>
> Do not put space between `{}`  
> `{os.version}` is correct  
> `{ os.version}` is invalid

<!-- -->


### Default type

The default type is `txt` and if you put an undefined type, it will be managed as `txt`.

The default type simply display the value without any format.

*eg*: `txt/100`, `map/100`, `dude/100`, ... will display the same thing

