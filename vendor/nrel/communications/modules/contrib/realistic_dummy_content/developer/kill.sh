#!/bin/bash
#
# Kill the development environment if it exists.
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

if [ -d "$BASEPATH"/tmp ]; then
  chmod -R +w ./tmp
  rm -rf ./tmp
fi

docker-compose kill
