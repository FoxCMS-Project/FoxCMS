<?php
/*
 |  FoxCMS      Content Management Simplified <www.foxcms.org>
 |  @file       ./system/controllers/class.user-controller.php
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

    class PageController extends Controller{
        /*
         |  CONSTRUCTOR
         |  @since  0.8.4
         */
        public function __construct(){
            if(!AuthUser::isLoggedIn()){
                return redirect(get_url("login"));
            }
            if(!AuthUser::hasPermission("page_view")){
                Flash::set("error", __("You don't have the permission to access this page!"));
                if(Setting::get("default-tab") == "page"){
                    return redirect(get_url("layout"));
                }
                return redirect(get_url());
            }
        }

        /*
         |  ACTION :: INDEX
         |  @since  0.8.4
         */
        public function index(){
            $page = Event::applyFilter("controller-page-index", "page/index");
            $this->setLayout("backend");
            $this->display($page, array(
                "root"              => Record::findByIdFrom("Page", 1),
                "content_children"  => $this->children(1, 0, true)
            ));
        }

        /*
         |  ACTION :: ADD
         |  @since  0.8.4
         */
        public function add($parent_id = 1){
            $page = new Page(Flash::get("post_data", array()));
            if(!AuthUser::hasPermission("page_add")){
                Flash::set("error", __("You don't have the permission to add a new Page!"));
                return redirect(get_url("page"));
            }

            // Post
            if(request_method() == "POST" && isset($_POST["page"])){
                return $this->_store("add", $page, $_POST["page"]);
            }

            // Page
            $page->parent_id   = $parent_id;
            $page->status_id   = Setting::get("default-status");
            $page->needs_login = Page::LOGIN_INHERIT;

            $parts = Flash::get("posts_parts_data", array());
            if(!empty($parts)){
                $sister = Record::findOneFrom("Page", "parent_id = :pid ORDER BY id DESC", array(":pid" => $parent_id));
                if($sister){
                    $sister_parts = Record::findAllFrom("PagePart", "page_id:pid ORDER BY id", array($sister->id));
                    foreach($sister_parts AS $part){
                        $parts[] = new PagePart(array(
                            "name"      => $part->name,
                            "filter_id" => Setting::get("default-filter")
                        ));
                    }
                }
            } else {
                $parts = array(new PagePart(array("filter_id" => Setting::get("default-filter"))));
            }

            // Display
            $this->setLayout("backend");
            $this->display(Event::applyFilter("controller-page-add", "page/edit"), array(
                "token"     => SecureToken::generateToken("page/add"),
                "action"    => "add",
                "tags"      => array(),
                "page"      => $page,
                "parts"     => $parts,
                "filters"   => Filter::findAll(),
                "layouts"   => Record::findAllFrom("Layout"),
                "behaviors" => Behavior::findAll(),
            ));
        }

        /*
         |  AJAX ACTION :: ADD A PART
         |  @since  0.8.4
         */
        public function addPart(){
            header("Content-Type: text/html; charset: UTF-8");
            $data = isset($_POST["part"])? $_POST["part"]: array();
            echo $this->_getPartView(
                (isset($data["index"])? (int) $data["index"]: 1),
                (isset($data["name"])? trim($data["name"]): "")
            );
        }

        /*
         |  ACTION :: EDIT
         |  @since  0.8.4
         */
        public function edit($id){
            if(($page = Page::findById($id)) === false){
                Flash::set("error", __("The passed Page couldn't be found!"));
                return redirect(get_url("page"));
            }
            if(!AuthUser::hasPermission("page_edit") || ($page->is_protected && !AuthUser::hasPermission("admin_edit"))){
                Flash::set("error", __("You don't have the permission to edit this Page!"));
                return redirect(get_url("page"));
            }

            // Post
            if(request_method() == "POST" && isset($_POST["page"])){
                return $this->_store("edit", $page, $_POST["page"]);
            }

            // Parts
            $parts = PagePart::findByPageId($id);
            if(empty($parts)){
                $parts = array(new PagePart());
            }

            // Display
            $this->setLayout("backend");
            $this->display(Event::applyFilter("controller-page-edit", "page/edit"), array(
                "token"     => SecureToken::generateToken("page/edit/{$page->id}"),
                "action"    => "edit",
                "tags"      => array(),
                "page"      => $page,
                "parts"     => $parts,
                "filters"   => Filter::findAll(),
                "layouts"   => Record::findAllFrom("Layout"),
                "behaviors" => Behavior::findAll(),
            ));
        }

        /*
         |  ACTION :: DELETE
         |  @since  0.8.4
         */
        public function delete($id){
            if(($page = Page::findById($id)) === false){
                Flash::set("error", __("The passed Page couldn't be found!"));
                return redirect(get_url("page"));
            }
            if(!AuthUser::hasPermission("page_delete") || $page->is_protected || $page->id <= 1){
                Flash::set("error", __("You don't have the permission to delete this Page!"));
                return redirect(get_url("page"));
            }

            // Check Token
            if(!isset($_GET["token"]) || !SecureToken::validateToken($_GET["token"], "page/delete/{$id}")){
                Flash::set("error", __("The passed token is invalid or expired!"));
                return redirect(get_url("page"));
            }

            // Delete Page Stuff
            $parts = PagePart::findByPageId($page->id);
            foreach($parts AS $part){
                if($part->delete()){
                    Event::apply("part_delete", $part);
                }
            }
            PageTag::deleteByPageId($page->id);

            // Delete Page
            if(!$page->delete()){
                Flash::set("error", __("An unknown error is occured while deleting the Page!"));
                return redirect(get_url("page"));
            }
            Event::apply("page_delete", $page);

            // Return
            Flash::set("success", __("The Page ':name' has been deleted successfully!", array(":name" => $page->name)));
            return redirect(get_url("page"));
        }

        /*
         |  ACTION :: REORDER
         |  @since  0.8.4
         */
        public function reorder(){
            $pages = $_POST["page"];

            $i = 0;
            foreach($pages AS $page_id => $parent_id){
                if($parent_id == 0){
                    $parent_id = 1;
                }
                $page = Record::findByIdFrom("Page", $page_id);
                $page->position = (int) ++$i;
                $page->parent_id = (int) $parent_id;
                $page->save();
            }
        }

        /*
         |  ACTION :: COPY A PAGE (TREE)
         |  @since  0.8.4
         */
        public function copy(){
            $page = Record::findByIdFrom("Page", $_POST["originalid"]);
            $root = Page::cloneTree($page, $page->parent_id);

            // Copied Edition
            $page = Record::findByIdFrom("Page", $root);
            $page->position += 1;
            $page->save();

            // New URLs
            $link  = PUBLIC_URL . ((MOD_REWRITE)? "?": "");
            $link .= ($page->path() != "")? $page->path() . URL_SUFFIX: $page->path;

            // Return new Data
            //@todo Remove this Wolf CMS-based way!
            $token = SecureToken::generateToken("page/delete/{$root}");
            echo implode("||", array(
                $root, get_url("page/edit/{$root}"), $page->title(), $page->slug(),
                $link, get_url("page/add/{$root}"), get_url("page/delete/{$root}") . "?token={$token}"
            ));
        }

        /*
         |  OUTPUT :: CHILDREN LIST
         |  @since  0.8.4
         */
        public function children($parent, $level, $return = false){
            $expanded = isset($_COOKIE["expanded_rows"])? explode(",", $_COOKIE["expanded_rows"]): array();
            $children = Page::childrenOf($parent);

            // Loop
            foreach($children AS $index => $child){
                $children[$index]->hasChildren = Page::hasChildren($child->id);
                $children[$index]->is_expanded = in_array($child->id, $expanded);
                if($children[$index]->is_expanded){
                    $children[$index]->children_rows = $this->children($child->id, $level + 1, true);
                }
            }

            // Get Content
            $content = new View("page/children", array(
                "children"  => $children,
                "level"     => $level + 1
            ));
            if($return){
                return $content->render();
            }
            $content->display();
        }

        /*
         |  OUTPUT :: GET PART VIEW
         |  @since  0.8.4
         */
        private function _getPartView($index = 1, $name = "", $filter = "", $content = ""){
            $part = new PagePart(array("name" => $name, "filter_id" => $filter, "content" => $content));
            return $this->render("page/part_edit", array("index" => $index, "part" => $part));
        }

        /*
         |  HANDLE :: VALIDATA POST
         |  @since  0.8.4
         */
        private function _validate($data, $page){
            $data["is_protected"] = isset($data["is_protected"]) && !empty($data["is_protected"])? 1: 0;

            // Validata Title
            $data["title"] = isset($data["title"])? trim($data["title"]): "";
            if(empty($data["title"])){
                Flash::set("error", __("You must specify a title for this Page!"));
                return false;
            }

            // Validate numbers
            foreach(array("parent_id", "layout_id", "needs_login") AS $id){
                if(!isset($data[$id]) || !is_numeric($data[$id])){
                    $data[$id] = 0;
                    Flash::set("error", __("The value for the field ':field' is invalid!", array(":field" => $id)));
                    continue;
                }
                $data[$id] = (int) $data[$id];
            }
            if($page->id > 1 && !Record::existsIn("Page", "id = :id", array(":id" => $data["parent_id"]))){
                Flash::set("error", __("The passed parent Page ID doesn't exist!"));
                return false;
            }

            // Validata Slug
            $data["slug"] = isset($data["slug"])? strtolower(trim($data["slug"])): "";
            var_dump($data["slug"]);
            if(empty($data["slug"]) && $page->id > 1){
                Flash::set("error", __("You must specify a slug for this Page!"));
                return false;
            }
            if($data["slug"] == ADMIN_DIR){
                Flash::set("error", __("Your Page slug cannot be named ':slug'!", array(":slug" => ADMIN_DIR)));
                return false;
            }
            if($page->id > 1 && preg_match("#[^a-z0-9_.-]#", $data["slug"])){
                Flash::set("error", __("Your Page slug contains invalid characters (a-z, 0-9, _, ., -)!"));
                return false;
            }

            $where = "parent_id = :pid AND slug = :slug AND id <> :id";
            $prepare = array(":pid" => $data["parent_id"], ":slug" => $data["slug"], ":id" => $id);
            if($page->id == 0 && Record::existsIn("Page", $where, $prepare)){
                Flash::set("error", __("Your Page slug ':slug' does already exists!", array(":slug" => ADMIN_DIR)));
                return false;
            }

            // Validate Behavior
            if(!empty($data["behavior_id"]) && preg_match("#[^a-z0-9\_\.\-]#", $data["behavior_id"])){
                Flash::set("error", __("Your Behaviour ID contains invalid characters (a-z, 0-9, _, ., -)!"));
                return false;
            }

            // Sanitize Dates
            foreach(array("created_on", "updated_on", "published_on", "valid_until") AS $field){
                if(!isset($data[$field]) || empty($data[$field])){
                    unset($data[$field], $data[$field . "_time"]);
                    continue;
                }

                // DateTime
                $date = trim($data[$field]);
                if(isset($data[$field . "_time"]) && !empty($data[$field . "_time"])){
                    $date .= " " . trim($data[$field . "_time"]);
                } else {
                    $date .= " " . date("H:i:s");
                }
                unset($data[$field], $data[$field . "_time"]);

                // Check
                if(($time = strtotime($date)) && date("Y", 0)+2 < date("Y", $time)){
                    $data[$field] = date("Y-m-d H:i:s", $time);
                }
            }

            // Sanitize Alpha Fields
            use_helper("Kses");
            foreach(array("keywords", "description") AS $field){
                $data[$field] = kses(trim($data[$field]), array());
            }
            if(!Setting::get("default-html-titles", false)){
                $data["title"] = kses(trim($data["title"]), array());
            }

            // Sanitize is_protected
            if(!empty($data["is_protected"]) && !AuthUser::hasPermission("admin_edit")){
                unset($data["is_protected"]);
            }
            return $data;
        }

        /*
         |  HANDLE :: STORE POST TO DB
         |  @since  0.8.4
         */
        private function _store($action, $page, $data){
            $url = "page/{$action}" . (($action == "edit")? "/{$page->id}": "");
            $parts = $_POST["part"];
            Flash::set("post_data", $data, "both");
            Flash::set("post_parts_data", $parts, "both");

            // Check Token
            if(!isset($_POST["token"]) || !SecureToken::validateToken($_POST["token"], $url)){
                Flash::set("error", __("The passed token is invalid or expired!"));
                return redirect(get_url($url));
            }

            // Validata Data
            if(($data = $this->_validate($data, $page)) === false){
                $page->setFromData(func_get_args(2));

                // Parts
                if(!empty($parts)){
                    foreach($parts AS $key => &$val){
                        $val = (object) $val;
                    }
                }

                // Display
                $this->setLayout("backend");
                return $this->display(Event::applyFilter("controller-page-edit", "page/edit"), array(
                    "token"     => SecureToken::generateToken($url),
                    "action"    => $action,
                    "tags"      => $_POST["page_tag"],
                    "page"      => $page,
                    "parts"     => $parts,
                    "filters"   => Filter::findAll(),
                    "layouts"   => Record::findAllFrom("Layout"),
                    "behaviors" => Behavior::findAll(),
                ));
            }
            $page->setFromData($data);
            Event::apply("page_{$action}_before_save", $page);

            // Store
            if(!$page->save()){
                Flash::set("error", __("An unknown error is occured while storing the Page!"));
                return redirect(get_url($url));
            }

            // Part Handling
            if($action == "edit"){
                foreach(PagePart::findByPageId($page->id) AS $old_part){
                    $not = true;
                    foreach($parts AS $part_id => $part){
                        $part["name"] = trim($part["name"]);
                        if($old_part->name == $part["name"]){
                            $not = false;

                            $part = new PagePart($part);
                            $part->page_id = $page->id;

                            Event::apply("part_edit_before_save", $part);
                            $part->save();
                            Event::apply("part_edit_after_save", $part);

                            unset($parts[$part_id]);
                            break;
                        }
                    }
                    if($not){
                        $old_part->delete();
                    }
                }
            }
            foreach($parts AS $data){
                $data["name"] = trim($data["name"]);
                $part = new PagePart($data);
                $part->page_id = $page->id;

                Event::apply("part_add_before_save", $part);
                $part->save();
                Event::apply("part_add_after_save", $part);
            }
            Event::apply("page_{$action}_after_save", $page);
            $page->setTags($_POST["page_tag"]["tags"]);

            // Return
            Flash::set("success", __("The Page has been successfully stored!"));
            if(isset($_POST["commit"])){
                return redirect(get_url("page"));
            }
            return redirect(get_url("page/edit/{$page->id}"));
        }
    }
