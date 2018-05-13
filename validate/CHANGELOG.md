CHANGELOG
=========

Version 0.2.0
-------------
-   Info: Fork of the ZIP Helper class, written by Martijn van der Kleijn @ WolfCMS.
-   Add: The new `iban()` method, which validates IBAN numbers.
-   Update: The `email()` method uses now `filter_var()` with the `FILTER_VALIDATE_EMAIL` flag.
-   Update: The `email_rfc()` method is now just an alias for `email()`.
-   Update: The `email_domain()` method returns now `NULL` if `checkdnsrr()` isn't available.
-   Update: The `phone()` method accepts now associated-arrays to range-validate numbers.
-   Update: The `range()` method accepts now 3-item-large ARRAY.
-   Update: The `color()` method validates now also RGB(a) and HSL(a) color schemes.
-   Update: The `color()` method accepts now a second parameter to configure the color scheme.
-   Update: The `validat_utf8()` method uses now the UTF-8 REGEX validation from [W3.org](https://www.w3.org/International/questions/qa-forms-utf-8.en).
-   Update: The `compliant_utf8()` method is now just an alias for `valid_utf8()`.


Version 0.1.0
-------------
-   Initial Version by Martijn van der Kleijn.
