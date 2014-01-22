/*PSK*/pml_version_cb(/*PSK*/
{
	"version"     : "0.9.9",
	"versiondevh" : "3e01c3256951e084ecedfb16a130288885565637",
	"changelog" : {
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
