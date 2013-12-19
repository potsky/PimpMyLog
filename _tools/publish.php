<?php
header("Content-Type: text/plain");

# sudoer
# %_www   ALL=(potsky) NOPASSWD: /usr/local/bin/grunt
passthru('cd ..; PATH=/usr/bin:/bin:/usr/sbin:/sbin:/usr/local/bin:/opt/local/bin; export PATH; . ../../../.profile; sudo -u potsky /usr/local/bin/grunt save build install 2>&1');
