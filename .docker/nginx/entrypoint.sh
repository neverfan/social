#!/bin/bash

/tmp/setenv.sh
crond -l 0 -L /var/log/cron.log
nginx 1> >(tee -a /var/log/nginx/access.log) 2> >(tee -a /var/log/nginx/error.log >&2)