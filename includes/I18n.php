<?php
/*
 |  FoxCMS      Content Management Simplified <www.foxcms.org>
 |  @file       ./system/func.fox.php
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

    class I18n{
        /*
         |  GLOBAL VARs
         */
        static private $fetch = false;
        static private $locale = NULL;
        static private $strings = array();
        static private $defaults = array();


        /*
         |  SET LOCALE
         |  @since  0.8.4
         |
         |  @param  multi   The respective locale to set or UNLL to use the default one.
         |
         |  @return bool    TRUE on sucess, FALSE on failure.
         */
        static public function setLocale($locale = NULL){
            self::$locale = empty($locale)? DEFAULT_LANGUAGE: $locale;

            // Load Default Strings
            $defaults = I18N_DIR . DEFAULT_LANGUAGE . "-message.php";
            if(!self::$fetch && self::$locale !== DEFAULT_LANGUAGE && file_exists($defaults)){
                $defaults = include($defaults);
                self::add($defaults, true);
            }
            self::$fetch = true;

            // Load Translation Strings
            $strings = I18N_DIR . self::$locale . "-message.php";
            if(file_exists($strings)){
                self::$strings = array();
                $strings = include($strings);
                return self::add($strings) > 0;
            }
            return false;
        }

        /*
         |  GET CURRENT LOCALE
         |  @since  0.8.4
         |
         |  @return string  The current locale string.
         */
        static public function getLocale(){
            return self::$locale;
        }

        /*
         |  ADD A TRANSLATION STRING
         |  @since  0.8.4
         |
         |  @param  array   The 'string' => 'translation' ARRAY pairs.
         |  @param  bool    TRUE to add these strings as default, FALSE to do it not.
         |
         |  @return multi   The number of added translation strings on success,
         |                  FALSE on failure.
         */
        static public function add($strings, $default = false){
            if(!is_array($strings)){
                return false;
            }

            $counter = 0;
            foreach($strings AS $key => $value){
                if(empty($key) || empty($value)){
                    continue;
                }
                if(!$default){
                    self::$strings[$key] = $value;
                } else {
                    self::$defaults[$key] = $value;
                }
                $counter++;
            }
            return $counter;
        }
        static public function addDefault($strings){
            return self::add($strings, true);
        }

        /*
         |  TRANSLATE A STRING
         |  @since  0.8.4
         |
         |  @param  string  The string to translate.
         |
         |  @return string  The translated string on success,
         |                  The default or passed string on failure.
         */
        static public function translate($string){
            if(isset(self::$strings[$string])){
                return self::$strings[$string];
            }
            if(isset(self::$defaults[$string])){
                return self::$defaults[$string];
            }
            return $string;
        }
        static public function getText($string){
            return self::translate($string);
        }

        /*
         |  CHECK IF LANGUAGE EXISTS / IS AVAILABLE
         |  @since  0.8.4
         |
         |  @param  string  The language code as STRING.
         |  @param  bool    TRUE to check if the language is also available,
         |                  FALSE to just check if the locale is valid.
         |
         |  @return bool    TRUE on success, FALSE on failure.
         */
        static public function isLanguage($locale, $available = false){
            $languages = ($available)? self::getAvailableLanguages(): self::getLanguages();
            $languages = array_map("strtolower", array_keys($languages));
            return in_array(strtolower($locale), $languages);
        }

        /*
         |  TRYS TO DETERMINES THE USER-PREFERRED LANGUAGE
         |  @since  0.8.4
         |
         |  @return array   An array of language-region codes.
         */
        static public function getPreferredLanguages(){
            $return = array();
            if(isset($_SERVER["HTTP_ACCEPT_LANGUAGE"])){
                $list = strtolower($_SERVER["HTTP_ACCEPT_LANGUAGE"]);
                $list = trim(str_replace(" ", "", $list));
                $list = array_filter(explode(",", $list));

                $languages = array_map("strtolower", array_keys(self::getLanguages()));
                foreach($list AS $language){
                    if(array_key_exists($language, $languages)){
                        $return[] = $language;
                    }
                }
            }
            return $return;
        }

        /*
         |  GET AVAILABLE LANGUAGE LIST
         |  @since  0.8.4
         */
        static public function getAvailableLanguages(){
            $return = array();
            if(is_dir(I18N_ROOT) && $handle = opendir(I18N_ROOT)){
                $languages = self::getLanguages();
                while(($file = readdir($handle)) !== false){
                    if(in_array($file, array(".", ".."))){
                        continue;
                    }

                    $file = explode("-", $file)[0];
                    if(strlen($file) < 2 || strlen($file) > 5){
                        continue;
                    }

                    if(array_key_exists($file, $languages)){
                        $return[$file] = $languages[$file];
                    }
                }
                closedir($handle);
            }
            return $return;
        }

        /*
         |  GET LANGUAGE LIST
         |  @since  0.8.4
         */
        static public function getLanguages(){
            return array(
                'aa'    => 'Afar',
                'ab'    => 'Abkhazian',
                'ae'    => 'Avestan',
                'af'    => 'Afrikaans',
                'ak'    => 'Akan',
                'am'    => 'Amharic',
                'an'    => 'Aragonese',
                'ar'    => 'Arabic',
                'ar_DZ' => 'Arabic/Algeria',
                'ar_BH' => 'Arabic/Bahrain',
                'ar_EG' => 'Arabic/Egypt',
                'ar_IQ' => 'Arabic/Iraq',
                'ar_JO' => 'Arabic/Jordan',
                'ar_KW' => 'Arabic/Kuwait',
                'ar_LB' => 'Arabic/Lebanon',
                'ar_LI' => 'Arabic/Libya',
                'ar_MA' => 'Arabic/Marocco',
                'ar_OM' => 'Arabic/Oman',
                'ar_QA' => 'Arabic/Qatar',
                'ar_SA' => 'Arabic/Saudi Arabia',
                'ar_SY' => 'Arabic/Syria',
                'ar_TN' => 'Arabic/Tunesia',
                'ar_AE' => 'Arabic/UAE',
                'ar_YE' => 'Arabic/Yemen',
                'as'    => 'Assamese',
                'av'    => 'Avaric',
                'ay'    => 'Aymara',
                'az'    => 'Azerbaijani',
                'ba'    => 'Bashkir',
                'be'    => 'Belarusian',
                'bg'    => 'Bulgarian',
                'bh'    => 'Bihari',
                'bi'    => 'Bislama',
                'bm'    => 'Bambara',
                'bn'    => 'Bengali',
                'bo'    => 'Tibetan',
                'br'    => 'Breton',
                'bs'    => 'Bosnian',
                'ca'    => 'Catalan',
                'ce'    => 'Chechen',
                'ch'    => 'Chamorro',
                'co'    => 'Corsican',
                'cr'    => 'Cree',
                'cs'    => 'Czech',
                'cu'    => 'Church Slavic',
                'cv'    => 'Chuvash',
                'cy'    => 'Welsh',
                'da'    => 'Danish',
                'de'    => 'German',
                'de_AT' => 'German/Austria',
                'de_DE' => 'German/Germany',
                'de_LI' => 'German/Liechtenstein',
                'de_LU' => 'German/Luxembourg',
                'de_CH' => 'German/Switzerland',
                'dv'    => 'Dhivehi',
                'dz'    => 'Dzongkha',
                'ee'    => 'Ewe',
                'el'    => 'Greek',
                'en'    => 'English',
                'en_AU' => 'English/Australia',
                'en_BZ' => 'English/Belize',
                'en_CA' => 'English/Canada',
                'en_IE' => 'English/Ireland',
                'en_JM' => 'English/Jamaica',
                'en_NZ' => 'English/New Zealand',
                'en_PH' => 'English/Philippines',
                'en_ZA' => 'English/South Africa',
                'en_TT' => 'English/Trinidad and Tobago',
                'en_UK' => 'English/United Kingdom',
                'en_US' => 'English/United States',
                'en_ZW' => 'English/Zimbabwe',
                'eo'    => 'Esperanto',
                'es'    => 'Spanish',
                'et'    => 'Estonian',
                'eu'    => 'Basque',
                'fa'    => 'Persian',
                'fa_IR' => 'Persian/Iran',
                'ff'    => 'Fulah',
                'fi'    => 'Finnish',
                'fj'    => 'Fijian',
                'fo'    => 'Faroese',
                'fr'    => 'French',
                'fr_BE' => 'French/Belgium',
                'fr_CA' => 'French/Canada',
                'fr_FR' => 'French/France',
                'fr_LU' => 'French/Luxembourg',
                'fr_MC' => 'French/Monaco',
                'fr_CH' => 'French/Switzerland',
                'fy'    => 'Western Frisian',
                'ga'    => 'Irish',
                'gd'    => 'Scottish Gaelic',
                'gl'    => 'Galician',
                'gn'    => 'Guarani',
                'gu'    => 'Gujarati',
                'gv'    => 'Manx',
                'ha'    => 'Hausa',
                'he'    => 'Hebrew',
                'hi'    => 'Hindi',
                'ho'    => 'Hiri Motu',
                'hr'    => 'Croatian',
                'ht'    => 'Haitian',
                'hu'    => 'Hungarian',
                'hy'    => 'Armenian',
                'hz'    => 'Herero',
                'ia'    => 'Interlingua',
                'id'    => 'Indonesian',
                'ie'    => 'Interlingue',
                'ig'    => 'Igbo',
                'ii'    => 'Sichuan Yi',
                'ik'    => 'Inupiaq',
                'io'    => 'Ido',
                'is'    => 'Icelandic',
                'it'    => 'Italian',
                'it_CH' => 'Italian/Switzerland',
                'iu'    => 'Inuktitut',
                'ja'    => 'Japanese',
                'jv'    => 'Javanese',
                'ka'    => 'Georgian',
                'kg'    => 'Kongo',
                'ki'    => 'Kikuyu',
                'kj'    => 'Kuanyama',
                'kk'    => 'Kazakh',
                'kl'    => 'Greenlandic',
                'km'    => 'Cambodian',
                'kn'    => 'Kannada',
                'ko'    => 'Korean',
                'ko_KP' => 'Korean/North Korea',
                'ko_KR' => 'Korean/South Korea',
                'kr'    => 'Kanuri',
                'ks'    => 'Kashmiri',
                'ku'    => 'Kurdish',
                'kv'    => 'Komi',
                'kw'    => 'Cornish',
                'ky'    => 'Kirghiz',
                'la'    => 'Latin',
                'lb'    => 'Luxembourgish',
                'lg'    => 'Ganda',
                'li'    => 'Limburgan',
                'ln'    => 'Lingala',
                'lo'    => 'Laothian',
                'lt'    => 'Lithuanian',
                'lu'    => 'Luba-Katanga',
                'lv'    => 'Latvian',
                'mg'    => 'Malagasy',
                'mh'    => 'Marshallese',
                'mi'    => 'Maori',
                'mk'    => 'Macedonian',
                'ml'    => 'Malayalam',
                'mn'    => 'Mongolian',
                'mo'    => 'Moldavian',
                'mr'    => 'Marathi',
                'ms'    => 'Malay',
                'mt'    => 'Maltese',
                'my'    => 'Burmese',
                'my_MM' => 'Burmese/Myanmar',
                'na'    => 'Nauru',
                'nb'    => 'Norwegian Bokmal',
                'nd'    => 'North Ndebele',
                'ne'    => 'Nepali',
                'ng'    => 'Ndonga',
                'nl'    => 'Dutch',
                'nl_BE' => 'Dutch/Belgium',
                'nn'    => 'Norwegian Nynorsk',
                'no'    => 'Norwegian',
                'nr'    => 'South Ndebele',
                'nv'    => 'Navajo',
                'ny'    => 'Nyanja',
                'oc'    => 'Occitan',
                'oj'    => 'Ojibwa',
                'om'    => 'Oromo',
                'or'    => 'Oriya',
                'os'    => 'Ossetian',
                'pa'    => 'Punjabi',
                'pi'    => 'Pali',
                'pl'    => 'Polish',
                'ps'    => 'Pushto',
                'pt'    => 'Portuguese',
                'pt_BR' => 'Portuguese/Brazil',
                'qu'    => 'Quechua',
                'rm'    => 'Romansh',
                'rn'    => 'Rundi',
                'ro'    => 'Romanian',
                'ru'    => 'Russian',
                'rw'    => 'Kinyarwanda',
                'sa'    => 'Sanskrit',
                'sc'    => 'Sardinian',
                'sd'    => 'Sindhi',
                'se'    => 'Northern Sami',
                'sg'    => 'Sangro',
                'sh'    => 'Serbo-Croatian',
                'si'    => 'Sinhala',
                'sk'    => 'Slovak',
                'sl'    => 'Slovenian',
                'sm'    => 'Samoan',
                'sn'    => 'Shona',
                'so'    => 'Somali',
                'sq'    => 'Albanian',
                'sr'    => 'Serbian',
                'ss'    => 'Siswati',
                'st'    => 'Sesotho',
                'su'    => 'Sudanese',
                'sv'    => 'Swedish',
                'sw'    => 'Swahili',
                'ta'    => 'Tamil',
                'te'    => 'Tegulu',
                'tg'    => 'Tajik',
                'th'    => 'Thai',
                'ti'    => 'Tigrinya',
                'tk'    => 'Turkmen',
                'tl'    => 'Tagalog',
                'tn'    => 'Tswana',
                'to'    => 'Tonga',
                'tr'    => 'Turkish',
                'ts'    => 'Tsonga',
                'tt'    => 'Tatar',
                'tw'    => 'Twi',
                'ty'    => 'Tahitian',
                'ug'    => 'Uighur',
                'uk'    => 'Ukrainian',
                'ur'    => 'Urdu',
                'uz'    => 'Uzbek',
                've'    => 'Venda',
                'vi'    => 'Vietnamese',
                'vo'    => 'Volapuk',
                'wa'    => 'Walloon',
                'wo'    => 'Wolof',
                'xh'    => 'Xhosa',
                'yi'    => 'Yiddish',
                'yo'    => 'Yoruba',
                'za'    => 'Zhuang',
                'zh'    => 'Chinese',
                'zh_CN' => 'Chinese/China',
                'zh_HK' => 'Chinese/Hong Kong',
                'zh_SG' => 'Chinese/Singapore',
                'zh_TW' => 'Chinese/Taiwan',
                'zu'    => 'Zulu'
            );
        }
    }

    ##
    ##  PROCEDURAL WAY
    ##

    /*
     |  TRANSLATE A STRING
     |  @since  0.8.4
     */
    function __($string, $args = array()){
        $string = I18n::translate($string);
        if(!empty($args)){
            $string = strtr($string, $args);
        }
        return $string;
    }
    function _e($string, $args = array()){
        print(__($string, $args));
    }

    /*
     |  TRANSLATE A PLURAL STRING
     |  @since  0.8.4
     */
    function _n($singular, $plural, $number, $args = array()){
        return __((($number > 1)? $singular: $plural), $args);
    }
    function _en($singular, $plural, $number, $args = array()){
        print(__((($number > 1)? $singular: $plural), $args));
    }
