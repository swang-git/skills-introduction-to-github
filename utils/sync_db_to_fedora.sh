#!/bin/zsh
set -euo pipefail

MARIADB_DUMP="/opt/homebrew/bin/mariadb-dump"
MARIADB_CLIENT="/opt/homebrew/bin/mariadb"
DB_NAME="prod"
FEDORA_IP="192.168.1.110"
TMP_DUMP="/tmp/dbsync_tmp.sql"
LOG_FILE="/Users/swang/tmp/logs/cn/dump_prod.log_$(date +%a)"

exec >>"$LOG_FILE" 2>&1

echo
echo "=== start at $(date) ==="
echo "User: $(id -un)"
echo "Home: ${HOME}"
#echo "Path: ${PATH}"
#echo ENV: `env`


echo "Testing ping to ${FEDORA_IP}"
if ping -c 3 -W 3 "${FEDORA_IP}"; then
    echo "PING OK"
else
    echo "PING FAILED host unreachable"
    exit 1
fi

echo "Testing TCP port 3306 ${FEDORA_IP}"
if nc -zv -w 5 "${FEDORA_IP}" 3306; then
    echo "TCP 3306 OPEN"
else
    echo "TCP 3306 FAILED"
    exit 1
fi

echo "Step 1: local dump to temp file"
"$MARIADB_DUMP" -uswang -pYbsjll11 -B "${DB_NAME}" --routines > "${TMP_DUMP}"

echo "Step 2: import temp file to remote fedora"
"$MARIADB_CLIENT" -uswang -pYbsjll11 -h"${FEDORA_IP}" < "${TMP_DUMP}"

echo "Step3: clean temp file"
# rm -f "${TMP_DUMP}"

echo "Pipeline completed OK"
