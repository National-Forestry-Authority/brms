Autotitle module for Drupal
---------------------------

INTRODUCTION
-----------
  This module allows to automatically set node title from
   the heading tags in content (H1-H6).

INSTALLATION:
-------------
  1. Extract the tar.gz into your 'modules' directory or get it
     via composer: composer require drupal/autotitle.
  2. Go to "Extend" after successfully login into admin.
  3. Enable the module at 'administer >> modules'.

REQUIREMENTS
------------
  Autotitle is standalone module, and it has no dependencies
  or any additional requirements.

CONFIGURATION
-------------
  1. Go to /admin/structure/types/manage/[node_type].
  2. Under the tab "Autotitle" there is an option to enable automatic titles.
     Check it to enable autotitle functionality. After
     enabling it, the title field will be hidden on your node form,
     but you can revert it at any time by visiting
     /admin/structure/types/manage/[node_type]/form-display.
  3. The source field decides where the headings should be fetched from. The
     default  is the body field. Available fields are only
     string, string_* and text, text_* types
  4. You can set the fallback title for cases in which there was no heading
     found in the source field.

UNINSTALLATION
--------------
  1. Go to /admin/modules/uninstall and find autotitle module.
  2. Uninstall the module

MAINTAINERS
-----------
  Current maintainers:
   * Mariusz Andrzejewski (sayco) - https://www.drupal.org/u/sayco
   * Marco Fernandes (marcofernandes) - https://www.drupal.org/u/marcofernandes
