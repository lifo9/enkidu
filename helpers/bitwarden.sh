#!/bin/sh
set -e
export BW_SESSION=$(bw unlock --passwordenv BW_PASSWORD --raw)
[ -z "$BW_SESSION" ] && exit 1
BW_COMMAND="bw --session $BW_SESSION"
$BW_COMMAND sync
unset BW_SESSION

get_custom_field() {
  ITEM=$($BW_COMMAND get item "$1")
  CUSTOM_FIELDS=$(echo "$ITEM" | jq '.fields[]')
  echo "$CUSTOM_FIELDS" | jq --arg name "$2" 'select(.name==$name) | .value' | tr -d '"'
}

get_username() {
  echo "$($BW_COMMAND get username "$1")"
}

get_password() {
  echo "$($BW_COMMAND get password "$1")"
}
