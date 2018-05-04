Roadmap
=======

Martijn van der Kleijn, the main developer of the "Wolf CMS" fork, has discontinued the project for
the time being. And to keep the mind of "Content Management Simplified" as well as the Frog CMS
and the Wolf CMS (of course) alive, i decided to takeover the future development.

I've many ideas and visions for the future of this project, but I would like to roll them out step
by step. That allows me on the one hand to achieve a slightly faster update rhythm to eradicate bugs
as well as security vulnerabilities. And on the other hand it's easier for plugin developers and
theme creators to adapt extensions from the Wolf to the Fox.

The core base / idea of the Frog CMS / Wolf CMS is of course maintained in the Fox CMS. However, the
code base itself has to be completely renewed / adapt to current standards. This renewal process
is carried out in 5 steps:

-   The Takeover (0.8.4): Basic Enhancements / Security Updates (and some more)
-   The User Update (0.9.0): A new User / Rights Management / API (and much more)
-   The Content Update (0.10.0): A new Content Management / API (and much more)
-   The Extension Update (0.11.0): A new Plugin / Theme API (and much more)
-   The new Fox CMS (1.0.0): The first stable / renewed version

Version 0.8.4
-------------
Codename: A Fox Among The Wolves **(FATW)**

The first version were I've takeover the project doesn't contain too many changes. The most
important changes are security-relevant, like the blowfish encryption instead of sha512 or the new
session / cookie system to prevent Hijacking Methods. A remastered `SecureToken` system as well as
a new `config` table scheme are also new.

I also decided to clearify and standardize the coding scheme / style, because some functions are
camelized and some other ones are underscored. The new official Fox style is here: underscored
functions and camelized class names / methods! It's may not important, but ... meh...

Instead of an install script, i decided to create a complete Wizard for the installation, migration
and for further upgrades of course. Next to them, the backend has also a nice new fur... In orange,
because it's a Fox. (But you can adjust the backend colors in future versions!).

The new System has also a own website: "www.foxcms.org", but it's maybe not online yet. Depending
on when you read this, I may still work on the own new home for the Fox CMS. (It's based on the
Fox CMS of course and will be awesome... With plugins, themes, a forum, ... I wish I had already
finished the site D:).


Version 0.9.0
-------------
Codename: A Fox And His Friends **(FAHS)**

Version 0.9.0 will be awesome... I hope, at least. It will contain a new User / Rights management,
so the `role`, `permission`, `role-permission`, `user-permission` and `user-role` tables gets
replaced by a single one, which contains and links to all respective informations.

Permissions can now again assigned to a user, independently of a role. A time-limited Assignment
of Roles / Permissions to Users will also be possible. And the Possibility to add / edit / manage
roles and permissions will also be available in the backend administration. With the new `user_meta`
table your are now also able to create and store custom user informations. The `UserMeta` API allows
it to access them also easily via plugin, or you just use the respective administration option.

*Further information follows.*

Version 0.10.0
--------------
Codename: A Fox Writes A Diary **(FWAD)**

Version 0.10.0 will turn the content system upside down. A new table-scheme for `page`, `page-part`,
`page-tag`, `layout`, `snippet` and `tag` gets introduced together with a complete rewritten
`Content` API, `ContentMeta` API, `Term` API. It will allow you to create custom content types,
content meta informations as well as taxonomies through the new `Plugin` interface.

*Further information follows.*

Version 0.11.0
--------------
Codename: A Fox And His House **(FAHH)**

Version 0.11.0 refreshes the surface / templates. A new `Backend` and `Frontent` / `Theme` API will
give you the possibility to create awesome templates with ease. This contains also some new awesome
demo / default frontend templates as well as a completely retreaded administration!

*Further information follows.*

Version 1.0.0
-------------
Codename: A Fox **(FOX)**

*Further information follows.*
