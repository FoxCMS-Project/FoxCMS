<?php
/*
 |  FoxCMS      Content Management Simplified <www.foxcms.org>
 |  @file       ./system/class.record.php
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

    class Record extends RecordConstants{
        /*
         |  DATA VARs
         */
        static protected $db = false;
        static protected $queries = array();

        /*
         |  HELPER :: CONNECT TO DATABASE
         |  @since  0.8.4
         |
         |  @param  bool    Prevent further PHP execution on error.
         |
         |  @return bool    TRUE on success, FALSE on failure. DIE id $dir is true.
         */
        static public function connect($die = true){
            if(!empty(self::$db)){
                return;
            }
            $pdo = false;
            $dsn = array();
            $charset = preg_replace("#[^a-z0-9]#", "", strtolower(DEFAULT_CHARSET));

            // MySQL
            if(DB_TYPE == "mysql"){
                if(!empty(DB_SOCKET)){
                    $dsn[] = "unix_socket=" . DB_SOCKET;
                } else {
                    $dsn[] = "host=" . DB_HOST;
                    $dsn[] = "port=" . DB_PORT;
                }
                $dsn[] = "dbname=" . DB_NAME;
                $dsn[] = "charset={$charset}" ;

                try{
                    $pdo = new PDO("mysql:" . implode(";", $dsn), DB_USER, DB_PASS);
                    $pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
                } catch(PDOException $error){
                    if($die){
                        die('DB Connection failed: '.$error->getMessage());
                    }
                    return false;
                }
            }

            // PostegreSQL
            if(DB_TYPE == "pgsql"){
                $dsn[] = "host=" . DB_HOST;
                $dsn[] = "port=" . DB_PORT;
                $dsn[] = "dbname=" . DB_NAME;
                $dsn[] = "user=" . DB_USER;
                $dsn[] = "password=" . DB_PASS;

                try{
                    $pdo = new PDO("pgsql:" . implode(";", $dsn));
                } catch(PDOException $error){
                    if($die){
                        die('DB Connection failed: '.$error->getMessage());
                    }
                    return false;
                }
            }

            // SQLite3
            if(DB_TYPE == "sqlite" && file_exists(DB_NAME) && is_file(DB_NAME)){
                try{
                    $pdo = new PDO("sqlite:" . DB_NAME);
                    if(is_a($pdo, "PDO")){
                        $pdo->sqliteCreateFunction("date_format", function($date, $format){
                            return strftime($format, strtotime($date));
                        }, 2);
                    }
                } catch(PDOException $error){
                    if($die){
                        die('DB Connection failed: '.$error->getMessage());
                    }
                    return false;
                }
            }

            // Init DB
            if(is_a($pdo, "PDO")){
                self::$db = $pdo;
                self::$db->exec("SET NAMES '{$charset}'");
                return true;
            }
            return false;
        }
        static public function connection(){
            return self::connect();
        }

        /*
         |  HELPER :: GET CONNECTION
         |  @since  0.8.4
         |
         |  @return object  The PDO object.
         */
        static public function getConnection(){
            return self::$db;
        }

        /*
         |  HELPER :: LOG A SQL QUERY
         |  @since  0.8.4
         */
        static public function logQuery($query){
            self::$queries[] = $query;
        }

        /*
         |  HELPER :: GET QUERY LOG
         |  @since  0.8.4
         |
         |  @return multi   The SQL Query log ARRAY on success, an empty ARRAY on failure.
         */
        static public function getQuerLog(){
            return self::$queries;
        }

        /*
         |  HELPER :: GET LAST QUERY
         |  @since  0..8.4
         |
         |  @return multi   The last SQL Query STRING on success, NULL on failure.
         */
        static public function getLastQuery(){
            if(count(self::$queries) == 0){
                return NULL;
            }
            return self::$queries[count(self::$queries)-1];
        }

        /*
         |  HELPER :: GET QUERY LOG COUNT
         |  @since  0.8.4
         |
         |  @return int     The SQL Query log count as INTEGER.
         */
        static public function getQueryCount(){
            return count(self::$queries);
        }

        /*
         |  HELPER :: PREFIX
         |  @since  0.8.4
         |
         |  @param  string  The table name to prefix.
         |
         |  @return string  The prefixed table name.
         */
        static public function prefix($table){
            if(!empty(DB_PREFIX) && !starts_with($table, DB_PREFIX)){
                $table = DB_PREFIX . $table;
            }
            return $table;
        }

        /*
         |  HELPER :: TABLE NAME
         |  @since  0.8.4
         |
         |  @param  string  The table name or class name as STRING.
         |  @param  bool    TRUE to prefix the table name, FALSE to do it not.
         |
         |  @return multi   The table name as STRING, FALSE on failure.
         */
        static public function table($data, $prefix = true){
            if(AutoLoader::exists($data)){
                if(defined("{$data}::TABLE_NAME")){
                    $data = constant("{$data}::TABLE_NAME");
                } else if(defined("{$data}::TABLE")){
                    $data = constant("{$data}::TABLE");
                }
            }
            return ($prefix)? self::prefix($data): $data;
        }
        static public function tableNameFromClassName($class){
            deprecated("Record::tableNameFromClassName", "Record::table");
            return self::table($class);
        }

        /*
         |  HELPER :: QUOTE DATA
         |  @since  0.8.4
         |
         |  @param  string  The respective data to quote / escape.
         |
         |  @return string  The quoted / escaped data string.
         */
        static public function quote($data){
            return self::$db->quote($data);
        }
        static public function escape($data){
            return self::quote($data);
        }

        /*
         |  HELPER :: LAST INSERT ID
         |  @since  0.8.4
         |
         |  @return int     The last insert ID as INT, 0 on failure.
         */
        static public function lastInsertId(){
            if(DB_TYPE == "pgsql"){
                $last = self::getLastQuery();
                if(($num = strpos($last, "INSERT INTO")) === false){
                    return 0;
                }

                $last = trim(substr($last, $num+strlen("INSERT INTO")));
                $last = explode(" ", $last, 2);
                return self::$db->lastInsertId("{$last[0]}_id_seq");
            }
            return self::$db->lastInsertId();
        }

        /*
         |  CORE :: DO A QUERY
         |  @since  0.8.4
         |
         |  @param  string  The SQL Query as STRING.
         |  @param  array   Some additional prepared arguments.
         |
         |  @return multi   An array of objects, a PDOStatement object or FALSE on failure.
         */
        static public function query($query, $prepare = array()){
            self::logQuery($query);

            if(is_array($prepare)){
                $stmt = self::$db->prepare($query);
                $stmt->execute($prepare);
                return $stmt->fetchAll(self::FETCH_OBJ);
            }
            $result = self::$db->query($query);
            return $result;
        }

        /*
         |  CORE :: INSERT A RECORD
         |  @since  0.8.4
         |
         |  @param  string  The class name of the record.
         |  @param  array   The key => value ARRAY pairs to insert.
         |  @param  array   Some optional prepared PDO statements.
         |
         |  @return bool    TRUE on success, FALSE on failure.
         */
        static public function insert($class, $data, $prepare = array()){
            $values = array();

            // Fetch Data
            foreach($data AS $key => $value){
                if(is_string($value) && array_key_exists($value, $prepare)){
                    $values[$key] = $value;
                } else {
                    $values[$key] = self::$db->quote(serializer($value));
                }
            }

            // Query
            $table  = self::table($class);
            $query  = "INSERT INTO {$table} (".implode(", ", array_keys($values)).") ";
            $query .= "VALUES (".implode(", ", array_values($values)).");";
            self::logQuery($query);

            if(!empty($prepare)){
                $stmt = self::$db->prepare($query);
                return $stmt->execute($prepare);
            }
            return self::$db->exec($query) !== false;
        }

        /*
         |  CORE :: UPDATE A RECORD
         |  @since  0.8.4
         |
         |  @param  string  The class name of the record.
         |  @param  array   The key => value ARRAY pairs to update.
         |  @param  string  The where clause as STRING.
         |  @param  array   Some optional prepared PDO statements.
         |
         |  @return bool    TRUE on success, FALSE on failure.
         */
        static public function update($class, $data, $where, $prepare = array()){
            $values = array();

            // Fetch Data
            foreach($data AS $key => $value){
                if(array_key_exists($value, $prepare)){
                    $values[$key] = "{$key} = {$value}";
                } else {
                    $values[$key] = "{$key} = " . self::$db->quote(serializer($value));
                }
            }

            // Query
            $table = self::table($class);
            $query = "UPDATE {$table} SET " . implode(", ", $values)." WHERE {$where};";
            self::logQuery($query);

            if(!empty($prepare)){
                $stmt = self::$db->prepare($query);
                return $stmt->execute($prepare);
            }
            return self::$db->exec($query) !== false;
        }

        /*
         |  CORE :: DELETE A RECORD
         |  @since  0.8.4
         |
         |  @param  string  The class name of the record.
         |  @param  string  The where clause as STRING.
         |  @param  array   Some optional prepared PDO statements.
         |
         |  @return bool    TRUE on success, FALSE on failure.
         */
        static public function deleteWhere($class, $where, $prepare = array()){
            $table = self::table($class);
            $query = "DELETE FROM {$table} WHERE {$where};";
            self::logQuery($query);

            // Query
            if(!empty($prepare)){
                $stmt = self::$db->prepare($query);
                return $stmt->execute($prepare);
            }
            return self::$db->exec($query) !== false;
        }

        /*
         |  CORE :: CHECK IF A RECORD EXISTS
         |  @since  0.8.4
         |
         |  @param  string  The class name of the record.
         |  @param  string  The where clause as STRING or FALSE.
         |  @param  array   Some optional prepared PDO statements.
         |
         |  @return bool    TRUE on success, FALSE on failure.
         */
        static public function existsIn($class, $where = false, $prepare = array()){
            $table = self::table($class);
            $where = ($where)? " WHERE {$where}": "";
            $query = "SELECT EXISTS(SELECT 1 FROM {$table} {$where}) LIMIT 1;";
            self::logQuery($query);

            // Query
            if(!empty($prepare)){
                $stmt = self::$db->prepare($query);
                $stmt->execute($prepare);
                return (bool) $stmt->fetchColumn();
            }
            return self::$db->exec($query);
        }

        /*
         |  CORE :: CHECK IF A TABLE EXISTS
         |  @since  0.8.4
         |
         |  @param  string  The table name or class name.
         |
         |  @return bool    TRUE on success, FALSE on failure.
         */
        static public function exists($table){
            $table = self::table($table);
            if(DB_TYPE == "sqlite"){
                $query = "SELECT COUNT(*) AS c FROM sqlite_master WHERE type = 'table' AND name = :table;";
            } else if(DB_TYPE == "pgsql"){
                $query = "SELECT COUNT(*) AS c FROM pg_tables WHERE tablename = :table;";
            } else {
                $query = "SELECT COUNT(*) AS c FROM information_schema.tables WHERE table_name = :table;";
            }
            self::logQuery($query);

            // Query
            $stmt = self::$db->prepare($query);
            if($stmt->execute(array(":table" => $table))){
                return $stmt->fetchColumn() > 0;
            }
            return false;
        }

        /*
         |  FINDER :: MAIN METHOD
         |  @since  0.8.4
         |
         |  @param  array   An array with additional query options.
         |
         |  @return multi   An array of Records or FALSE on failure.
         */
        static public function find($options = array()){
            $class   = get_called_class();
            $table   = self::table($class);
            $options = (!is_array($options))? array(): $options;

            // Query Data
            $ses    = isset($options["select"]) ? trim($options["select"])   : "";
            $frs    = isset($options["from"])   ? trim($options["from"])     : "";
            $jos    = isset($options["joins"])  ? trim($options["joins"])    : "";
            $whs    = isset($options["where"])  ? trim($options["where"])    : "";
            $gbs    = isset($options["group"])  ? trim($options["group"])    : "";
            $has    = isset($options["having"]) ? trim($options["having"])   : "";
            $obs    = isset($options["order"])  ? trim($options["order"])    : "";
            $lis    = isset($options["limit"])  ? (int) $options["limit"]    : 0;
            $ofs    = isset($options["offset"]) ? (int) $options["offset"]   : 0;
            $values = isset($options["values"]) ? (array) $options["values"] : array();
            $single = ($lis === 1) ? true : false;

            // Prepare Query Parts
            $select      = empty($ses) ? "SELECT *"         : "SELECT $ses";
            $from        = empty($frs) ? "FROM $table"      : "FROM $frs";
            $joins       = empty($jos) ? ""                 : $jos;
            $where       = empty($whs) ? ""                 : "WHERE $whs";
            $group_by    = empty($gbs) ? ""                 : "GROUP BY $gbs";
            $having      = empty($has) ? ""                 : "HAVING $has";
            $order_by    = empty($obs) ? ""                 : "ORDER BY $obs";
            $limit       = $lis > 0    ? "LIMIT $lis"       : "";
            $offset      = $ofs > 0    ? "OFFSET $ofs"      : "";

            // Query
            $query  = "$select $from $joins $where $group_by $having $order_by $limit $offset";
            $return = self::findBySql($query, $values);
            return ($single)? (!empty($return) ? $return[0] : false) : $return;
        }

        /*
         |  FINDER :: FIND BY SQL
         |  @since  0.8.4
         |
         |  @param  string  The complete SQL query as STRING.
         |  @param  array   Some optional prepared PDO statements.
         |
         |  @return multi   An array of Records or FALSE on failure.
         */
        static private function findBySql($query, $prepare = array()){
            $class = get_called_class();
            self::logQuery($query);

            // Prepare and Execute
            if(!empty($prepare)){
                $stmt = self::$db->prepare($query);
                if($stmt->execute($prepare) == false){
                    return false;
                }
            } else {
                if(($stmt = self::$db->query($query)) == false){
                    return false;
                }
            }

            // Fetch and Return
            $return = array();
            while($object = $stmt->fetchObject($class)){
                $return[] = $object;
            }
            return $return;
        }

        /*
         |  FINDER :: FIND BY ID
         |  @since  0.8.4
         |
         |  @param  int     The respective id.
         |
         |  @return multi   A single Record or FALSE on failure.
         */
        static public function findById($id){
            return self::findOne(array(
                "where"     => "id = :id",
                "values"    => array(":id" => (int) $id)
            ));
        }

        /*
         |  FINDER :: FIND BY ID FROM
         |  @since  0.8.4
         |
         |  @param  string  The class name of the record.
         |  @param  int     The respective id.
         |
         |  @return multi   A single Record or FALSE on failure.
         */
        static public function findByIdFrom($class, $id){
            if(!method_exists($class, "findById")){
                return false;
            }
            return $class::findById($id);
        }

        /*
         |  FINDER :: FIND ONE
         |  @since  0.8.4
         |
         |  @param  array   An array with additional query options.
         |
         |  @return multi   A single Record or FALSE on failure.
         */
        static public function findOne($options = array()){
            return self::find(array_merge($options, array("limit" => 1)));
        }

        /*
         |  FINDER :: FIND ONE FROM
         |  @since  0.8.4
         |
         |  @param  string  The class name of the record.
         |  @param  string  The where clause as STRING or FALSE.
         |  @param  array   Some optional prepared PDO statements.
         |
         |  @return multi   A single Record or FALSE on failure.
         */
        static public function findOneFrom($class, $where, $prepare = array()){
            if(!method_exists($class, "findOne")){
                return false;
            }
            return $class::findOne(array("where" => $where, "values" => $prepare));
        }

        /*
         |  FINDER :: FIND ALL FROM
         |  @since  0.8.4
         |
         |  @param  string  The class name of the record.
         |  @param  string  The where clause as STRING or FALSE.
         |  @param  array   Some optional prepared PDO statements.
         |
         |  @return multi   An array of Records or FALSE on failure.
         */
        static public function findAllFrom($class, $where = false, $prepare = array()){
            if(!method_exists($class, "find")){
                return false;
            }
            if($where){
                return $class::find(array("where" => $where, "values" => $prepare));
            }
            return $class::find();
        }

        /*
         |  CORE :: COUNT SOME DATA
         |  @since  0.8.4
         |
         |  @param  string  The class name of the record.
         |  @param  string  The where clause as STRING or FALSE.
         |  @param  array   Some optional prepared PDO statements.
         |
         |  @return int     The respective count.
         */
        static public function countFrom($class, $where = false, $prepare = array()){
            $table = self::table($class);
            $where = ($where)? " WHERE {$where}": "";
            $query = "SELECT COUNT(*) AS nb_rows FROM {$table} {$where};";
            self::logQuery($query);

            // Query
            if(!empty($prepare)){
                $stmt = self::$db->prepare($query);
                $stmt->execute($prepare);
                return (int) $stmt->fetchColumn();
            }
            return self::$db->exec($query);
        }


        /*
         |  INSTANCE VARs
         */
        protected $_columns = array();

        /*
         |  CONSTRUCTOR
         |  @since  0.8.4
         */
        public function __construct($data = false){
            if(is_array($data)){
                $this->setFromData($data);
            }
        }

        /*
         |  INSTANCE :: SET DATA
         |  @since  0.8.4
         |
         |  @param  array   Multipel key => value array pairs.
         |
         |  @return void
         */
        public function setFromData($data){
            if(is_object($data)){
                $data = (array) $data;
            }
            if(!is_array($data)){
                return;
            }

            foreach($data AS $key => $value){
                if(starts_with($key, array("_", "*", "\0"))){
                    continue;
                }
                if(is_numeric($value)){
                    $float = floatval($value);
                    if($float && intval($float) != $float){
                        $this->{$key} = (float) $value;
                    } else {
                        $this->{$key} = (int) $value;
                    }
                } else {
                    $this->{$key} = unserializer($value);
                }
                $this->_columns[] = $key;
            }
        }

        /*
         |  INSTANCE :: SAVE ITEM
         |  @since  0.8.4
         |
         |  @return bool    TRUE on success, FALSE on failure.
         */
        public function save(){
            if(!$this->beforeSave()){
                return false;
            }
            $values = array();

            if(empty($this->id)){
                if(!$this->beforeInsert()){
                    return false;
                }

                // Fetch Data
                $columns = $this->getColumns();
                foreach($columns AS $column){
                    if($column === "id" || starts_with($column, "_")){
                        continue;
                    }

                    if(!empty($this->$column) || is_numeric($this->$column)){
                        $values[$column] = self::$db->quote(serializer($this->$column));
                    } else if(isset($this->$column)){
                        if(DB_TYPE != "sqlite"){
                            $values[$column] = "DEFAULT";
                        }
                    }
                }

                // Query
                $table  = self::table(get_class($this));
                $query  = "INSERT INTO {$table} (".implode(", ", array_keys($values)).") ";
                $query .= "VALUES (".implode(", ", array_values($values)).");";
                self::logQuery($query);

                if(($return = self::$db->exec($query)) === false){
                    return false;
                }
                $this->id = self::lastInsertId();

                // Hook After
                if(!$this->afterInsert()){
                    return false;
                }
            } else {
                if(!$this->beforeUpdate()){
                    return false;
                }

                // Fetch Data
                $columns = $this->getColumns();
                foreach($columns AS $column){
                    if($column === "id" || starts_with($column, "_")){
                        continue;
                    }

                    if(!empty($this->$column) || is_numeric($this->$column)){
                        $values[$column] = "{$column} = " . self::$db->quote(serializer($this->$column));
                    } else if(isset($this->$column)){
                        if(DB_TYPE != "sqlite"){
                            $values[$column] = "{$column} = DEFAULT";
                        } else {
                            $values[$column] = "{$column} = ''";
                        }
                    }
                }

                // Query
                $table  = self::table(get_class($this));
                $query  = "UPDATE {$table} SET " . implode(", ", $values)." ";
                $query .= "WHERE id = {$this->id};";
                self::logQuery($query);

                if(($return = self::$db->exec($query)) === false){
                    return false;
                }

                // Hook After
                if(!$this->afterUpdate()){
                    return false;
                }
            }

            // Hook After
            if(!$this->afterSave()){
                return false;
            }
            return $return !== false;
        }

        /*
         |  INSTANCE :: DELETE ITEM
         |  @since  0.8.4
         |
         |  @return bool    TRUE on success, FALSE on failure.
         */
        public function delete(){
            if(!$this->beforeDelete()){
                return false;
            }

            // Query
            $table = self::table(get_class($this));
            $query = "DELETE FROM {$table} WHERE id = " . self::$db->quote((int) $this->id) . ";";
            if(($return = self::$db->exec($query)) === false){
                return false;
            }

            // Hook After
            if(!$this->afterDelete()){
                $this->save();
                return false;
            }
            self::logQuery($query);
            return $return !== false;
        }

        /*
         |  INSTANCE :: TRY TO GET ALL TABLE COLUMNS
         |  @since  0.8.4
         |
         |  @param  bool    TRUE to try to fetch all object vars, FALSE to return the index.
         |
         |  @return array   An array with probable all table columns.
         */
        public function getColumns(){
            $vars = array_keys(get_object_vars($this));
            foreach($vars AS &$var){
                if(starts_with($var, "_")){
                    $var = NULL;
                }
            }
            return array_filter($vars);
        }

        /*
         |  HOOK :: BEFORE SAVE
         |  @since  0.8.4
         */
        public function beforeSave(){
            return true;
        }

        /*
         |  HOOK :: AFTER SAVE
         |  @since  0.8.4
         */
        public function afterSave(){
            return true;
        }

        /*
         |  HOOK :: BEFORE INSERT
         |  @since  0.8.4
         */
        public function beforeInsert(){
            return true;
        }

        /*
         |  HOOK :: AFTER INSERT
         |  @since  0.8.4
         */
        public function afterInsert(){
            return true;
        }

        /*
         |  HOOK :: BEFORE UPDATE
         |  @since  0.8.4
         */
        public function beforeUpdate(){
            return true;
        }

        /*
         |  HOOK :: AFTER UPDATE
         |  @since  0.8.4
         */
        public function afterUpdate(){
            return true;
        }

        /*
         |  HOOK :: BEFORE DELETE
         |  @since  0.8.4
         */
        public function beforeDelete(){
            return true;
        }

        /*
         |  HOOK :: AFTER DELETE
         |  @since  0.8.4
         */
        public function afterDelete(){
            return true;
        }
    }


    /*
     |  This class constants are part of the normal Wolf Record class. I'm unsure if all
     |  of them are still | really needed, but for security and backward compatibility
     |  reasons, I will leave them for the moment.
     */
    class RecordConstants{
        const PARAM_BOOL = 5;
        const PARAM_NULL = 0;
        const PARAM_INT = 1;
        const PARAM_STR = 2;
        const PARAM_LOB = 3;
        const PARAM_STMT = 4;
        const PARAM_INPUT_OUTPUT = -2147483648;
        const PARAM_EVT_ALLOC = 0;
        const PARAM_EVT_FREE = 1;
        const PARAM_EVT_EXEC_PRE = 2;
        const PARAM_EVT_EXEC_POST = 3;
        const PARAM_EVT_FETCH_PRE = 4;
        const PARAM_EVT_FETCH_POST = 5;
        const PARAM_EVT_NORMALIZE = 6;

        const FETCH_LAZY = 1;
        const FETCH_ASSOC = 2;
        const FETCH_NUM = 3;
        const FETCH_BOTH = 4;
        const FETCH_OBJ = 5;
        const FETCH_BOUND = 6;
        const FETCH_COLUMN = 7;
        const FETCH_CLASS = 8;
        const FETCH_INTO = 9;
        const FETCH_FUNC = 10;
        const FETCH_GROUP = 65536;
        const FETCH_UNIQUE = 196608;
        const FETCH_CLASSTYPE = 262144;
        const FETCH_SERIALIZE = 524288;
        const FETCH_PROPS_LATE = 1048576;
        const FETCH_NAMED = 11;

        const ATTR_AUTOCOMMIT = 0;
        const ATTR_PREFETCH = 1;
        const ATTR_TIMEOUT = 2;
        const ATTR_ERRMODE = 3;
        const ATTR_SERVER_VERSION = 4;
        const ATTR_CLIENT_VERSION = 5;
        const ATTR_SERVER_INFO = 6;
        const ATTR_CONNECTION_STATUS = 7;
        const ATTR_CASE = 8;
        const ATTR_CURSOR_NAME = 9;
        const ATTR_CURSOR = 10;
        const ATTR_ORACLE_NULLS = 11;
        const ATTR_PERSISTENT = 12;
        const ATTR_STATEMENT_CLASS = 13;
        const ATTR_FETCH_TABLE_NAMES = 14;
        const ATTR_FETCH_CATALOG_NAMES = 15;
        const ATTR_DRIVER_NAME = 16;
        const ATTR_STRINGIFY_FETCHES = 17;
        const ATTR_MAX_COLUMN_LEN = 18;
        const ATTR_EMULATE_PREPARES = 20;
        const ATTR_DEFAULT_FETCH_MODE = 19;

        const ERRMODE_SILENT = 0;
        const ERRMODE_WARNING = 1;
        const ERRMODE_EXCEPTION = 2;
        const CASE_NATURAL = 0;
        const CASE_LOWER = 2;
        const CASE_UPPER = 1;
        const NULL_NATURAL = 0;
        const NULL_EMPTY_STRING = 1;
        const NULL_TO_STRING = 2;
        const ERR_NONE = '00000';
        const FETCH_ORI_NEXT = 0;
        const FETCH_ORI_PRIOR = 1;
        const FETCH_ORI_FIRST = 2;
        const FETCH_ORI_LAST = 3;
        const FETCH_ORI_ABS = 4;
        const FETCH_ORI_REL = 5;
        const CURSOR_FWDONLY = 0;
        const CURSOR_SCROLL = 1;
        const MYSQL_ATTR_USE_BUFFERED_QUERY = 1000;
        const MYSQL_ATTR_LOCAL_INFILE = 1001;
        const MYSQL_ATTR_INIT_COMMAND = 1002;
        const MYSQL_ATTR_READ_DEFAULT_FILE = 1003;
        const MYSQL_ATTR_READ_DEFAULT_GROUP = 1004;
        const MYSQL_ATTR_MAX_BUFFER_SIZE = 1005;
        const MYSQL_ATTR_DIRECT_QUERY = 1006;
    }
