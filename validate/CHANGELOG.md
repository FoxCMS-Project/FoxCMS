CHANGELOG
=========

Version 0.2.0
-------------
-   Info: Fork of the ZIP Helper class, written by Martijn van der Kleijn @ WolfCMS.
-   Update: The `iban()` method validates IBAN numbers.
-   Update: Use `filter_var` with the `FILTER_VALIDATE_EMAIL` on the `email()` method.
-   Update: The `email_rfc()` method is now just an alias for `email()`.
-   Update: Return `NULL` if the `checkdnsrr()` function on the `email_domain()` method isn't available.
-   Update: A associated-array parameter possibility on the `phone()` method.
-   Update: A third $range-parameter on the `range()` method to modify the step.
-   Update: A second parameter on the `color()` method to configure the color type.
-   Update: The `color()` method can now validata RGBa and HSLa color strings.
-   Update: The `valid_utf8()` method uses now the UTF-8 REGEX check from [W3.org](https://www.w3.org/International/questions/qa-forms-utf-8.en).
-   Update: The `compliant_utf8()` method is now just an alias for `valid_utf8()`.
-   Update: The `$postcodes` are now also available outside the function.


Version 0.1.0
-------------
-   First version by Martijn van der Kleijn