CONSTANTS
=========

System Data
-----------
### START
This constant stores the microtime, where the Fox CMS script starts.

### FOXCMS
This constant stores the respective view, which is currently called: 'focxms' for all normal page
calls, 'cron' for **real** cronjob calls and 'wizard' for the Fox CMS Wizard.

### DS
This constant stores the platform-depended directory separator, which should be "/" on the most
platforms except on Windwos, which uses a single "\\".

### FOX_VERSION
This constant stores the installed / used Fox version.

### FOX_STATUS
This constant stores the installed / used Fox status string.

### FOX_CODE
This constant stores the installed / used Fox build code.

### CMS_VERSION
This constant stores the installed / used Fox Version, with the status, which can be used for
semantic versioning comparison.

### Fox Environment Steps
#### FOX_INIT
This constant just shows if init method of the core Fox class has been called or not.

#### FOX_CHECK
This constant just shows if check method of the core Fox class has been called or not.

#### FOX_LOAD
This constant just shows if load method of the core Fox class has been called or not.

#### FOX_RENDER
This constant just shows if render method of the core Fox class has been called or not.

Database Settings
-----------------
### DB_TYPE
```php
# Default Value
define("DB_TYPE", (string) "mysql");
```
The used DataBase type: Choose between MySQL / MariaDB (`mysql`), PostgreSQL (`pgsql`) and SQLite3
(`sqlite`). Other DataBase systems are NOT supported!

### DB_HOST
```php
# Default Value
define("DB_HOST", (string) "localhost");
```
The DataBase host name on MySQL / MariaDB and PostgreSQL DataBase types. Leave it empty, if you are
using SQLite3!

### DB_PORT
```php
# Default Value
define("DB_PORT", (int) (DB_TYPE == "mysql")? 3306: 5432);
```
The DataBase port on MySQL / MariaDB and PostgreSQL DataBase types. Leave it empty, if your are
using SQLite3.

### DB_SOCKET
```php
# Default Value
define("DB_SOCKET", NULL);
```
The DataBase socket instead of the host name / port number on MySQL / MariaDB and PostgreSQL
DataBase types. Leave it empty, if your are using SQLite3.

### DB_USER
```php
# Default Value
define("URL_USER", NULL);
```
The DataBase username on MySQL / MariaDB and PostgreSQL DataBase types, it's recommended to don't
use root, admin, postrgre or other administrator accounts on productive system! Leave it empty, if
your are using SQLite3.

### DB_PASS
```php
# Default Value
define("DB_PASS", NULL);
```
The DataBase password, which fits to the username, on MySQL / MariaDB and PostgreSQL DataBase types.
It's recommended to **don't** use an DB User-Account with a weak (or completely without a) password.
Leave it empty, if your are using SQLite3.

### DB_NAME
```php
# Default Value
define("DB_NAME", (string) "foxcms");
```
The DataBase name where the Fox CMS is installed on on MySQL / MariaDB and PostgreSQL DataBase type.
Write in the **absolute** path to the DataBase file on SQLite3, it's recommend to store the SQLite3
file outside of the webservers root directory (Make sure, that the webserver has read and write
permissions on the respective folder / file)!

### DB_PREFIX
```php
# Default Value
define("DB_PREFIX", (string) "fox_");
```
The database table prefix on MySQL / MariaDB and PostgreSQL DataBase types. The Prefix **should**
end with an **_** or and **-**! Leave if it empty, if your are using SQLite3.


Core Settings
-------------
### FOX_PUBLIC
```php
# Default Value
define("FOX_PUBLIC", (string) "");
```
The public URL to your Fox Installation, for example: "http://www.example.org". Please **don't**
use this constant in your scripts / themes, you **should** always use `PUBLIC_URL` to reference
to your base Website URL!

### ADMIN_DIR
```php
# Default Value
define("ADMIN_DIR", (string) "admin");
```
The virtual admin directory of your Fox CMS installation, make sure that this folder **does NOT**
use the same name as a basic Fox CMS directory! This constant should also don't start or end with
slashes!

### URL_SUFFIX
```php
# Default Value
define("URL_SUFFIX", (string) ".html");
```
A suffix for your URLs, like `.html` to emit static pages or `.asp` to emit ~~Microsoft trash~~,
Microsoft's Active Server Pages. It's one of the most popular and independent CMS core Features
ever... And yes, I'm joking.

### MOD_REWRITE
```php
# Default Value
define("MOD_REWRITE", (bool) false);
```
This constant decides about using pretty clean URLs, if set to TRUE, or to use a question mark
between the URL and the path, if set to FALSE. Please note: You **MUST** define and enable the
rewrite (mod) function on your WebServer (it's the `.htaccess` file on Apache) to get this
function working!

### UPDATER
```php
# Default Value
define("UPDATER", (bool) true);
```
This constant controls the Updater-function of your Fox CMS and the installed / available Plugins.

### UPDATER_TIMEOUT
```php
# Default Value
define("UPDATER_TIMEOUT", (int) 30);
```
This constant prevents too long loading times on the Updater function if the respective Host / XML
file can't be reached (or has a really bad connection). The value is in seconds!

### POORMANSCRON
```php
# Default Value
define("POORMANSCRON", (bool) true);
```
This constants enabled and disabled the Poormanscron CronJob solution, which depends on the
websites traffic. Disable this function, if you are using a real CronJob solution!

### POORMANSCRON_INTERVAL
```php
# Default Value
define("POORMANSCRON_INTERVAL", (int) 3600);
```
This constants controls the interval / time in seconds which must be elapse, before the CronJob
system gets called again.


Security Settings
-----------------
### FOX_ID
```php
# Default Value
define("FOX_ID", (bool) false);
```
This constant is required (MUST be a STRING) and is used for many site- or user- related functions.
Like \_SESSION and \_COOKIE storages, hash and crypt functions and so on. This string should be as
unique as possible.

### HTTPS_MODE
```php
# Default Value
define("HTTPS_MODE", (bool) false);
```
This constants controls the HTTPS (Hypertext Transfer Protocol Secure Protocol) redirection. You
can set this variable to "always" (for HTTPS everywhere), "backend" (to use HTTPS on the
administration only), "frontend" (to use HTTPS on the Website only) or to False (for HTTPS nowhere).
Make sure HTTPS is enabled and works on your server BEFORE you enable this option!

### SESSION_KEY
```php
# Default Value
define("SESSION_KEY", (string) FOX_ID . "_s");
```
This constant controls the used Session key, which is used to store User logins and other user or
site relevant informations within the session storage.

### SESSION_LIFE
```php
# Default Value
define("SESSION_LIFE", (int) 3600);
```
This constant defines the maximum lifetime of an invoked session in seconds!

### COOKIE_KEY
```php
# Default Value
define("COOKIE_KEY", (string) FOX_ID . "_c");
```
This constant controls the used Cookie key, which is used to store User logins and other user or
site relevant informations within the Cookie storage.

### COOKIE_HTTP
```php
# Default Value
define("COOKIE_HTTP", (bool) true);
```
This constants enables the HTTP only mode of the setcookie function, to prevent the access to the
cookies through another language, like JavaScript.

### COOKIE_LIFE
```php
# Default Value
define("COOKIE_LIFE", (int) 3600);
```
This constant defines the maximum lifetime of an invoked cookie in seconds!

### TOKEN_LIFE
```php
# Default Value
define("TOKEN_LIFE", (int) 1800);
```
This constant defines the **default** maximum lifetime of an secure token in seconds!

### LOGIN_LIFE
```php
# Default Value
define("LOGIN_LIFE", (int) 1209600);
```
This constant defines the maximum lifetime of an user-login cookie in seconds!

### LOGIN_PROTECTION
```php
# Default Value
define("LOGIN_PROTECTION", (bool) true);
```
This constant enables the Login User-Account protection, which temporary locks IP-Addresses and
User Accounts on too many faulty login attempts.

### LOGIN_PROTECTION_EXP
```php
# Default Value
define("LOGIN_PROTECTION", (bool) true);
```
This constant enables the exponentially increased `LOGIN_PROTECTION_TIME` at repeated locks by the
login protection system.

### LOGIN_PROTECTION_TIME
```php
# Default Value
define("LOGIN_PROTECTION_TIME", (int) 30);
```
This constant controls the time in seconds, which a IP-Address or  User Account gets locked after
too many faulty login attempts (which is controlled by the `LOGIN_PROTECTION_ATTEMPTS` constant).

### LOGIN_PROTECTION_ATTEMPTS
```php
# Default Value
define("LOGIN_PROTECTION_ATTEMPTS", (int) 5);
```
This constant controls the maximum attempts per IP-Address and User-Account before the Login
Protection-Lock intervenes.

### DEBUG_MODE
```php
# Default Value
define("DEBUG_MODE", (bool) true);
```
This constant enables and disables error messages and Exceptions for development and debugging.

### GLOBAL_XSS_FILTER
```php
# Default Value
define("GLOBAL_XSS_FILTER", (bool) true);
```
This constant enabled and disabled the XSS-Filtering of the Superglobal Variables.


Default Settings
----------------
### DEFAULT_CHARSET
```php
# Default Value
define("DEFAULT_CHARSET", (string) "UTF-8");
```
The default charset, which should used for all the respective systems and actions.

### DEFAULT_LANGUAGE
```php
# Default Value
define("DEFAULT_LANGUAGE", (string) "en");
```
The default language, which should used as fallback if the configured language isn't available.

### DEFAULT_TIMEZONE
```php
# Default Value
define("DEFAULT_TIMEZONE", (string) "Europe/London");
```
The default timezone, which should be used for all respective systems and actions.

### DEFAUT_CONTROLLER
```php
# Default Value
define("DEFAULT_CONTROLLER", (string) "Page");
```
The default Controller, which should be used as fallback if the respective requested Controller
couldn't be found, couldn't be fetched or is invalid.

### DEFAULT_ACTION
```php
# Default Value
define("DEFAULT_ACTION", (string) "index");
```
The default Controller-Action, which should be used as fallback if the Controller doesn't provide
the requested action or the requested action is invalid.


Directory Paths
---------------
The following constants stores the **relative** path to the respective folders.

### BASE_DIR
```php
# Default Value
define("BASE_DIR", "." . DS);
```
This constant defines the **relative** path to the Fox CMS installation!

### CONTENT_DIR
```php
# Default Value
define("CONTENT_DIR", (string) BASE_DIR . "content" . DS);
```
This constant defines the respective **relative** path to the respective folder!

### I18N_DIR
```php
# Default Value
define("I18N_DIR", (string) CONTENT_DIR . "i18n" . DS);
```
This constant defines the respective **relative** path to the respective folder!

### PLUGINS_DIR
```php
# Default Value
define("PLUGINS_DIR", (string) CONTENT_DIR . "plugins" . DS);
```
This constant defines the respective **relative** path to the respective folder!

### THEMES_DIR
```php
# Default Value
define("THEMES_DIR", (string) CONTENT_DIR . "themes" . DS);
```
This constant defines the respective **relative** path to the respective folder!

### UPLOADS_DIR
```php
# Default Value
define("UPLOADS_DIR", (string) CONTENT_DIR . "uploads" . DS);
```
This constant defines the respective **relative** path to the respective folder!

### INCLUDES_DIR
```php
# Default Value
define("INCLUDES_DIR", (string) BASE_DIR . "includes" . DS);
```
This constant defines the respective **relative** path to the respective folder!

### SYSTEM_DIR
```php
# Default Value
define("SYSTEM_DIR", (string) BASE_DIR . "system" . DS);
```
This constant defines the respective **relative** path to the respective folder!

### FOX_DIR
```php
# Default Value
define("FOX_DIR", NULL);
```
The server directory of your Fox Installation, that's important when your Fox CMS Installation
is located in another sub-directory as your index.php. With this constant your are also able to
use multiple index.php files / Fox CMS websites on a single Fox CMS installation. Leave this
constant on `NULL` if you don't move your index.php file / use multiple Websites.

Your example Installation:
-   ./my-server/my-foxcms/&lt;your-fox-cms-installation&gt;

Your example Fox CMS Websites:
-   ./my-server/foxcms-mysql/&lt;Website 1&gt;
    -   FOX_DIR = `../foxcms/`
    -   FOX_PUBLIC = `http://www.example.org/foxcms-mysql`
    -   BASE_DIR = `../foxcms/`
-   ./my-server/foxcms-pgsql/&lt;Website 2&gt;
    -   FOX_DIR = `../foxcms/`
    -   FOX_PUBLIC = `http://www.example.org/foxcms-pgsql`
    -   BASE_DIR = `../foxcms/`
-   ./my-server/foxcms-sqlite/&lt;Website 3&gt;
    -   FOX_DIR = `../foxcms/`
    -   FOX_PUBLIC = `http://www.example.org/foxcms-sqlite`
    -   BASE_DIR = `../foxcms/`

Root Paths
----------
The following constants stores the **absolute** path to the respective folders.

### BASE_ROOT
```php
# Default Value
define("BASE_ROOT", (string) realpath(BASE_DIR) .  DS);
```
This constant defines the **absolute** path to the Fox CMS installation!

### CONTENT_ROOT
```php
# Default Value
define("CONTENT_ROOT", (string) BASE_ROOT . "content" . DS);
```
This constant defines the respective **absolute** path to the respective folder!

### I18N_ROOT
```php
# Default Value
define("I18N_ROOT", (string) CONTENT_ROOT . "i18n" . DS);
```
This constant defines the respective **absolute** path to the respective folder!

### PLUGINS_ROOT
```php
# Default Value
define("PLUGINS_ROOT", (string) CONTENT_ROOT . "plugins" . DS);
```
This constant defines the respective **absolute** path to the respective folder!

### THEMES_ROOT
```php
# Default Value
define("THEMES_ROOT", (string) CONTENT_ROOT . "themes" . DS);
```
This constant defines the respective **absolute** path to the respective folder!

### UPLOADS_ROOT
```php
# Default Value
define("UPLOADS_ROOT", (string) CONTENT_ROOT . "uploads" . DS);
```
This constant defines the respective **absolute** path to the respective folder!

### INCLUDES_ROOT
```php
# Default Value
define("INCLUDES_ROOT", (string) BASE_ROOT . "includes" . DS);
```
This constant defines the respective **absolute** path to the respective folder!

### SYSTEM_ROOT
```php
# Default Value
define("SYSTEM_ROOT", (string) BASE_ROOT . "system" . DS);
```
This constant defines the respective **absolute** path to the respective folder!

HTML Paths
----------
The following constants stores the HTML path to the respective folder.

### PUBLIC_HTML
```php
# Default Value
define("PUBLIC_HTML", (string) "<base-dir>");
```
This constant defines the **relative** HTML path to the Fox CMS installation! The `BASE_HTML`
constant is used in the Wolf CMS for the current View (so either Backend or Frontend), so the
Fox CMS defines and uses this constant as "base" HTML path.

### BASE_HTML
```php
# Default Value
define("BASE_HTML", (string) "<base-dir>[/<admin-dir>]");
```
This constant defines the **relative** HTML path to the current view. So either to the frontend or
to the backend. The Wolf CMS has defined this behavior for this constant, and we MUST follow this
concept for backward compatibility. Use `PUBLIC_HTML` to get the base directory on each view.

### CONTENT_HTML
```php
# Default Value
define("CONTENT_HTML", (string) PUBLIC_HTML . "<content-dir>");
```
This constant defines the respective **relative** HTML path to the respective folder!

### I18N_HTML
```php
# Default Value
define("I18N_HTML", (string) PUBLIC_HTML . "<i18n-dir>");
```
This constant defines the respective **relative** HTML path to the respective folder!

### PLUGINS_HTML
```php
# Default Value
define("PLUGINS_HTML", (string) PUBLIC_HTML . "<plugins-dir>");
```
This constant defines the respective **relative** HTML path to the respective folder!

### THEMES_HTML
```php
# Default Value
define("THEMES_HTML", (string) PUBLIC_HTML . "<themes-dir>");
```
This constant defines the respective **relative** HTML path to the respective folder!

### UPLOADS_HTML
```php
# Default Value
define("UPLOADS_HTML", (string) PUBLIC_HTML . "<uploads-dir>");
```
This constant defines the respective **relative** HTML path to the respective folder!

### INCLUDES_HTML
```php
# Default Value
define("INCLUDES_HTML", (string) PUBLIC_HTML . "<includes-dir>");
```
This constant defines the respective **relative** HTML path to the respective folder!

### SYSTEM_HTML
```php
# Default Value
define("SYSTEM_HTML", (string) PUBLIC_HTML . "<system-dir>");
```
This constant defines the respective **relative** HTML path to the respective folder!

URL Paths
---------
The following constants stores the direct URL to the respective folder.

### PUBLIC_URL
```php
# Default Value
define("PUBLIC_URL", (string) "https://www.example.org/");
```
This constant defines the **absolute** URL to the Fox CMS installation! The `BASE_URL` constant is
used in the Wolf CMS for the current View (so either Backend or Frontend), so the Fox CMS defines
and uses this constant as "base" URL.

### BASE_URL
```php
# Default Value
define("BASE_URL", (string) "https://www.example.org/[<admin-dir>/]");
```
This constant defines the **absolute** URL to the current view. So either to the frontend or to the
backend. The Wolf CMS has defined this behavior for this constant, and we MUST follow this concept
for backward compatibility. Use `PUBLIC_URL` to get the base URL on each view.

### CONTENT_URL
```php
# Default Value
define("CONTENT_URL", (string) "https://www.example.org/content/");
```
This constant defines the respective **absolute** URL to the respective folder!

### I18N_URL
```php
# Default Value
define("I18N_URL", (string) "https://www.example.org/content/i18n/");
```
This constant defines the respective **absolute** URL to the respective folder!

### PLUGINS_URL
```php
# Default Value
define("PLUGINS_URL", (string) "https://www.example.org/content/plugins/");
```
This constant defines the respective **absolute** URL to the respective folder!

### THEMES_URL
```php
# Default Value
define("THEMES_URL", (string) "https://www.example.org/content/themes/");
```
This constant defines the respective **absolute** URL to the respective folder!

### UPLOADS_URL
```php
# Default Value
define("UPLOADS_URL", (string) "https://www.example.org/content/uploads/");
```
This constant defines the respective **absolute** URL to the respective folder!

### INCLUDES_URL
```php
# Default Value
define("INCLUDES_URL", (string) "https://www.example.org/includes/");
```
This constant defines the respective **absolute** URL to the respective folder!

### SYSTEM_URL
```php
# Default Value
define("SYSTEM_URL", (string) "https://www.example.org/system/");
```
This constant defines the respective **absolute** URL to the respective folder!



**Deprecated**
--------------
### URL_PUBLIC
Replaced by `FOX_PUBLIC`.

### DEFAULT_LOCALE
Replaced by `DEFAULT_LANGUAGE`.

### SESSION_LIFETIME
Replaced by `SESSION_LIFE`.

### REMEMBER_LOGIN_LIFETIME
Replaced by `LOGIN_LIFE`.

### GLOBAL_XSS_FILTERING
Replaced by `GLOBAL_XSS_FILTER`.

### DEBUG
Replaced by `DEBUG_MODE`.

### TABLE_PREFIX
Replaced by `DB_PREFIX`.

### CHECK_UPDATES
Replaced by `UPDATER`.

### CHECK_TIMEOUT
Replaced by `UPDATER_TIMEOUT`.

### USE_HTTPS
Replaced by `HTTPS_MODE`.

### COOKIE_HTTP_ONLY
Replaced by `COOKIE_HTTP`.

### USE_MOD_REWRITE
Replaced by `MOD_REWRITE`.

### USE_POORMANSCRON
Replaced by `POORMANSCRON`.

### ALLOW_LOGIN_WITH_EMAIL
Just set to true, has no further function!

### DELAY_ON_INVALID_LOGIN
Replaced by `LOGIN_PROTECTION`.

### DELAY_ONCE_EVERY
Replaced by `LOGIN_PROTECTION_TIME`.

### DELAY_FIRST_AFTER
Replaced by `LOGIN_PROTECTION_ATTEMPTS`.

### SECURE_TOKEN_EXPIRY
Replaced by `TOKEN_LIFE`.
