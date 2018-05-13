CHANGELOG
=========

Version 0.2.0
-------------
-   Info: The class is deprecated, please use the new Gettext system!
-   Add: The new `init()` method to release the system from the Fox constants.
-   Add: The new `translate()` method repalces `getText()` and doesn't return "MISSING_DEFAULT_STRING" anymore!
-   Add: The new `getAvailableLanguages()` method returns an array with all locale / languages within the default locale path.
-   Add: The new `getLanguages()` method returns an array with all locale / languages codes.
-   Add: The new `isLocale()` method checks if the locale code is valid (and if it's available if the second parameter is true).
-   Add: The `_e()` alias function for `__()` to print the string directly.
-   Add: The `_n()` function, which translates a singular or plural string.
-   Add: The `_en()` alias function for `_n()` to print the string directly.
-   Update: The `setLocale()` method takes over the work of the `loadArray()` method.
-   Update: The `setLocale()` method tries to find different files in the following order: 
`<locale>.php`, `<locale>-message.php`, `<locale>.json` and `<locale>-message.json`.
-   Update: The `getLocale()` method allows now to return the default locale instead of the current one.
-   Update: The `add()` method allows to add default strings with the second parameter.
-   Update: The `add()` method returns now the number of added strings or FALSE on failure instead of nothing.
-   Update: The `addDefault()` method is now just an alias for `add()`.
-   Remove: The class doesn't throw an exception anymore!
-   Bugfix: The `setLocale()` does not load the strings twice (if $default -> $locale).

Version 0.1.0
-------------
-   Initial Version by Martijn van der Kleijn.