#!/bin/bash

#Stops if a command fails or variable is missing or something, idk (thank you random guy on stackoverflow)
set -euo pipefail

#loading our api
source /etc/eventplanner/ticketmaster.env

#this is where our data is saved
OUTPUT_DIR="/opt/eventplanner"
OUTPUT_FILE="$OUTPUT_DIR/events.json"

mkdir -p "$OUTPUT_DIR"

echo "Starting Ticketmaster update: $(date)"

#Download events from ticketmaster
#first saving to temp file so a failed request wont ruin the entire thing lol
TEMP_FILE=$(mktemp "$OUTPUT_DIR/events.XXXXXX")
trap 'rm -f "$TEMP_FILE"' EXIT

curl --fail --silent --show-error \
	--retry 3 \
	--max-time 30 \
	--get \
	"https://app.ticketmaster.com/discovery/v2/events.json" \
	--data-urlencode "apikey=$TM_API_KEY" \
	--data-urlencode "countryCode=US" \
	--data-urlencode "size=100" \
	--output "$TEMP_FILE"

#now checking if our response contains an event array
jq -e '._embedded.events | type == "array"' "$TEMP_FILE" > /dev/null

#now itll replace existing data after a successful validation
mv "$TEMP_FILE" "$OUTPUT_FILE"

echo "Ticketmaster data updated successfully: $(date)"
echo "Events saved: $(jq '._embedded.events | length' "$OUTPUT_FILE")"
