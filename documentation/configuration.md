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



# Globals

#### CHECK\_UPGRADE

Whether *Pimp My Log* should check for a new available version on launch or not. You should let this value to `true` given that the check is done in background and only downloads a few bytes.

> **Note**  
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

Users can personally overwrite this value with a GET parameter when launching *Pimp My Log*. More informations are available [here](/documentation/usage.html#languageselector).

---

#### LOGS\_MAX

This is the number of logs to display by default for all log files. This value is overwritten by the `max` value of the `files` object.

Default:

```json
"LOGS_MAX" : 10
```

--- 

#### LOGS_REFRESH

This is the frequency in seconds to refresh the logs for all log files. This value is overwritten by the `refresh` value of the `files` object.

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

This is the url of the current version file in production. If you have installed the *beta* branch instead of the *master* one, install this constant and replace word *master* by *beta* in the url. *Pimp my Log* will then check for beta upgrades instead of production upgrades.

Default:

```json
"PIMPMYLOG_VERSION_URL" : "http:\/\/raw.github.com\/potsky\/PimpMyLog\/master\/version.jsonp"
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

Each user can shift its timezone by [setting a GET parameter](/documentation/usage.html#timezone).

Default:

```json
"USER_TIME_ZONE" : "Europe\/Paris"
```

---

# Badges


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

# Files

#### Structure

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

#### Software structure

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


#### Keys


##### display



Example:

```json
"display" : "Apache Error #1"
```

##### path



Example:

```json
"path" : "\/opt\/local\/apache2\/logs\/error_log"
```

##### refresh



Example:

```json
"refresh" : 5
```

##### max



Example:

```json
"max" : 10
```

##### notify



Example:

```json
"notify" : true
```

##### format

###### regex



Example:

```json
"regex": "|^\\[(.*)\\] \\[(.*)\\] (\\[client (.*)\\] )*((?!\\[client ).*)(, referer: (.*))*$|U"
```

###### match



Example:

```json
"match": {
    "Date" : 1,
    "IP" : 4,
    "Log" : 5,
    "Severity" : 2,
    "Referer" : 7
}
```

or special for date :

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
    "IP"       : 12,
    "Log"      : 14,
    "Severity" : 10,
    "Referer"  : 16,
},
```

or with concatenation :

```json
"match": {
    "IP"       : 12,
    "Log"      : { " : " , 10 , 14 },
    "Severity" : 10,
    "Referer"  : 16,
},
```


###### types



Example:

```json
"types": {
    "Date" : "date:H:i:s",
    "IP" : "ip:http",
    "Log" : "pre/100",
    "Severity" : "badge:severity",
    "Referer" : "link"
}
```

####### format of types

```json
"field" : "type[:value][/count][\count]"
```

* `type/100`
* `type:value/100`
* `type:value\20`



####### type *badge*

Default : no color



######## value *http*



######## value *severity*



####### type *date*

Eg: `date:H:i:s`

Date format is PHP date format and is available [here](http://php.net/manual/function.date.php).

####### type *numeral*

Values are available [here](http://numeraljs.com)

Eg: `numeral:0b`

####### type *ip*




######## value *geo*

Eg: `ip:geo` will result in `http://www.geoiptool.com/en/?IP=...`. The global constant `GEOIP_URL` is used.

####### type *link*

Eg: `ip:http` will result in `http://...`

Eg: `ip:sunk ` will result in `sunk://...`

####### type *ua*

Values are available [here](https://github.com/faisalman/ua-parser-js)

Eg:

`ua:{os.name} {os.version} | {browser.name} {browser.version}\/100`

####### other types



###### exclude



Example:

```json
"exclude": {
    "Log": ["\/PHP Stack trace:\/", "\/PHP *[0-9]*\\. \/"]
}
```





