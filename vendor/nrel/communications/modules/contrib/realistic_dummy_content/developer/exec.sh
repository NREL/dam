#/bin/bash
#
# Executes a command on a container.
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

if [ -z "$1" ]; then
  echo 'Please specify a container name as defined in docker-compose.yml.'
  exit 1
fi
if [ -z "$2" ]; then
  echo 'Please specify the command you would like to execute.'
fi

COMPOSECONTAINER="$1"
COMMAND="$2"

# The LXC driver does not support exec, see
# https://circleci.com/docs/docker/#docker-exec; the solution suggested in
# the documentation does not work, so we will try a different approach:
# Because Circle uses LXC and does not support exec, we'll use
# our run scripts instead. Instead of executing a command on an
# existing container, run-*.sh will create a new
# container linked to our database, run a command, and destroy the
# container thereafter.
./frameworks/"$COMPOSECONTAINER"/exec.sh "$COMMAND"
