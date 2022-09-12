#!/bin/bash

VERSION_FILE=../version.php

function get_current_version() {
	grep "^\$plugin->version" ${VERSION_FILE} | rev | cut -d ' ' -f 1 | rev | cut -d ';' -f 1
}

function update_version_number() {
	local current=$(get_current_version)
	local new=$(date "+%Y%m%d")"00"
	[[ ${current} -ge ${new} ]] && new=$((current+1))
	echo "Going from version $current to version $new."
	sed -i "s/\$plugin->version.*$/\$plugin->version   = ${new};/" ${VERSION_FILE}
}

update_version_number

# EOF
