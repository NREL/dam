/**
 * @file
 * README file for file_download_tracker.
 */

CONTENTS
--------

1. Introduction
2. Requirements
3. Installation
4. Usage
5. Configuration
6. Maintainers

1. INTRODUCTION
---------------
* This module will track the downloads for each files and pages.
* Generating the reports for per file downloads, per page downloads and details regarding users downloading the files
  directly as well as files getting downloaded from pages.
* Providing seperate menu links for per file downloads report and per page downloads report.

2. REQUIREMENTS
---------------
This module requires the following module:

 * file_entity (https://drupal.org/project/file_entity)

3. INSTALLATION
---------------

* Install as you would normally install a contributed Drupal module. See:
  https://www.drupal.org/docs/8/extending-drupal-8/installing-contributed-modules-find-import-enable-configure-drupal-8
  for further information.

4. Usage:
---------
  * Add a file field to any content type.
  * In manage display, change the format from Generic file to download link.
  * Then, when the user clicks on the file, that time file is going to be tracked.
  * That tracked informations will be displayed in views.
  * The menu links for the reports is given below.
  * Menu Links.
    - Per File Download: /file-download-tracker-file-report
    - Per Page Download: /file-download-tracker-page-report
  * We can dispatch the event in two ways.
    - Core file download link (using hook_file_download())
    - file entity download link (using hook_file_transfer())
  * You can also create new view using the entity type (file_download_entity) .

5. CONFIGURATION
----------------
  * There is no configuration settings here.

6. MAINTAINERS
--------------
Current maintainers:
   * Chirag Shah (chishah92) - https://drupal.org/user/2866197
   * Padma Priya Suriyaprakash (padma28) - https://drupal.org/user/2637281
   * Rajeshwari Variar (rajeshwari10) - https://drupal.org/user/3270754

