<?php
/*
 |  FoxCMS      Content Management Simplified <www.foxcms.org>
 |  @file       ./system/class.finder.php
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



    /**
     * Abstract class that allows models to easily implement find..By.. methods.
     *
     * By extending the `Finder` abstract class, users of an extending model can
     * make use of simple find.. and find..By.. methods without having to implement
     * them in the actual model.
     *
     * Example usage:
     *
     * <code>
     * class MyModel extends Finder {
     *     // code as if extending Record
     * }
     *
     * $object  = MyModel::findOneById(2);
     * $objects = MyModel::findAll();
     * </code>
     *
     * Users may consider these methods as generated wrappers around Record::find().
     *
     * Non-trivial example:
     *
     * <code>
     * // find users with same name
     * $objects = MyModel::findIdNameEmailByNameOrderedByIdAsc('mike');
     * </code>
     *
     * @author Martijn van der Kleijn <martijn.niji@gmail.com>
     * @copyright (c) 2014, Martijn van der Kleijn.
     */
    abstract class Finder extends Record {

        // Reserved keywords that can be used to construct a find method.
        private static $reserved = array(
            'all',
            'one',
            'by',
            'and',
            'ordered'
        );

        /**
         * Adds `SELECT` entry and passes on control to next find_* method.
         *
         * @param   array $commands         Array of tokens based on called virtual find.. method.
         * @param   array $options          Array of options, being built up for virtual find.. method.
         * @throws  BadMethodCallException  On failing to pass on to valid find_* method.
         */
        private static function find_all(&$commands, &$options = array()) {
            $options['select'][] = '*';

            if (is_array($commands) && !empty($commands)) {
                if (in_array($commands[0], self::$reserved)) {
                    $cmd = 'find_'.$commands[0];
                    self::$cmd(array_splice($commands, 1), $options);
                }
                else {
                    throw new BadMethodCallException("Unknown find method including ${$commands[0]}.");
                }
            }
        }

        /**
         * Adds `SELECT` entry with a `LIMIT` of one and passes on control to next find_* method.
         *
         * @param   array $commands         Array of tokens based on called virtual find.. method.
         * @param   array $options          Array of options, being built up for virtual find.. method.
         * @throws  BadMethodCallException  On failing to pass on to valid find_* method.
         */
        private static function find_one(&$commands, &$options = array()) {
            $options['select'][] = '*';
            $options['limit'][] = 1;

            if (is_array($commands) && !empty($commands)) {
                $cmd = array_shift($commands);

                if (in_array($cmd, self::$reserved)) {
                    $cmd = 'find_'.$cmd;
                    self::$cmd($commands, $options);
                }
                else {
                    throw new BadMethodCallException("Unknown find method including $cmd.");
                }
            }
        }

        /**
         * Adds `ORDER BY` entry and passes on control to next find_* method.
         *
         * @param   array $commands         Array of tokens based on called virtual find.. method.
         * @param   array $options          Array of options, being built up for virtual find.. method.
         * @throws  BadMethodCallException  On call to incorrectly named virtual find.. method.
         */
        private static function find_ordered(&$commands, &$options = array()) {
            if (is_array($commands) && !empty($commands) && array_shift($commands) == 'by') {
                $cmd = array_shift($commands);

                if (in_array($cmd, self::$reserved)) {
                    $cmd = 'find_'.$cmd;
                    self::$cmd($commands, $options);
                }
                else {
                    if (count($commands) > 0 && ($commands[0] == 'asc' || $commands[0] == 'desc')) {
                        $cmd .= ' '.array_shift($commands);
                    }
                    $options['order'][] = $cmd;
                }
            }
            else {
                throw new BadMethodCallException();
            }
        }

        /**
         * Adds `AND` entry and passes on control to next find_* method.
         *
         * @param   array $commands     Array of tokens based on called virtual find.. method.
         * @param   array $options      Array of options, being built up for virtual find.. method.
         */
        private static function find_and(&$commands, &$options = array()) {
            if (is_array($commands) && !empty($commands)) {
                if (in_array($commands[0], self::$reserved)) {
                    $cmd = 'find_'.$commands[0];
                    self::$cmd(array_splice($commands, 1), $options);
                }
                else {
                    $options['where'][] = "${commands[0]}=?";
                }
            }
        }

        /**
         * Adds `WHERE` entry and passes on control to next find_* method.
         *
         * @param   array $commands     Array of tokens based on called virtual find.. method.
         * @param   array $options      Array of options, being built up for virtual find.. method.
         */
        private static function find_by(&$commands, &$options = array()) {
            for ($i=0; $i<count($commands); $i++) {
                if (!in_array($commands[$i], self::$reserved)) {
                    $options['where'][] = "${commands[$i]}=?";
                }
                else {
                    $cmd = 'find_'.$commands[$i];
                    self::$cmd(array_splice($commands, $i+1), $options);
                }
            }
        }

        /**
         * Implements a virtual find.. or find..By.. method.
         *
         * Note: this is, of course, automatically called.
         *
         * @param string    $name       Name of virtual find.. method.
         * @param array     $arguments  Array of arguments given to virtual find.. method.
         * @ignore
         */
        public static function __callStatic($name, $arguments) {
            // Options array to later pass on to Record::find()
            $options = array(
                'select' => array(),
                'where'  => array(),
                'order'  => array(),
                'limit'  => array(),
                'offset' => array()
            );

            // Check if this is a correct find.. or find..By.. method call.
            preg_match("/^find[A-Z][a-z]+.*/", $name, $matches);
            if ( empty($matches) ) {
                // Its not, try our parent.
                parent::__callStatic($name, $arguments);
            }

            // Match the virtual method's name and lowercase entries.
            preg_match_all("/([A-Z][a-z]+)/", $name, $matches);
            $matches = array_map('strtolower', $matches[1]);

            // Run through matches and try to fire subcommands.
            for($i = 0; $i < count($matches); $i++) {
                $entry = array_shift($matches);

                // If its not a reserved name, assume its a field name and add to SELECT.
                if (!in_array($entry, self::$reserved)) {
                    $options['select'][] = $entry;
                }
                else {
                    $cmd = 'find_'.$entry;
                    self::$cmd($matches, $options);
                }
            }

            // Prep options for Record::find()
            $options['select'] = implode(','     , $options['select']);
            $options['where']  = implode(' AND ' , $options['where']);
            $options['order']  = implode(','     , $options['order']);
            $options['limit']  = (int) implode('', $options['limit']);
            $options['offset'] = (int) implode('', $options['offset']);
            $options['values'] = $arguments;

            // Run options through Record::find()
            return self::find($options);
        }
    }
