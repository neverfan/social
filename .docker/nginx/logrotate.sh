#!/bin/bash

TMP=$(tail -n100000 /var/log/nginx/access.log)
echo $TMP > /var/log/nginx/access.log

TMP=$(tail -n100000 /var/log/nginx/error.log)
echo $TMP > /var/log/nginx/error.log
