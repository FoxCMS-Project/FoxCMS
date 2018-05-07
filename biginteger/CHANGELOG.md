CHANGELOG
=========

Version 1.0.3
-------------
-   Fix PHP7 constructor deprecation notice.
-   Other bug fixes.

Version 1.0.2
-------------
-   (internal, bcmath) use OpenSSL, if available, for modular exponentiation.
-   (internal) use 64-bit ints, if available, and 64-bit floats, otherwise.
-   (all) improve random number generation.
-   change license to less restrictive MIT license.

Version 1.0.1
-------------
-   (internal, bcmath) use OpenSSL, if available, for modular exponentiation.
-   (internal) use 64-bit ints, if available, and 64-bit floats, otherwise.
-   (all) improve random number generation.
-   change license to less restrictive MIT license.
-   Other bug fixes.

Version 1.0.0
-------------
-   significant speed-ups
-   new functions: `gcd()`, `extendedGCD()`, `isPrime()`, `random()`, `randomPrime()`, `bitwise_leftRotate()`, `bitwise_rightRotate()`, `setPrecision()`, `toHex()`, `toBits()` and `equals()`.
-   PHP_Compat is now optional. Store it in your include path if you require PHP4 support but otherwise it is unrequired.

Version 1.0.0 - RC.3
--------------------
-   Fix for bug [#9654](https://pear.php.net/bugs/9654): In `MODE_INTERNAL`, modPow doesn't work occasionally.
-   Other bug fixes.

Version 1.0.0 - RC.2
--------------------
-   Added `bitwise_or()`, `bitwise_and()`, `bitwise_xor()` and `bitwise_not()`.

Version 1.0.0 - RC.1
--------------------
-   Initial PEAR Release