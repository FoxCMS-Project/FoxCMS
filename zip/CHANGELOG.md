CHANGELOG
=========

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
-   First version by Philippe Archambault