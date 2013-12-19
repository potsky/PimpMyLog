<?php
passthru('cd ..; PATH=/usr/bin:/bin:/usr/sbin:/sbin:/usr/local/bin:/opt/local/bin; export PATH; . ../../../.profile; sudo -u potsky /usr/local/bin/grunt save build');
