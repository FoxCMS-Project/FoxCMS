<?php
/*
 |  FoxCMS      Content Management Simplified <www.foxcms.org>
 |  @file       ./system/class.page.php
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

    class Page extends Content{
        const TABLE = "page";

        // Deprecated Constants (See ROADMAP)
        const STATUS_DRAFT = 1;
        const STATUS_PREVIEW = 10;
        const STATUS_PUBLISHED = 100;
        const STATUS_HIDDEN = 101;
        const STATUS_ARCHIVED = 200;
        const LOGIN_NOT_REQUIRED = 0;
        const LOGIN_REQUIRED = 1;
        const LOGIN_INHERIT = 2;

        /*
         |  HELPER :: GET URL BY ID
         |  @since  0.8.4
         |
         |  @param  int     The Page id as INT.
         |  @param  bool    TRUE to add the URL_SUFFIX, False to do it not.
         |
         |  @return string  The full path to this page, NULL on failure.
         */
        static public function urlById($page_id, $suffix = true){
            if(!is_numeric($page_id) || $page_id <= 0){
                return NULL;
            }
            if(($page = self::findById($page_id)) == false){
                return NULL;
            }
            return $page->url($suffix);
        }

        /*
         |  HELPER :: GET LINK BY ID
         |  @since  0.8.4
         |
         |  @param  int     The Page id as INT.
         |  @param  string  The used title, NULL to use the Page title.
         |  @param  string  Some additional link attributes.
         |
         |  @return string  The link element as STRING, NULL on failure.
         */
        static public function linkById($page_id, $title = NULL, $attributes = ""){
            if(!is_numeric($page_id) || $page_id <= 0){
                return NULL;
            }
            if(($page = self::findById($page_id)) == false){
                return NULL;
            }
            return $page->link($title, $attributes);
        }

        /*
         |  FINDER :: FIND
         |  @since  0.8.4
         |
         |  @param  array   An array with additional query options.
         |
         |  @return multi   An array of Post objects or FALSE on failure.
         */
        static public function find($options = array()){
            if(!is_array($options)){
                return self::findByPath($options);
            }
            $class   = get_called_class();
            $table   = self::table($class);
            $prefix  = DB_PREFIX;

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

            // Joins
            $jos   .= "LEFT JOIN {$prefix}user creator ON page.created_by = creator.id ";
            $jos   .= "LEFT JOIN {$prefix}user updater ON page.updated_by = updater.id ";
            $jos   .= "LEFT JOIN {$prefix}user publisher ON page.published_by = publisher.id ";

            // Prepare Query Parts
            $select      = "page.*, creator.name AS author, updater.name AS updater, publisher.name AS publisher ";
            $select      = empty($ses) ? "SELECT $select"     : "SELECT $ses";
            $from        = empty($frs) ? "FROM $table AS page": "FROM $frs";
            $joins       = empty($jos) ? ""                   : $jos;
            $where       = empty($whs) ? ""                   : "WHERE $whs";
            $group_by    = empty($gbs) ? ""                   : "GROUP BY $gbs";
            $having      = empty($has) ? ""                   : "HAVING $has";
            $order_by    = empty($obs) ? ""                   : "ORDER BY $obs";
            $limit       = $lis > 0    ? "LIMIT $lis"         : "";
            $offset      = $ofs > 0    ? "OFFSET $ofs"        : "";

            // Query
            $query  = "$select $from $joins $where $group_by $having $order_by $limit $offset";
            $return = self::findBySql($query, $values);
            return ($single)? (!empty($return) ? $return[0] : false) : $return;
        }
        static public function findAll($options = array()){
            return self::find($options = array());
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
                if($stmt->execute($prepare) === false){
                    return false;
                }
            } else {
                if(($stmt = self::$db->query($query)) == false){
                    return false;
                }
            }

            // Fetch and Return
            $class = "Page";
            $return = array();
            while($page = $stmt->fetchObject($class)){
                if(($parent = $page->parent()) !== false && !empty($parent->behaviout_id)){
                    $class = Behavior::loadPageHack($parent->behavior_id);
                }

                $page = new $class($page, $parent);
                $page->part = self::getParts($page->id);
                $return[] = $page;
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
                "where"     => "page.id = :id",
                "values"    => array(":id" => (int) $id)
            ));
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
         |  FINDER :: FIND BY PATH
         |  @since  0.8.4
         |
         |  @param  string  The path/to/the Page as STRING.
         |  @param  bool    TRUE to return status-type posts.
         |
         |  @return multi   The Page object or FALSE in failure
         */
        static public function findByPath($path, $all = false){
            $path = trim($path, "/");
            $page = new stdClass();
            $page->id = 0;

            // Loop
            $url = "";
            $slugs = array_merge(array(""), explode_path($path));
            $parent = 0;
            foreach($slugs AS $slug){
                $url = ltrim($url . "/" . $slug, "/");

                $page = self::findBySlug($slug, $parent, $all);
                if(is_a($page, "Page")){
                    if(!empty($page->behavior_id)){
                        $params = explode_path(substr($path, strlen($url)));
                        $page->{$page->behavior_id} = Behavior::load($page->behavior_id, $page, $params);
                        return $page;
                    }
                } else {
                    break;
                }
                $parent = $page;
            }
            return $page;
        }
        static public function find_page_by_uri($path){
            deprecated("", "Page::findByPath");
            return self::findByPath($path);
        }
        static public function findByUri($path, $all = false){
            deprecated("", "Page::findByPath");
            return self::findByPath($path, $all);
        }

        /*
         |  FINDER :: FIND BY SLUG
         |  @since  0.8.4
         |
         |  @param  string  The respective Page salt.
         |  @param  object  The parent Post object of False.
         |  @param  bool    TRUE to return all status-type posts, FALSE to just use public.
         |
         |  @return multi   The Page object or FALSE in failure
         */
        static public function findBySlug($slug, &$parent, $all = false){
            if(empty($slug)){
                return self::findOne(array("where" => "page.parent_id = 0"));
            }
            $parent_id = ($parent)? (int) $parent->id: 0;

            if($all){
                return self::findOne(array(
                    "where"     => "page.slug = :slug AND page.parent_id = :pid",
                    "values"    => array(":slug" => $slug, ":pid" => $parent_id)
                ));
            }
            return self::findOne(array(
                "where"     => "page.slug = :slug AND page.parent_id = :pid AND (page.status_id IN (".self::STATUS_PUBLISHED.", ".self::STATUS_HIDDEN."))",
                "values"    => array(":slug" => $slug, ":pid" => $parent_id)
            ));
        }

        /*
         |  FINDER :: FIND BY BEHAVIOR
         |  @since  0.8.4
         |
         |  @param  string  The respective behvaior name as STRING.
         |  @param  multi   The Post parent id as INT or false.
         |
         |  @return multi   The Page object or FALSE in failure
         */
        static public function findByBehavior($name, $parent_id = false){
            if(is_numeric($parent_id)){
                return self::findOne(array(
                    "where"     => "page.behavior_id = :bid AND page.parent_id = :pid",
                    "values"    => array(":bid" => $name, ":pid" => (int) $parent_id)
                ));
            }
            return self::findOne(array(
                "where"     => "page.behavior_id = :bid",
                "values"    => array(":bid" => $name)
            ));
        }

        /*
         |  FINDER :: ALL CHILDRENs
         |  @since  0.8.4
         |
         |  @param  int     The Post parent id as INT.
         |
         |  @return multi   The Page objects as ARRAY or FALSE in failure.
         */
        static public function childrenOf($parent_id){
            return self::find(array(
                "where"     => "page.parent_id = :pid",
                "values"    =>  array(":pid" => (int) $parent_id),
                "order"     => "page.position ASC, page.id DESC"
            ));
        }

        /*
         |  FINDER :: CHECK IF CHILDREN EXISTS
         |  @since  0.8.4
         |
         |  @param  int     The Post parent id as INT.
         |
         |  @return bool    TRUE if the post has childs, FALSE if not.
         */
        static public function hasChildren($parent_id){
            return self::countFrom("page", "parent_id = :pid", array(":pid" => (int) $parent_id)) > 0;
        }

        /*
         |  DELETE CHILDREN OF
         |  @since  0.8.4
         |
         |  @param  int     The Post parent id as INT.
         |
         |  @return bool    TRUE if the children could be deleted, FALSE if not.
         */
        static public function deleteChildrenOf($parent_id){
            if(!is_numeric($parent_id) || $parent_id <= 0){
                return false;
            }

            if(self::hasChildren($parent_id)){
                if(($children = self::childrenOf($id)) == false){
                    return false;
                }
                if(is_a($children, "Page")){
                    $children = array($children);
                }
                foreach($children AS $child){
                    if(!$child->delete()){
                        return false;
                    }
                }
            }
            return true;
        }

        /*
         |  CLONE TREE
         |  @since  0.8.4
         |
         |  @param  object  The page object.
         |  @param  int     The Post parent id as INT.
         |
         |  @return multi   The new root ID on success, FALSE on failure.
         */
        static public function cloneTree($page, $parent_id){
            if(!is_a($page, "Page") || !is_numeric($parent_id)){
                return false;
            }
            static $new_root_id = false;

            // Clone
            $clone = Page::findById($page->id);
            $clone->id = NULL;
            $clone->parent_id = (int) $parent_id;

            if(!$new_root_id){
                $clone->slug  .= "-copy";
                $clone->title .= " (copy)";
            }
            if(!$clone->save()){
                return false;
            }

            // Clone Page Parts
            $parts = PagePart::findByPageId($page->id);
            foreach($parts AS $part){
                $part->id = NULL;
                $part->page_id = $clone->id;
                $part->save();
            }

            // Clone page Tags
            $tags = $page->tags();
            foreach($tags AS $id => $name){
                $tag = new PageTag(array("page_id" => $clone->id, "tag_id" => $id));
                $tag->save();
            }

            // Recursive
            if(!$new_root_id){
                $new_root_id = $clone->id;
            }

            // Close and Update childrens
            if(Page::hasChildren($page->id)){
                foreach(Page::childrenOf($page->id) AS $child){
                    Page::cloneTree($child, $clone->id);
                }
            }
            return $new_root_id;
        }

        /*
         |  GET PAGE PARTs
         |  @since  0.8.4
         |
         |  @param  int     The Page id as INT.
         |
         |  @return object  The page parts within an OBJECT.
         */
        static public function getParts($page_id){
            $prefix = DB_PREFIX;
            $query  = "SELECT name, content_html FROM {$prefix}page_part WHERE page_id = :pid;";
            Record::logQuery($query);

            // Handle
            $return = new stdClass();
            if($stmt = Record::getConnection()->prepare($query)){
                $stmt->execute(array(":pid" => $page_id));
                while($part = $stmt->fetchObject()){
                    $return->{$part->name} = $part;
                }
            }
            return $return;
        }
        static public function get_parts($page_id){
            deprecated("Page::get_parts()", "Page::getParts()");
            return self::getParts($page_id);
        }


        /*
         |  DATA VARs
         */
        public $id = 0;

        public $slug;
        public $title;
        public $breadcrumb;
        public $keywords;
        public $description;
        public $position;

        public $parent_id;
        public $layout_id;
        public $status_id;
        public $behavior_id;

        public $is_protected;
        public $needs_login;

        public $created_on;
        public $updated_on;
        public $published_on;
        public $created_by;
        public $updated_by;
        public $published_by;
        public $valid_until;

        /*
         |  INSTANCE VARs
         */
        public $author;
        public $updater;
        public $publisher;
        protected $parent = false;
        protected $path = false;
        protected $level = false;
        protected $tags = false;

        /*
         |  CONSTRUCTOR
         |  @since  0.8.4
         */
        public function __construct($object = NULL, $parent = NULL){
            if(!empty($parent)){
                $this->parent = $parent;
            }
            if(!empty($object)){
                $this->setFromData((array) $object);
            }
        }

        /*
         |  OVERWRITE GET COLUMNS
         |  @since  0.8.4
         */
        public function getColumns(){
            return array(
                "id", "slug", "title", "breadcrumb", "keywords", "description", "position",
                "parent_id", "layout_id", "status_id", "behavior_id", "is_protected",
                "needs_login", "created_on", "updated_on", "published_on", "created_by",
                "updated_by", "published_by", "valid_until"
            );
        }

        /*
         |  GET EXECUTIONT TIME (?)
         |  @since  0.8.4
         */
        public function executionTime(){
            return execution_time();
        }

        /*
         |  DATA FUNCTIONs
         |  @since  0.8.4
         */
        public function id(){
            return $this->id;
        }
        public function slug(){
            return $this->slug;
        }
        public function title(){
            return $this->title;
        }
        public function breadcrumb(){
            return $this->breadcrumb;
        }
        public function keywords(){
            return $this->keywords;
        }
        public function description(){
            return $this->description;
        }
        public function position(){
            return $this->position;
        }
        public function parentID(){
            return $this->parent_id;
        }
        public function layoutID(){
            return $this->layout_id;
        }
        public function statusID(){
            return $this->status_id;
        }
        public function behaviorID(){
            return $this->behavior_id;
        }
        public function author(){
            return $this->author;
        }
        public function updater(){
            return $this->updater;
        }
        public function publisher(){
            return $this->publisher;
        }
        public function authorID(){
            return $this->created_by;
        }
        public function updaterID(){
            return $this->updated_by;
        }
        public function publisherID(){
            return $this->publisher_by;
        }

        /*
         |  IS LOGIN NEEDED
         |  @since  0.8.4
         */
        public function getLoginNeeded(){
            if($this->needs_login == self::LOGIN_INHERIT && ($parent = $this->parent()) !== false){
                return $parent->getLoginNeeded();
            }
            return $this->needs_login == self::LOGIN_REQUIRED;
        }
        public function loginNeeded(){
            return $this->getLoginNeeded();
        }

        /*
         |  GET PAGE PATH
         |  @since  0.8.4
         |
         |  @return string  The full path to this page.
         */
        public function path(){
            if(!$this->path){
                if($this->parent() !== false){
                    $this->path = trim($this->parent()->path()."/".$this->slug, "/");
                } else {
                    $this->path = trim($this->slug, "/");
                }
            }
            return $this->path;
        }
        public function uri(){
            deprecated("Page->uri()", "Page->path()");
            return $this->path();
        }
        public function getUri(){
            deprecated("Page->uri()", "Page->path()");
            return $this->path();
        }

        /*
         |  GET PAGE URL
         |  @since  0.8.4
         |
         |  @param  bool    TRUE to add the URL_SUFFIX, False to do it not.
         |
         |  @return string  The full path to this page.
         */
        public function url($suffix = true){
            if(!$suffix){
                return PUBLIC_URL . $this->path();
            }
            return PUBLIC_URL . $this->path() . (($this->path() !== "")? URL_SUFFIX: "");
        }

        /*
         |  CREATE A CLICKABLE URL LINK
         |  @since  0.8.4
         |
         |  @param  string  The used title, NULL to use the Page title.
         |  @param  string  Some additional link attributes.
         |
         |  @return string  The link element as STRING.
         */
        public function link($title = NULL, $attributes = ""){
            if(empty($title)){
                $title = $this->title();
            }
            return sprintf('<a href="%s" %s>%s</a>', $this->url(true), $attributes, $title);
        }

        /*
         |  GET THE LEVEL
         |  @since  0.8.4
         |
         |  @return int     The current page level.
         */
        public function level(){
            if(!$this->level){
                $path = $this->path();
                $this->level = empty($path)? 0: substr_count($path, "/")+1;
            }
            return $this->level;
        }

        /*
         |  GET ALL THE TAGS
         |  @since  0.8.4
         |
         |  @return array   All tags within an ARRAY.
         */
        public function tags(){
            if($this->tags === false){
                $prefix = DB_PREFIX;
                $query  = "SELECT tag.id AS id, tag.name AS tag ";
                $query .= "FROM {$prefix}page_tag AS page_tag, {$prefix}tag AS tag ";
                $query .= "WHERE page_tag.page_id = {$this->id} AND page_tag.tag_id = tag.id;";
                Record::logQuery($query);

                // Handle
                $tags = array();
                if(($stmt = Record::getConnection()->prepare($query)) !== false){
                    $stmt->execute();
                    while($tag = $stmt->fetchObject()){
                        $tags[$tag->id] = $tag->tag;
                    }
                }
                $this->tags = $tags;
            }
            return $this->tags;
        }
        public function getTags(){
            deprecated("Page->getTags()", "Page->tags()");
            return $this->tags();
        }

        /*
         |  GET DATE
         |  @since  0.8.4
         |
         |  @param  string  The date format as STRING see for more informations:
         |                  http://php.net/manual/function.strftime.php
         |  @param  string  The type 'created', 'updated' or 'published'.
         |
         |  @return string  The formatted date string.
         */
        public function date($format = "%a, %e. %b %Y", $type = "published"){
            if(strtoupper(substr(PHP_OS, 0, 3)) == "WIN"){
                $format = preg_replace("#(?<!%)((?:%%)*)%e#", '\1%#d', $format);
            }
            if(in_array($type, array("valid", "valid_until"))){
                return strftime($format, strtotime($this->valid_until));
            }
            return strftime($format, strtotime($this->{$type . "_on"}));
        }

        /*
         |  GET DATETIME
         |  @since  0.8.4
         |
         |  @param  string  The date format as STRING see for more informations:
         |                  http://php.net/manual/function.date.php
         |  @param  string  The type 'created', 'updated' or 'published'.
         |
         |  @return string  The formatted date string.
         */
        public function datetime($format = "D, d. M Y", $type = "published"){
            if(in_array($type, array("valid", "valid_until"))){
                return date($format, strtotime($this->valid_until));
            }
            return date($format, strtotime($this->{$type . "_on"}));
        }

        /*
         |  GET BREADCRUMBs
         |  @since  0.8.4
         |
         |  @param  string  The separator between the items.
         |  @param  array   Some additional configurations.
         |
         |  @return string  The complate breadcrumb path.
         */
        public function breadcrumbs($separator = " &rsaquo; ", $config = array()){
            $default = array(
                "container_start"   => '<div class="breadcrumbs">',
                "container_end"     => '</div>',
                "link_start"        => '<a href=":url" title=":title" class="crumb crumb-:slug">',
                "link_end"          => '</a>',
                "current_start"     => '<span class="crumb-current">',
                "current_end"       => '<span>',
                "separator_start"   => '<span class="crumb-separater">',
                "separator_end"     => '<span>'
            );
            $config = array_merge($default, array_intersect_key($config, $default));

            // Render
            $return = is_string($config["container_start"])? $config["container_start"]: "";
            while(($item = $this->parent()) !== false){
                $rep = array(
                    ":id"           => $item->id(),
                    ":url"          => $item->url(),
                    ":title"        => $item->title(),
                    ":breadcrumb"   => $item->breadcrumb(),
                    ":slug"         => preg_replace("#[^a-z0-9_-]#", "-", strtolower($item->slug()))
                );

                $render .= str_replace(array_keys($rep), array_values($rep), $config["link_start"]);
                $render .= $this->breadcrumb;
                $render .= str_replace(array_keys($rep), array_values($rep), $config["link_end"]);

                if(is_string($config["separator_start"]) && is_string($config["separator_end"])){
                    $render .= $config["separator_start"] . $separator . $config["separator_end"];
                } else {
                    $render .= $separator;
                }
            }
            if(is_string($config["current_start"]) && is_string($config["current_end"])){
                $render .= $config["current_start"] . $this->breadcrumb . $config["current_end"];
            } else {
                $render .= $this->breadcrumb;
            }
            $return .= is_string($config["container_end"])? $config["container_end"]: "";
        }

        /*
         |  GET THE CONTENT
         |  @todo   Edit
         |  @since  0.8.4
         |
         |  @param  string  The respective Page Part.
         |  @param  bool    TRUE to check the parents for this part content, FALSE to do it not.
         |
         |  @return multi   The respective content on success, NULL on failure.
         */
        public function content($part = "body", $inherit = false){
            if(isset($this->part->$part)){
                ob_start();
                eval("?>" . $this->part->$part->content_html);
                $return = ob_get_contents();
                ob_end_clean();
                return $return;
            }

            if($inherit && ($parent = $this->parent()) !== false){
                return $parent->content($part, true);
            }
            return NULL;
        }

        /*
         |  CHECK IF CONTENT EXISTS AND IS'NT EMPTY
         |  @todo   Edit
         |  @since  0.8.4
         |
         |  @param  string  The respective Page Part.
         |  @param  bool    TRUE to check the parents for this part content, FALSE to do it not.
         |
         |  @return bool    TRUE if the content part exists, FALSE if not.
         */
        public function hasContent($part = "body", $inherit = false){
            if(isset($this->part->$part)){
                $test = trim($this->part->$part->content_html);
                return !empty($test);
            }

            if($inherit && ($parent = $this->parent()) !== false){
                return $parent->hasContent($part, true);
            }
            return false;
        }

        /*
         |  CHECK IF CONTENT EXISTs
         |  @todo   Edit
         |  @since  0.8.4
         |
         |  @param  string  The respective Page Part.
         |  @param  bool    TRUE to check the parents for this part content, FALSE to do it not.
         |
         |  @return bool    TRUE if the content part exists, FALSE if not.
         */
        public function partExists($part = "body", $inherit = false){
            if(isset($this->part->$part)){
                return true;
            }

            if($inherit && ($parent = $this->parent()) !== false){
                return $parent->partExists($part, true);
            }
            return false;
        }

        /*
         |  NAVIGATION :: GET PREVIOUS ITEM
         |  @since  0.8.4
         |
         |  @return multi   The previous Post object or FALSE on failure.
         */
        public function previous(){
            if(!$this->parent()){
                return $this->parent()->children(array(
                    "limit" => 1,
                    "where" => "page.position < {$this->position} AND page.id < {$this->id}",
                    "order" => "page.position DESC"
                ));
            }
            return false;
        }
        public function prev(){
            return $this->previous();
        }

        /*
         |  NAVIGATION :: GET NEXT ITEM
         |  @since  0.8.4
         |
         |  @return multi   The next Post object or FALSE on failure.
         */
        public function next(){
            if(!$this->parent()){
                return $this->parent()->children(array(
                    "limit" => 1,
                    "where" => "page.position > {$this->position} AND page.id > {$this->id}",
                    "order" => "page.position DESC"
                ));
            }
            return false;
        }

        /*
         |  NAVIGATION :: GET PARENT
         |  @since  0.8.4
         |
         |  @param  int     A additional level argument.
         |
         |  @return multi   The parent Post object or FALSE on failure.
         */
        public function parent($level = NULL){
            if($this->parent === false && $this->parent_id != 0){
                $this->parent = self::findById($this->parent_id);
            }
            if($level === NULL){
                return $this->parent;
            }

            if($level > $this->level()){
                return false;
            } else if($level == $this->level()){
                return $this;
            }
            return $this->parent->parent($level);
        }

        /*
         |  NAVIGATION :: GET CHILDREN
         |  @since  0.8.4
         |
         |  @param  array   Some additional find array arguments.
         |  @param  array   Some additional query prepared statements.
         |  @param  bool    TRUE to include hidden posts, FALSE to do it not.
         |
         |  @return multi   The children posts within an ARRAY, FALSE on falure.
         */
        public function children($args = array(), $prepare = array(), $hidden = false){
            $where  = isset($args["where"])?  $args["where"]:  "";
            $order  = isset($args["order"])?  $args["order"]:  "";
            $offset = isset($args["offset"])? $args["offset"]: "";
            $limit  = isset($args["limit"])?  $args["limit"]:  "";

            // Adapt Args
            if($offset == 0 && isset($_GET["page"])){
                $offset = (int) $_GET["page"]-1 * $limit;
            }
            $where  = (trim($where) == "")? "": "AND {$where}";
            $order  = (trim($order) == "")? "": "ORDER BY {$order}";
            $limit  = ($limit > 0)? "LIMIT {$limit}": "";
            $offset = ($offset > 0)? "OFFSET {$limit}": "";

            // Create Query
            $prefix = DB_PREFIX;
            $query  = "SELECT page.*, author.name AS author, updater.name AS updater, publisher.name AS publisher ";
            $query .= "FROM {$prefix}page AS page ";
            $query .= "LEFT JOIN {$prefix}user author ON author.id = page.created_by ";
            $query .= "LEFT JOIN {$prefix}user updater ON updater.id = page.updated_by ";
            $query .= "LEFT JOIN {$prefix}user publisher ON publisher.id = page.published_by ";
            $query .= "WHERE page.parent_id = :pid AND (page.valid_until IS NULL OR page.valid_until < :date) ";
            if($hidden){
                $query .= "AND (page.status_id = ".self::STATUS_PUBLISHED." OR page.status_id = ".self::STATUS_HIDDEN.") ";
            } else {
                $query .= "AND page.status_id = ".self::STATUS_PUBLISHED." ";
            }
            $query .= "$where $order $limit $offset;";
            Record::logQuery($query);

            // Handle
            $class = "Page";
            $pages = array();
            if(!empty($this->behavior_id)){
                $class = Behavior::loadPageHack($this->behavior_id);
            }

            if($stmt = Record::getConnection()->prepare($query)){
                $stmt->execute(array_merge($prepare, array(":pid" => $this->id, ":date" => date("Y-m-d H:i:s"))));
                while($object = $stmt->fetchObject()){
                    $page = new $class($object, $this);
                    $page->part = self::getParts($page->id);
                    $pages[] = $page;
                }
            }

            // Return
            if(isset($args["limit"]) && $args["limit"] == 1){
                return (count($pages) > 0)? $pages[0]: false;
            }
            return $pages;
        }

        /*
         |  COUNT CHILDREN
         |  @since  0.8.4
         |
         |  @param  array   Some additional find array arguments.
         |  @param  array   Some additional query prepared statements.
         |  @param  bool    TRUE to include hidden posts, FALSE to do it not.
         |
         |  @return int     The respective children count as INT.
         */
        public function childrenCount($args = array(), $prepare = array(), $hidden = false){
            $where  = isset($args["where"])?  $args["where"]:  "";
            $offset = isset($args["offset"])? $args["offset"]: "";
            $limit  = isset($args["limit"])?  $args["limit"]:  "";

            // Adapt Args
            if($offset == 0 && isset($_GET["page"])){
                $offset = (int) $_GET["page"]-1 * $limit;
            }
            $where  = (trim($where) == "")? "": "AND {$where}";
            $limit  = ($limit > 0)? "LIMIT {$limit}": "";
            $offset = ($offset > 0)? "OFFSET {$limit}": "";

            // Create Query
            $table  = Record::table(self::TABLE);
            $query  = "SELECT COUNT(*) AS num_rows FROM {$table} ";
            $query .= "WHERE parent_id = :pid AND (valid_until IS NULL OR valid_until < :date) ";
            if($hidden){
                $query .= "AND (status_id = ".self::STATUS_PUBLISHED." OR status_id = ".self::STATUS_HIDDEN.") ";
            } else {
                $query .= "AND status_id = ".self::STATUS_PUBLISHED." ";
            }
            $query .= "$where $limit $offset;";
            Record::logQuery($query);

            // Handle and Return
            $stmt = Record::getConnection()->prepare($query);
            $stmt->execute(array_merge($prepare, array(":pid" => $this->parent_id, ":date" => date("Y-m-d H:i:s"))));
            return (int) $stmt->fetchColumn();
        }

        /*
         |  INCLUDE A SNIPPET
         |  @todo   Edit
         |  @since  0.8.4
         |
         |  @param  string  The snippet name AS string.
         |
         |  @return bool    TRUE on success, FALSE on failure.
         */
        public function includeSnippet($name){
            if(($snippet = Snippet::findByName($name)) !== false){
                eval("?>" . $snippet->content_html);
                return true;
            }
            return false;
        }

        /*
         |  EXECUTE THE LAYOUT
         |  @todo   Edit
         |  @since  0.8.4
         */
        public function _executeLayout(){
            global $FoxPage;

            // Get Layout ID
            if(!$this->layout_id){
                $parent = $this;
                while(($parent = $parent->parent()) !== false){
                    if($parent->layout_id){
                        $this->layout_id = $parent->layout_id;
                        break;
                    }
                }
            }
            if(!$this->layout_id){
                die("You need to set a layout!");
            }
            $FoxPage = $this;

            // Query
            $prefix = DB_PREFIX;
            $query  = "SELECT content_type, content FROM {$prefix}layout WHERE id = :lid;";
            Record::logQuery($query);

            // Handle
            $stmt = Record::getConnection()->prepare($query);
            $stmt->execute(array(":lid" => $this->layout_id));
            if($layout = $stmt->fetchObject()){
                if($layout->content_type == ""){
                    $layout->content_type = "text/html";
                }
                header("Content-Type: {$layout->content_type}; charset=" . DEFAULT_CHARSET);
                // @todo Edit
                eval("?>" . $layout->content);
            }
        }

        /*
         |  SET / SAVE TAGS
         |  @since  0.8.4
         |
         |  @param  multi   A single Tag as STRING, multiple comma-separated or as ARRAY.
         |
         |  @return bool    TRUE on success, FALSe on failure.
         */
        public function setTags($tags){
            if(is_string($tags)){
                $tags = array_map("trim", explode(",", $tags));
            }
            $tags = array_filter(array_unique($tags));
            $current = $this->tags();

            // Check Tags
            if(!is_array($tags) || (count($tags) == 0 && count($current) == 0)){
                return false;
            }

            // Handle Tags
            $new_tags = array_values(array_diff($tags, $current));
            $old_tags = array_values(array_diff($current, $tags));

            // Delete All Tags
            if(count($old_tags) > 0){
                $old_tags = Tag::findByName($old_tags);
                foreach($old_tags AS $tag){
                    Record::deleteWhere("page_tag", "page_id = :pid AND tag_id = :tid", array(
                        ":pid" => $this->id, ":tid" => $tag->id
                    ));
                    $tag->count--;
                    $tag->save();
                }
            }

            // Add new Tags
            if(count($new_tags) > 0){
                foreach($new_tags AS $name){
                    if(($tag = Tag::findByName($name)) == false){
                        $tag = new Tag(array("name" => trim($name)));
                    }
                    $tag->count++;
                    if($tag->save()){
                        $rel = new PageTag(array("page_id" => $this->id, "tag_id" => $tag->id));
                        $rel->save();
                    }
                }
            }
            return true;
        }
        public function saveTags($tags){
            deprecated("Page->saveTags()", "Page->setTags()");
            return $this->setTags($tags);
        }

        /*
         |  HOOK :: BEFORE INSERT
         |  @since  0.8.4
         */
        public function beforeInsert(){
            if(empty($this->status_id)){
                $this->status_id = self::STATUS_DRAFT;
            }
            if(empty($this->breadcrumb)){
                $this->creadcrumb = $this->title;
            }

            $this->created_on = date("Y-m-d H:i:s");
            $this->created_by = AuthUser::getId();
            $this->updated_on = date("Y-m-d H:i:s", 0);
            $this->updated_by = 0;
            if($this->status_id != self::STATUS_PUBLISHED){
                $this->published_on = date("Y-m-d H:i:s", 0);
                $this->published_by = 0;
            } else {
                $this->published_on = date("Y-m-d H:i:s");
                $this->published_by = AuthUser::getId();
            }
            $this->position = 0;
            return true;
        }

        /*
         |  HOOK :: BEFORE UPDATE
         |  @since  0.8.4
         */
        public function beforeUpdate(){
            if(empty($this->status_id)){
                $this->status_id = self::STATUS_DRAFT;
            }

            $this->updated_on = date("Y-m-d H:i:s");
            $this->updated_by = AuthUser::getId();
            if($this->status_id != self::STATUS_PUBLISHED){
                $this->published_on = date("Y-m-d H:i:s", 0);
                $this->published_by = 0;
            } else if($this->published_on <= date("Y-m-d H:i:s", 0)){
                $this->published_on = date("Y-m-d H:i:s");
                $this->published_by = AuthUser::getId();
            }
            return true;
        }

        /*
         |  HOOK :: BEFORE DELETE
         |  @since  0.8.4
         */
        public function beforeDelete(){
            return self::deleteChildrenOf($this->id) && PagePart::deleteByPageId($this->id) && PageTag::deleteByPageId($this->id);
        }
    }
