#!/usr/bin/env sh
set -euxo pipefail

while true
do
	if test -f "/config/Main.php"; then
		unique_key=$(grep application_unique_key /config/Main.php | awk -F"'" '{print $2}')
		wget -qO- http://crm/cron.php?app_key=${unique_key}
	fi

	sleep 60
done
