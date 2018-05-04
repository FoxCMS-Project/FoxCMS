<?php
/*
 |  FoxCMS      Content Management Simplified <www.foxcms.org>
 |  @file       ./system/class.plugin.php
 |  @author     SamBrishes@pytesNET
 |  @version    0.8.4 [0.8.4] - Alpha
 |
 |  @license    GNU GPL v3
 |  @copyright  Copyright © 2015 - 2018 SamBrishes, pytesNET <pytes@gmx.net>
 |
 |  @history    Copyright © 2009 - 2015 Martijn van der Kleijn <martijn.niji@gmail.com>
 |              Copyright © 2008 - 2009 Philippe Archambault <philippe.archambault@gmail.com>
 */
    if(!defined("FOXCMS")){ die(); }

    class Plugin{
        /*
         |  GLOBAL VARs
         */
        static public $plugins = array();           // Enabled Plugins
        static public $infos = array();             // All Plugins
        static protected $filecache = array();

        static public $controllers = array();
        static public $javascripts = array();
        static public $stylesheets = array();

        /*
         |  HELPER :: STORE PLUGIN SETTING
         |  @since  0.8.4
         |
         |  @return bool    TRUE on success, FALSE on failure.
         */
        static private function save(){
            return  Setting::saveFromData(array("site-plugins" => serialize(self::$plugins)));
        }

        /*
         |  HELPER :: FIND ALL PLUGINS
         |  @since  0.8.4
         |
         |  @return array   An array with all valid plugin folders / ids.
         */
        static public function findAll(){
            $dir = PLUGINS_ROOT;

            $plugins = array();
            if($handle = opendir($dir)){
                while(($plugin_id = readdir($handle)) !== false){
                    if(in_array($plugin_id, array(".", "..")) || is_file($dir . $plugin_id)){
                        continue;
                    }
                    if(array_key_exists($plugin_id, self::$infos)){
                        $plugins[$plugin_id] = self::$infos[$plugin_id];
                    }
                }
                closedir($handle);
            }
            ksort($plugins);
            return (object) $plugins;
        }

        /*
         |  INIT
         |  @since  0.8.4
         */
        static public function init(){
            $dir = PLUGINS_ROOT;

            // Get Loaded Plugins
            self::$plugins = Setting::get("site-plugins");

            // Fetch Plugin Folder
            if($handle = opendir($dir)){
                while(($plugin_id = readdir($handle)) !== false){
                    if(in_array($plugin_id, array(".", "..")) || is_file($dir . $plugin_id)){
                        continue;
                    }
                    $path = $dir . $plugin_id . DS;
                    $i18n = $path . "i18n" . DS;

                    // Get Main File
                    if(file_exists($path . "index.php")){
                        self::$infos[$plugin_id] = array();
                        include_once($path . "index.php");
                    } else if(file_exists($path . "{$plugin_id}.php")){
                        self::$infos[$plugin_id] = array();
                        include_once($path . "{$plugin_id}.php");
                    } else {
                        continue;
                    }

                    // Get Handle Files
                    foreach(array("enable", "disable", "uninstall") AS $type){
                        if(file_exists($path . $type . ".php")){
                            self::$infos[$plugin_id][$type] = $path . "{$type}.php";
                        }
                    }

                    // Get Translations
                    if(is_dir($i18n) && file_exists($i18n . I18n::getLocale() . "-message.php")){
                        $strings = include_once($i18n . I18n::getLocale() . "-message.php");
                        I18n::add($strings);
                    }
                    if(is_dir($i18n) && file_exists($i18n . DEFAULT_LANGUAGE . "-message.php")){
                        $defaults = include_once($i18n . DEFAULT_LANGUAGE . "-message.php");
                        I18n::add($defaults, true);
                    }
                    self::$infos[$plugin_id] = (object) self::$infos[$plugin_id];
                }
                closedir($handle);
            }

            // Load Plugins
            foreach(self::$plugins AS $plugin_id => $status){
                if($status != 1 || !array_key_exists($plugin_id, self::$infos)){
                    continue;
                }
            }
        }

        /*
         |  WOLF API :: SET PLUGIN INFOs
         |  @since  0.8.4
         |
         |  @param  array   An array with Wolf-known plugin data:
         |                  "id"                        The plugin folder
         |                  "title"                     The plugin title
         |                  "description"               The plugin description
         |                  "author"                    The plugin author
         |                  "version"                   The plugin version
         |                  "license"                   The plugin license
         |                  "update_url"                The update URL
         |                  "require_wolf_version"      The required CMS Version
         |                  "require_php_extensions"    The required PHP extension
         |                  "website"                   The plugin website
         |
         |  @return bool    TRUE on success, FALSE on failure.
         */
        static public function setInfos($data){
            if(!isset($data["id"]) || !array_key_exists($data["id"], self::$infos)){
                return false;
            }
            self::$infos[$data["id"]] = array_merge(self::$infos[$data["id"]], $data);
            return true;
        }

        /*
         |  WOLF API :: ACTIVATE PLUGIN
         |  @since  0.8.4
         |
         |  @param  string  The plugin id.
         |
         |  @return bool    TRUE on success, FALSE on failure.
         */
        static public function activate($plugin_id){
            if(!array_key_exists($plugin_id, self::$infos)){
                return false;
            }
            self::$plugins[$plugin_id] = 1;
            self::save();

            if(isset(self::$infos["enable"])){
                include(self::$infos["enable"]);
            }
            if(isset(self::$controllers[$plugin_id])){
                $class = Inflector::camelize($plugin_id) . "Controller";
                AutoLoader::addFile($class, self::$controllers[$plugin_id]->file);
            }
            return true;
        }

        /*
         |  WOLF API :: IS ENABLED
         |  @since  0.8.4
         |
         |  @param  string  The plugin id.
         |
         |  @return bool    TRUE on success, FALSE on failure.
         */
        static public function isEnabled($plugin_id){
            return (array_key_exists($plugin_id, self::$plugins) && self::$plugins[$plugin_id] == 1);
        }

        /*
         |  WOLF API :: DEACTIVATE PLUGIN
         |  @since  0.8.4
         |
         |  @param  string  The plugin id.
         |
         |  @return bool    TRUE on success, FALSE on failure.
         */
        static public function deactivate($plugin_id){
            if(!array_key_exists($plugin_id, self::$infos)){
                return false;
            }
            if(array_key_exists($plugin_id, self::$plugins)){
                self::$plugins[$plugin_id] = 0;
                self::save();
            }

            if(isset(self::$infos["disable"])){
                include(self::$infos["disable"]);
            }
            return true;
        }

        /*
         |  WOLF API :: UNINSTALL PLUGIN
         |  @since  0.8.4
         |
         |  @param  string  The plugin id.
         |
         |  @return bool    TRUE on success, FALSE on failure.
         */
        static public function uninstall($plugin_id){
            if(!array_key_exists($plugin_id, self::$infos)){
                return false;
            }
            if(array_key_exists($plugin_id, self::$plugins)){
                unset(self::$plugins[$plugin_id]);
                self::save();
            }

            if(isset(self::$infos["uninstall"])){
                include(self::$infos["uninstall"]);
            }
            return true;
        }


        /*
         |  WOLF API :: CHECK THE PLUGIN REQUIREMENTS
         |  @since  0.8.4
         |
         |  @param  object  The Plugin object.
         |  @oaram  array   An linked error array.
         |
         |  @return bool    TRUE if all requirements are available, FALSE if not.
         */
        static public function hasPrerequisites($plugin, &$errors = array()){
            if(isset($plugin->require_wolf_version)){
                if(version_compare($plugin->require_wolf_version, CMS_VERSION, ">=")){
                    $errors[] = __("The plugin requires a minimum of Wolf CMS version :v.", array(':v' => $plugin->require_wolf_version));
                }
            }
            if(isset($plugin->require_php_extensions)){
                $php = array_map("trim", explode(",", $plugin->require_php_extensions));
                if(!empty($php)){
                    foreach($php AS $ext){
                        if(!extension_loaded($ext)){
                            $errors[] = __("One or more required PHP extension is missing: :exts.", array(':exts', $plugin->require_php_extentions));
                        }
                    }
                }
            }
            return !(count($errors) > 0);
        }

        /*
         |  WOLF API :: CHECK 4 UPDATES
         |  @since  0.8.4
         |
         |  @param  object  The Plugin object.
         |
         |  @return string  'Unknown' if no update_url is availabe,
         |                  'latest'  if the plugin is up-to-date,
         |                  'error'   If the update_url couldn't be reached.
         */
        static public function checkLatest($plugin){
            if(!UPDATER || !isset($plugin->update_url)){
                return __("Unknown");
            }
            if(array_key_exists($plugin->id, self::$filecache)){
                return self::$filecache[$plugin->id];
            }

            // Load
            $agent = "PHP/Fox CMS v." . FOX_VERSION."/Updater";
            $return = url_get_contents($plugin->update_url, UPDATER_TIMEOUT, $agent);
            if(empty($return)){
                return __("Unkown");
            }
            self::$filecache[$plugin->id] = __("error");

            // Parse Data
            $xml = simplexml_load_string($return);
            foreach($xml AS $node){
                if($plugin->id == $node->id){
                    if(version_compare($plugin->version, $node->version, "<=")){
                        self::$filecache[$plugin->id] = __("latest");
                    } else {
                        self::$filecache[$plugin->id] = (string) $node->version;
                    }
                    break;
                }
            }
            return self::$filecache[$plugin->id];
        }


        /*
         |  WOLF API :: ADD
         |  @since  0.8.4
         |
         |  @param  string  The plugin id as STRING.
         |  @param  string  The admin menu label.
         |  @param  array   The required user permissions as ARRAY, or an empty array.
         |  @param  bool    TRUE to show the admin menu tab, FALSE to hide it.
         |
         |  @return bool    TRUE on success, FALSE on failure.
         */
        static public function addController($plugin_id, $label, $perms = array(), $tab = true){
            if(!isset(self::$plugins[$plugin_id])){
                return false;
            }
            $class = Inflector::camelize($plugin_id) . "Controller";
            $file  = PLUGINS_ROOT . $plugin_id . DS . $class . ".php";

            // Check File
            if(!file_exists($file)){
                if(DEBUG_MODE){
                    throw new Exception("Plugin controller file not found: {$file}");
                }
                return false;
            }

            // Add Contoller
            self::$controllers[$plugin_id] = (object) array(
                "file"          => $file,
                "label"         => ucfirst($label),
                "class_name"    => $class,
                "permissions"   => $perms,
                "show_tab"      => $tab
            );
            AutoLoader::addFile($class, self::$controllers[$plugin_id]->file);
            return true;
        }

        /*
         |  WOLF API :: ADD
         |  @since  0.8.4
         |
         |  @param  string  The plugin id as STRING.
         |  @param  string  The absolute or relative path to the file.
         |  @param  string  The file version number or NULL.
         |
         |  @return bool    TRUE on success, FALSE on failure.
         */
        static public function addJavascript($plugin_id, $file, $version = NULL){
            if(!isset(self::$plugins[$plugin_id])){
                return false;
            }

            if(!file_exists($file) && !file_exists(PLUGINS_ROOT . $plugin_id . DS . $file)){
                return false;
            }
            self::$javascripts[$plugin_id] = $file . (!empty($version)? "?v={$version}": "");
        }

        /*
         |  WOLF API :: ADD
         |  @since  0.8.4
         |
         |  @param  string  The plugin id as STRING.
         |  @param  string  The absolute or relative path to the file.
         |  @param  string  The file version number or NULL.
         |
         |  @return bool    TRUE on success, FALSE on failure.
         */
        static public function addStylesheet($plugin_id, $file, $version = NULL){
            if(!isset(self::$plugins[$plugin_id])){
                return false;
            }

            if(!file_exists($file) && !file_exists(PLUGINS_ROOT . $plugin_id . DS . $file)){
                return false;
            }
            self::$stylesheets[$plugin_id] = $file . (!empty($version)? "?v={$version}": "");
        }

        /*
         |  WOLF API :: HAS PLUGIN SETTINGs PAGE
         |  @since  0.8.4
         |
         |  @return bool    TRUE if the plugin has a settings page, FALSE if not.
         */
        static public function hasSettingsPage($plugin_id){
            if(!array_key_exists($plugin_id, self::$infos) || !array_key_exists($plugin_id, self::$controllers)){
                return false;
            }
            $class = self::$controllers[$plugin_id]->class_name;
            return (class_exists($class) && method_exists($class, "settings"));
        }

        /*
         |  WOLF API :: HAS PLUGIN DOCUMENTATION PAGE
         |  @since  0.8.4
         |
         |  @return bool    TRUE if the plugin has a documentation page, FALSE if not.
         */
        static public function hasDocumentationPage($plugin_id){
            if(!array_key_exists($plugin_id, self::$infos) || !array_key_exists($plugin_id, self::$controllers)){
                return false;
            }
            $class = self::$controllers[$plugin_id]->class_name;
            return (class_exists($class) && method_exists($class, "documentation"));
        }

        /*
         |  WOLF API :: DELETE ALL PLUGIN SETTINGs
         |  @since  0.8.4
         |
         |  @param  string  The plugin ID as STRING.
         |
         |  @return bool    TRUE on success, FALSE on failure.
         */
        static public function deleteAllSettings($plugin_id){
            if(!array_key_exists($plugin_id, self::$infos)){
                return false;
            }

            $table = Record::table("Setting");
            $query = "DELETE FROM {$table} WHERE type = :pid;";
            Record::logQuery($query);

            // Handle
            $stmt = Record::getConnection()->prepare($query);
            return $stmt->execute(array(":pid" => "{$plugin_id}_plugin"));
        }

        /*
         |  WOLF API :: SET ALL SETTINGs
         |  @since  0.8.4
         |
         |  @param  array   The key => value ARRAY paired plugin settings.
         |  @param  string  The plugin ID as STRING.
         |
         |  @return bool    TRUE on success, FALSE on failure.
         */
        static public function setAllSettings($settings, $plugin_id){
            if(!array_key_exists($plugin_id, self::$infos)){
                return false;
            }
            if(!is_array($settings) || empty($settings)){
                return false;
            }
            $table = Record::table("Setting");
            $update = self::getAllSettings($plugin_id);

            // Loop Settings
            foreach($settings AS $name => $value){
                if(!is_string($name)){
                    continue;
                }
                if(array_key_exists($name, $update)){
                    $query = "UPDATE {$table} SET value = :val WHERE name = :name AND type = :pid;";
                    $prepare = array(":val" => serializer($value), ":pid" => "{$plugin_id}_plugin");
                } else {
                    $query = "INSERT INTO {$table} (name, value, type) VALUES (:name, :val, :pid);";
                    $prepare = array(":name" => $name, ":val" => serializer($value), ":pid" => "{$plugin_id}_plugin");
                }
                Record::logQuery($query);

                // Handle
                $stmt = Record::getConnection()->prepare($query);
                $stmt->execute($prepare);
            }
            return true;
        }

        /*
         |  WOLF API :: SET A SINGLE SETTING
         |  @since  0.8.4
         |
         |  @param  string  The plugin setting name.
         |  @param  multi   The plugin setting value.
         |  @param  string  The plugin ID as STRING.
         |
         |  @return bool    TRUE on success, FALSE on failure.
         */
        static public function setSetting($name, $value, $plugin_id){
            if(!is_string($name) || !array_key_exists($plugin_id, self::$infos)){
                return false;
            }
            return self::setAllSettings(array($name => $value), $plugin_id);
        }

        /*
         |  WOLF API :: GET ALL SETTINGs
         |  @since  0.8.4
         |
         |  @param  string  The plugin ID as STRING.
         |
         |  @return multi   A key => value plugin settings ARRAY, or FALSE on failure.
         */
        static public function getAllSettings($plugin_id){
            if(!array_key_exists($plugin_id, self::$infos)){
                return false;
            }

            // Query
            $table = Record::table("Setting");
            $query = "SELECT name, value FROM {$table} WHERE type = :pid;";
            Record::logQuery($query);

            // Handle
            $stmt = Record::getConnection()->prepare($query);
            $stmt->execute(array(":pid" => "{$plugin_id}_plugin"));

            $return = array();
            while($setting = $stmt->fetchObject()){
                $return[$setting->name] = unserializer($setting->value);
            }
            return $return;
        }

        /*
         |  WOLF API :: GET A SINGLE SETTINg
         |  @since  0.8.4
         |
         |  @param  string  The single plugin setting name.
         |  @param  string  The plugin ID as STRING.
         |
         |  @return multi   The single setting value on success, FALSE on failure
         */
        static public function getSetting($name, $plugin_id){
            if(!is_string($name) || empty($name) || !array_key_exists($plugin_id, self::$infos)){
                return false;
            }

            // Query
            $table = Record::table("Setting");
            $query = "SELECT value from {$table} WHERE name = :name AND type = :pid LIMIT 1";
            Record::logQuery($query);

            // Handle
            $stmt = Record::getConnection()->prepare($query);
            $stmt->execute(array(":name" => $name, ":pid" => "{$plugin_id}_plugin"));
            return unserializer($stmt->fetchColumn());
        }
    }
