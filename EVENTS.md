EVENTS (ACTIONS / FILTERS)
==========================
The Fox CMS differentiates between actions and filters. Actions can only be executed once, are
placed on specific execution points, shouldn't return anything and doesn't require any parameter.
Filters can be used as often as needed (or as often as you want), can be placed everywhere and
requires at least a single parameter, which is returned filtered.

Below you will find almost all Fox CMS Actions and Filters as well as all Wolf CMS Observer
Actions, which are also available through the Fox CMS own `Event` class. Fox CMS Events, which
starts with a single \_ (underscore) are used for development or internal stuff and should NOT
be used / hooked by custom functions.


Fox CMS Actions
---------------
### `cronjob-{cron}-start`
Before a cron gets executed, `{cron}` gets replaced by the respective unique cron name.
-   File: `system/models/class.cron.php`
-   Parameters: -

### `cronjob-{cron}-end`
After a cron has been executed, `{cron}` gets replaced by the respective unique cron name.
-   File: `system/models/class.cron.php`
-   Parameters: -


Fox CMS Filters
---------------
### `cronjob-{cron}-args`
Pass an empty array and should return an array, which can be passed to the cron job itself.
Each returning non-array type prevents the execution of the respective cronjob function. `{cron}`
gets replaced by the respective unique cron name.
-   File: `system/models/class.cron.php`
-   Parameters:
    -   `(array)` An empty array for your cronjob function arguments.

### `controller-{controller}-{action}`
Pass the page, which should be displayed by the `{action}` method of the `{controller}` class.
For Example: This filter allows you to change the default login or administration settings page.
-   File: `system/controllers/class.login-controller.php`
-   Parameters:
    -   `(string)` The relative page path to display.


Wolf CMS Actions
----------------
### `cron_run`
This Observer is used to execute cronjobs in the "Wolf CMS" - ways. You should use the cronjob
functions itself, which are provided by the Cron class, instead of this observer!
-   File: `system/models/class.cron.php:66`
-   Parameters: -

### `login_required`
Allows Plugins to execute some stuff on the administration login page.
-   File: `system/controllers/class.login-controller.php`
-   Parameters:
    -   `(string)` The redirect string, which has been passed through the Flash.

### `login_requested`
Allows Plugins to execute some stuff before the requested login gets handled.
-   File: `system/controllers/class.login-controller.php`
-   Parameters:
    -   `(string)` The redirect string, which has been passed through the Flash.

### `admin_login_success`
Allows Plugins to execute some stuff after the login has been successfully finished
-   File: `system/controllers/class.login-controller.php`
-   Parameters:
    -   `(string)` The used username on the login form.

### `admin_login_failed`
Allows Plugins to execute some stuff after the login has NOT been successfully finished
-   File: `system/controllers/class.login-controller.php`
-   Parameters:
    -   `(string)` The used username on the login form.

### `logout_requested`
Allows Plugins to execute some stuff before the requested logout gets handled.
-   File: `system/controllers/class.login-controller.php`
-   Parameters: -

### `admin_after_logout`
Allows Plugins to execute some stuff after the logout could be handled successfully.
-   File: `system/controllers/class.login-controller.php`
-   Parameters:
    -   `(string)` The username of the logged-out user.

### `layout_after_{action}`
This hook is called on the respective controller and after the layout {action}s "add", "edit" or
"delete" gets called.
-   File: `system/controllers/class.layout-controller.php`
-   Parameters:
    -   `(object)` The respective Layout object instance.

### `snippet_after_{action}`
This hook is called on the respective controller and after the snippet {action}s "add", "edit" or
"delete" gets called.
-   File: `system/controllers/class.snippet-controller.php`
-   Parameters:
    -   `(object)` The respective Snippet object instance.

### `user_after_{action}`
This hook is called on the respective controller and after the user {action}s "add", "edit" or
"delete" gets called.
-   File: `system/controllers/class.user-controller.php`
-   Parameters:
    -   `(object)` The respective User object instance.

### `page_{action}_{type}_save`
This hook is called on the respective controller and before as well as after ({type}) the page
{action}s "add" or "edit" gets called.
-   File: `system/controllers/class.page-controller.php`
-   Parameters:
    -   `(object)` The respective Page object instance.

### `page_delete`
This hook is called on the respective controller and after the page delete action is called.
-   File: `system/controllers/class.page-controller.php`
-   Parameters:
    -   `(object)` The respective Page object instance.

### `part_{action}_{type}_save`
This hook is called on the respective controller and before as well as after ({type}) the part
{action}s "add" or "edit" gets called.
-   File: `system/controllers/class.page-controller.php`
-   Parameters:
    -   `(object)` The respective Part object instance.

### `part_delete`
This hook is called on the respective controller and after the part delete action is called.
-   File: `system/controllers/class.page-controller.php`
-   Parameters:
    -   `(object)` The respective Part object instance.

### `plugin_after_enable`
-   File: `system/controllers/class.setting-controller.php`
-   Parameters:
    -   `(string)` The respective / unique plugin ID.

### `plugin_after_disable`
-   File: `system/controllers/class.setting-controller.php`
-   Parameters:
    -   `(string)` The respective / unique plugin ID.

### `plugin_after_uninstall`
-   File: `system/controllers/class.setting-controller.php`
-   Parameters:
    -   `(string)` The respective / unique plugin ID.

### `user_edit_view_after_details`
This hook gets called AFTER the basic output is rendered. It allows to add custom user fields on
the respective administration page.
-   File: `system/views/user/edit.php`
-   Parameters:
    -   `(object)` The respective User object instance.

### `view_page_edit_tab_links`
-   File: `system/views/page/edit.php`
-   Parameters:
    -   `(object)` The respective Page object instance
    -   `(array)` An array with all respective PagePart object instanced
    -   `(string)` The current action `add` or `edit`.

### `view_page_edit_tabs`
-   File: `system/views/page/edit.php`
-   Parameters:
    -   `(object)` The respective Page object instance
    -   `(array)` An array with all respective PagePart object instanced
    -   `(string)` The current action `add` or `edit`.

### `view_page_after_edit_tabs`
-   File: `system/views/page/edit.php`
-   Parameters:
    -   `(object)` The respective Page object instance
    -   `(array)` An array with all respective PagePart object instanced
    -   `(string)` The current action `add` or `edit`.

### `view_page_edit_plugins`
-   File: `system/views/page/edit.php`
-   Parameters:
    -   `(object)` The respective Page object instance
    -   `(array)` An array with all respective PagePart object instanced
    -   `(string)` The current action `add` or `edit`.

### `view_page_edit_popup`
-   File: `system/views/page/edit.php`
-   Parameters:
    -   `(object)` The respective Page object instance
    -   `(array)` An array with all respective PagePart object instanced
    -   `(string)` The current action `add` or `edit`.

### `view_backend_layout_head`
-   File: `system/layout/backend.php`
-   Parameters: -

### `view_backend_list_plugin`
-   File: `system/layout/backend.php`
-   Parameters:
    -   `(string)` The unige plugin ID.
    -   `(object)` The plugin object.

### `dispatch_route_found`
-   File: `system/class.fox.php`
-   Parameters:
    -   `(string)` The requested path.

### `page_requested`
This Event pass the current requested path and expects a path back!
-   File: `system/class.fox.php`
-   Parameters:
    -   `(string)` The requested path.

### `page_found`
-   File: `system/class.fox.php`
-   Parameters:
    -   `(object)` The requested page object.

### `page_not_found`
-   File: `system/func.fox.php`
-   Parameters:
    -   `(string)` The requested URL.
