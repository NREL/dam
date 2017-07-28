#/bin/bash
#
# Self-tests.
#
set -e

./exec.sh drupal7 'drush eval "realistic_dummy_content_api_selftest()"'
./exec.sh drupal7 \
  "drush en -y simpletest && \
  php ./scripts/run-tests.sh \
    --class \
    --url http://localhost \
    --verbose \
    RealisticDummyContentDatabaseTestCase"

./exec.sh drupal7 'drush generate-realistic'

./exec.sh drupal7 'drush -y dis realistic_dummy_content'
./exec.sh drupal7 'drush -y dis realistic_dummy_content_api'
./exec.sh drupal7 'drush -y pm-uninstall realistic_dummy_content'
./exec.sh drupal7 'drush -y pm-uninstall realistic_dummy_content_api'
