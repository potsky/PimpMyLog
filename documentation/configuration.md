---
layout: default
title: Configuration
---

# Globals

## CHECK_UPGRADE



Default:

```json
"CHECK_UPGRADE" : true
```

## FOOTER



Default:

```json
"FOOTER" : "&copy; Potsky<\/a> 2007-2013 - Pimp my Log<\/a>"
```

## GEOIP_URL



Default:

```json
"GEOIP_URL" : "http:\/\/www.geoiptool.com\/en\/?IP=%p"
```

## GOOGLE_ANALYTICS



Default:

```json
"GOOGLE_ANALYTICS" : "UA-XXXXX-X"
```

## LOCALE

You can set a default locale for all users. By default, this value is not set and the displayed language is the user browser one. 

If you set `LOCALE : "fr_FR"` for example, all users will see *Pimp My Log* in French by default whatever is the browser language.

Users can personally override this value with a GET parameter when launching *Pimp My Log*. More informations are available [here](/documentation/usage.html#languageselector).

## LOGS_MAX



Default:

```json
"LOGS_MAX" : 10
```

## LOGS_REFRESH



Default: 

```json
"LOGS_REFRESH" : 7
```

## MAX\_SEARCH\_LOG\_TIME



Default:

```json
"MAX_SEARCH_LOG_TIME" : 3
```

## NAV_TITLE



Default:

```json
"NAV_TITLE" : ""
```

## NOTIFICATION



Default:

```json
"NOTIFICATION" : true
```

## NOTIFICATION_TITLE



Default: 

```json
"NOTIFICATION_TITLE" : "New logs [%f]"
```

## PULL\_TO\_REFRESH



Default:

```json
"PULL_TO_REFRESH" : true
```

## TITLE



Default:

```json
"TITLE" : "Pimp my Log"
```

## USER\_TIME\_ZONE


Default:

```json
"USER_TIME_ZONE" : "Europe\/Paris"
```


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

## Structure

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

## Software structure

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


## Keys


### display



Example:

```json
"display" : "Apache Error #1"
```

### path



Example:

```json
"path" : "\/opt\/local\/apache2\/logs\/error_log"
```

### refresh



Example:

```json
"refresh" : 5
```

### max



Example:

```json
"max" : 10
```

### notify



Example:

```json
"notify" : true
```

### format

#### regex



Example:

```json
"regex": "|^\\[(.*)\\] \\[(.*)\\] (\\[client (.*)\\] )*((?!\\[client ).*)(, referer: (.*))*$|U"
```

#### match



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


#### types



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

##### format of types

```json
"field" : "type[:value][/count][\count]"
```

* `type/100`
* `type:value/100`
* `type:value\20`



##### type *badge*

Default : no color



###### value *http*



###### value *severity*



##### type *date*

Eg: `date:H:i:s`

Date format is PHP date format and is available [here](http://php.net/manual/function.date.php).

##### type *numeral*

Values are available [here](http://numeraljs.com)

Eg: `numeral:0b`

##### type *ip*




###### value *geo*

Eg: `ip:geo` will result in `http://www.geoiptool.com/en/?IP=...`. The global constant `GEOIP_URL` is used.

##### type *link*

Eg: `ip:http` will result in `http://...`

Eg: `ip:sunk ` will result in `sunk://...`

##### type *ua*

Values are available [here](https://github.com/faisalman/ua-parser-js)

Eg:

`ua:{os.name} {os.version} | {browser.name} {browser.version}\/100`

##### other types



#### exclude



Example:

```json
"exclude": {
    "Log": ["\/PHP Stack trace:\/", "\/PHP *[0-9]*\\. \/"]
}
```





