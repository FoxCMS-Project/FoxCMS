CHANGELOG
=========

Version 0.2.0
-------------
-   Update: The `hash()` method returns false, if the eMail address is invalid!
-   Update: The `url()` method checks if the image exists, when `$default` is 404 or an
own http* link (and returns false / the default http link if the gravatar doesn't exist).
-   Update: The `url()` method validates now all parameters before adding to the query.
-   Update: The `image()` method sets now `width` and `height` depending to the `size` parameter.
-   Update: The `image()` method strips / slash and cleans now the custom attribute values.
-   Update: The `image()` method filters `width`, `height` and `src` from the `$attr` array.

Version 0.1.0
-------------
-   Initial Version by Martijn van der Kleijn.
