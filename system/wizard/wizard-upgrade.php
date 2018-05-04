<?php
/*
 |  FoxCMS      Content Management Simplified <www.foxcms.org>
 |  @file       ./system/wizard/wizard-upgrade.php
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
    
    class WizardUpgrade{
        /*
         |  INSTANCE VARs
         */
        private $step;
        private $user;
        
        /*
         |  CONSTRUCTOR
         |  @since  0.8.4
         */
        public function __construct(){
            // Future Stuff :3
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
         |  HANDLE
         |  @since  0.8.4
         */
        public function handle(){
            return 0;
        }
    }
