CHANGELOG
=========

Version 0.2.0 - Beta
--------------------
-   Info: The parameters of the individual methods have been changed, but they are fully backwards compatible!
-   Add: The new `build()` method now takes over the creation for all elements.
-   Add: The new `order()` method allows to order the attributes within the element.
-   Add: The attribute names and the input types will now be validated.
-   Add: The configuration "config:xml" to create a XML-valid field.
-   Add: The configuration "config:order" to order the attribute names / fields.
-   Update: The attribute ID will be validated and sanitized.
-   Update: Add the attribute name to the ID on all radio/checkbox fields to ensure it's unique.
-   Bugfix: Tags within attributes gets striped / slashes and cleaned with `htmlentities()`.

Version 0.1.0
-------------
-   Initial Version by Martijn van der Kleijn.
