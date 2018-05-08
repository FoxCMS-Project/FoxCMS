<?php
/*
 |  FoxCMS      Content Management Simplified <www.foxcms.org>
 |  @file       ./defaults.php
 |  @author     SamBrishes@pytesNET
 |  @version    0.8.4 [0.8.4] - Alpha
 |
 |  @license    GNU GPL v3
 |  @copyright  Copyright © 2015 - 2018 SamBrishes, pytesNET <pytes@gmx.net>
 |
 |  @history    Copyright © 2009 - 2015 Martijn van der Kleijn <martijn.niji@gmail.com>
 |              Copyright © 2008 - 2009 Philippe Archambault <philippe.archambault@gmail.com>
 */
    if(!defined("FOXCMS")){ die(); }

    /*
     |  PLEASE DON'T EDIT THIS FILE
     |  Copy 'n' Paste the desired constants to your ./config.php file!
     */

    ##
    ##  DATABASE SETTINGS
    ##

    /*
     |  DATABASE :: TYPE
     |  Your databaste type: "mysql" (MySQL), "pgsql" (PostgreSQL) or "sqlite" (SQLite3).
     */
    if(!defined("DB_TYPE")){
        define("DB_TYPE", "mysql");
    }

    /*
     |  DATABASE :: HOST
     |  (MySQL|PGSQL) Your database host name (mostly "localhost").
     */
    if(!defined("DB_HOST")){
        define("DB_HOST", "localhost");
    }

    /*
     |  DATABASE :: PORT
     |  (MySQL|PGSQL) Your database port for non-defaults. (MySQL: 3306; PostgreSQL: 5432).
     */
    if(!defined("DB_PORT")){
        define("DB_PORT", (DB_TYPE == "mysql")? 3306: 5432);
    }

    /*
     |  DATABASE :: SOCKET
     |  (MySQL|PGSQL) Your database unix socket path (use this instead of the host and port).
     */
    if(!defined("DB_SOCKET")){
        define("DB_SOCKET", NULL);
    }

    /*
     |  DATABASE :: USERNAME
     |  (MySQL|PGSQL) The database username (please don't use root on productive systems).
     */
    if(!defined("DB_USER")){
        define("DB_USER", NULL);
    }

    /*
     |  DATABASE :: PASSWORD
     |  (MySQL|PGSQL) The database password (please don't leave it empty on productive systems).
     */
    if(!defined("DB_PASS")){
        define("DB_PASS", NULL);
    }

    /*
     |  DATABASE :: TABLE NAME
     |  (MySQL|PGSQL|SQLite3) The database table name or path to the SQLite3 file.
     */
    if(!defined("DB_NAME")){
        define("DB_NAME", "foxcms");
    }

    /*
     |  DATABASE :: TABLE PREFIX
     |  The database table prefix.
     */
    if(!defined("DB_PREFIX")){
        define("DB_PREFIX", "fox_");
    }


    ##
    ##  CORE SETTINGs
    ##

    /*
     |  CORE :: PUBLIC URL
     |  The full / absolute URL to your FoxCMS installation.
     */
    if(!defined("FOX_PUBLIC")){
        define("FOX_PUBLIC", "");
    }

    /*
     |  SECURITY :: ADMIN DIR
     |  The virtual admin directory name for the backend.
     */
    if(!defined("ADMIN_DIR")){
        define("ADMIN_DIR", "admin");
    }

    /*
     |  CORE :: URL SUFFIX
     |  A suffix for each page / url to simulate static pages for example.
     */
    if(!defined("URL_SUFFIX")){
        define("URL_SUFFIX", ".html");
    }

    /*
     |  CORE :: MOD REWRITE
     |  TRUE to enable the mod rewrite function, FALSE to disable it.
     */
    if(!defined("MOD_REWRITE")){
        define("MOD_REWRITE", false);
    }

    /*
     |  CORE :: UPDATER
     |  TRUE to enable the Update Checker, FALSE to disable it.
     */
    if(!defined("UPDATER")){
        define("UPDATER", true);
    }

    /*
     |  CORE :: UPDATER TIMEOUT
     |  The duration of an update check in seconds, before he gets timeouted.
     */
    if(!defined("UPDATER_TIMEOUT")){
        define("UPDATER_TIMEOUT", 30);
    }

    /*
     |  CORE :: USE POORMANSCRON
     |  TRUE to enable poormanscron cron solution, FALSE to disable it.
     */
    if(!defined("POORMANSCRON")){
        define("POORMANSCRON", true);
    }

    /*
     |  CORE :: USE POORMANSCRON
     |  The poormanscron interval in seconds..
     */
    if(!defined("POORMANSCRON_INTERVAL")){
        define("POORMANSCRON_INTERVAL", 3600);
    }


    ##
    ##  SECURITY SETTINGs
    ##

    /*
     |  SECURITY :: UNIQUE FOX ID
     |  The unique FoxID is used for sessions, cookies and other user-related stuff.
     */
    if(!defined("FOX_ID")){
        define("FOX_ID", false);
    }

    /*
     |  SECURITY :: HTTPS
     |  Configure the HTTPs protocol, available options:
     |  -   "always" for frontend and backend.
     |  -   "backend" only for the backend.
     |  -   "frontend" only for the frontend.
     */
    if(!defined("HTTPS_MODE")){
        define("HTTPS_MODE", false);
    }

    /*
     |  SECURITY :: SESSION KEY
     |  Your unique FoxCMS Session Key for your website.
     */
    if(!defined("SESSION_KEY")){
        define("SESSION_KEY", FOX_ID . "s");
    }

    /*
     |  SECURITY :: SESSION LIFETIME
     |  The lifetime of a session, before it becomes invalid. (The counter of this time get's
     |  reseted in each user-interaction, it only counts if the user does absolutly nothing.
     */
    if(!defined("SESSION_LIFE")){
        define("SESSION_LIFE", 3600);      // 60 minutes
    }

    /*
     |  SECURITY :: COOKIE KEY
     |  Your unique FoxCMS Cookie Key for your website.
     */
    if(!defined("COOKIE_KEY")){
        define("COOKIE_KEY", FOX_ID . "c");
    }

    /*
     |  SECURITY :: COOKIE HTTP-ONLY
     |  TRUE to enable the "http-only" mode, FALSE to do it not.
     */
    if(!defined("COOKIE_HTTP")){
        define("COOKIE_HTTP", true);
    }

    /*
     |  SECURITY :: COOKIE LIFE
     |  The lifetime of a cookie, before it becomes invalid. (The counter of this time get's
     |  reseted in each user-interaction, it only counts if the user does absolutly nothing.
     */
    if(!defined("COOKIE_LIFE")){
        define("COOKIE_LIFE", 3600);        // 60 minutes
    }

    /*
     |  SECURITY :: TOKEN EXPIRATION
     |  The lifetime of a token in seconds, before it becomes invalid.
     */
    if(!defined("TOKEN_LIFE")){
        define("TOKEN_LIFE", 1800);         // 30 minutes
    }

    /*
     |  SECURITY :: REMEMBER LOGIN LIFE
     |  The lifetime of a token in seconds, before it becomes invalid.
     */
    if(!defined("LOGIN_LIFE")){
        define("LOGIN_LIFE", 1209600);      // 2 Weeks
    }

    /*
     |  SECURITY :: LOGIN PROTECTION
     |  TRUE to enable the Login protection system, FALSE to disable it.
     */
    if(!defined("LOGIN_PROTECTION")){
        define("LOGIN_PROTECTION", true);
    }

    /*
     |  SECURITY :: LOGIN PROTECTION MULTIPLICATOR
     |  TRUE to increate the block-time exponentielly, FALSE to leave it.
     */
    if(!defined("LOGIN_PROTECTION_EXP")){
        define("LOGIN_PROTECTION_EXP", true);
    }

    /*
     |  SECURITY :: LOGIN PROTECTION TIME
     |  The time in seconds of how long a user account and a IP address get's blocked after
     |  too many invalid or expired attempts.
     */
    if(!defined("LOGIN_PROTECTION_TIME")){
        define("LOGIN_PROTECTION_TIME", 30);
    }

    /*
     |  SECURITY :: LOGIN PROTECTION
     |  The number of invalid or expired attempts before the block gets active.
     */
    if(!defined("LOGIN_PROTECTION_ATTEMPTS")){
        define("LOGIN_PROTECTION_ATTEMPTS", 5);
    }

    /*
     |  SECURITY :: DEBUG MODE
     |  TRUE to enable the debug mode and show each error message, FALSE to disable it.
     */
    if(!defined("DEBUG_MODE")){
        define("DEBUG_MODE", true);
    }

    /*
     |  SECURITY :: EXPERIMENTAL XSS FILTERING
     |  TRUE to XSS-filter the global variables, FALSE to do it not.
     */
    if(!defined("GLOBAL_XSS_FILTER")){
        define("GLOBAL_XSS_FILTER", true);
    }


    ##
    ##  DEFAULTs
    ##

    /*
     |  DEFAULT :: CHARSET
     |  The used charset for the database and language functions (Recommended: UTF-8).
     */
    if(!defined("DEFAULT_CHARSET")){
        define("DEFAULT_CHARSET", "UTF-8");
    }

    /*
     |  DEFAULT :: LANGUAGE
     |  The used fallback language if the user oder db language is faulty or incomplete.
     */
    if(!defined("DEFAULT_LANGUAGE")){
        define("DEFAULT_LANGUAGE", "en");
    }

    /*
     |  DEFAULT :: TIMEZONE
     |  The used timezone for the datetime and locale settings.
     */
    if(!defined("DEFAULT_TIMEZONE")){
        define("DEFAULT_TIMEZONE", "Europe/London");
    }

    /*
     |  DEFAULT :: MVC FALLBACK CONTROLLER
     |  The default controller, which shoud used as fallback on failure.
     */
    if(!defined("DEFAULT_CONTROLLER")){
        define("DEFAULT_CONTROLLER", "index");
    }

    /*
     |  DEFAULT :: MVC FALLBACK ACTION
     |  The default action, which shoud used as fallback on failure.
     */
    if(!defined("DEFAULT_ACTION")){
        define("DEFAULT_ACTION", "index");
    }


    ##
    ##  DIRECTORY SETTINGs
    ##

    /*
     |  '*_DIR'  constants are the relative paths.
     |  '*_ROOT' constants are the absolute paths.
     |  '*_HTML' constants are the relative URLs (without protocol / domain)
     |  '*_URL'  constants are the absolute URLs.
     */
    if(!defined("CONTENT_DIR")){
        define("CONTENT_DIR",   BASE_DIR . "content" . DS);
    }
    if(!defined("I18N_DIR")){
        define("I18N_DIR",      CONTENT_DIR . "i18n" . DS);
    }
    if(!defined("PLUGINS_DIR")){
        define("PLUGINS_DIR",   CONTENT_DIR . "plugins" . DS);
    }
    if(!defined("THEMES_DIR")){
        define("THEMES_DIR",    CONTENT_DIR . "themes" . DS);
    }
    if(!defined("UPLOADS_DIR")){
        define("UPLOADS_DIR",   CONTENT_DIR . "uploads" . DS);
    }
    if(!defined("INCLUDES_DIR")){
        define("INCLUDES_DIR",  BASE_DIR . "includes" . DS);
    }
    if(!defined("SYSTEM_DIR")){
        define("SYSTEM_DIR",    BASE_DIR . "system" . DS);
    }
