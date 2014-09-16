#!/bin/bash

DELTA_HOUR=1 # nb of hours to remove
test -f "$1" && FILE="$1" || FILE="`zenity title='ICS file downloaded from ADExpert' --file-selection --filename=ADECal.ics`"

sed -i 's/\r//g' "$FILE" # remove LF chars
sed -i ':a;N;$!ba;s/\n //g' "$FILE" # remove new line in description
sed -i 's/DESCRIPTION:\\n/DESCRIPTION:/g' "$FILE" # remove first useless \n in desc

rm -f "$FILE-time"
while read -r LINE; do
	if [ "${LINE:0:7}" = "DTSTART" -o "${LINE:0:5}" = "DTEND" ]; then
		LENGTH=${#LINE}
		NEW_HOUR=${LINE:$LENGTH-7:2}
		FIRST_DIGIT=""
		[ "${NEW_HOUR:0:1}" = "0" ] && NEW_HOUR=${NEW_HOUR:1} && FIRST_DIGIT="0"
		TIME=$(($NEW_HOUR-$DELTA_HOUR))
		LINE=${LINE:0:$LENGTH-7}$FIRST_DIGIT$TIME${LINE:$LENGTH-5}
	fi
	echo "$LINE" >> "$FILE-time"
done < $FILE
mv "$FILE-time" "$FILE"

