<?php
/*
 |  FoxCMS      Content Management Simplified <www.foxcms.org>
 |  @file       ./system/models/class.cron.php
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

    class Cron extends Record{
        const TABLE = "config_cron";

        /*
         |  GLOBAL VARs
         */
        static protected $cronjobs = array();

        /*
         |  INIT
         |  @since  0.8.4
         */
        static public function init(){
            $table = self::prefix("config_cron");
            $query = self::$db->query("SELECT cron FROM {$table};");
            self::$cronjobs = $query->fetchAll();

            // Trigger Cronjob, when /cron.php is called
            if(FOXCMS == "cron"){
                return self::execute();
            }

            // Hook POORMANSCRON job on normal page calls
            $cronjob = (int) Setting::get("site-cronjob", time());
            if(POORMANSCRON && time() - $cronjob >= POORMANSCRON_INTERVAL){
                Event::add("page_before_execute_layout", array("Cron", "execute"));
            }
        }

        /*
         |  EVENT :: EXECUTE CRONJOB
         |  @since  0.8.4
         */
        static public function execute(){
            $date = date("Y-m-d H:i:s");
            $table = self::prefix(self::TABLE);
            $where  = "(cron.status = 'active' OR cron.status = 'once') ";
            $where .= "AND cron.nextcall <= '{$date}' AND cron.nextcall > com.lastcall";

            $result = self::find(array(
                "select"    => "cron.*",
                "from"      => $table,
                "joins"     => "INNER JOIN {$table} AS com ON cron.id = com.id",
                "where"     => $where
            ));
            while($job = $result->fetchObject("Cron")){
                $job->call();
            }

            // Old Wolf CMS Cronjob
            Event::apply("cron_run");
        }

        /*
         |  REGISTER A NEW CRON JOB
         |  @since  0.8.4
         |
         |  @param  string  A cronjob name as STRING.
         |  @param  callb.  A callback function, which should be called:
         |                      Pass only function names:   "my_cronjob"
         |                      OR static method names as:  "MyClass::cronJob"
         |  @param  int     The timestamp for the first cronjob call (within the current year!).
         |  @param  multi   The cronjob interal in seconds as INT or as STRING:
         |                      hourly      Each Hour, starting at $time.
         |                      twicedaily  Each 12-hours, starting at $time.
         |                      daily       Each Daily, starting at $time.
         |                      weekly      Each Week, starting at $time.
         |                      monthly     Each Month, starting at $time
         |
         |  @return multi   The unique cronjob ID on success, FALSE on failure.
         */
        static public function register($cron, $callback, $time = NULL, $interval = NULL){
            $cron = preg_replace("#[^a-z0-9_-]#", "", strtolower(trim($cron)));
            if(in_array($cron, self::$cronjobs)){
                if(DEBUG_MODE){
                    throw new Exception("The passed cronjob ID '{$cron}' does already exist!");
                }
                return false;
            }

            // Check Callback function
            if(!is_string($callback) || (is_string($callable) && !is_callable($callback))){
                if(DEBUG_MODE){
                    throw new Exception("The passed callback function for the cronjob '{$cron}' is invalid!");
                }
                return false;
            }

            // Check Time
            if(empty($time)){
                $time = time();
            }
            if(date("Y", $time) < date("Y")){
                if(DEBUG_MODE){
                    throw new Exception("The passed timestamp for the cronjob '{$cron}' is invalid!");
                }
                return false;
            }

            // Check Interval
            if(is_string($interval)){
                $data = array("hourly" => 3600, "twicedaily" => 43200, "daily" => 86400, "weekly" => 604800, "monthly" => 2592000);
                $interval = isset($data[$interval])? $data[$interval]: 0;
            }
            if(!is_int($interval) || $interval < 60){
                if(DEBUG_MODE){
                    throw new Exception("The passed interval for the cronjob '{$cron}' is invalid!");
                }
                return false;
            }

            // Set new Cronjob
            $cron = new self(array(
                "cron"          => $cron,
                "status"        => "active",
                "callback"      => $callback,
                "interval"      => $interval,
                "starttime"     => date("Y-m-d H:i:s", $time),
                "nextcall"      => date("Y-m-d H:i:s", $time + $interval),
                "lastcall"      => date("Y-m-d H:i:s", 0)
            ));
            if($cron->save()){
                return $cron->cron;
            }
            return false;
        }

        /*
         |  UNREGISTER A NEW CRON JOB
         |  @since  0.8.4
         |
         |  @param  string  A unique cronjob ID as STRING.
         |
         |  @return bool    TRUE on success, FALSE on failure.
         */
        static public function unregister($cron){
            if(in_array($cron, self::$cronjobs)){
                unset(self::$cronjobs[array_search($cron, self::$cronjobs)]);
            }
            return self::deleteWhere("config_cron", array("cron = :cron"), array(":cron" => $cron));
        }


        /*
         |  DATA VARs
         */
        public $id;
        public $cron;
        public $status;
        public $callback;
        public $interval;
        public $starttime;
        public $nextcall;
        public $lastcall;

        /*
         |  CALL CRONJOB
         |  @since  0.8.4
         */
        public function call(){
            if($this->status == "inactive"){
                return false;
            }
            if(!is_callable($this->callback)){
                $this->status = "inactive";
                $this->save();
                return false;
            }

            // Check Time
            $time = strtotime($this->lastcall) + $this->interval;
            if($this->nextcall > date("Y-m-d H:i:s") || $this->nextcall <= date("Y-m-d H:i:s", $time)){
                return false;
            }

            // Call
            Event::apply("cronjob-{$this->cron}-start", array());
            $args = Event::applyFilter("cronjob-{$this->cron}-args", array(array()));
            if(is_array($args)){
                call_user_func_array($this->callback, $args);
                Event::apply("cronjob-{$this->cron}-end", array());
            }

            // Save and Return
            $this->save();
            return true;
        }

        /*
         |  DATA :: GET
         |  @since  0.8.4
         |
         |  @param  bool    TRUE to get the timestamp as INT, FALSE to get the DATETIME STRING.
         |
         |  @return multi   A integer or string, depending on $time;
         */
        public function starttime($time = true){
            return ($time)? strtotime($starttime): $this->starttime;
        }

        /*
         |  DATA :: GET
         |  @since  0.8.4
         |
         |  @param  bool    TRUE to get the timestamp as INT, FALSE to get the DATETIME STRING.
         |
         |  @return multi   A integer or string, depending on $time;
         */
        public function nextcall($time = true){
            return ($time)? strtotime($nextcall): $this->nextcall;
        }

        /*
         |  DATA :: GET
         |  @since  0.8.4
         |
         |  @param  bool    TRUE to get the timestamp as INT, FALSE to get the DATETIME STRING.
         |
         |  @return multi   A integer or string, depending on $time;
         */
        public function lastcall($time = true){
            return ($time)? strtotime($lastcall): $this->lastcall;
        }

        /*
         |  WOLF CMS :: GET LAST RUN TIME
         |  @since  0.8.4
         */
        public function getLastRunTime(){
            deprecated("Cron->getLastRunTime()", "Cron->lastcall()");
            return strtotime($this->lastcall);
        }

        /*
         |  WOLF CMS :: GENERATE WEB BUG
         |  @since  0.8.4
         */
        public function generateWebBug(){
            deprecated("Cron->generateWebBug()");
        }

        /*
         |  HOOK :: BEFORE INSERT
         |  @since  0.8.4
         */
        public function beforeInsert(){
            if(self::existsIn("config_cron", array("cron = :cron"), array(":cron" => $this->cron))){
                return false;
            }
            if(empty($this->interval) || empty($this->starttime) || !is_int($this->interval)){
                return false;
            }
            if(empty($this->nextcall)){
                $this->nextcall = date("Y-m-d H:i:s", strtotime($this->starttime) + $this->interval);
            }
            if(empty($this->lastcall)){
                $this->lastcall = date("Y-m-d H:i:s", 0);
            }
            return true;
        }

        /*
         |  HOOK :: BEFORE UPDATE
         |  @since  0.8.4
         */
        public function beforeUpdate(){
            $this->lastcall = date("Y-m-d H:i:s", time());
            if($this->status !== "active"){
                $this->nextcall = NULL;
            } else {
                $this->nextcall = date("Y-m-d H:i:s", time() + $this->interval);
            }
            return true;
        }
    }
