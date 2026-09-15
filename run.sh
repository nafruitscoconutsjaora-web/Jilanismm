#!/usr/bin/env bash
set -e

# Start MariaDB service if not running
service mariadb status >/dev/null 2>&1 || service mariadb start

# Ensure Apache logs and directories exist
mkdir -p /var/log/apache2
mkdir -p /app/applet/storage/logs
mkdir -p /app/applet/storage/sessions
chmod -R 777 /app/applet/storage

# Run Apache in foreground (ignores any CLI arguments like --port or --host passed by npm)
exec /usr/sbin/apache2ctl -D FOREGROUND
