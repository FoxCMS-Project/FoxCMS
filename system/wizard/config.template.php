<?php die(); ?>
/*
 |  FoxCMS      Content Management Simplified <www.foxcms.org>
 |  @file       ./config.php
 |  @version    {version}
 |  @created    {creation}
 */

    /*
     |  View the ./defaults.php file to see all available constants!
     */

    ##
    ##  DATABASE SETTINGs
    ##

    /*
     |  DATABASE :: TYPE
     |  Your databaste type: "mysql" (MySQL), "pgsql" (PostgreSQL) or "sqlite" (SQLite3).
     */
    define("DB_TYPE", "{db-type}");

    /*
     |  DATABASE :: HOST
     |  (MySQL|PGSQL) Your database host name (mostly "localhost").
     */
    define("DB_HOST", "{db-host}");

    /*
     |  DATABASE :: PORT
     |  (MySQL|PGSQL) Your database port for non-defaults. (MySQL: 3306; PostgreSQL: 5432).
     */
    define("DB_PORT", {db-port});

    /*
     |  DATABASE :: SOCKET
     |  (MySQL|PGSQL) Your database unix socket path (use this instead of the host and port).
     */
    define("DB_SOCKET", "{db-socket}");

    /*
     |  DATABASE :: USERNAME
     |  (MySQL|PGSQL) The database username (please don't use root on productive systems).
     */
    define("DB_USER", "{db-user}");

    /*
     |  DATABASE :: PASSWORD
     |  (MySQL|PGSQL) The database password (please don't leave it empty on productive systems).
     */
    define("DB_PASS", "{db-pass}");

    /*
     |  DATABASE :: TABLE NAME
     |  (MySQL|PGSQL|SQLite3) The database table name or path to the SQLite3 file.
     */
    define("DB_NAME", "{db-name}");

    /*
     |  DATABASE :: TABLE PREFIX
     |  The database table prefix.
     */
    define("DB_PREFIX", "{db-prefix}");


    ##
    ##  CORE SETTINGs
    ##

    /*
     |  CORE :: PUBLIC URL
     |  The full / absolute URL to your FoxCMS installation.
     */
    define("FOX_PUBLIC", "{fox-public}");

    /*
     |  CORE :: ADMIN DIR
     |  The virtual admin directory name for the backend.
     */
    define("ADMIN_DIR", "{admin-dir}");

    /*
     |  CORE :: URL SUFFIX
     |  A suffix for each page / url to simulate static pages for example.
     */
    define("URL_SUFFIX", "{url-suffix}");

    /*
     |  CORE :: MOD REWRITE
     |  TRUE to enable the mod rewrite function, FALSE to disable it.
     */
    define("MOD_REWRITE", {mod-rewrite});


    ##
    ##  SECURITY SETTINGs
    ##

    /*
     |  SECURITY :: UNIQUE FOX ID
     |  The unique FoxID is used for sessions, cookies and other user-related stuff.
     */
    define("FOX_ID", "{fox-id}");

    /*
     |  SECURITY :: SESSION KEY
     |  Your unique FoxCMS Session Key for your website.
     */
    define("SESSION_KEY", "{session-key}");

    /*
     |  SECURITY :: COOKIE KEY
     |  Your unique FoxCMS Cookie Key for your website.
     */
    define("COOKIE_KEY", "{cookie-key}");

    /*
     |  SECURITY :: HTTPS
     |  Configure the HTTPs protocol, available options:
     |  -   "always" for frontend and backend.
     |  -   "backend" only for the backend.
     |  -   "frontend" only for the frontend.
     */
    define("HTTPS_MODE", "{https-mode}");

    /*
     |  SECURITY :: DEBUG MODE
     |  TRUE to enable the debug mode and show each error message, FALSE to disable it.
     */
    define("DEBUG_MODE", {debug-mode});
