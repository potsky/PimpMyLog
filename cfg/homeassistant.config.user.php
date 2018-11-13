<?php
function homeassistant_get_config( $type , $file , $software , $counter ) {
    if ( $type == 'log' ) {
        return<<<EOF
            "$software$counter": {
                "display" : "Home Assistant #$counter",
                "path"    : "$file",
                "refresh" : 5,
                "max"     : 20,
                "format"  : {
                    "regex": "/([0-9]*-[0-9]*-[0-9]*) ([0-9]*:[0-9]*:[0-9]*) ([A-Za-z]*) \\\\((.*)\\\\) \\\\[(.*)\\\\] (.*)/m",
                    "export_title" : "Log",
                    "match": {
                            "Date"      : 1,
                            "Time"      : 2,
                            "Severity"  : 3,
                            "Thread"    : 4,
                            "Component" : 5,
                            "Message"   : 6
                    },
                    "types": {
                            "Date"      : "date:Y/m/d",
                            "Time"      : "date:H:i:s",
                            "Severity"  : "badge:severity",
                            "Thread"    : "txt",
                            "Component" : "txt",
                            "Message"   : "txt"
                    }
                }
            }
EOF;
        }
    }
?>