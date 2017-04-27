/*PSK*/pml_version_cb(/*PSK*/
{
	"version"     : "1.7.14",
	"versiondevh" : "025d83c29c6cf8dbb697aa966c9e9f8713ec92f1",
	"messages"    : {
        "20161214" : "<p>Hi folks!</p><p>Life has been really complicated this last year but I hope things will be fun again and I hope I will have time to update Pimp My Log.</p>",
        "20141121" : "Thank you to people who have starred the project on GitHub !",
		"20141012" : "<p>Hi folks!</p><p>This alert box has been added to send important messages to you about <em>Pimp my Log</em>. It is not intended to be a <em>Spam Zone</em>.</p><p>I need to know if I should continue the development of <em>Pimp My Log</em> and how many people are interested in <em>Pimp my Log</em>. Would you just let me know by starring the project in GitHub please?</p><p><iframe src=\"http://pimpmylog.com/github-btn.html?user=potsky&repo=PimpMyLog&type=watch&count=true\" allowtransparency=\"true\" frameborder=\"0\" scrolling=\"0\" width=\"170\" height=\"30\"></iframe></p><p>And don&#8217;t worry, <em>Pimp my Log</em> will be in open-source forever.</p><p><em>potsky</em></p>",
		"20141011" : "This message will never be shown. Never Gonna Give You Up !"
	},
	"changelog"   : {
        "1.7.14" : {
            "released" : "2017-04-27",
            "fixed" : [
                "unexpected T_FUNCTION, [PHP 5.2] (#118)",
                "Undefined index: logs (<a href=\"http://support.pimpmylog.com/discussions/problems/162-undefined-index-logs\" target=\"_blank\">more info</a>)"
            ]
        },
        "1.7.13" : {
            "released" : "2017-02-23",
            "new" : [
                "Add configuration examples in the cfg-examples folder (#112)"
            ],
            "fixed" : [
                "globals.USER_TIME_ZONE not working (#114)"
            ],
            "changed" : [
                "Bower dependencies updated (#113)"
            ]
        },
        "1.7.12" : {
            "released" : "2017-02-16",
            "new" : [
                "Add port type for TCP/UDP ports (#111)"
            ],
            "fixed" : [
                "BUG: error with Array Index (contains the log id) (#108)"
            ]
        },
        "1.7.11" : {
            "released" : "2016-12-14",
            "new" : [
                "Search for log paths within sub-directories of specified Apache paths (#103)",
                "Allow installation to custom installer path (#107)",
                "Custom configuration file path (#102)"
            ],
            "fixed" : [
                "BUG: fseek() expects parameter 2 to be integer, float given (<a href=\"http://support.pimpmylog.com/discussions/problems/150-bug-fseek-expects-parameter-2-to-be-integer-float-given\" target=\"_blank\">more info</a> and #108)"
            ]
        },
		"1.7.10" : {
			"released" : "2015-11-24",
			"fixed" : [
				"Regex tester error in debugger for authenticated users (<a href=\"http://support.pimpmylog.com/discussions/problems/75-bug-on-debugger\" target=\"_blank\">more info</a>)"
			]
		},
		"1.7.9" : {
			"released" : "2015-05-27",
			"new" : [
				"Support for date formats (parsing): U,c and r"
			]
		},
		"1.7.8" : {
			"released" : "2015-05-23",
			"fixed" : [
				"Restore support for PHP 5.2"
			]
		},
		"1.7.7" : {
			"released" : "2014-12-23",
			"fixed" : [
				"Cut text in modes txt, pre and preformatted displays hellip; instead of ..."
			]
		},
		"1.7.6" : {
			"released" : "2014-12-23",
			"new" : [
				"New type 'preformatted': it is the same type as 'pre' but new lines and tabs strings are replaced by real new lines and real tabs. var_dump and other debug functions returns are now well displayed!"
			],
			"changed" : [
				"Type output change: txt, pre and preformatted types (not prefake) are now in full raw text format. HTML is no more parsed. Use 'prefake' to let HTML in your logs be parsed",
				"Dev: javascript files are split now, Grunt will concat them before uglifying"
			],
			"fixed" : [
				"Configuration : max, refresh and notifiy parameters was no more optional since Pimp My Log 1.6"
			]
		},
		"1.7.5" : {
			"released" : "2014-12-03",
			"new" : [
				"Rick has hidden an Easter Egg. In december, I know... but will you find it?"
			],
			"fixed" : [
				"Bug with the open_basedir ini parameter set with non composer installations (<a href=\"http://support.pimpmylog.com/discussions/problems/68-trouble-with-alias\" target=\"_blank\">more info</a>)"
			],
			"changed" : [
				"Session rewrite to alert user if the PHP session path is not in the open_basedir ini parameter",
				"Admins can connect to the debugger without creating a lock file",
				"Add the changelog link in the footer (#90)",
				"Debugger configuration rights check now manages unallowed paths due to open_basedir restrictions",
				"Add the debugger link in the footer"
			]
		},
		"1.7.4" : {
			"released" : "2014-12-01",
			"fixed" : [
				"Ugly error on installations without authentication"
			]
		},
		"1.7.3" : {
			"released" : "2014-11-30",
			"fixed" : [
				"Fix PHP error log format for PHP 5.3 (#89)",
				"Fix a javascript error when viewing a user who is granted on a non-existing log file",
				"Signout was an UI infinite loop when the last anonymous file did not exist anymore"
			]
		},
		"1.7.2" : {
			"released" : "2014-11-29",
			"new" : [
				"Alert message when Suhosin PHP extension is loaded",
				"Enable the SAFE_MODE latch file to let users run Pimp My Log on ultra secured PHP installations (<a href=\"http://support.pimpmylog.com/kb/configuration/php-installations-with-suhosin-extension-and-exotic-ini-settings\" target=\"_blank\">more info</a>)"
			],
			"changed" : [
				"Glob files are now considered as unique for user access"
			],
			"fixed" : [
				"Fix the user management popup in Firefox (<a href=\"http://support.pimpmylog.com/discussions/suggestions/57-ihm-for-addupdate-logifile-to-scan\" target=\"_blank\">more info</a>)"
			]
		},
		"1.7.1" : {
			"released" : "2014-11-23",
			"changed" : [
				"Debugger : add new informations in the configuration panel",
				"User management : add tooltips to show the log file path",
				"Unit tests : add IIS and nginx logs"
			],
			"fixed" : [
				"PML now supports PHP installations without multibytes functions (mb_) in degraded mode",
				"Fix the modal scrolling when admin has to manage to many log files"
			]
		},
		"1.7.0" : {
			"released" : "2014-11-22",
			"new" : [
				"Install with composer is now operational, you can update without losing your configuration files (#85)"
			],
			"fixed" : [
				"Password recovery check on password length was buggy"
			]
		},
		"1.6.4" : {
			"released" : "2014-11-21",
			"changed" : [
				"Add commands to upgrade via git and via composer",
				"Add a copy to clipboard buttons for upgrade commands"
			]
		},
		"1.6.3" : {
			"released" : "2014-11-21",
			"new" : [
				"Install with composer"
			]
		},
		"1.6.2" : {
			"released" : "2014-11-21",
			"new" : [
				"Change autoupgrade message : add a link to the blog for the change log and more"
			]
		},
		"1.6.1" : {
			"released" : "2014-11-19",
			"fixed" : [
				"Tag sorting on PHP 5.2 and PHP 5.3"
			]
		},
		"1.6.0" : {
			"released" : "2014-11-18",
			"new" : [
				"Tag your log files and organize them in folders (#80)",
				"Auto-upgrade fr GIT installs (#81)"
			],
			"fixed" : [
				"Remove a debug trace (#83)",
				"Cannot scroll anymore in the log list (#82)",
				"Fix RSS and ATOM exports (#84)"
			]
		},
		"1.5.2" : {
			"released" : "2014-11-14",
			"fixed" : [
				"Add a text for the user menu when displayed on a mobile",
				"Restore PHP 5.2 support (<a href=\"http://support.pimpmylog.com/discussions/problems/52-php-fatal-error-undefined-class-constant-all-in-incglobalincphp-on-line-106\" target=\"_blank\">more info</a> and <a href=\"http://support.pimpmylog.com/discussions/problems/54-php-warning-unexpected-character-in-input-ascii92-state1-in-incglobalincphp-on-line-3xx\" target=\"_blank\">here</a>)"
			],
			"changed" : [
				"Display the export popup for all formats to let users copy the link instead of opening it directly",
				"Add an open button for all export formats",
				"Display JSON pretty print also for PHP 5.2 and 5.3",
				"Add a shortcut CTRL-R in the regex editor in the debugger to launch the test instead of clicking on the TEST button",
				"Add loaders for possible long operations",
				"Add log timezone support by default (<a href=\"http://support.pimpmylog.com/discussions/questions/33-date-with-timezone\" target=\"_blank\">more info</a>)"
			]
		},
		"1.5.1" : {
			"released" : "2014-11-14",
			"fixed" : [
				"Configuration file split fixed"
			]
		},
		"1.5.0" : {
			"released" : "2014-11-13",
			"new"      : [
				"Feature #64 : More logs",
				"Feature #61 : Authentication (Take a look <a href=\"http://support.pimpmylog.com/kb/configuration/enable-authentication-on-already-installed-instances\">here to enable authentication</a> on an existing instance)",
				"Feature #76 : Anonymous access to let guest view some logs and protect other logs for genuine users",
				"Feature #72 : Access token to secure exports and for API coming in v2.0",
				"Feature #71 : Admins can sign in as other users to check their accounts",
				"Feature #70 : Export logs in Atom, CSV, JSON, JSONP, Pretty JSON, RSS and XML",
				"Authentication : admin can view logs to check who sign in, sign out, ...",
				"Authentication : change password feature",
				"Authentication : reset access token in the profile menu",
				"Debugger : new debugger available at /inc/test.php",
				"Debugger : enable/disable authentication for the instance",
				"Debugger : reset a user password",
				"Debugger : new security management",
				"New global parameter <code>AUTH_LOG_FILE_COUNT</code> to set the maximum number of entries in the auth log file. Set this value to 0 if you want to disable authentication logs",
				"New global parameter <code>SORT_LOG_FILES</code> to sort your log files in the log selector on top left. Documentation is <a href=\"http://pimpmylog.com/documentation/configuration.html#SORT_LOG_FILES\">here</a>.",
				"New global parameter <code>EXPORT</code> to disable export feature globally",
				"New global parameter <code>FORGOTTEN_YOUR_PASSWORD_URL</code> to set the Password Forgotten link on the login page",
				"New configuration file with extension <code>.php</code> instead of <code>.json</code> to avoid direct calls in web browser and then people can see where are located your log files. <code>.json</code> files are still supported. Upgrading your configuration files is really straightforward, instructions are <a href=\"http://support.pimpmylog.com/kb/configuration/upgrade-json-configuration-files-to-php-configuration-files\">here</a>.",
				"New configuration parameter <code>export_title</code> to set the RSS title field per log file"
			],
			"fixed" : [
				"Apache 2.4 configurator could have problems to find logs format in some case, now Pimp My Log write a log and read it to parse its format"
			],
			"changed" : [
				"New pretty design with new Ubuntu font, I hope you like it",
				"Increase menu accessibility on mobile devices",
				"Start refactoring for version 2.0 with unit tests"
			]
		},
		"1.3.0" : {
			"released" : "2014-10-12",
			"new"      : [
				"Configuration file can be splitted in several files in subfolder <code>config.user.d</code>. Documentation is <a href=\"http://pimpmylog.com/documentation/configuration.html#USER_CONFIGURATION_DIR\">here</a>. (#62)",
				"New messaging system for development team... First message scheduled for version 1.3 :-)"
			],
			"fixed" : [
				"Remove debug logs while configuration",
				"Numeral language was not set correctly"
			],
			"changed" : [
				"Change design on some elements",
				"Upgrade Bootstrap to 3.2.0, jQuery to 2.1.1 and ZClip to 1.1.5",
				"Global refactoring to make it faster",
				"Remove LESS and use SASS now",
				"Loading icon is now animating (not in IE8 and IE9)"
			]
		},
		"1.2.1" : {
			"released" : "2014-09-21",
			"fixed" : [
				"Support non ASCII log files"
			]
		},
		"1.2" : {
			"released" : "2014-08-31",
			"new" : [
				"Column sorting #5"
			],
			"changed" : [
				"Left colored new log marker is displayed until new logs are displayed and is no more removed on next refresh"
			]
		},
		"1.1.1" : {
			"released" : "2014-07-25",
			"new" : [
				"You can now choose which columns to display at runtime #19"
			]
		},
		"1.1" : {
			"released" : "2014-07-14",
			"new" : [
				"New parser type prefake to simulate a pre field. This is a workaround for copy/paste logs from Firefox",
				"Log marker: click on the date field to toggle a row marker",
				"Log marker: new button in settings to clear all markers"
			],
			"fixed" : [
				"Brazilian Portuguese is now available in the settings menu",
				"You can now copy/paste all logs in Excel, formatting is preserved"
			]
		},
		"1.0.6" : {
			"released" : "2014-07-06",
			"fixed" : [
				"Add Brazilian Portuguese by Cassio Santos"
			]
		},
		"1.0.5" : {
			"released" : "2014-06-05",
			"fixed" : [
				"Apache 2.4 error in auto-configuation"
			]
		},
		"1.0.4" : {
			"released" : "2014-04-20",
			"fixed" : [
				"PHP error file now support referer",
				"Severity badges are case insensitive now, really this time !"
			]
		},
		"1.0.3" : {
			"released" : "2014-04-20",
			"new" : [
				"Severity badges are case insensitive now"
			]
		},
		"1.0.2" : {
			"released" : "2014-01-31",
			"changed" : [
				"Clean PHP code"
			]
		},
		"1.0.1" : {
			"released" : "2014-01-30",
			"changed" : [
				"Add a link in the upgrade message to the Pimp My Log upgrade documentation #56"
			],
			"new" : [
				"Add support for the LOCALE global parameter",
				"Add user settings button",
				"Add language selector user setting",
				"Add timezone selector user setting",
				"Add wide log table setting",
				"Now clicking on the logo will reload Pimp My Log with default user settings",
				"The url is automatically updated with the current user settings, so you can bookmark any view"
			]
		},
		"1.0.0" : {
			"released" : "2014-01-28",
			"fixed" : [
				"PHP Notice while configuration process when apache log file is empty #51"
			],
			"changed" : [
				"Several custom log file paths can be separated by a coma or by a new line now",
				"Configuration paths can now use globs",
				"Log table is now full width streched"
			],
			"new" : [
				"Support PHP logs #52",
				"Support NGINX server logs #53",
				"Support IIS server logs #54",
				"Add log type format in the footer"
			]
		},
		"0.9.9" : {
			"released" : "2014-01-22",
			"changed" : [
				"Clean code again and reduce files size",
				"You can now click on the logo in the debugger and in the configurator"
			],
			"new" : [
				"Add new global parameter TITLE_FILE to customize the page title according to the current displayed file #50",
				"Add a copy to clipboard action when configuring Pimp My Log",
				"PML can be launched with any log file by default, not the first defined only (use http://pml/?i=apache2 for example)"
			]
		},
		"0.9.8" : {
			"released" : "2014-01-20",
			"changed" : [
				"Clean code"
			],
			"fixed" : [
				"Wrong path for chrome notification image #49"
			]
		},
		"0.9.7" : {
			"released" : "2014-01-14",
			"new" : [
				"Copy the result of your debugger work to your clipboard. Then you just have to copy-paste in your configuration file."
			],
			"changed" : [
				"Update french language"
			],
			"fixed" : [
				"Unencoded file paths in the configuration process"
			]
		},
		"0.9.6" : {
			"notice" : "The separator feature in the match array has been replaced by a concatenation of all tokens and provided strings. Your configuration will not be broken but the displayed result will differ.",
			"released" : "2014-01-14",
			"new" : [
				"Multiline support closes (#46)",
				"Enable multiline and types customization in the debugger",
				"Debugger now supports exactly same features as the production parser",
				"Match array separator no longer exists and is replaced by a concatenator which is more powerful (#47)",
				"New file selector option (deactivate by default) to support several hundreds of log files (#45)"
			],
			"changed" : [
				"Change the upgrade url, github is really too slow..."
			],
			"fixed" : [
				"Fix a bug in the apache 2.4 configuration file"
			]
		},
		"0.9.5" : {
			"notice" : "The date format in the match array has changed. Please read documentation to upgrade your configuration file.",
			"released" : "2014-01-10",
			"changed" : [
				"Exclude object is optional now in configuration file",
				"New date format support when month is a number"
			]
		},
		"0.9.4" : {
			"released" : "2014-01-09",
			"fixed" : [
				"Change jsonp to js extension for IE9",
				"Fixed bootstrap 3.0.3 striped lines bug",
				"Add default color for undefined badges type",
				"Use strict mode in javascript",
				"Fix a bug when slash is in a date type format",
				"HTTP command should not be wrapped"
			]
		},
		"0.9.3" : {
			"released" : "2014-01-08",
			"fixed" : [
				"[Main] Desktop notifications broken"
			]
		},
		"0.9.2" : {
			"released" : "2014-01-01",
			"fixed" : [
				"[Configuration] Cannot add custom logs when no default path is available #43"
			]
		},
		"0.9.1" : {
			"released" : "2013-12-28",
			"new" : [
				"Add french translations #3"
			],
			"changed" : [
				"Upgrade check management #41 #42"
			],
			"fixed" : [
				"Language support in ajax requests"
			]
		},
		"0.9.0" : {
			"notice" : "First public pre-release of Pimp my Log!",
			"released" : "2013-12-22"
		}
	}
}
/*PSK*/);/*PSK*/
