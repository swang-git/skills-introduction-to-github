#!/bin/bash
echo "==================== PHP STATUS REPORT ===================="
echo "[1] Shell default php:"
which php
php -v 2>/dev/null

echo -e "\n[2] All php in PATH:"
type -a php 2>/dev/null

echo -e "\n[3] Brew PHP services:"
brew services list | grep php

echo -e "\n[4] Locate php-fpm binary from brew php:"
if [[ -d /opt/homebrew/opt/php ]]; then
    FPM_BIN="/opt/homebrew/opt/php/sbin/php-fpm"
elif [[ -d /usr/local/opt/php ]]; then
    FPM_BIN="/usr/local/opt/php/sbin/php-fpm"
else
    FPM_BIN=""
fi
if [[ -n "$FPM_BIN" && -x "$FPM_BIN" ]]; then
    echo "$FPM_BIN"
    $FPM_BIN -v 2>/dev/null
else
    echo "brew php fpm binary not found"
fi

echo -e "\n[5] Running php-fpm processes:"
ps aux | grep -i php-fpm | grep -v grep

echo -e "\n[6] Apache loaded php modules (mod_php check):"
httpd -M 2>/dev/null | grep -i php || echo "No mod_php loaded or httpd not present"

echo -e "\n[7] Check php-fpm listen port 9000:"
lsof -i :9000 2>/dev/null || echo "Nothing on :9000"

echo -e "\n[8] php.ini (cli):"
php --ini 2>/dev/null
echo "============================================================"
