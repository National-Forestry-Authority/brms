INTRODUCTION
------------

The Views Striping module adds CSS classes to a view's rows to create stripes
of alternating colours.

Drupal core's themes have the required CSS styles already defined. You may need
to define CSS stylings for the 'odd' and 'even' classes in other themes.

The following striping types are provided:

- alternating: stripes that switch each row
- field value: stripes that switch each time the value of a particular field
  changes

This works for the following view styles:

- table
- aggregated table from https://www.drupal.org/project/views_aggregator

REQUIREMENTS
------------

This module requires no modules outside of Drupal core.

INSTALLATION
------------

1. Install as you would normally install a contributed Drupal module. Visit
   https://www.drupal.org/node/895232/ for further information.
2. Visit the Views advanced settings at /admin/structure/views/settings/advanced
3. In the 'Display Extenders' section, enable the 'Row striping' display
   extender.
4. Edit your view.
5. In the Table settings, select one of the striping types.
