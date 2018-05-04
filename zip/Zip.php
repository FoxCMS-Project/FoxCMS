<?php
/*
 |  FoxCMS      Content Management Simplified <www.foxcms.org>
 |  @file       ./includes/ZIP.php
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
 |  The following websites contains all required informations, which were unavoidable for the 
 |  creation of this class:
 |  
 |  -   https://pkware.cachefly.net/webdocs/casestudies/APPNOTE.TXT
 |  -   https://users.cs.jmu.edu/buchhofp/forensics/formats/pkzip.html
 |  -   php.net/manual/class.ziparchive.php
 */

    class Zip{
        const FLAGS = "\x00\x00";
        const VERSION = "\x14\x00";
        const SIGNATURE = "\x50\x4b";
        const COMPRESSION = "\x08\x00";
        
        /*
         |  SETTINGs
         */
        public $zipArchive = false;
        public $compression = 6;
        
        /*
         |  ZIP ARCHIVE
         */
        private $zipFilename;
        private $zipInstance;
        
        /*
         |  FALLBACK
         */
        private $offset = 0;
        private $headers = array();
        private $central = array();
        private $counter = 0;
        
        /*
         |  CONSTRUCTOR
         |  @since  0.2.0
         |
         |  @param  bool    TRUE to check and use ZipArchive if available,
         |                  FALSE to use the PKZip PHP compression per default.
         |  @param  int     The compression level between -1 and 9.
         */
        public function __construct($ziparchive = false, $compression = 6){
            if($ziparchive){
                $this->zipArchive = class_exists("ZipArchive", false);
                $this->zipInstance = ($this->zipArchive)? new ZipArchive(): false;
            }
            if($this->zipArchive){
                $this->zipFilename = tempnam(sys_get_temp_dir(), "tzp") . ".zip";
                $this->zipInstance->open($this->zipFilename, ZipArchive::CREATE);
            }
            $this->compression = ($compression >= -1 && $compression <= 9)? $compression: 6;
        }
        
        /*
         |  DESTRUCTOR
         |  @since  0.2.0
         */
        public function __destruct(){
            $this->clear(false);
        }
        
        /*
         |  HELPER :: CONVERT UNIX TO DOS TIME
         |  @since  0.2.0
         |
         |  @param  int     The respective timestamp as INTEGER.
         */
        private function hexTime($time){
            $array = getdate((is_int($time) && $time > 0)? $time: time());
            if($array["year"] < 1980){
                $array = getdate(time());
            }
            
            // Return as HEX
            $time = dechex(
                (($array["year"] - 1980) << 25) | ($array["mon"] << 21) | ($array["mday"] << 16) | 
                ($array["hours"] << 11) | ($array["minutes"] << 5)| ($array["seconds"] >> 1)
            );
            $time = $time[6].$time[7].$time[4].$time[5].$time[2].$time[3].$time[0].$time[1];
            return pack("H*", $time);
        }
        
        /*
         |  ADD A FILE
         |  @since  0.1.0
         |  @update 0.2.0
         |
         |  @param  string  The relative or absolute filepath or the respective file content.
         |  @param  string  The local path within the archive file.
         |  @param  int     The timestamp to use.
         |  
         |  @return bool    TRUE on success, FALSE on failure.
         */
        public function addFile($data, $path, $time = 0){
            if((!is_string($data) && !is_numeric($data)) || !is_string($path)){
                return false;
            }
            
            // Sanitize Data
            if(is_string($data) && file_exists($data) && is_file($data)){
                $data = file_get_contents($data);
            }
            $path = trim(str_replace("\\", "/", $path), "/");
            $time = $this->hexTime($time);
            
            // Zip Archive
            if($this->zipArchive){
                return $this->zipInstance->addFromString($path, $data);
            }
            
            // Fallback
            $crcval = crc32($data);
            $length = strlen($data);
            if(version_compare(PHP_VERSION, "5.4.0", ">=")){
                $gzcval = gzcompress($data, $this->compression, ZLIB_ENCODING_DEFLATE);
            } else {
                $gzcval = gzcompress($data, $this->compression);
            }
            $gzcval = substr(substr($gzcval, 0, strlen($gzcval) - 4), 2); // Fix CRC Bug
            $gzclen = strlen($gzcval);
            
            /*
             |  LOCAL FILE HEADER
             |  01      SIGNATURE
             |  02      Version needed to extract this archive.
             |  03      General purpose bit flag.
             |  04      Compression method.
             |  05      Last modification DOS datetime.
             |  06      CRC32 value.
             |  07      Compressed Filesize.
             |  08      Uncompressed Filesize.
             |  09      Length of the filename inside the archive.
             |  10      Length of the extra fields.
             |  11      The relative path / filename inside the archive.
             |  12      The main file data value.
             */
            $this->headers[] = implode("", array(
                self::SIGNATURE . "\x03\x04", 
                self::VERSION, 
                self::FLAGS, 
                self::COMPRESSION,
                $time,
                pack("V", $crcval),
                pack("V", $gzclen),
                pack("V", $length),
                pack("v", strlen($path)),
                pack("v", 0),
                $path,
                $gzcval
            ));
            
            /*
             |  CENTRAL DIRECTORY RECORD
             |  01      SIGNATURE
             |  02      MadeBy Version numbers.
             |  03      Version needed to extract this archive.
             |  04      General purpose bit flag.
             |  05      Compression method.
             |  06      Last modification DOS datetime.
             |  07      CRC32 value.
             |  08      Compressed Filesize.
             |  09      Uncompressed Filesize.
             |  10      Length of the filename inside the archive.
             |  11      Length of the extra fields.
             |  12      Length of the file comment.
             |  13      The disk number where the file exists.
             |  14      Internal file attributes.
             |  15      External file attributes.
             |  16      Offset of the local file header.
             |  17      The relative path / filename inside the archive.
             */
            $this->central[] = implode("", array(
                self::SIGNATURE . "\x01\x02", 
                "\x00\x00", 
                self::VERSION, 
                self::FLAGS, 
                self::COMPRESSION,
                $time,
                pack("V", $crcval),
                pack("V", $gzclen),
                pack("V", $length),
                pack("v", strlen($path)),
                pack("v", 0),
                pack("v", 0),
                pack("v", 0),
                pack("v", 0),
                pack("V", 32),
                pack("V", $this->offset),
                $path
            ));
            
            // Count Offset and Return
            $this->offset += strlen($this->headers[count($this->headers)-1]);
            return true;
        }
        
        /*
         |  ADD MULTIPLE FILES
         |  @since  0.2.0
         |
         |  @param  array   Multiple 'local/file/path' => "filepath/or/filecontent" ARRAY pairs.
         |
         |  @return int     The number of successfully added elements / files.
         */
        public function addFiles($array){
            if(!is_array($array)){
                return false;
            }
            foreach($array AS $path => &$data){
                $data = $this->addFile($data, $path);
            }
            return array_filter(array_values($array));
        }
        
        /*
         |  ADD FOLDER
         |  @since  0.2.0
         |
         |  @param  string  The path to the folder, which should be zipped.
         |  @param  string  The local path within the zip file.
         |  @param  bool    TRUE to zip recursive and include all sub directories,
         |                  FALSE to just zip all files within the $path folder.
         |  @param  bool    TRUE to include empty folders on recursive zips.
         |                  FALSE to skip empty folders.
         |
         |  @return multi   The number as INT of successfully added elements / files,
         |                  FALSE on failure.
         */
        public function addFolder($path, $local = "/", $recursive = false, $empty = false){
            if(!file_exists($path) || !is_dir($path)){
                return false;
            }
            
            // Chech Path
            $path = str_replace(array("/", "\\"), DIRECTORY_SEPARATOR, realpath($path));
            if(strpos($path, DIRECTORY_SEPARATOR) !== strlen($path)-1){
                $path .= DIRECTORY_SEPARATOR;
            }
            
            // Check Local
            if(!is_string($local)){
                $local = "";
            }
            $local = trim(str_replace("\\", "/", $local), "/") . "/";
            
            // Start Flow
            $this->counter = 0;
            $this->addFolderFlow($path, "", $local, !!$recursive, !!$empty);
            return $this->counter;
        }
        
        /*
         |  HELPER :: ADD FOLDER LOOP
         |  @since  0.2.0
         |
         |  @param  string  The base path to the folder, which should be zipped.
         |  @param  string  The further path, within the base path, on recursive calls.
         |  @param  string  The local path within the zip file.
         |  @param  bool    TRUE to zip recursive and include all sub directories,
         |                  FALSE to just zip all files within the $path folder.
         |  @param  bool    TRUE to include empty folders on recursive zips.
         |                  FALSE to skip empty folders.
         |
         |  @return int     The number of successfully added elements / files.
         */
        private function addFolderFlow($base, $path = "", $local = "", $recursive = false, $empty = false){
            $path = str_replace(array("/", "\\"), DIRECTORY_SEPARATOR, $path);
            $path = trim($path, DIRECTORY_SEPARATOR);
            if(!empty($path)){
                $path .= DIRECTORY_SEPARATOR;
            }
            
            $count = 0;
            $handle = opendir($base . $path);
            while(($file = readdir($handle)) !== false){
                if(in_array($file, array(".", ".."))){
                    continue;
                }
                if(is_dir($base . $path . $file)){
                    if($recursive){
                        $count = $this->addFolderFlow($base, $path . $file, $local, $recursive, $empty);
                        if($count == 0 && $empty){
                            $this->addEmptyFolder($local . $path . $file);
                        }
                    }
                    continue;
                }
                if(is_file($base . $path . $file)){
                    if($this->addFile($base . $path . $file, $local . $path . $file)){
                        $count++;
                        $this->counter++;
                    }
                }
            }
            closedir($handle);
            return $count;
        }
        
        /*
         |  ADD EMPTY FOLDER
         |  @since  0.2.0
         |
         |  @param  string  The local path structure within the zip file.
         |  
         |  @return bool    TRUE on success, FALSE on failure.
         */
        public function addEmptyFolder($path){
            $path = trim(str_replace("\\", "/", $path), "/") . "/";
            $time = $this->hexTime(time());
            
            // ZipArchive
            if($this->zipArchive){
                return $this->zipInstance->addEmptyDir($path);
            }
            
            // Add Header
            $this->headers[] = implode("", array(
                self::SIGNATURE . "\x03\x04", 
                self::VERSION, 
                self::FLAGS, 
                "\x00\x00", 
                $time,
                pack("V", 0),
                pack("V", 0),
                pack("V", 0),
                pack("v", strlen($path)),
                pack("v", 0),
                $path,
                ""
            ));
            
            // Add Central
            $this->central[] = implode("", array(
                self::SIGNATURE . "\x01\x02", 
                "\x14\x03", 
                self::VERSION, 
                self::FLAGS, 
                "\x00\x00", 
                $time,
                pack("V", 0),
                pack("V", 0),
                pack("V", 0),
                pack("v", strlen($path)),
                pack("v", 0),
                pack("v", 0),
                pack("v", 0),
                pack("v", 0),
                "\x00\x00\xFF\x41",
                pack("V", $this->offset),
                $path
            ));
            
            // Count Offset and Return
            $this->offset += strlen($this->headers[count($this->headers)-1]);
            return true;
        }
        
        /*
         |  CLEAR DATA STRINGs
         |  @since  0.1.0
         |  @update 0.2.0
         */
        public function clear($new = true){
            if($this->zipArchive){
                if(is_a($this->zipInstance, "ZipArchive")){
                    $this->zipInstance->close();
                }
                if(strpos($this->zipFilename, sys_get_temp_dir()) === 0 && file_exists($this->zipFilename)){
                    @unlink($this->zipFilename);
                }
                if($new){
                    $this->zipFilename = "./temp-".time().".zip";
                    $this->zipInstance = new ZipArchive();
                    $this->zipInstance->open($this->zipFilename, ZipArchive::CREATE);
                }
            }
            $this->offset = 0;
            $this->headers = array();
            $this->central = array();
            return true;
        }
        
        /*
         |  DUMBS OUT THE FILE
         |  @since  0.1.0
         |  @update 0.2.0
         */
        public function file(){
            $comment = "PK-Zipped with https://www.github.com/FoxCMS/ZIP";
            
            // ZipArchive
            if($this->zipArchive){
                $this->zipInstance->setArchiveComment($comment);
                $this->zipInstance->close();
                
                $content = file_get_contents($this->zipFilename);
                
                $this->zipInstance = new ZipArchive();
                $this->zipInstance->open($this->zipFilename);
                return $content;
            }
            
            // Fallback
            $headers = implode("", $this->headers);
            $central = implode("", $this->central);
            /*
             |  RETURN
             |  01      The file header items.
             |  02      The central directory items.
             |  03      The signature for the end of the central directory record.
             |  04      The number of this disk / part.
             |  05      The number of the disk / part where the central directory starts.
             |  06      The number of central directoy entries on this disk.
             |  07      Total number of entries on this disk / part.
             |  08      Total number of entries in general.
             |  09      Length of the central directory.
             |  10      Offset where the central directory starts.
             |  11      The length of the following comment field.
             |  12      The archive comment.
             */
            return implode("", array(
                $headers,
                $central,
                self::SIGNATURE . "\x05\x06", 
                "\x00",
                "\x00",
                "\x00",
                "\x00",
                pack("v", count($this->central)),
                pack("v", count($this->central)),
                pack("V", strlen($central)),
                pack("V", strlen($headers)),
                pack("v", strlen($comment)),
                $comment
            ));
        }
        
        /*
         |  STORE THE ZIP FILE
         |  @since  0.1.0
         |  @update 0.2.0
         |
         |  @param  string  The filename with the respective path to store the archive.
         |  @param  bool    TRUE to overwrite existing archives, FALSE to do it not.
         |
         |  @return bool    TRUE on success, FALSE on failure.
         */
        public function save($filename = "archive.zip", $overwrite = false){
            if(file_exists($filename) && !$overwrite){
                return false;
            }
            
            // Zip Archive
            if($this->zipArchive){
                if(is_a($this->zipInstance, "ZipArchive")){
                    $this->zipInstance->close();
                }
                if(@file_put_contents($filename, file_get_contents($this->zipFilename))){
                    @unlink($this->zipFilename);
                    
                    $this->zipFilename = $filename;
                    $this->zipInstance = new ZipArchive();
                    return $this->zipInstance->open($this->zipFilename);
                }
                return false;
            }
            
            // Fallback
            return @file_put_contents($filename, $this->file()) !== false;
        }
        
        /*
         |  DOWNLOAD THE FILE
         |  @since  0.1.0
         |  @update 0.2.0
         |
         |  @param  string  The filename for the archiv.
         |  @param  bool    TRUE to exit after the execution, FALSE to do it not.
         |
         |  @return void
         */
        public function download($filename = "archive.zip", $exit = false){
            $file = $this->file();
            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Cache-Control: private", false);
            header("Content-Type: application/zip");
            header("Content-Disposition: attachment; filename={$filename};" );
            header("Content-Transfer-Encoding: binary");
            header("Content-Length: " . strlen($file));
            print ($file);
            if($exit){
                die();
            }
        }
    }
