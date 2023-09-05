#!/bin/bash

# $1 = URL
# $2 = Look for this string in the response (optional)
# $3 = Push URL for the Uptime Kuma
# $4 = Basic Auth (optional)

is_service_up() {
    local url=$1
    local keyword=$2

    if [[ -n "$keyword" ]]; then
        curl -sL -u $auth "$url" | grep -q "$keyword"
    else
        [[ $(curl -sL -u $auth -o /dev/null -w "%{http_code}" "$url") -eq 200 ]]
    fi

    return $?
}

auth=${4:-':'}
if is_service_up "$1" "$2" "$auth"; then
    request_time_ms=$(curl -u $auth -sLo /dev/null -w "%{time_total}" "$1" | awk '{printf "%.0f\n", $1 * 1000}')
    curl -s -o /dev/null "$3&ping=$request_time_ms"
fi
