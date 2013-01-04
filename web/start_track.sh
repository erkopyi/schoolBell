#!/bin/bash

if [ "$1" ]; then
    SONG=$1
else
        exit 1
fi
echo $1
cd /data/projects/schoolBell/files
/usr/bin/mpg321 -a hw:1,0 "${SONG}"  >/dev/null 2>&1 &
exit 0
