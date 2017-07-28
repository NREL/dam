#!/bin/bash
#
# Run linting on PHP.
#
set -e

BASEPATH="$(pwd)"

echo -e '[lint] About to lint php files.'

cd "$BASEPATH"/.. && docker run -v "$(pwd)":/code \
  dcycle/php-lint \
  --ignore=developer/tmp/* \
  --standard=Drupal \
  --report=full \
  /code

cd "$BASEPATH"/.. && docker run -v "$(pwd)":/code \
  dcycle/php-lint \
  --ignore=developer/tmp/* \
  --standard=Drupal \
  --report=full \
  /code/realistic_dummy_content.module

cd "$BASEPATH"/.. && docker run -v "$(pwd)":/code \
  dcycle/php-lint \
  --ignore=developer/tmp/* \
  --standard=Drupal \
  --report=full \
  /code/api/realistic_dummy_content_api.module
