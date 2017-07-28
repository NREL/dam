#!/bin/bash
#
# Install Drupal 7
#

set -e

cp -r /sites-default/* sites/default

# In order to prevent the "unable to send mail" error, we are including
# the "install_configure_form" line, which itself forces us to include the
# profile (standard), which would otherwise be optional. See the output
# of "drush help si" from where this is taken.

drush si \
  -y \
  --db-url=mysql://root:@database/drupal \
  --account-name=admin \
  --account-pass=admin \
  standard \
  install_configure_form.update_status_module='array(FALSE,FALSE)'

drush en realistic_dummy_content devel_generate devel -y
