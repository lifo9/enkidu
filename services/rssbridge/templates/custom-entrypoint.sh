#!/usr/bin/env bash

ORIGINAL_APP_DIR="/app"
APP_DIR="/tmp/app"

# fix app permissions
cp --no-preserve=ownership -r "$ORIGINAL_APP_DIR" "$APP_DIR"

# - Find custom files (bridges, formats, whitelist, config.ini) in the /config folder
# - Copy them to the respective folders in $APP_DIR
# This will overwrite previous configs, bridges and formats of same name
# If there are no matching files, rss-bridge works like default.

find /config/ -type f -name '*' -print0 2>/dev/null |
    while IFS= read -r -d '' file; do
        file_name="$(basename "$file")" # Strip leading path
        if [[ $file_name = *" "* ]]; then
            printf 'Custom file %s has a space in the name and will be skipped.\n' "$file_name"
            continue
        fi
        case "$file_name" in
        *Bridge.php)
            yes | cp "$file" "$APP_DIR/bridges/"
            printf "Custom Bridge %s added.\n" $file_name
            ;;
        *Format.php)
            yes | cp "$file" "$APP_DIR/formats/"
            printf "Custom Format %s added.\n" $file_name
            ;;
        config.ini.php)
            yes | cp "$file" "$APP_DIR/"
            printf "Custom config.ini.php added.\n"
            ;;
        whitelist.txt)
            yes | cp "$file" "$APP_DIR/"
            printf "Custom whitelist.txt added.\n"
            ;;
        DEBUG)
            yes | cp "$file" "$APP_DIR/"
            printf "DEBUG file added.\n"
            ;;
        esac
    done

# nginx will daemonize
nginx -c /nginx.conf

# php-fpm should not daemonize
php-fpm8.2 --nodaemonize
