#!/bin/bash
echo "Apache now ready for work! Starting Apache...";
source /etc/apache2/envvars
exec /usr/sbin/apache2 -D FOREGROUND
