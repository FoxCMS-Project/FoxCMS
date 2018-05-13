CHANGELOG
=========

Version 0.2.1
-------------
-   Add: The new `msDOSTime()` function, which replaces the old `hexTime()`, and checks now for the min and max dos stamps.
-   Update: Merged both `substr()` calls for the CRC-32 checksum fix.
-   Update: Replace "array() - implode()" method into a "just-write" walk.
-   Update: The `addFile()` method allows now a fourth parameter to set a file comment.
-   Update: The `addFiles()` method allows now the $time and $comment parameters too.
-   Update: The `addEmptyFolder()` method allows now the $time and $comment parameters too.

Version 0.2.0
-------------
-   Info: Fork of the ZIP Helper class, written by Philippe Archambault @ FrogCMS.
-   Add: Constructor method, with 2 parameters / settings.
-   Add: Destructor method, to clean some ZipArchive trash.
-   Add: ZipArchive functionallity next to the fallback PKZip method.
-   Add: Compression-Level on non-ZipArchive method.
-   Add: The `addFiles()` method to add multiple files at once.
-   Add: the `addFolder()` and `addFolderFlow()` method to add a whole directory (recursivly.)
-   Add: The `addEmptyFolder()` method to add empty folder structures.
-   Update: The MS-DOS Date doesn't use `eval()` to handle the result.
-   Update: The `clear()` method allows now a parameter to create an new ZipArchive instance.
-   Update: The `file()` method adds now a comment to the ZIP Archive befor it get's returned.
-   Update: The `save()` method allows now a second parameter to overwrite existing archive files.
-   Update: The `download()` method allows now a second parameter to execute after the  output.
-   Remove: The private `_unix2DosTime()` function get's replaced by `hexTime()`.

Version 0.1.0
-------------
-   Initial Version by Philippe Archambault.
