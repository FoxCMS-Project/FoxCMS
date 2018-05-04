Migrate the Wolf CMS 0.8.3(.1) to Fox CMS 0.8.4
===============================================
You can use the automatic migration wizard to upgrade your Wolf CMS installation to the Fox CMS
version 0.8.4. This wizard requires a complete read and write access to the respective folders,
to handle this migration.

You can also follow the following instructions to migrate to the Fox CMS version manually.

Step #1 - New Folder structure
------------------------------
The Fox CMS has renamed each main folder, to easify the migration. (So the Wolf CMS files doesn't
merge with the Fox CMS files, when uploading to your server).

First of all: Upload the Fox CMS content onto your web server to the respective folder. Replaces
the basic files: 'index.php', 'security.php', '.htaccess' as well as all '*.md' files and remove
any other unnecessary Wolf CMS file in the basic folder (as well as the /docs directory).

Move all of your plugins from '/wolf/plugins' to '/content/plugins'. If you use custom helpers,
move them as well from '/wolf/helpers' to '/includes'. Custom controllers, layouts, models or
views can also be moved to '/system/controllers', '/system/layouts', '/system/models' or
'/system/views' respectively.

Step #2 - Database Dumbing
--------------------------
Create a database dump / backup to ensure, that nothing can be lost on invalid / faulty migrations.
The Wizard tries to backup your database also, but it's more safe if you own your own backup

Step #3 - Database Upgrade
--------------------------
Visit your Website, the main index.php file should redirect you automatically to the migration
wizard. If not visit "http://www.your-website.com/system/wizard" directly.

If your config.php file is read and accessible, the Wizard should offer you the "Migrate" option,
next to the "New Installation" button. Press the button and let the magic happen.

After the migration could be successfully performed, the wizard offers you to add / update / change
some basic settings / constants and updates your config.php file.

That's it, happy coding.
