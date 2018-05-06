<?php
/*
 |  FoxCMS      Content Management Simplified <www.foxcms.org>
 |  @file       ./system/wizard/wizard-install.php
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

    class WizardInstall{
        /*
         |  INSTANCE VARs
         */
        private $step;
        private $user;

        /*
         |  CONSTRUCTOR
         |  @since  0.8.4
         */
        public function __construct(){
            $user = isset($_GET["step"])? $_GET["step"]: 0;
            while(true){
                if($this->checkDatabase(false)){
                    if(isset($_SESSION["wizard_install"])){
                        $this->step = 3;
                        $this->user = 3;
                        unset($_SESSION["wizard_install"]);
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
                if(!$this->checkConfig()){
                    $this->step = 1;
                    $this->user = ($user > 1)? 1: $user;
                    break;
                }

                // Step 2
                if(!$this->checkDatabase()){
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
         |  HANDLE
         |  @since  0.8.4
         */
        public function handle($data){
            if(!isset($data["wizard"]) || $data["wizard"] !== "install"){
                return false;
            }
            if(!isset($data["install"])){
                return false;
            }

            // Write Configuration
            if($data["install"] == "config"){
                if(($data = $this->validateConfig($data)) === false){
                    $_SESSION["wizard_form_config"] = func_get_arg(0);
                    Wizard::quit(URL_PUBLIC . "system/wizard/?wizard=install&step=1");
                }
                if($this->writeConfig($data) === false){
                    $_SESSION["wizard_form_config"] = func_get_arg(0);
                    Wizard::quit(URL_PUBLIC . "system/wizard/?wizard=install&step=1");
                }
                Wizard::quit(URL_PUBLIC . "system/wizard/?wizard=install&step=2");
            }

            // Write Database
            if($data["install"] == "database"){
                if(($data = $this->validateDatabase($data)) === false){
                    $_SESSION["wizard_form_database"] = func_get_arg(0);
                    Wizard::quit(URL_PUBLIC . "system/wizard/?wizard=install&step=2");
                }
                if(($pdo = Wizard::DB()) === false){
                    $_SESSION["wizard_form_database"] = func_get_arg(0);
                    Wizard::quit(URL_PUBLIC . "system/wizard/?wizard=install&step=2");
                }

                // Install
                define("FOX_WIZARD_DATA", DB_TYPE);
                define("FOX_WIZARD_" . strtoupper(DB_TYPE), true);
                require_once(SYSTEM_ROOT . "wizard" . DS . "db-" . DB_TYPE . ".php");
                require_once(SYSTEM_ROOT . "wizard" . DS . "db-data.php");

                wizard_db_install($pdo);
                wizard_data_install("Record", $data);

                // Delete and Return
                $_SESSION["wizard_install"] = true;
                Wizard::quit(URL_PUBLIC . "system/wizard/?wizard=install&step=3");
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
                "pdo"       => array("title" => "PDO Support"),
                "read"      => array("title" => "Readablility"),
                "write"     => array("title" => "Writability")
            );

            // Check PHP
            if(version_compare(PHP_VERSION, "5.3.7", "<")){
                $return["php"]["string"] = "You're using PHP ".PHP_VERSION."!";
                $return["php"]["status"] = "error"; $error = true;
            } else {
                $return["php"]["string"] = "You're using PHP ".PHP_VERSION."!";
                $return["php"]["status"] = "success";
            }

            // Check PDO
            if(!class_exists("PDO", false)){
                $return["pdo"]["string"] = "The PHP PDO extension is missing!";
                $return["pdo"]["status"] = "error"; $error = true;
            } else {
                $drivers = PDO::getAvailableDrivers();
                $support = array();
                if(in_array("mysql", $drivers)){
                    $support[] = "MySQL";
                }
                if(in_array("pgsql", $drivers)){
                    $support[] = "PostgreSQL";
                }
                if(in_array("sqlite", $drivers)){
                    $support[] = "SQLite3";
                }

                if(empty($support)){
                    $return["pdo"]["string"] = "PDO doesn't support any db driver!";
                    $return["pdo"]["status"] = "error"; $error = true;
                } else {
                    $return["pdo"]["string"] = "PDO is available (".implode(", ", $support).")!";
                    $return["pdo"]["status"] = "success";
                }
            }

            // Check Readable
            $dirs = array();
            $temp = array("INCLUDES", "SYSTEM");
            foreach($temp AS $test){
                if(!readable(constant($test."_ROOT"))){
                    $dirs[] = dirname(constant($test."_ROOT"));
                }
            }
            if(!empty($dirs)){
                $return["read"]["string"] = "This directories need to be readable: " . implode(", ", $dirs);
                $return["read"]["status"] = "error"; $error = true;
            } else {
                $return["read"]["string"] = "The system directories are readable!";
                $return["read"]["status"] = "success";
            }

            // Check Writable
            $dirs = array();
            $temp = array("CONTENT", "I18N", "PLUGINS", "THEMES", "UPLOADS");
            foreach($temp AS $test){
                if(!readable(constant($test."_ROOT"))){
                    $dirs[] = dirname(constant($test."_ROOT"));
                }
            }
            if(!empty($dirs)){
                $return["write"]["string"] = "This directories need to be writable: " . implode(", ", $dirs);
                $return["write"]["status"] = "error"; $error = true;
            } else {
                $return["write"]["string"] = "The content directories are writable!";
                $return["write"]["status"] = "success";
            }

            // Return Data
            if($bool){
                return !$error;
            }
            return $return;
        }

        /*
         |  CHECK :: CONFIGURATION
         |  @since  0.8.4
         */
        public function checkConfig(){
            if(!file_exists(BASE_DIR . "config.php")){
                return false;
            }
            if(!defined("DB_TYPE") || !defined("DB_NAME")){
                return false;
            }
            if(!defined("FOX_PUBLIC") || !defined("FOX_CHECK")){
                return false;
            }
            if(!defined("FOX_ID") || !defined("COOKIE_KEY") || !defined("SESSION_KEY")){
                return false;
            }
            return true;
        }

        /*
         |  CHECK :: DATABASE
         |  @since  0.8.4
         */
        public function checkDatabase($error = true){
            if(!$this->checkConfig()){
                return false;
            }
            if(($pdo = Wizard::DB(NULL, $error)) === false){
                return false;
            }
            if(!Wizard::DBtables($pdo, array("config", "config_cron", "config_token", "user"))){
                return false;
            }
            return true;
        }

        /*
         |  VALIDATE :: CONFIG DATA
         |  @since  0.8.4
         */
        public function validateConfig($data){
            if(!in_array($data["db-type"], array("mysql", "pgsql", "sqlite"))){
                Wizard::addError("The passed field ':field' is empty or invalid!", array(":field" => "DataBase Type"));
                return false;
            }
            if(empty($data["db-name"])){
                Wizard::addError("The passed field ':field' cannot be empty!", array(":field" => "DataBase Name / File"));
                return false;
            }
            if($data["db-type"] !== "sqlite" && empty($data["db-user"])){
                Wizard::addError("The passed field ':field' cannot be empty!", array(":field" => "DataBase User"));
                return false;
            }
            if($data["db-type"] == "sqlite"){
                $data["db-name"] = str_replace("\\/", DS, $data["db-name"]);
                if(strpos($data["db-name"], DS) === false){
                    $data["db-name"] = BASE_DIR . $data["db-name"];
                }
                $file = basename($data["db-name"]);
                $path = realpath(dirname($data["db-name"]) . DS);

                if(!$path || !file_exists($path)){
                    Wizard::addError("The SQLite3 Path doesn't exist! Please choose an existing Path for the DataBase file!");
                    return false;
                }
                if(!writable($path)){
                    Wizard::addError("The SQLite3 File cannot be used nor created! Please choose a path with write permissions!");
                    return false;
                }
                $data["db-name"] = $path . DS . (ends_with($file, ".sqlite3")? $file: $file . ".sqlite3");
                $data["db-name"] = addcslashes($data["db-name"], "\\");
            }
            if(($pdo = Wizard::DB($data)) === false){
                return false;
            }

            // DB Prefix
            $data["db-prefix"] = trim($data["db-prefix"]);
            if($data["db-type"] !== "sqlite" && empty($data["db-prefix"])){
                $data["db-prefix"] = "fox_";
            }
            if($data["db-type"] !== "sqlite" && !ends_with($data["db-prefix"], array("_", "-"))){
                $data["db-prefix"] = $data["db-prefix"] . "_";
            }

            // Fox Public
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
         |  VALIDATE :: DATABASE DATA
         |  @since  0.8.4
         */
        public function validateDatabase($data){
            use_helper("I18n");

            // Site Title
            $data["site-title"] = trim($data["site-title"]);
            if(empty($data["site-title"])){
                $data["site-title"] = "My FoxCMS Website";
            }

            // Site eMail
            $data["site-email"] = trim($data["site-email"]);
            if(($data["site-email"] = filter_var($data["site-email"], FILTER_SANITIZE_EMAIL)) === false){
                Wizard::addError("The passed field ':field' seems to be invalid!", array(":field" => "Site eMail Address"));
                return false;
            }

            // Site Language
            if(!I18n::isLanguage($data["site-language"])){
                $data["site-language"] = "en";
            }

            // Admin Username
            $data["admin-name"] = $data["admin-username"] = trim($data["admin-username"]);
            if(empty($data["admin-username"])){
                Wizard::addError("The passed field ':field' cannot be empty!", array(":field" => "Admin Username"));
                return false;
            }
            $data["admin-username"] = preg_replace("#[^a-z0-9_-]#", "", strtolower($data["admin-username"]));

            // Admin eMail
            $data["admin-email"] = trim($data["admin-email"]);
            if(($data["admin-email"] = filter_var($data["admin-email"], FILTER_SANITIZE_EMAIL)) === false){
                Wizard::addError("The passed field ':field' seems to be invalid!", array(":field" => "Admin eMail Address"));
                return false;
            }

            // Admin Language
            if(!I18n::isLanguage($data["admin-language"])){
                $data["admin-language"] = "en";
            }

            // Admin Password
            if(empty($data["admin-password"]) || strlen($data["admin-password"]) < 6){
                Wizard::addError("The passed Admin Password is too short (At least 6 characters)!");
                return false;
            }
            if($data["admin-password"] !== $data["admin-password2"]){
                Wizard::addError("The passed Admin Password doesn't match with the Confirm Password field!");
                return false;
            }
            unset($data["admin-password2"]);

            // Return
            return $data;
        }

        /*
         |  HANDLE :: WRITE CONFIGURATION FILE
         |  @since  0.8.4
         */
        private function writeConfig($data){
            $replace = array_intersect_key($data, array_flip(array(
                "db-type", "db-host", "db-port", "db-socket", "db-user", "db-pass",
                "db-name", "db-prefix", "fox-public", "admin-dir", "url-suffix",
                "mod-rewrite", "fox-id", "https-mode", "session-key", "cookie-key",
                "debug-mode"
            )));
            $keys    = array_map(function($value){ return '{'.$value.'}'; }, array_keys($replace));
            $replace = array_merge(array_combine($keys, array_values($replace)), array(
                "{version}"   => FOX_VERSION . " - " . FOX_STATUS,
                "{creation}"  => date("Y-m-d H:i:s")
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
                $error .= '<p class="text-right" style="margin-bottom:0;"><a href="?wizard=install&step=2" class="button button-alert">Continue</a></p>';
                Wizard::addError($error);
                return false;
            }
            return true;
        }
    }
