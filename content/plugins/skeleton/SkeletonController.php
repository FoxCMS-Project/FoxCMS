<?php
/*
 |  FoxCMS Skeleton Plugin
 |  @file       ./skeleton/SkeletonController.php
 |  @author     SamBrishes@pytesNET
 |  @version    1.2.0 [1.2.0] - Alpha
 |
 |  @license    GNU GPL v3
 |  @copyright  Copyright © 2015 - 2018 SamBrishes, pytesNET <pytes@gmx.net>
 |
 |  @history    Copyright © 2009 - 2015 Martijn van der Kleijn <martijn.niji@gmail.com>
 |              Copyright © 2008 - 2009 Philippe Archambault <philippe.archambault@gmail.com>
 */
    if(!defined("FOXCMS")){ die(); }

    class SkeletonController extends PluginController{
        /*
         |  CONSTRUCTOR
         |  @info   The place, where your controller starts.
         |  @since  1.0.0
         |  @update 1.2.0
         */
        public function __construct(){
            $this->setLayout("backend");
            $this->assignToLayout("sidenar", new View("../../plugins/skeleton/views/sidebar"));
        }

        /*
         |  INDEX PAGE
         |  @since  1.0.0
         |  @update 1.2.0
         */
        public function index(){
            return $this->documentation();
        }

        /*
         |  DOCUMENTATION PAGE
         |  @since  1.0.0
         |  @update 1.2.0
         */
        public function documentation(){
            return $this->display("skeleton/views/documentation");
        }

        /*
         |  SETTINGS PAGE
         |  @since  1.0.0
         |  @update 1.2.0
         */
        public function settings(){
            return $this->display("skeleton/views/settings", Plugin::getAllSettings("skeleton"));
        }
    }
