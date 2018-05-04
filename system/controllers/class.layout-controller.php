<?php
/*
 |  FoxCMS      Content Management Simplified <www.foxcms.org>
 |  @file       ./system/controllers/class.layout-controller.php
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

    class LayoutController extends Controller{
        /*
         |  CONSTRUCTOR
         |  @since  0.8.4
         */
        public function __construct(){
            if(!AuthUser::isLoggedIn()){
                return redirect(get_url("login"));
            }
            if(!AuthUser::hasPermission("layout_view")){
                Flash::set("error", __("You don't have the permission to access this page!"));
                if(Setting::get("default-tab") == "layout"){
                    return redirect(get_url("page"));
                }
                return redirect(get_url());
            }
            $this->setLayout("backend");
            $this->assignToLayout("sidebar", new View("layout/sidebar"));
        }

        /*
         |  ACTION :: INDEX
         |  @since  0.8.4
         */
        public function index(){
            $page = Event::applyFilter("controller-layout-index", "layout/index");
            $this->display($page, array(
                "layouts"   => Record::findAllFrom("Layout", "1=1 ORDER BY position")
            ));
        }

        /*
         |  ACTION :: ADD
         |  @since  0.8.4
         */
        public function add(){
            $layout = new Layout(Flash::get("post_data", array()));
            if(!AuthUser::hasPermission("layout_add")){
                Flash::set("error", __("You don't have the permission to add a new Layout!"));
                return redirect(get_url("layout"));
            }

            // Post
            if(request_method() == "POST" && isset($_POST["layout"])){
                return $this->_store("add", $layout, $_POST["layout"]);
            }

            // Display
            $page = Event::applyFilter("controller-layout-add", "layout/edit");
            $this->display($page, array(
                "token"     => SecureToken::generateToken("layout/add"),
                "action"    => "add",
                "layout"    => $layout
            ));
        }

        /*
         |  ACTION :: EDIT
         |  @since  0.8.4
         */
        public function edit($id){
            if(($layout = Layout::findById($id)) === false){
                Flash::set("error", __("The passed Layout couldn't be found!"));
                return redirect(get_url("layout"));
            }
            if(!AuthUser::hasPermission("layout_edit")){
                Flash::set("error", __("You don't have the permission to edit this Layout!"));
                return redirect(get_url("layout"));
            }

            // Post
            if(request_method() == "POST" && isset($_POST["layout"])){
                return $this->_store("edit", $layout, $_POST["layout"]);
            }

            // Display
            $page = Event::applyFilter("controller-layout-edit", "layout/edit");
            $this->display($page, array(
                "token"     => SecureToken::generateToken("layout/edit/{$layout->id}"),
                "action"    => "edit",
                "layout"    => $layout
            ));
        }

        /*
         |  ACTION :: DELETE
         |  @since  0.8.4
         */
        public function delete($id){
            if(($layout = Layout::findById($id)) === false){
                Flash::set("error", __("The passed Layout couldn't be found!"));
                return redirect(get_url("layout"));
            }
            if(!AuthUser::hasPermission("layout_delete")){
                Flash::set("error", __("You don't have the permission to delete this Layout!"));
                return redirect(get_url("layout"));
            }

            // Check Token
            if(!isset($_GET["token"]) || !SecureToken::validateToken($_GET["token"], "layout/delete/{$id}")){
                Flash::set("error", __("The passed token is invalid or expired!"));
                return redirect(get_url("layout"));
            }

            // Check Layout
            if($layout->isUsed()){
                Flash::set("error", "The Layout '<b>:name</b>' is in Use and CANNOT be deleted!", array(":name" => $layout->name));
                return redirect(get_url("layout"));
            } else if(!$layout->delete()){
                Flash::set("error", __("An unknown error is occured while deleting the Layout!"));
                return redirect(get_url("layout"));
            }
            Event::apply("layout_after_delete", $layout);

            // Return
            Flash::set("success", __("The Layout ':name' has been deleted successfully!", array(":name" => $layout->name)));
            return redirect(get_url("layout"));
        }

        /*
         |  ACTION :: REORDER
         |  @since  0.8.4
         */
        private function reorder(){
            parse_str($_POST["data"], $data);
            foreach($data["layouts"] AS $position => $layout_id){
                $layout = Record::findByIdFrom("Layout", $layout_id);
                $layout->position = (int) $position + 1;
                $layout->save();
            }
        }

        /*
         |  HANDLE :: STORE LAYOUT TO DB
         |  @since  0.8.4
         */
        private function _store($action, $layout, $data){
            $url = "layout/{$action}" . (($action == "edit")? "/".$layout->id: "");
            Flash::set("post_data", $data, "both");

            // Check Token
            if(!isset($_POST["token"]) || !SecureToken::validateToken($_POST["token"], $url)){
                Flash::set("error", __("The passed token is invalid or expired!"));
                return redirect(get_url($url));
            }

            // Validata Data
            if(empty($data["name"])){
                Flash::set("error", __("You must specify a name for this Layout!"));
                return redirect(get_url($url));
            }
            if(empty($data["content_type"])){
                Flash::set("error", __("You must specify a content type for this Layout!"));
                return redirect(get_url($url));
            }
            $layout->setFromData($data);

            // Store
            if(!$layout->save()){
                Flash::set("error", __("An unknown error is occured while storing the Layout!"));
                return redirect(get_url($data));
            }
            Event::apply("layout_after_{$action}", $layout);

            // Return
            Flash::set("success", __("The Layout has been successfully stored!"));
            if(isset($_POST["commit"])){
                return redirect(get_url("layout"));
            }
            return redirect(get_url("layout/edit/{$layout->id}"));
        }
    }
