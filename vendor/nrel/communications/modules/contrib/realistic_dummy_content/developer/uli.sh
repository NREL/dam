#!/bin/bash
#
# Get login links to all environments.
#

set -e

BASEPATH="$(pwd)"
SCRIPTNAME="$(basename "$0")"

if [ ! -f "$BASEPATH"/"$SCRIPTNAME" ]; then
  echo -e '[error] Please run this script from the developer directory of'
  echo -e '        realistic_dummy_content, like this:'
  echo -e ''
  echo -e '            cd /path/to/realistic_dummy_content/developer'
  echo -e "            ./$SCRIPTNAME"
  echo -e ''
  exit 1;
fi

for FRAMEWORK in $(/bin/ls frameworks | grep -v README); do
  DOMAIN=$(docker-compose port "$FRAMEWORK" 80)
  echo -e "To log into your $FRAMEWORK environment go to:"
  echo -e ""
  echo -e ' ==> '"$(./exec.sh "$FRAMEWORK" 'drush uli')"|sed "s/default/$DOMAIN/g"
  echo -e ""
done
