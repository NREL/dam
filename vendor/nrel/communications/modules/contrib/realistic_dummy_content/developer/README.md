This directory contains resources if you want to participate in the
development of realistic_dummy_content.

Prerequisites for the use of scripts herein are the latest versions of
Docker and Docker Compose.

For all scripts, your working directory needs to be `./developer/`.

Among the useful scripts are:

 * `./test.sh`: Run all tests.
 * `./test.sh fast`: Run only fast tests.
 * `./build-dev-environment.sh`: Build development
   environments and get one-time login links to the environments.
 * `./kill.sh`: Kill development environments.
 * `./exec.sh drupal7 'drush cc all'`: Run a Drush command
   (in this example clear cache) on Drupal 7.
 * `./exec.sh drupal8 'drush cr'`: Run a Drush command
   (in this example rebuild cache) on Drupal 8.
 * `./uli.sh`: Get one-time login links for all environments.
