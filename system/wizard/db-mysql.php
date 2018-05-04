<?php
/*
 |  FoxCMS      Content Management Simplified <www.foxcms.org>
 |  @file       ./system/wizard/db-mysql.php
 |  @author     SamBrishes@pytesNET
 |  @version    0.8.4 [0.8.4] - Alpha
 |
 |  @license    GNU GPL v3
 |  @copyright  Copyright © 2015 - 2018 SamBrishes, pytesNET <pytes@gmx.net>
 |
 |  @history    Copyright © 2009 - 2015 Martijn van der Kleijn <martijn.niji@gmail.com>
 |              Copyright © 2008 - 2009 Philippe Archambault <philippe.archambault@gmail.com>
 */
    if(!defined("FOXCMS") || (defined("FOXCMS") && FOXCMS !== "wizard")){ die(); }
    if(!defined("FOX_WIZARD_MYSQL")){ die(); }

    /*
     |  INSTALL THE DATABASE
     |  @since  0.8.4
     */
    function wizard_db_install($db){
        $prefix = DB_PREFIX;

        /*
         |  NEW CONFIG TABLE (replaces setting and plugin_settings)
         */
        $db->exec("CREATE TABLE IF NOT EXISTS {$prefix}config(
            `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            `name` VARCHAR(64) NOT NULL,
            `value` TEXT NULL,
            `type` VARCHAR(64) NOT NULL DEFAULT 'unknown',
            PRIMARY KEY  (`id`),
            UNIQUE KEY config_type_name (`type`,`name`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

        /*
         |  NEW CONFIG CRON TABLE (replaces cron)
         */
        $db->exec("CREATE TABLE IF NOT EXISTS {$prefix}config_cron(
            `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            `cron` VARCHAR(64) NOT NULL,
            `status` VARCHAR(30) NOT NULL,
            `callback` VARCHAR(100) NOT NULL,
            `interval` INT(11) UNSIGNED NOT NULL DEFAULT 60,
            `starttime` DATETIME NULL,
            `nextcall` DATETIME NULL,
            `lastcall` DATETIME NULL,
            PRIMARY KEY  (`id`),
            UNIQUE KEY cron_callback (`cron`,`callback`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

        /*
         |  NEW SECURE TOKEN TABLE
         */
        $db->exec("CREATE TABLE IF NOT EXISTS {$prefix}config_token(
            `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            `token` VARCHAR(64) NULL DEFAULT NULL,
            `secure` VARCHAR(128) NULL DEFAULT NULL,
            `nonce` VARCHAR(100) DEFAULT NULL,
            `userhash` VARCHAR(200) DEFAULT NULL,
            `created_on` DATETIME NOT NULL,
            `valid_until` DATETIME NOT NULL,
            PRIMARY KEY  (`id`),
            UNIQUE KEY user_nonce (`userhash`, `nonce`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

        /*
         |  OLD LAYOUT TABLE
         */
        $db->exec("CREATE TABLE IF NOT EXISTS {$prefix}layout(
            `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            `name` VARCHAR(100) DEFAULT NULL,
            `content` TEXT NULL,
            `content_type` VARCHAR(80) DEFAULT NULL,
            `position` MEDIUMINT(6) UNSIGNED DEFAULT NULL,
            `created_on` DATETIME DEFAULT NULL,
            `updated_on` DATETIME DEFAULT NULL,
            `created_by` BIGINT(20) UNSIGNED DEFAULT NULL,
            `updated_by` BIGINT(20) UNSIGNED DEFAULT NULL,
            PRIMARY KEY  (`id`),
            UNIQUE KEY layout_name (`name`)
         ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

         /*
          |  OLD PAGE TABLE
          */
         $db->exec("CREATE TABLE IF NOT EXISTS {$prefix}page(
            `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            `slug` VARCHAR(100) NOT NULL DEFAULT '',
            `title` VARCHAR(255) DEFAULT NULL,
            `breadcrumb` VARCHAR(160) DEFAULT NULL,
            `keywords` VARCHAR(255) DEFAULT NULL,
            `description` TEXT NULL,
            `position` MEDIUMINT(6) UNSIGNED DEFAULT NULL,
            `parent_id` BIGINT(20) UNSIGNED NULL,
            `layout_id` BIGINT(20) UNSIGNED NULL,
            `status_id` BIGINT(20) UNSIGNED NULL,
            `behavior_id` VARCHAR(25) NOT NULL DEFAULT '',
            `is_protected` TINYINT(1) NOT NULL DEFAULT '0',
            `needs_login` TINYINT(1) NOT NULL DEFAULT '2',
            `created_on` DATETIME DEFAULT NULL,
            `updated_on` DATETIME DEFAULT NULL,
            `published_on` DATETIME DEFAULT NULL,
            `created_by` BIGINT(20) UNSIGNED DEFAULT NULL,
            `updated_by` BIGINT(20) UNSIGNED DEFAULT NULL,
            `published_by` BIGINT(20) UNSIGNED DEFAULT NULL,
            `valid_until` DATETIME NULL,
            PRIMARY KEY  (`id`)
         ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

         /*
          |  OLD PAGE PART TABLE
          */
         $db->exec("CREATE TABLE IF NOT EXISTS {$prefix}page_part(
            `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            `name` VARCHAR(100) DEFAULT NULL,
            `page_id` BIGINT(20) UNSIGNED NOT NULL,
            `filter_id` VARCHAR(25) DEFAULT NULL,
            `content` LONGTEXT NULL,
            `content_html` LONGTEXT NULL,
            PRIMARY KEY  (`id`)
         ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

         /*
          |  OLD PAGE TAG TABLE
          */
         $db->exec("CREATE TABLE IF NOT EXISTS {$prefix}page_tag(
             `page_id` BIGINT(20) UNSIGNED NOT NULL,
             `tag_id` BIGINT(20) UNSIGNED NOT NULL,
             UNIQUE KEY page_tag (`page_id`,`tag_id`)
         ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

         /*
          |  OLD PERMISSION TABLE
          */
         $db->exec("CREATE TABLE IF NOT EXISTS {$prefix}permission(
             `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
             `name` VARCHAR(25) NOT NULL,
             PRIMARY KEY  (`id`),
             UNIQUE KEY permission_name (`name`)
         ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

         /*
          |  OLD ROLE TABLE
          */
         $db->exec("CREATE TABLE IF NOT EXISTS {$prefix}role(
             `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
             `name` VARCHAR(25) NOT NULL,
             PRIMARY KEY  (`id`),
             UNIQUE KEY role_name (`name`)
         ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

         /*
          |  OLD SNIPPET TABLE
          */
         $db->exec("CREATE TABLE IF NOT EXISTS {$prefix}snippet(
             `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
             `name` VARCHAR(100) NOT NULL DEFAULT '',
             `filter_id` VARCHAR(25) DEFAULT NULL,
             `content` TEXT NULL,
             `content_html` TEXT NULL,
             `position` MEDIUMINT(6) UNSIGNED DEFAULT NULL,
             `created_on` DATETIME DEFAULT NULL,
             `updated_on` DATETIME DEFAULT NULL,
             `created_by` BIGINT(20) UNSIGNED DEFAULT NULL,
             `updated_by` BIGINT(20) UNSIGNED DEFAULT NULL,
             PRIMARY KEY  (`id`),
             UNIQUE KEY snippet_name (`name`)
         ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

         /*
          |  OLD TAG TABLE
          */
         $db->exec("CREATE TABLE IF NOT EXISTS {$prefix}tag(
             `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
             `name` VARCHAR(100) NOT NULL DEFAULT '',
             `count` INT(11) UNSIGNED NOT NULL,
             PRIMARY KEY  (`id`),
             UNIQUE KEY tag_name (`name`)
         ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

         /*
          |  OLD USER TABLE
          */
         $db->exec("CREATE TABLE IF NOT EXISTS {$prefix}user(
             `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
             `username` VARCHAR(64) NOT NULL,
             `name` VARCHAR(100) DEFAULT NULL,
             `email` VARCHAR(255) DEFAULT NULL,
             `password` TEXT NOT NULL,
             `salt` TEXT NOT NULL,
             `language` VARCHAR(10) DEFAULT 'en',
             `cookie` VARCHAR(100) DEFAULT NULL,
             `session` VARCHAR(100) DEFAULT NULL,
             `last_login` DATETIME DEFAULT NULL,
             `last_failure` DATETIME DEFAULT NULL,
             `failure_count` INT(11) UNSIGNED DEFAULT NULL,
             `created_on` DATETIME DEFAULT NULL,
             `updated_on` DATETIME DEFAULT NULL,
             `created_by` BIGINT(20) UNSIGNED DEFAULT NULL,
             `updated_by` BIGINT(20) UNSIGNED DEFAULT NULL,
             PRIMARY KEY  (`id`),
             UNIQUE KEY user_username (`username`),
             CONSTRAINT uc_email UNIQUE (`email`)
         ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

         /*
          |  OLD USER ROLE TABLE
          */
         $db->exec("CREATE TABLE IF NOT EXISTS {$prefix}user_role(
             `user_id` BIGINT(20) UNSIGNED NOT NULL,
             `role_id` BIGINT(20) UNSIGNED NOT NULL,
             UNIQUE KEY role_to_user (`user_id`,`role_id`)
         ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

         /*
          |  OLD ROLE PERMISSION TABLE
          */
         $db->exec("CREATE TABLE IF NOT EXISTS {$prefix}role_permission(
             `role_id` BIGINT(20) UNSIGNED NOT NULL,
             `permission_id` BIGINT(20) UNSIGNED NOT NULL,
             UNIQUE KEY permission_to_role (`role_id`,`permission_id`)
         ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
    }

    /*
     |  UPGRADE THE DATABASE
     |  @since  0.8.4
     */
    function wizard_db_upgrade($db){
        // Future Stuff :3
    }

    /*
     |  MIGRATE THE DATABASE
     |  @since  0.8.4
     */
    function wizard_db_migrate($db){
        $prefix = TABLE_PREFIX;

        /*
         |  CREATE THE NEW CONFIG TABLE
         */
        $db->exec("CREATE TABLE IF NOT EXISTS {$prefix}config(
            `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            `name` VARCHAR(64) NOT NULL,
            `value` TEXT NULL,
            `type` VARCHAR(64) NOT NULL DEFAULT '.',
            PRIMARY KEY  (`id`),
            UNIQUE KEY config_type_name (`type`,`name`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

        /*
         |  CREATE NEW CONFIG CRON TABLE (replaces cron)
         */
        $db->exec("CREATE TABLE IF NOT EXISTS {$prefix}config_cron(
            `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            `cron` VARCHAR(64) NOT NULL,
            `status` VARCHAR(30) NOT NULL,
            `callback` VARCHAR(100) NOT NULL,
            `interval` INT(11) UNSIGNED NOT NULL DEFAULT 60,
            `starttime` DATETIME NULL,
            `nextcall` DATETIME NULL,
            `lastcall` DATETIME NULL,
            PRIMARY KEY  (`id`),
            UNIQUE KEY cron_callback (`cron`,`callback`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

        /*
         |  CREATE NEW SECURE TOKEN TABLE (replaces secure_token)
         */
        $db->exec("CREATE TABLE IF NOT EXISTS {$prefix}config_token(
            `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            `token` VARCHAR(64) NULL DEFAULT NULL,
            `secure` VARCHAR(128) NULL DEFAULT NULL,
            `nonce` VARCHAR(100) DEFAULT NULL,
            `userhash` VARCHAR(200) DEFAULT NULL,
            `created_on` DATETIME NOT NULL,
            `valid_until` DATETIME NOT NULL,
            PRIMARY KEY  (`id`),
            UNIQUE KEY user_nonce (`userhash`, `nonce`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

        /*
         |  ALTER LAYOUT TABLE
         */
        $db->exec("ALTER TABLE {$prefix}layout MODIFY COLUMN `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT;");
        $db->exec("ALTER TABLE {$prefix}layout CHANGE COLUMN `created_by_id` `created_by` BIGINT(20) UNSIGNED DEFAULT NULL;");
        $db->exec("ALTER TABLE {$prefix}layout CHANGE COLUMN `updated_by_id` `updated_by` BIGINT(20) UNSIGNED DEFAULT NULL;");
        $db->exec("ALTER TABLE {$prefix}layout MODIFY COLUMN `content_type` VARCHAR(80) AFTER `content`;");
        $db->exec("ALTER TABLE {$prefix}layout MODIFY COLUMN `position` MEDIUMINT(6) AFTER `content_type`;");

        /*
         | ALTER PAGE TABLE
         */
        $db->exec("ALTER TABLE {$prefix}page MODIFY COLUMN `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT;");
        $db->exec("ALTER TABLE {$prefix}page MODIFY COLUMN `parent_id` BIGINT(20) UNSIGNED NOT NULL;");
        $db->exec("ALTER TABLE {$prefix}page MODIFY COLUMN `layout_id` BIGINT(20) UNSIGNED NOT NULL;");
        $db->exec("ALTER TABLE {$prefix}page MODIFY COLUMN `status_id` BIGINT(20) UNSIGNED NOT NULL AFTER `layout_id`;");
        $db->exec("ALTER TABLE {$prefix}page CHANGE COLUMN `created_by_id` `created_by` BIGINT(20) UNSIGNED DEFAULT NULL;");
        $db->exec("ALTER TABLE {$prefix}page CHANGE COLUMN `updated_by_id` `updated_by` BIGINT(20) UNSIGNED DEFAULT NULL;");
        $db->exec("ALTER TABLE {$prefix}page ADD COLUMN `published_by` BIGINT(20) UNSIGNED DEFAULT NULL AFTER `updated_by`;");
        $db->exec("ALTER TABLE {$prefix}page MODIFY COLUMN `slug` VARCHAR(100) NOT NULL DEFAULT '' AFTER `id`;");
        $db->exec("ALTER TABLE {$prefix}page MODIFY COLUMN `is_protected` TINYINT(1) NOT NULL DEFAULT '0' AFTER `behavior_id`;");
        $db->exec("ALTER TABLE {$prefix}page MODIFY COLUMN `needs_login` TINYINT(1) NOT NULL DEFAULT '2' AFTER `is_protected`;");
        $db->exec("ALTER TABLE {$prefix}page MODIFY COLUMN `updated_on` DATETIME DEFAULT NULL AFTER `created_on`;");
        $db->exec("ALTER TABLE {$prefix}page MODIFY COLUMN `valid_until` DATETIME NULL AFTER `published_by`;");
        $db->exec("ALTER TABLE {$prefix}page MODIFY COLUMN `position` MEDIUMINT(6) AFTER `description`;");

        /*
         |  ALTER PAGE PART TABLE
         */
        $db->exec("ALTER TABLE {$prefix}page_part MODIFY COLUMN `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT;");
        $db->exec("ALTER TABLE {$prefix}page_part MODIFY COLUMN `page_id` BIGINT(20) UNSIGNED DEFAULT NULL AFTER `name`;");

        /*
         |  ALTER PAGE TAG TABLE
         */
        $db->exec("ALTER TABLE {$prefix}page_tag MODIFY COLUMN `page_id` BIGINT(20) UNSIGNED NOT NULL;");
        $db->exec("ALTER TABLE {$prefix}page_tag MODIFY COLUMN `tag_id` BIGINT(20) UNSIGNED NOT NULL;");

        /*
         |  ALTER PERMISSION TABLE
         */
        $db->exec("ALTER TABLE {$prefix}permission MODIFY COLUMN `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT;");

        /*
         |  ALTER ROLE TABLE
         */
        $db->exec("ALTER TABLE {$prefix}role MODIFY COLUMN `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT;");

        /*
         |  ALTER SNIPPET TABLE
         */
        $db->exec("ALTER TABLE {$prefix}snippet MODIFY COLUMN `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT;");
        $db->exec("ALTER TABLE {$prefix}snippet CHANGE COLUMN `created_by_id` `created_by` BIGINT(20) UNSIGNED DEFAULT NULL;");
        $db->exec("ALTER TABLE {$prefix}snippet CHANGE COLUMN `updated_by_id` `updated_by` BIGINT(20) UNSIGNED DEFAULT NULL;");
        $db->exec("ALTER TABLE {$prefix}snippet MODIFY COLUMN `position` MEDIUMINT(6) AFTER `content_html`;");

        /*
         |  ALTER TAG TABLE
         */
        $db->exec("ALTER TABLE {$prefix}tag MODIFY COLUMN `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT;");

        /*
         |  ALTER USER TABLE
         */
        $db->exec("ALTER TABLE {$prefix}user MODIFY COLUMN `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT;");
        $db->exec("ALTER TABLE {$prefix}user MODIFY COLUMN `username` VARCHAR(64) NOT NULL AFTER `id`;");
        $db->exec("ALTER TABLE {$prefix}user MODIFY COLUMN `password` TEXT NOT NULL;");
        $db->exec("ALTER TABLE {$prefix}user MODIFY COLUMN `salt` TEXT NOT NULL;");
        $db->exec("ALTER TABLE {$prefix}user ADD COLUMN `session` VARCHAR(100) DEFAULT NULL AFTER `language`;");
        $db->exec("ALTER TABLE {$prefix}user ADD COLUMN `cookie` VARCHAR(100) DEFAULT NULL AFTER `language`;");
        $db->exec("ALTER TABLE {$prefix}user CHANGE COLUMN `created_by_id` `created_by` BIGINT(20) UNSIGNED DEFAULT NULL;");
        $db->exec("ALTER TABLE {$prefix}user CHANGE COLUMN `updated_by_id` `updated_by` BIGINT(20) UNSIGNED DEFAULT NULL;");

        /*
         |  ALTER USER ROLE TABLE
         */
        $db->exec("ALTER TABLE {$prefix}user_role MODIFY COLUMN `user_id` BIGINT(20) UNSIGNED NOT NULL;");
        $db->exec("ALTER TABLE {$prefix}user_role MODIFY COLUMN `role_id` BIGINT(20) UNSIGNED NOT NULL;");

        /*
         |  ALTER ROLE TABLE
         */
        $db->exec("ALTER TABLE {$prefix}role_permission MODIFY COLUMN `role_id` BIGINT(20) UNSIGNED NOT NULL;");
        $db->exec("ALTER TABLE {$prefix}role_permission MODIFY COLUMN `permission_id` BIGINT(20) UNSIGNED NOT NULL;");
    }
