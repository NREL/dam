#/bin/bash
#
# Runs a command on a throwaway container.
# Use this instead of exec, because we want to avoid "polluting" our
# containers with exec, and the LXC driver used by Circle does not support
# exec.
#
set -e

# Used in ./lib/prepare-run.source, below
export COMPOSERCONTAINER=drupal8

if [ -z "$1" ]; then
  echo 'Please specify a command to run.'
  exit 1
fi
COMMAND="$1"

source ./lib/prepare-run.source

# We are linking the names of the containers in the context of the network,
# not the absolute container names that we can retrieve via docker ps.
docker run \
  -v "$(pwd)"/../:/var/www/html/modules/realistic_dummy_content \
  -v "$(pwd)"/tmp/drupal8:/var/www/html/sites/default \
  --link database:database \
  -w /var/www/html \
  --net "$NETWORK" \
  "$IMAGE" /bin/bash -c "$COMMAND"
