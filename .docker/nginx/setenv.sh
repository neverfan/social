#!/usr/bin/env bash

cd "$(dirname "$0")"
source ./setenv.inc.sh

config=/etc/nginx/sites-available/site.conf
config_template=/etc/nginx/sites-available/site.conf.template
config_template_source1=/site.conf
config_template_source2=/site.conf.template

main
