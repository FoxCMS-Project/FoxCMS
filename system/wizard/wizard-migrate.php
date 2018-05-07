<?php
/*
 |  FoxCMS      Content Management Simplified <www.foxcms.org>
 |  @file       ./system/wizard/wizard-migrate.php
 |  @author     SamBrishes@pytesNET
 |  @version    0.8.4 [0.8.4] - Alpha
 |
 |  @license    GNU GPL v3
 |  @copyright  Copyright © 2015 - 2018 SamBrishes, pytesNET <pytes@gmx.net>
 |
 |  @history    Copyright © 2009 - 2015 Martijn van der Kleijn <martijn.niji@gmail.com>
 |              Copyright © 2008 - 2009 Philippe Archambault <philippe.archambault@gmail.com>
 */
    if(!defined("FOXCMS") || (defined("FOXCMS") && FOXCMS !== "wizard")){ die(); }

    class WizardMigrate{
        /*
         |  INSTANCE VARs
         */
        private $step;
        private $user;
        private $loggedIn = false;

        /*
         |  CONSTRUCTOR
         |  @since  0.8.4
         */
        public function __construct(){
            $user = isset($_GET["step"])? $_GET["step"]: 0;
            while(true){
                if($this->checkMigration()){
                    if(isset($_SESSION["wizard_admin"])){
                        $this->step = 3;
                        $this->user = 3;
                        unset($_SESSION["wizard_admin"]);
                        break;
                    }
                    Wizard::quit(FOX_PUBLIC);
                }

                // Step 0
                if(!$this->checkRequirements(true)){
                    $this->step = 0;
                    $this->user = 0;
                    break;
                }

                // Step 1
                if(!$this->checkBackup()){
                    $this->step = 1;
                    $this->user = ($user > 1)? 1: $user;
                    break;
                }
                if(!isset($_SESSION["wizard_admin"]) || ($pdo = Wizard::DB()) == false){
                    $this->step = 1;
                    $this->user = ($user > 1)? 1: $user;
                    break;
                }
                if(!$this->validateUser($_SESSION["wizard_admin"])){
                    $this->step = 1;
                    $this->user = ($user > 1)? 1: $user;
                    unset($_SESSION["wizard_admin"]);
                    break;
                }
                $this->loggedIn = true;

                // Step 2
                if(!$this->checkMigration()){
                    $this->step = 2;
                    $this->user = ($user > 2)? 2: $user;
                    break;
                }
            }
        }

        /*
         |  GET :: STEP
         |  @since  0.8.4
         */
        public function step(){
            return $this->step;
        }

        /*
         |  GET :: USER STEP
         |  @since  0.8.4
         */
        public function user(){
            return $this->user;
        }

        /*
         |  GET :: LOGGED IN
         |  @since  0.8.4
         */
        public function loggedIn(){
            return $this->loggedIn;
        }

        /*
         |  HANDLE
         |  @since  0.8.4
         */
        public function handle($data){
            if(!isset($data["wizard"]) || $data["wizard"] !== "migrate"){
                return false;
            }
            if(!isset($data["migrate"])){
                return false;
            }

            // Check User
            if(!isset($_SESSION["wizard_admin"])){
                if(!isset($data["admin-user"]) || !isset($data["admin-pass"])){
                    Wizard::addError("Only logged-in Administrators are able to start the Migration.");
                    Wizard::quit(URL_PUBLIC . "system/wizard/?wizard=migrate&step=1");
                }
                if(!$this->loginUser($data["admin-user"], $data["admin-pass"])){
                    Wizard::quit(URL_PUBLIC . "system/wizard/?wizard=migrate&step=1");
                }
            }

            // Backup Data
            if($data["migrate"] == "backup"){
                if(isset($data["store"])){
                    if(!$this->migrateFiles($data["store"])){
                        Wizard::addError("An unknown error occured during the File-Migration, possibly not all data has been migrated!");
                    }
                }
                if(!$this->createBackup()){
                    Wizard::addError("An unknown error occured during the File-Backup, possibly not all data has been stored in the ZIP file!");
                }
                Wizard::quit(URL_PUBLIC . "system/wizard/?wizard=migrate&step=2");
            }

            // Migrate Data
            if($data["migrate"] == "tothefox"){
                if(($data = $this->validateConfig($data)) === false){
                    return false;
                }

                // Add Database
                list($type, $dsn) = explode(":", DB_DSN, 2);
                if($type == "sqlite"){
                    $data["type"] = $type;
                    $data["port"] = 0;
                    $data["dbname"] = $dsn;
                } else {
                    $dsn = explode(";", $dsn);
                    foreach($dsn AS $string){
                        list($key, $value) = explode("=", $string, 2);
                        $data[$key] = $value;
                    }
                    $data["type"] = $type;
                    $data["user"] = defined("DB_USER")? DB_USER: NULL;
                    $data["pass"] = defined("DB_PASS")? DB_PASS: NULL;
                }

                // Handle
                if(!($pdo = Wizard::DB($data))){
                    Wizard::quit(URL_PUBLIC . "system/wizard/?wizard=migrate&step=2");
                }
                if(!$this->writeConfig($data)){
                    Wizard::quit(URL_PUBLIC . "system/wizard/?wizard=migrate&step=2");
                }
                if(!$this->writeHTACCESS()){
                    Wizard::quit(URL_PUBLIC . "system/wizard/?wizard=migrate&step=2");
                }

                // Migrate
                define("FOX_WIZARD_DATA", $data["type"]);
                define("FOX_WIZARD_" . strtoupper($data["type"]), true);
                require_once(SYSTEM_ROOT . "wizard" . DS . "db-" . $data["type"] . ".php");
                require_once(SYSTEM_ROOT . "wizard" . DS . "db-data.php");

                wizard_db_migrate($pdo);
                wizard_data_migrate($pdo, $data);

                // Delete and Return
                $this->deleteWolfCMS();
                Wizard::quit(URL_PUBLIC . "system/wizard/?wizard=migrate&step=3");
            }
            return false;
        }

        /*
         |  CHECK :: REQUIREMENTS
         |  @since  0.8.4
         */
        public function checkRequirements($bool = false){
            $error = false;
            $return = array(
                "php"       => array("title" => "PHP >= 5.3.7"),
                "wolf"      => array("title" => "Wolf CMS"),
                "config"    => array("title" => "Wolf CMS Config"),
                "database"  => array("title" => "Wolf CMS Database")
            );

            // Check PHP
            if(version_compare(PHP_VERSION, "5.3.7", "<")){
                $return["php"]["string"] = "You're using PHP ".PHP_VERSION."!";
                $return["php"]["status"] = "error"; $error = true;
            } else {
                $return["php"]["string"] = "You're using PHP ".PHP_VERSION."!";
                $return["php"]["status"] = "success";
            }

            // Check Wolf Version
            if(!file_exists(BASE_DIR . "public") || !file_exists(BASE_DIR . "wolf")){
                $return["wolf"]["string"] = "Wolf CMS Installation not found!";
                $return["wolf"]["status"] = "error"; $error = true;
            } else if(!writable(BASE_DIR . "public") || !writable(BASE_DIR . "wolf")){
                $return["wolf"]["string"] = "Wolf CMS Version isn't writable!";
                $return["wolf"]["status"] = "error"; $error = true;
            } else {
                $return["wolf"]["string"] = "Wolf CMS Installation found!";
                $return["wolf"]["status"] = "success";
            }

            // Check Wolf Configuration
            if(!file_exists(BASE_DIR . "config.php")){
                $return["config"]["string"] = "Wolf CMS Configuration not found!";
                $return["config"]["status"] = "error"; $error = true;
            } else if(!writable(BASE_DIR . "config.php")){
                $return["config"]["string"] = "Wolf CMS Configuration isn't writable!";
                $return["config"]["status"] = "error"; $error = true;
            } else {
                include_once(BASE_DIR . "config.php");
                if(!defined("DB_DSN") || !defined("URL_PUBLIC")){
                    $return["config"]["string"] = "Wolf CMS Configuration seems invalid!";
                    $return["config"]["status"] = "error"; $error = true;
                } else {
                    $return["config"]["string"] = "Wolf CMS Configuration found!";
                    $return["config"]["status"] = "success";
                }
            }

            // Check Wolf Database
            $pdo = false;
            if(defined("DB_DSN") && starts_with(DB_DSN, "sqlite")){
                try{
                    $pdo = new PDO(DB_DSN);
                } catch(PDOException $error){
                    $return["database"]["string"] = "Wolf CMS Database File isn't connectable!";
                    $return["database"]["status"] = "error"; $error = true;
                }
            } else if(defined("DB_DSN") && !starts_with(DB_DSN, "sqlite")){
                try{
                    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS);
                } catch(PDOException $error){
                    $return["database"]["string"] = "Wolf CMS Database isn't connectable!";
                    $return["database"]["status"] = "error"; $error = true;
                }
            } else {
                $return["database"]["string"] = "Wolf CMS Database not found!";
                $return["database"]["status"] = "error"; $error = true;
            }
            if(is_a($pdo, "PDO")){
                if(Wizard::DBTables($pdo, array("setting", "cron", "secure_token"))){
                    $return["database"]["string"] = "Wolf CMS Database found!";
                    $return["database"]["status"] = "success";
                } else {
                    $return["database"]["string"] = "Wolf CMS Database not found!";
                    $return["database"]["status"] = "error"; $error = true;
                }
            }

            // Return Data
            if($bool){
                return !$error;
            }
            return $return;
        }

        /*
         |  CHECK :: WOLF CMS FILEs
         |  @since  0.8.4
         */
        public function checkFiles(){
            $dir = BASE_DIR;
            foreach($this->dataDirs() AS $id => $data){
                $path = $data["wolf-path"];
                $skip = $data["skip"];
                if(!is_dir($path)){
                    $return[$id] = array("files" => NULL);
                    continue;
                }

                $handle = opendir($path);
                $return[$id] = array("files" => array());
                while(($file = readdir($handle)) !== false){
                    if(in_array($file, array(".", ".."))){
                        continue;
                    }

                    if(is_file($path . $file)){
                        $file = str_replace(".php", "", $file);
                        if(ends_with($file, array(".html", ".md"))){
                            continue;
                        }
                        if(!in_array($file, $skip)){
                            $return[$id]["files"][] = $file;
                        }
                    } else {
                        if(!in_array($file, $skip)){
                            $return[$id]["files"][] = $file;
                        }
                    }
                }
                closedir($handle);
            }
            return $return;
        }

        /*
         |  CHECK :: WOLF CMS BACKUP
         |  @since  0.8.4
         */
        public function checkBackup(){
            if(file_exists(BASE_DIR . "wolf-cms-backup.zip")){
                return true;
            }
            return false;
        }

        /*
         |  CHECK :: MIGRATION
         |  @since  0.8.4
         */
        public function checkMigration(){
            if(file_exists(BASE_DIR . "public") || file_exists(BASE_DIR . "wolf")){
                return false;
            }
            if(($pdo = Wizard::DB(NULL, false)) == false){
                return false;
            }
            if(Wizard::DBTables($pdo, array("setting", "cron", "secure_token"))){
                return false;
            }
            if(!Wizard::DBTables($pdo, array("config", "config_cron", "config_token"))){
                return false;
            }
            return true;
        }

        /*
         |  VALIDATE :: USER
         |  @since  0.8.4
         */
        public function validateUser($hash){
            if(($pdo = Wizard::DB()) == false){
                return false;
            }

            // Get Data
            list($user, $hash) = array_pad(explode("#", $hash, 2), 2, NULL);
            if(empty($user) || empty($hash)){
                return false;
            }

            // Get User
            $query = "SELECT * FROM ".TABLE_PREFIX."user WHERE username = :username;";
            $stmt = $pdo->prepare($query);
            $stmt->execute(array(":username" => $user));
            if(($user = $stmt->fetchObject()) == false){
                return false;
            }

            // Validate User
            return compare(sha1(session_id() . $user->salt . $user->email), $hash);
        }

        /*
         |  VALIDATE :: CONFIG DATA
         |  @since  0.8.4
         */
        public function validateConfig($data){
            $data["fox-public"] = rtrim(trim($data["fox-public"]), "/");
            if(empty($data["fox-public"])){
                Wizard::addError("The passed field ':field' cannot be empty!", array(":field" => "Fox Public URL"));
                return false;
            }
            if(!starts_with($data["fox-public"], "http")){
                $data["fox-public"] = "http://" . $data["fox-public"];
                if(($data["fox-public"] = filter_var($data["fox-public"], FILTER_VALIDATE_URL)) === false){
                    Wizard::addError("The passed field ':field' seems to be invalid!", array(":field" => "Fox Public URL"));
                    return false;
                }
            }

            // HTTPs Mode
            if(!in_array($data["https-mode"], array("0", "backend", "frontend", "always"))){
                $data["https-mode"] = starts_with($data["fox-public"], "https")? "awlays": "0";
            }

            // Admin DIR
            $data["admin-dir"] = trim(trim($data["admin-dir"]), "/\\");
            if(empty($data["admin-dir"])){
                Wizard::addError("The passed field ':field' cannot be empty!", array(":field" => "Admin Directory"));
                return false;
            }
            if(file_exists(BASE_ROOT . $data["admin-dir"])){
                Wizard::addError("The Admin Dir value cannot be use the same name as a base Fox CMS folder!");
                return false;
            }

            // URL Suffix
            if(!empty($data["url-suffix"]) && !starts_with($data["url-suffix"], ".")){
                $data["url-suffix"] = "." . $data["url-suffix"];
            }
            if(strlen($data["url-suffix"]) > 10){
                $data["url-suffix"] = "";
            }

            // FoxID
            $data["fox-id"] = trim($data["fox-id"]);
            if(empty($data["fox-id"])){
                Wizard::addError("The passed field ':field' cannot be empty!", array(":field" => "Unique Fox ID"));
                return false;
            }

            // Session Key
            $data["session-key"] = trim($data["session-key"]);
            if(empty($data["session-key"])){
                $data["session-key"] = $data["fox-id"] . "_s";
            }

            // Cookie Key
            $data["cookie-key"] = trim($data["cookie-key"]);
            if(empty($data["cookie-key"])){
                $data["cookie-key"] = $data["fox-id"] . "_c";
            }

            // Mod Rewrite
            if(!in_array($data["mod-rewrite"], array("0", "1"))){
                $data["mod-rewrite"] = 0;
            }
            $data["mod-rewrite"] = $data["mod-rewrite"]? "true": "false";

            // Debug Mode
            if(!in_array($data["debug-mode"], array("0", "1"))){
                $data["debug-mode"] = 0;
            }
            $data["debug-mode"] = $data["debug-mode"]? "true": "false";

            // Return
            return $data;
        }

        /*
         |  HANDLE :: USER LOGIN
         |  @since  0.8.4
         */
        private function loginUser($user, $password){
            if(($pdo = Wizard::DB()) == false){
                return false;
            }

            // Get Admin Role ID
            $query = "SELECT id FROM ".TABLE_PREFIX."role WHERE name = 'administrator'";
            if(($role = $pdo->query($query)) == false){
                Wizard::addError("The Administration Account couldn't be found or is invalid!");
                return false;
            }
            $role = $role->fetchColumn();

            // Get Uknown User
            $query = "SELECT * FROM ".TABLE_PREFIX."user WHERE username = :username;";
            $stmt = $pdo->prepare($query);
            $stmt->execute(array(":username" => strtolower($user)));
            if(($user = $stmt->fetchObject()) == false){
                Wizard::addError("The Administration Account couldn't be found or is invalid!");
                return false;
            }

            // Check if User is Admin
            $query  = "SELECT COUNT(*) AS row_count FROM ".TABLE_PREFIX."user_role WHERE ";
            $query .= "user_id = {$user->id} AND role_id = {$role};";
            if(($test = $pdo->query($query)) == false){
                Wizard::addError("The Administration Account couldn't be found or is invalid!");
                return false;
            }
            if($test->fetchColumn() < 1){
                Wizard::addError("The passed User Account doesn't has the permission to start the migration!");
                return false;
            }

            // Check Password
            use_helper("Hash");
            $hash = new Crypt_Hash("sha512");
            if(!compare($user->password, bin2hex($hash->hash($password.$user->salt)))){
                Wizard::addError("The passed User Account or Password is wrong!");
                return false;
            }

            // Pseudo Login
            $_SESSION["wizard_admin"] = $user->username . "#" . sha1(session_id() . $user->salt . $user->email);
            return true;
        }

        /*
         |  HANDLE :: MIGRATE THE WOLF WITH THE FOX
         |  @since  0.8.4
         */
        private function migrateFiles($folders){
            if(!is_array($folders)){
                return false;
            }

            $data = $this->dataDirs();
            foreach($folders AS $folder){
                if(!array_key_exists($folder, $data)){
                    continue;
                }
                $path = $data[$folder]["wolf-path"];
                $dest = $data[$folder]["fox-path"];
                $skip = $data[$folder]["skip"];

                if(!file_exists($path) || !is_dir($path)){
                    continue;
                }

                // Loop
                $handle = opendir($path);
                while(($file = readdir($handle)) !== false){
                    if(in_array($file, array(".", ".."))){
                        continue;
                    }

                    if(is_file($path . $file)){
                        $file = str_replace(".php", "", $file);
                        if(ends_with($file, array(".html", ".md"))){
                            continue;
                        }
                        if(in_array($file, $skip)){
                            continue;
                        }
                        if(!@copy($path . $file, $dest . $file)){
                            $error = true;
                        }
                    } else if(is_dir($path . $file)){
                        if(in_array($file, $skip)){
                            continue;
                        }
                        if(!copy_recursive($path . $file . DS, $dest . $file . DS)){
                            $error = true;
                        }
                    }
                }
                closedir($handle);
            }
            return !isset($error);
        }

        /*
         |  HANDLE :: BACKUP THE WOLF
         |  @since  0.8.4
         */
        private function createBackup(){
            use_helper("ZIP");
            $zip = new Zip(false, 6);
            $time = time();
            foreach($this->dataWolf() AS $item){
                if(!file_exists($item)){
                    continue;
                }
                if(is_dir($item)){
                    $zip->addFolder($item, basename($item), true, true);
                } else if(is_file($item)){
                    $zip->addFile($item, basename($item), $time);
                }
            }
            return $zip->save(BASE_DIR . "wolf-cms-backup.zip", true);
        }

        /*
         |  HANDLE :: WRITE CONFIGURATION FILE
         |  @since  0.8.4
         */
        private function writeConfig($data){
            $replace = array_intersect_key($data, array_flip(array(
                "fox-public", "admin-dir", "url-suffix",  "mod-rewrite", "fox-id", "https-mode",
                "session-key", "cookie-key", "debug-mode"
            )));
            $keys    = array_map(function($value){ return '{'.$value.'}'; }, array_keys($replace));
            $replace = array_merge(array_combine($keys, array_values($replace)), array(
                "{version}"     => FOX_VERSION . " - " . FOX_STATUS . " (Migrated)",
                "{creation}"    => date("Y-m-d H:i:s"),
                "{db-type}"     => $data["type"],
                "{db-host}"     => isset($data["host"])? $data["host"]: "",
                "{db-port}"     => isset($data["port"])? $data["port"]: "",
                "{db-socket}"   => isset($data["unix_socket"])? $data["unix_socket"]: "",
                "{db-user}"     => isset($data["user"])? $data["user"]: DB_USER,
                "{db-pass}"     => isset($data["password"])? $data["password"]: DB_PASS,
                "{db-name}"     => isset($data["dbname"])? $data["dbname"]: "",
                "{db-prefix}"   => TABLE_PREFIX
            ));

            // Replace Placeholder
            if(!file_exists(SYSTEM_ROOT . "wizard" . DS . "config.template.php")){
                Wizard::addError("The configuration template within the Fox Wizard couldn't be found!");
                return false;
            }
            $content = file_get_contents(SYSTEM_ROOT . "wizard" . DS . "config.template.php");
            $content = strtr(explode("\n", $content, 2)[1], $replace);

            // Try to write file
            if(@file_put_contents(BASE_ROOT . "config.php", "<?php\n".$content) === false){
                $error  = "The <b>config.php</b> file in your base Fox CMS directory couldn't be created or wasn't readable. ";
                $error .= "Please create the <b>config.php</b> file manually in the root Fox CMS folder and paste in the following content. ";
                $error .= "Press the button below this textarea field to continue!";
                $error .= "<textarea>&lt;?php\n{$content}</textarea>";
                $error .= '<p class="text-right" style="margin-bottom:0;"><a href="?wizard=migrate&step=2" class="button button-alert">Continue</a></p>';
                Wizard::addError($error);
                return false;
            }
            return true;
        }

        /*
         |  HANDLE :: WRITE NEW .HTACCESS
         |  @since  0.8.4
         */
        private function writeHTACCESS(){
            if(file_exists(BASE_DIR . ".htaccess")){
                $fetch = file_get_contents(BASE_DIR . ".htaccess");
                foreach(explode("\n", $fetch) AS $line){
                    $line = trim($line);
                    if(empty($line) || ($num = strpos($line, "RewriteBase")) === false){
                        continue;
                    }
                    $base = substr($line, $num+strlen("RewriteBase"));
                    $base = trim($base);
                }
            }
            if(!isset($base)){
                $root = str_replace("\\", "/", $_SERVER["DOCUMENT_ROOT"]);
                $base = str_replace($root, "", str_replace("\\", "/", realpath(BASE_DIR)));
                $base = "/" . trim($base, "\\/") . "/";
            }
            $base = str_replace("//", "/", $base);

            $content  = "Options -Indexes +FollowSymLinks\n\n";
            $content .= "##\n";
            $content .= "##  REWRITE RULES\n";
            $content .= "##\n";
            $content .= "<IfModule mod_rewrite.c>\n";
            $content .= "    RewriteEngine On\n\n";
            $content .= "    # The subdirectory which contains the Fox CMS, or just /\n";
            $content .= "    RewriteBase {$base}\n\n";
            $content .= "    RewriteCond %{REQUEST_FILENAME} !-f\n";
            $content .= "    RewriteCond %{REQUEST_FILENAME} !-d\n";
            $content .= "    RewriteCond %{REQUEST_FILENAME} !-l\n";
            $content .= "    RewriteRule ^(.*)$ index.php?FOX=$1 [L,QSA]\n";
            $content .= "</IfModule>\n";
            if(@file_put_contents(BASE_ROOT . ".htaccess", $content) === false){
                $error  = "The <b>.htaccess</b> file in your base Fox CMS directory couldn't be created! ";
                $error .= "Please create the <b>.htaccess</b> file manually in the root Fox CMS folder and paste in the following content. ";
                $error .= "Press the button below this textarea field to continue!";
                $error .= "<textarea>{$content}</textarea>";
                $error .= '<p class="text-right" style="margin-bottom:0;"><a href="?wizard=migrate&step=2" class="button button-alert">Continue</a></p>';
                Wizard::addError($error);
                return false;
            }
            return true;
        }

        /*
         |  HANDLE :: DELETE THE WOLF
         |  @since  0.8.4
         */
        private function deleteWolfCMS(){
            foreach($this->dataWolf() AS $item){
                if(!file_exists($item) || ends_with($item, ".htaccess")){
                    continue;
                }
                if(is_dir($item)){
                    unlink_recursive($item);
                } else if(is_file($item)){
                    @unlink($item);
                }
            }
            return true;
        }

        /*
         |  DATA :: MIGRATION DIRs
         |  @since  0.8.4
         */
        public function dataDirs(){
            return array(
                "helpers"       => array(
                    "wolf-path" => BASE_DIR . "wolf" . DS . "helpers" . DS,
                    "fox-path"  => BASE_DIR . "includes" . DS,
                    "skip"      => array(
                        "BigInteger", "Email", "Form", "Gravatar", "Hash", "I18n", "Kses", "Pagination",
                        "Upload", "Validate", "Zip"
                    )
                ),
                "controllers"   => array(
                    "wolf-path" => BASE_DIR . "wolf" . DS . "app" . DS . "controllers" . DS,
                    "fox-path"  => BASE_DIR . "system" . DS . "controllers" . DS,
                    "skip"      => array(
                        "LayoutController", "LoginController", "PageController", "PluginController",
                        "SettingController", "SnippetController", "UserController"
                    )
                ),
                "models"        => array(
                    "wolf-path" => BASE_DIR . "wolf" . DS . "app" . DS . "models" . DS,
                    "fox-path"  => BASE_DIR . "system" . DS . "models" . DS,
                    "skip"      => array(
                        "AuthUser", "Behavior", "Cron", "Filter", "Layout", "Node", "Page", "PagePart",
                        "PageTag", "Permission", "Plugin", "Role", "RolePermission", "SecureToken",
                        "Setting", "Snippet", "Tag", "User", "UserPermission", "UserRole"
                    )
                ),
                "plugins"       => array(
                    "wolf-path" => BASE_DIR . "wolf" . DS . "plugins" . DS,
                    "fox-path"  => BASE_DIR . "content" . DS . "plugins" . DS,
                    "skip"      => array(
                        "archive", "backup_restore", "comment", "file_manager", "markdown", "multi_lang",
                        "page_not_found", "skeleton", "textile"
                    )
                ),
                "images"        => array(
                    "wolf-path" => BASE_DIR . "public" . DS . "images" . DS,
                    "fox-path"  => BASE_DIR . "content" . DS . "uploads" . DS,
                    "skip"      => array()
                ),
                "themes"        => array(
                    "wolf-path" => BASE_DIR . "public" . DS . "themes" . DS,
                    "fox-path"  => BASE_DIR . "content" . DS . "themes" . DS,
                    "skip"      => array()
                )
            );
        }

        /*
         |  DATA :: WOLF DATA
         |  @since  0.8.4
         */
        public function dataWolf(){
            return array(
                BASE_DIR . "docs" . DS,
                BASE_DIR . "public" . DS,
                BASE_DIR . "wolf" . DS,
                BASE_DIR . ".htaccess",
                BASE_DIR . "composer.json",
                BASE_DIR . "CONTRIBUTING.md",
                BASE_DIR . "favicon.ico",
                BASE_DIR . "security.php"
            );
        }
    }
