<?php
/*
 |  A **deprecated** Crypt_Hash helper class.
 |  @file       ./Hash.php
 |  @author     SamBrishes@pytesNET
 |  @version    0.2.0 [0.2.0] - Beta
 |
 |  @license    GNU GPL v3
 |  @copyright  Copyright © 2015 - 2018 SamBrishes, pytesNET <pytes@gmx.net>
 |
 |  @history    Copyright © 2009 - 2015 Martijn van der Kleijn <martijn.niji@gmail.com>
 |              Copyright © 2008 - 2009 Philippe Archambault <philippe.archambault@gmail.com>
 */

    /*
     |  DEPRECATED
     |  Please just use `hash()` or `mhash()`, thanks!
     */
    class Crypt_Hash{
        /*
         |  ISNTANCE DATA
         */
        public $key = false;
        public $hash = "sha1";


        /*
         |  CONSTRUCTOR
         |  @since  0.1.0
         */
        function __construct($hash = "sha1", $key = false){
            $this->setHash($hash);
            $this->setKey($key);
        }

        /*
         |  SET KEY
         |  @since  0.1.0
         */
        public function setKey($key){
            if($key === false){
                $this->key = false;
                return true;
            }
            if(!empty($key) && is_string($key)){
                $this->key = $key;
                return true;
            }
            return false;
        }

        /*
         |  SET HASH
         |  @since  0.1.0
         */
        public function setHash($hash){
            if(in_array($hash, hash_algos())){
                $this->hash = $hash;
                return true;
            }
            return false;
        }

        /*
         |  HASH
         |  @since  0.1.0
         */
        public function hash($data){
            if(!empty($this->key)){
                return mhash(constant("MHASH_" . strtoupper($this->hash)), $data, $this->key);
            }
            return hash($this->hash, $data);
        }
    }
