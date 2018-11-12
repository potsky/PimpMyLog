<?php
function homeassistant_get_config( $type , $file , $software , $counter ) {
    if ( $type == 'log' ) {
        return<<<EOF
            "$software$counter": {
                "display" : "Home Assistant #$counter",
                "path"    : "$file",
                "refresh" : 5,
                "max"     : 20,
                "regex": "/(.*) (.*) (.*)",
                "match": {
                        "Date"    : 1,
                        "Time"    : 2,
                        "Message" : 3
                },
                "types": {
                        "Date"    : "txt",
                        "Time"    : "txt",
                        "Message" : "txt"
                }
            }
EOF;
        }
    }
?>