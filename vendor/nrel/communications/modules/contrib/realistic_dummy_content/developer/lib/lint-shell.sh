#!/bin/bash
#
# Run linting on shell scripts.
#
set -e

BASEPATH="$(pwd)"

echo -e '[lint] About to lint bash scripts.'

# See https://github.com/dcycle/docker-shell-lint
cd "$BASEPATH"/.. && find . -name "*.sh" -print0 | \
  xargs -0 docker run -v "$(pwd)":/code dcycle/shell-lint
