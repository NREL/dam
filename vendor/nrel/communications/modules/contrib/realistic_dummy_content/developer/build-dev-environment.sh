#!/bin/bash
#
# Build a development environment.
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

which docker-compose > /dev/null 2> /dev/null || {
  echo -e "[error] Calling which docker-compose yielded an error."
  echo -e "        Please run this from within a machine which has Docker and"
  echo -e "        Docker Compose installed."
  exit 1;
}

./kill.sh

docker-compose build && \
docker-compose up \
  -d \
  --remove-orphans

SECONDS=15
echo -e "Waiting $SECONDS seconds for the database container to warm up."
sleep "$SECONDS"

for FRAMEWORK in $(/bin/ls frameworks | grep -v README); do
  ./exec.sh "$FRAMEWORK" '/resources/install.sh'
done

echo -e ""
echo -e "-----"
echo -e ""
echo -e "Congratulations! Your development environments are ready."
echo -e ""
./uli.sh
echo -e ""
echo -e "The same code for realistic_dummy_content can be used for"
echo -e "Drupal 7 and Drupal 8. Changes you make to the code at "
echo -e "$(pwd) will be reflected on both environments."
