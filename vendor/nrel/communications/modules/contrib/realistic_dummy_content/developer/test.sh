#!/bin/bash
#
# Runs some linting and unit tests, then creates instances Drupal 7, 8 and
# Backdrop 1, and runs some tests on those environments.
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

echo -e '[info] Test preflight.'
./lib/preflight.sh
echo -e '[info] Lint PHP.'
./lib/lint-php.sh
echo -e '[info] Lint shell.'
./lib/lint-shell.sh
echo -e '[info] Unit tests.'
./lib/unit.sh

if [ "$1" == "fast" ]; then
  echo -e '[info] You specified "fast" so we are exiting after fast checks'
  echo -e '       such as linting and unit tests. Full integration tests with'
  echo -e '       real environments can be performed if you run this script'
  echo -e '       without the fast parameter.'
  echo -e ''
  exit 0;
else
  echo -e '[info] You can specify "fast" to run only fast tests.'
fi

echo -e '[info] Build development environments.'
./build-dev-environment.sh

echo -e '[info] Running self-tests.'
for FRAMEWORK in $(/bin/ls frameworks | grep -v README); do
  echo -e "[info] Self-test for $FRAMEWORK."
  ./frameworks/"$FRAMEWORK"/selftest.sh
done
