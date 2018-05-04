<?php
/*
 |  FoxCMS      Content Management Simplified <www.foxcms.org>
 |  @file       ./system/controllers/class.plugin-controller.php
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

    class PluginController extends Controller{
        /*
         |  INSTANCE VARs
         */
        public $url;
        public $plugin;
        public $frontend;

        /*
         |  CONSTRUCTOR
         |  @since  0.8.4
         */
        public function __construct(){
            if(CMS_BACKEND && !AuthUser::isLoggedIn()){
                redirect(get_url("login"));
            }
        }

        /*
         |  MAGIC CALL
         |  @since  0.8.4
         */
        public function __call($callback, $arg){
            if(!CMS_BACKEND){
                return Page::$function(implode(", ", $args));
            }
            return false;
        }

        /*
         |  MAGIC GET
         |  @since  0.8.4
         */
        public function __get($variable){
            if(isset(Page::$$variable)){
                return Page::$$variable;
            }
            return false;
        }

        /*
         |  CORE :: SETS A RESPECTIVE LAYOUT
         |  @since  0.8.4
         |
         |  @param  string  The layout as STRING.
         */
        public function setLayout($layout){
            if(CMS_BACKEND){
                parent::setLayout($layout);
            } else {
                $this->frontend = $layout;
            }
        }

        /*
         |  EXECUTE ACTION
         |  @since  0.8.4
         */
        public function execute($action, $params = array()){
            if(!isset(Plugin::$controllers[$action])){
                if(DEBUG_MODE){
                    throw new Exeption("The Action '{$action}' couldn't be found or is invalid!");
                }
                return false;
            }

            $plugin = Plugin::$controllers[$action];
            if(!file_exists($plugin->file)){
                if(DEBUG_MODE){
                    throw new Exeption("The Plugin Controller File '{$plugin->file}' couldn't be found!");
                }
                return false;
            }

            // Get controller
            include_once($plugin->file);
            $controller = new $plugin->class_name();

            $action = (count($params) > 0)? array_shift($params): "index";
            $action = (is_callable(array($controller, $action)))? $action: "index";
            call_user_func_array(array($controller, $action), $params);
            return true;
        }

        /*
         |  EXECUTE FRONTEND LAYOUT
         |  @since  0.8.4
         */
        public function executeFrontendLayout(){
            $table = Record::prefix("layout");
            $query = "SELECT content_type, content FROM {$table} WHERE name = :name;";
            Record::logQuery($query);

            // Handle Query
            $stmt = Record::getConnection()->prepare($query);
            $stmt->execute(array(":name" => $this->frontend));
            if(($layout = $stmt->fetchObject) === false){
                return false;
            }

            // Execute Layout
            if($layout->content_type == ""){
                $layout->content_type = "text/html";
            }
            header("Content-Type: {$layout->content_type}; charset=" . DEFAULT_CHARSET);
            $this->url = CURRENT_PATH;
            //@todo edit
            eval("?>" . $layout->content);
            return true;
        }

        /*
         |  OUTPUT :: DISPLAY
         |  @since  0.8.4
         |
         |  @param  string  The name of the view to render as STRING.
         |  @param  array   Some additional arrays for the view class within an ARRAY.
         |  @param  bool    TRUE to exit, FALSE to do it not.
         |
         |  @return void
         */
        public function display($view, $vars = array(), $exit = false){
            if(!CMS_BACKEND){
                $this->content = $this->render($view, $vars);
                $this->executeFrontendLayout();
                if($exit){
                    die();
                }
            }
            return parent::display($view, $vars, $exit);
        }

        /*
         |  OUTPUT :: RENDER
         |  @since  0.8.4
         |
         |  @param  string  The name of the view to render as STRING.
         |  @param  array   Some additional arrays for the view class within an ARRAY.
         |
         |  @return object  A new VIEW instance.
         */
        public function render($view, $vars = array()){
            if(CMS_BACKEND){
                if($this->layout){
                    $this->layout_vars["content_for_layout"] = new View("../../plugins/{$view}", $vars);
                    return new View("../layouts/{$this->layout}", $this->layout_vars);
                }
                return new View("../../plugins/{$view}", $vars);
            }
            return parent::render($view, $vars);
        }
    }
