#!/bin/sh

set -e

# Выполнение startup команд
if [ ! -z "$STARTUP_CMD" ]; then
  echo "Переменная STARTUP_CMD: $STARTUP_CMD"
  eval "$STARTUP_CMD"
fi
