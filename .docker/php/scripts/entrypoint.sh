#!/bin/sh

set -e

/usr/local/scripts/env.sh
/usr/local/scripts/setup.sh
/usr/local/scripts/startup.sh

# from docker-php-entrypoint
# first arg is `-f` or `--some-option`
if [ "${1#-}" != "$1" ]; then
	set -- php "$@"
fi

exec "$@"
