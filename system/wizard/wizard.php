<?php
/*
 |  FoxCMS      Content Management Simplified <www.foxcms.org>
 |  @file       ./system/wizard/wizard.php
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

    class Wizard{
        /*
         |  HELPER :: QUIT WIZARD
         |  @since  0.8.4
         |
         |  @param  string  The URL to redirect.
         */
        static public function quit($url){
            self::shutdown();
            header("Location: {$url}");
            die();
        }

        /*
         |  HELPER :: SHUTDOWN WIZARD
         |  @since  0.8.4
         */
        static public function shutdown(){

        }

        /*
         |  HANDLE :: ADD INFO
         |  @since  0.8.4
         |  @todo   Include translation System.
         |
         |  @param  string  The respective Message string.
         |  @param  array   Some additional parameters for the string.
         */
        static public function addInfo($string, $params = array()){
            if(!isset($_SESSION["wizard_infos"]) || !is_array($_SESSION["wizard_infos"])){
                $_SESSION["wizard_infos"] = array();
            }
            $_SESSION["wizard_infos"][] = strtr($string, $params);
            return true;
        }

        /*
         |  HANDLE :: ADD ERROR
         |  @since  0.8.4
         |  @todo   Include translation System.
         |
         |  @param  string  The respective Message string.
         |  @param  array   Some additional parameters for the string.
         */
        static public function addError($string, $params = array()){
            if(!isset($_SESSION["wizard_errors"]) || !is_array($_SESSION["wizard_errors"])){
                $_SESSION["wizard_errors"] = array();
            }
            $_SESSION["wizard_errors"][] = strtr($string, $params);
            return true;
        }

        /*
         |  HANDLE :: ADD SUCCESS
         |  @since  0.8.4
         |  @todo   Include translation System.
         |
         |  @param  string  The respective Message string.
         |  @param  array   Some additional parameters for the string.
         */
        static public function addSuccess($string, $params = array()){
            if(!isset($_SESSION["wizard_success"]) || !is_array($_SESSION["wizard_success"])){
                $_SESSION["wizard_success"] = array();
            }
            $_SESSION["wizard_success"][] = strtr($string, $params);
            return true;
        }

        /*
         |  TOOL :: CHECK DATABASE
         |  @since  0.8.4
         |
         |  @param  array   The database settings:
         |                      [db-]dsn
         |                      [db-]type
         |                      [db-]host
         |                      [db-]user
         |                      [db-]pass[word]
         |                      [db-]name
         |                      [db-|unix_]socket
         |  @param  bool    TRUE to set an error, FALSE to do it not.
         |
         |  @return multi   The PDO instance on success, FALSE on failure.
         */
        static public function DB($data = array(), $show_error = true){
            if(empty($data)){
                if(defined("DB_DSN")){
                    $data = array(
                        "dsn"       => DB_DSN,
                        "user"      => defined("DB_USER")? DB_USER: NULL,
                        "pass"      => defined("DB_PASS")? DB_PASS: NULL
                    );
                } else if(defined("DB_TYPE") && (defined("DB_HOST") || defined("DB_SOCKET"))){
                    $data = array(
                        "type"      => defined("DB_TYPE")? DB_TYPE: NULL,
                        "socket"    => defined("DB_SOCKET")? DB_SOCKET: NULL,
                        "host"      => defined("DB_HOST")? DB_HOST: NULL,
                        "port"      => defined("DB_PORT")? DB_PORT: NULL,
                        "user"      => defined("DB_USER")? DB_USER: NULL,
                        "pass"      => defined("DB_PASS")? DB_PASS: NULL,
                        "name"      => defined("DB_NAME")? DB_NAME: NULL
                    );
                } else {
                    if($show_error){
                        self::addError("Not enough data for a DataBase connection.");
                    }
                    return false;
                }
            }

            // Get Standard-DB-Array
            $db = array();
            foreach(array("dsn", "type", "host", "user", "name", "pass", "socket") AS $type){
                if(isset($data[$type])){
                    $db[$type] = $data[$type];
                    continue;
                }
                if(isset($data["db-" . $type])){
                    $db[$type] = $data["db-" . $type];
                    continue;
                }
                if($type == "name" && isset($data["dbname"])){
                    $db[$type] = $data["dbname"];
                    continue;
                }
                if($type == "pass" && isset($data["db-" . $type . "word"])){
                    $db[$type] = $data["db-" . $type . "word"];
                    continue;
                }
                if($type == "socket" && isset($data["unix_" . $type])){
                    $db[$type] = $data["unix_" . $type];
                    continue;
                }
            }

            // Try to Connect >> DSN
            if(isset($db["dsn"]) && !empty($db["dsn"])){
                $db["user"] = isset($db["user"])? $db["user"]: NULL;
                $db["pass"] = isset($db["pass"])? $db["pass"]: NULL;
                try{
                    $pdo = new PDO($db["dsn"], $db["user"], $db["pass"]);
                } catch(PDOException $error){
                    if($show_error){
                        self::addError("The DataBase connection failed! :err", array(":err" => $error->getMessage()));
                    }
                    return false;
                }
                return $pdo;
            }

            // Try to Connect >> MySQL
            if(isset($db["type"]) && $db["type"] == "mysql"){
                if(!isset($db["name"]) || !isset($db["user"])){
                    if($show_error){
                        self::addError("The DataBase table name as well as a DataBase username are required!");
                    }
                    return false;
                }

                if(!isset($db["socket"]) || empty($db["socket"])){
                    $db["host"] = (!isset($db["host"]) || empty($db["host"]))? "localhost": $db["host"];
                    $db["port"] = (!isset($db["port"]) || empty($db["port"]))? 3306 : (int) $db["port"];
                    $dsn = "mysql:host={$db['host']};port={$db['port']};dbname={$db['name']}";
                } else {
                    $dsn = "mysql:unix_socket={$db['socket']};dbname={$db['name']}";
                }

                try{
                    $pdo = new PDO($dsn, $db["user"], isset($db["pass"])? $db["pass"]: NULL);
                } catch(PDOException $error){
                    if($show_error){
                        self::addError("The DataBase connection failed! :err", array(":err" => $error->getMessage()));
                    }
                    return false;
                }
                return $pdo;
            }

            // Try to Connect >> PGSQL
            if(isset($db["type"]) && $db["type"] == "pgsql"){
                if(!isset($db["name"]) ||!isset($db["user"])){
                    if($show_error){
                        self::addError("The DataBase table name as well as a DataBase username are required!");
                    }
                    return false;
                }

                if(!isset($db["socket"]) || empty($db["socket"])){
                    $db["host"] = (!isset($db["host"]) || empty($db["host"]))? "localhost": $db["host"];
                    $db["port"] = (!isset($db["port"]) || empty($db["port"]))? 5432 : (int) $db["port"];
                    $dsn = "pgsql:host={$db['host']};port={$db['port']};dbname={$db['name']}";
                } else {
                    $dsn = "pgsql:unix_socket={$db['socket']};dbname={$db['name']}";
                }

                try{
                    $pdo = new PDO($dsn, $db["user"], isset($db["pass"])? $db["pass"]: NULL);
                } catch(PDOException $error){
                    if($show_error){
                        self::addError("The DataBase connection failed! :err", array(":err" => $error->getMessage()));
                    }
                    return false;
                }
                return $pdo;
            }

            // Try to Connect >> SQLite
            if(isset($db["type"]) && $db["type"] == "sqlite"){
                if(!isset($db["name"])){
                    if($show_error){
                        self::addError("The DataBase path to the SQLite3 file is required!");
                    }
                    return false;
                }

                $dsn = "sqlite:{$db["name"]}";
                try{
                    $pdo = new PDO($dsn);
                } catch(PDOException $error){
                    if($show_error){
                        self::addError("The DataBase connection failed! :err", array(":err" => $error->getMessage()));
                    }
                    return false;
                }
                return $pdo;
            }

            if($show_error){
                self::addError("The DataBase connection failed, due to an unknown error!");
            }
            return false;
        }

        /*
         |  TOOL :: DATABASE TABLES
         |  @since  0.8.4
         */
        static public function DBTables($pdo, $tables = array()){
            if(!is_a($pdo, "PDO") || !is_array($tables)){
                return false;
            }

            // Get Query
            $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
            if($driver == "sqlite"){
                $query = "SELECT COUNT(*) AS c FROM sqlite_master WHERE type = 'table' AND name = :table;";
            } else if($driver == "pgsql"){
                $query = "SELECT COUNT(*) AS c FROM pg_tables WHERE tablename = :table;";
            } else if($driver = "mysql"){
                $query = "SELECT COUNT(*) AS c FROM information_schema.tables WHERE table_name = :table;";
            } else {
                return false;
            }

            // Check tables
            foreach($tables AS &$table){
                $stmt = $pdo->prepare($query);
                if(!$stmt->execute(array(":table" => TABLE_PREFIX . $table))){
                    $table = NULL;
                    continue;
                }
                if($stmt->fetchColumn() == 0){
                    $table = NULL;
                    continue;
                }
            }
            return count(array_filter($tables)) == count($tables);
        }


        /*
         |  INSTANCE VARs
         */
        public $infos;
        public $errors;
        public $success;

        private $type;      // The current Wizard page
        private $step;      // The highest possible step
        private $user;      // The current user step
        private $instance;

        /*
         |  CONTRUCTOR
         |  @since  0.8.4
         */
        public function __construct(){
            global $wizard_type, $wizard_step, $wizard_user;

            // Get Data
            foreach(array("infos", "errors", "success") AS $type){
                if(!isset($_SESSION["wizard_{$type}"])){
                    continue;
                }
                $this->$type = $_SESSION["wizard_{$type}"];
                unset($_SESSION["wizard_{$type}"]);
            }

            // Get Index
            $type = isset($_GET["wizard"])? $_GET["wizard"]: "index";
            if(!in_array($type, array("index", "install", "upgrade", "migrate"))){
                 $type = "index";
            }

            // Get Step
            switch($type){
                case "install":
                    $this->instance = new WizardInstall();
                    $step = $this->instance->step();
                    $user = $this->instance->user();
                    break;
                case "upgrade":
                    $this->instance = new WizardUpgrade();
                    $step = $this->instance->step();
                    $user = $this->instance->user();
                    break;
                case "migrate":
                    $this->instance = new WizardMigrate();
                    $step = $this->instance->step();
                    $user = $this->instance->user();
                    break;
                default:
                    $step = 0;
                    $user = 0;
                    break;
            }

            // Set Data
            $wizard_type = $this->type = $type;
            $wizard_step = $this->step = $step;
            $wizard_user = $this->user = $user;

            // Check Post
            if(!empty($_POST) && isset($_POST["wizard"]) && $_POST["wizard"] == $type){
                $this->instance->handle($_POST);
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
         |  GET :: TYPE
         |  @since  0.8.4
         */
        public function type(){
            return $this->type;
        }

        /*
         |  GET :: INSTANCE
         |  @since  0.8.4
         */
        public function instance(){
            return $this->instance;
        }

        /*
         |  RENDER TABS
         |  @since  0.8.4
         */
        public function renderTabs(){
            ob_start();
            if($this->type == "index"){
                ?>
                    <ul class="tabs">
                        <li class="tab active">Fox Wizard</li>
                    </ul>
                <?php
            } else if($this->type == "install"){
                ?>
                    <ul class="tabs">
                        <li class="tab <?php echo ($this->user == 0)? "active": ""; ?>">Check Requirements</li>
                        <li class="tab <?php echo ($this->user == 1)? "active": ""; ?>">Basic Configuration</li>
                        <li class="tab <?php echo ($this->user == 2)? "active": ""; ?>">Database Installation</li>
                        <li class="tab <?php echo ($this->user == 3)? "active": ""; ?>">Finish</li>
                    </ul>
                <?php
            } else if($this->type == "upgrade"){
                ?>
                    <ul class="tabs">
                        <li class="tab active">Future Stuff :3</li>
                    </ul>
                <?php
            } else if($this->type == "migrate"){
                ?>
                    <ul class="tabs">
                        <li class="tab <?php echo ($this->user == 0)? "active": ""; ?>">Check Migration</li>
                        <li class="tab <?php echo ($this->user == 1)? "active": ""; ?>">Create a Backup</li>
                        <li class="tab <?php echo ($this->user == 2)? "active": ""; ?>">Migrate and Remove</li>
                        <li class="tab <?php echo ($this->user == 3)? "active": ""; ?>">Finish</li>
                    </ul>
                <?php
            }
            $content = ob_get_contents();
            ob_end_clean();
            return $content;
        }
    }
