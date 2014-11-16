<?php
/*! pimpmylog - 1.5.2 - e1adeefb3e037035916e83ef6691d24b47e1c284*/
/*
 * pimpmylog
 * http://pimpmylog.com
 *
 * Copyright (c) 2014 Potsky, contributors
 * Licensed under the GPLv3 license.
 */
?>
<?php if(realpath(__FILE__)===realpath($_SERVER["SCRIPT_FILENAME"])){header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found');die();}?>
{
	"globals": {
		"_remove_me_to_set_CHECK_UPGRADE"          : true,
		"_remove_me_to_set_EXPORT"                 : true,
		"_remove_me_to_set_FILE_SELECTOR"          : "bs | html",
		"_remove_me_to_set_FOOTER"                 : "&copy; <a href=\"http:\/\/www.potsky.com\" target=\"doc\">Potsky<\/a> 2013 - <a href=\"http:\/\/pimpmylog.com\" target=\"doc\">Pimp my Log<\/a>",
		"_remove_me_to_set_GEOIP_URL"              : "http:\/\/www.geoiptool.com\/en\/?IP=%p",
		"_remove_me_to_set_GOOGLE_ANALYTICS"       : "UA-XXXXX-X",
		"_remove_me_to_set_LOCALE"                 : "fr_FR",
		"_remove_me_to_set_LOGS_MAX"               : 10,
		"_remove_me_to_set_LOGS_REFRESH"           : 7,
		"_remove_me_to_set_MAX_SEARCH_LOG_TIME"    : 3,
		"_remove_me_to_set_NAV_TITLE"              : "",
		"_remove_me_to_set_NOTIFICATION"           : true,
		"_remove_me_to_set_NOTIFICATION_TITLE"     : "New logs [%f]",
		"_remove_me_to_set_PIMPMYLOG_VERSION_URL"  : "http:\/\/demo.pimpmylog.com\/version.js",
		"_remove_me_to_set_PULL_TO_REFRESH"        : true,
		"_remove_me_to_set_SORT_LOG_FILES"         : "default | display-asc | display-insensitive | display-desc | display-insensitive-desc",
		"_remove_me_to_set_TITLE"                  : "Pimp my Log",
		"_remove_me_to_set_TITLE_FILE"             : "Pimp my Log [%f]",
		"_remove_me_to_set_USER_CONFIGURATION_DIR" : "config.user.d",
		"_remove_me_to_set_USER_TIME_ZONE"         : "Europe\/Paris"
	},

	"badges": {
		"severity": {
			"debug"       : "success",
			"info"        : "success",
			"notice"      : "default",
			"Notice"      : "info",
			"warn"        : "warning",
			"error"       : "danger",
			"crit"        : "danger",
			"alert"       : "danger",
			"emerg"       : "danger",
			"Notice"      : "info",
			"fatal error" : "danger",
			"parse error" : "danger",
			"Warning"     : "warning"
		},
		"http": {
			"1" : "info",
			"2" : "success",
			"3" : "default",
			"4" : "warning",
			"5" : "danger"
		}
	},

	"files": {
"FILES":"FILES"
	}
}
