<?php
/*
 |  FoxCMS      Content Management Simplified <www.foxcms.org>
 |  @file       ./system/controllers/class.snippet-controller.php
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

    class SnippetController extends Controller{
        /*
         |  CONSTRUCTOR
         |  @since  0.8.4
         */
        public function __construct(){
            if(!AuthUser::isLoggedIn()){
                return redirect(get_url("login"));
            }
            if(!AuthUser::hasPermission("snippet_view")){
                Flash::set("error", __("You don't have the permission to access this page!"));
                if(Setting::get("default-tab") == "snippet"){
                    return redirect(get_url("page"));
                }
                return redirect(get_url());
            }
            $this->setLayout("backend");
            $this->assignToLayout("sidebar", new View("snippet/sidebar"));
        }

        /*
         |  ACTION :: INDEX
         |  @since  0.8.4
         */
        public function index(){
            $page = Event::applyFilter("controller-snippet-index", "snippet/index");
            $this->display($page, array(
                "snippets"   => Record::findAllFrom("Snippet", "1=1 ORDER BY position")
            ));
        }

        /*
         |  ACTION :: ADD
         |  @since  0.8.4
         */
        public function add(){
            $snippet = new Snippet(Flash::get("post_data", array()));
            if(!AuthUser::hasPermission("snippet_add")){
                Flash::set("error", __("You don't have the permission to add a new Snippet!"));
                return redirect(get_url("snippet"));
            }

            // POST
            if(request_method() == "POST" && $_POST["snippet"]){
                return $this->_store("add", $snippet, $_POST["snippet"]);
            }

            // Display
            $page = Event::applyFilter("controller-snippet-add", "snippet/edit");
            $this->display($page, array(
                "token"     => SecureToken::generateToken("snippet/add"),
                "action"    => "add",
                "snippet"   => $snippet,
                "filters"   => Filter::findAll()
            ));
        }

        /*
         |  ACTION :: EDIT
         |  @since  0.8.4
         */
        public function edit($id){
            if(($snippet = Snippet::findById($id)) === false){
                Flash::set("error", __("The passed Snippet couldn't be found!"));
                return redirect(get_url("snippet"));
            }
            if(!AuthUser::hasPermission("snippet_edit")){
                Flash::set("error", __("You don't have the permission to edit this Snippet!"));
                return redirect(get_url("snippet"));
            }

            // POST
            if(request_method() == "POST" && $_POST["snippet"]){
                return $this->_store("edit", $snippet, $_POST["snippet"]);
            }

            // Display
            $page = Event::applyFilter("controller-snippet-edit", "snippet/edit");
            $this->display($page, array(
                "token"     => SecureToken::generateToken("snippet/edit/{$snippet->id}"),
                "action"    => "edit",
                "snippet"   => $snippet
            ));
        }

        /*
         |  ACTION :: DELETE
         |  @since  0.8.4
         */
        public function delete($id){
            if(($snippet = Snippet::findById($id)) === false){
                Flash::set("error", __("The passed Snippet couldn't be found!"));
                return redirect(get_url("snippet"));
            }
            if(!AuthUser::hasPermission("snippet_delete")){
                Flash::set("error", __("You don't have the permission to delete this Snippet!"));
                return redirect(get_url("snippet"));
            }

            // Check Token
            if(!isset($_GET["token"]) || !SecureToken::validateToken($_GET["token"], "snippet/delete/{$id}")){
                Flash::set("error", __("The passed token is invalid or expired!"));
                return redirect(get_url("snippet"));
            }

            // Delete Snippet
            if(!$snippet->delete()){
                Flash::set("error", __("An unknown error is occured while deleting the Snippet!"));
                return redirect(get_url("snippet"));
            }
            Event::apply("snippet_after_delete", $snippet);

            // Return
            Flash::set("success", __("The Snippet ':name' has been deleted successfully!", array(":name" => $snippet->name)));
            return redirect(get_url("snippet"));
        }

        /*
         |  ACTION :: REORDER
         |  @since  0.8.4
         */
        private function reorder(){
            parse_str($_POST["data"], $data);
            foreach($data["snippets"] AS $position => $snippet_id){
                $snippet = Record::findByIdFrom("Snippet", $snippet_id);
                $snippet->position = (int) $position + 1;
                $snippet->save();
            }
        }

        /*
         |  HANDLE :: STORE SNIPPET TO DB
         |  @since  0.8.4
         */
        private function _store($action, $snippet, $data){
            $url = "snippet/{$action}" . (($action == "edit")? "/".$snippet->id: "");
            Flash::set("post_data", $data, "both");

            // Check Token
            if(!isset($_POST["token"]) || !SecureToken::validateToken($_POST["token"], $url)){
                Flash::set("error", __("The passed token is invalid or expired!"));
                return redirect(get_url($url));
            }

            // Validata Data
            if(empty($data["name"])){
                Flash::set("error", __("You must specify a name for this Snippet!"));
                return redirect(get_url($url));
            }
            $snippet->setFromData($data);

            // Store
            if(!$snippet->save()){
                Flash::set("error", __("An unknown error is occured while storing the Snippet!"));
                return redirect(get_url($url));
            }
            Event::apply("snippet_after_$action", $snippet);

            // Return
            Flash::set("success", __("The Snippet has been successfully stored!"));
            if(isset($_POST["commit"])){
                return redirect(get_url("snippet"));
            }
            return redirect(get_url("snippet/edit/{$snippet->id}"));
        }
    }
