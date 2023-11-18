#!/bin/bash

###########################################################
#
#   This is an optional script which can be run on a cron to clean up expired files
#
###########################################################

textDropScript="drop.php"

#set -e

timeout=$(php -r ' include("'${textDropScript}'"); echo $maxLength; ' 2> /dev/null)
dir=$(php -r ' include("'${textDropScript}'"); echo $filePath; ' 2> /dev/null)
currentTime=$(date +"%s")

# loop over all files in our temp path
for file in $(find $dir -type f)
do
  # Get the created time from the json blob
  createdTime=$(cat $file | jq -r .time 2> /dev/null)

  # Is this an integer?  There may be other files in the directory
  if [[ "$createdTime" =~ ^[0-9]+$ ]]
  then
      expireTime=$((createdTime+timeout))

      if [ "$expireTime" -lt "$currentTime" ]
      then
        echo "cleanup ${file}"
        rm ${file}
      fi
  fi
done
