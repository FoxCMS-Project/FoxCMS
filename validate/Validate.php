<?php
/*
 |  A Validation helper class.
 |  @file       ./Validate.php
 |  @author     SamBrishes@pytesNET
 |  @version    0.2.0 [0.2.0] - Beta
 |
 |  @license    GNU GPL v3
 |  @copyright  Copyright © 2015 - 2018 SamBrishes, pytesNET <pytes@gmx.net>
 |
 |  @history    Copyright © 2009 - 2015 Martijn van der Kleijn <martijn.niji@gmail.com>
 |              Copyright © 2008 - 2009 Philippe Archambault <philippe.archambault@gmail.com>
 */

    class Validate{
        /*
         |  VALIDATE EMAIL
         |  @since  0.1.0
         |  @update 0.2.0
         |
         |  @param  string  The respective eMail to validate.
         |
         |  @return bool    TRUE if the eMail seems valid, FALSE if not.
         */
        static public function email($email){
            return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
        }
        static public function email_rfc($email){
            return self::email($email);
        }

        /*
         |  VALIDATE EMAIL DOMAIN MX RECORD
         |  @since  0.1.0
         |  @update 0.2.0
         |
         |  @param  string  The respective eMail to validate.
         |
         |  @return multi   TRUE if the MX-Reord of the Provider is valid, FALSE if not,
         |                  NULL if the `checkdnsrr()` function isn't available!
         */
        static public function email_domain($email){
            if(!function_exists("checkdnsrr")){
                return NULL;
            }
            $provider = explode("@", $email);
            return checkdnsrr(array_pop($provider), "MX");
        }

        /*
         |  VALIDATE URL
         |  @since  0.1.0
         |  @update 0.2.0
         |
         |  @param  string  The respective URL to validate (MUST to stat with http(s)!).
         |
         |  @return bool    TRUE if the URL seems valid, FALSE if not.
         */
        static public function url($url){
            return filter_var($url, FILTER_VALIDATE_URL, FILTER_FLAG_HOST_REQUIRED) !== false;
        }

        /*
         |  VALIDATE IP
         |  @since  0.1.0
         |  @update 0.2.0
         |
         |  @param  string  The respective IP to validate.
         |  @param  bool    TRUE to validate IPv6 addresses too, FALSE to validate just IPv4!
         |  @param  bool    TRUE to allow private IP ranges, FALSE if not.
         |
         |  @return bool    TRUE if the IP seems valid, FALSE if not.
         */
        static public function ip($ip, $ipv6 = true, $private = true){
            $flags = FILTER_FLAG_NO_RES_RANGE;
            if(!$private){
                $flags |= FILTER_FLAG_NO_PRIV_RANGE;
            }
            if(!$ipv6){
                $flags |= FILTER_FLAG_IPV4;
            }
            return filter_var($ip, FILTER_VALIDATE_IP, $flags) !== false;
        }

        /*
         |  VALIDATE CREDIT CARD
         |  @since  0.1.0
         |  @update 0.2.0
         |
         |  @param  string  The respective credit card number to validate.
         |  @param  multi   A single credit card provider as STRING, multiple as ARRAY:
         |                  NULL to don't check for the provider.
         |
         |  @return bool    TRUE if the Credit Card number seems valid, FALSE if not.
         */
        static public function credit_card($number, $provider = NULL){
            if(($number = preg_replace("#\D+#", "", $number)) == ""){
                return false;
            }
            if(is_array($provider)){
                foreach($provider AS $test){
                    if(self::credit_card($number, $test)){
                        return true;
                    }
                }
                return false;
            } else if(!is_string($provider) || empty($provider)){
                $provider = "default";
            }

            // Get Provider
            if(!isset(self::$cards[$provider])){
                return false;
            }
            $card = self::$cards[$provider];
            $length = strlen($number);

            // Check
            if(!in_array($length, preg_split("#\D+#", $card["length"]))){
                return false;
            }
            if(!preg_match("#^{$card["prefix"]}#", $number)){
                return false;
            }
            if($card["luhn"] == false){
                return true;
            }

            // Checsum
            $check = 0;
            for($i = $length - 1; $i >= 0; $i -= 2){
                $check += substr($number, $i, 1);
            }
            for($i = $length - 2; $i >= 0; $i -= 2){
                $double = substr($number, $i, 1) * 2;
                $check += ($double >= 10)? $double - 9: $double;
            }
            return ($check % 10) == 0;
        }

        /*
         |  VALIDATE IBAN NUMBER
         |  @since  0.1.0
         |  @update 0.2.0
         |
         |  @param  string  The respective IBAN number to validate.
         |
         |  @return bool    TRUE if the IBAN number seems valid, FALSE if not.
         */
        static public function iban($iban){
            $iban = strtolower(str_replace(" ", "", $iban));
            if(!array_key_exists(substr($iban, 0, 2), self::$ibans)){
                return false;
            }
            if(strlen($iban) !== self::$ibans[substr($iban, 0, 2)]){
                return false;
            }
            $chars = array_combine(range("a", "z"), range(10, 35));
            $moved = strtr(substr($iban, 4) . substr($iban, 0, 4), $chars);
            return (bcmod($moved, "97") == 1);
        }

        /*
         |  VALIDATE PHONE NUMBERs
         |  @since  0.1.0
         |  @update 0.2.0
         |
         |  @param  string  The respective phone number to validate.
         |  @param  multi   A single phone number length as INT, multiple as ARRAY,
         |                  use an associated array to test a range:
         |                      array("min" => 7, "max" => 15).
         |
         |  @return bool    TRUE if the Phone number seems valid, FALSE if not.
         */
        static public function phone($number, $length = array(7, 10, 11)){
            if(is_int($length) || is_numeric($length)){
                $length = array($length);
            }
            if(!is_array($length)){
                return false;
            }
            $number = strlen(preg_replace("#\D+#", "", $number));

            if(isset($length["min"]) || isset($length["max"])){
                if(isset($length["min"]) && $number < $length["min"]){
                    return false;
                }
                if(isset($length["max"]) && $number > $length){
                    return false;
                }
                return true;
            }
            return in_array($number, $length);
        }

        /*
         |  VALIDATE DATE
         |  @since  0.1.0
         |  @update 0.2.0
         |
         |  @param  string  The respective date string to validate.
         |
         |  @return bool    TRUE if the date seems valid, FALSE if not.
         */
        static public function date($date){
            return (strtotime($date) !== false && strtotime($date) > strtotime(date("Y", 0)));
        }

        /*
         |  VALIDATE DATETIME
         |  @since  0.1.0
         |  @update 0.2.0
         |
         |  @param  string  The respective datetime string to validate.
         |
         |  @return bool    TRUE if the DateTieme is valid, FALSE if not.
         */
        static public function datetime($datetime){
            if(!preg_match("#^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}#", $datetime)){
                return false;
            }
            return (strtotime($datetime) !== false && strtotime($datetime) > strtotime(date("Y", 0)));
        }

        /*
         |  VALIDATE ALPHA
         |  @test   Tests for the following characters only 'a-z', 'A-Z'!
         |  @since  0.1.0
         |  @update 0.2.0
         |
         |  @param  string  The respective string to validate.
         |  @param  bool    TRUE to test for UTF8 characters, FALSE to do it not.
         |
         |  @return bool    TRUE if the string consist of the respective chars only, FALSE if not.
         */
        static public function alpha($string, $utf8 = false){
            if(!$utf8){
                return ctype_alpha($string);
            }
            return (bool) preg_match("#^\pL++$#uD", $string);
        }

        /*
         |  VALIDATE ALPHA-NUMERIC
         |  @test   Tests for the following characters only 'a-z', 'A-Z', '0-9'!
         |  @since  0.1.0
         |  @update 0.2.0
         |
         |  @param  string  The respective string to validate.
         |  @param  bool    TRUE to test for UTF8 characters, FALSE to do it not.
         |
         |  @return bool    TRUE if the string consist of the respective chars only, FALSE if not.
         */
        static public function alpha_numeric($string, $utf8 = false){
            if(!$utf8){
                return ctype_alnum($string);
            }
            return (bool) preg_match("#^[\pL\pN]++$#uD", $string);
        }

        /*
         |  VALIDATE ALPHA-NUMERIC-DASH
         |  @test   Tests for the following characters only 'a-z', 'A-Z', '0-9', '-', '_'!
         |  @since  0.1.0
         |  @update 0.2.0
         |
         |  @param  string  The respective string to validate.
         |  @param  bool    TRUE to test for UTF8 characters, FALSE to do it not.
         |
         |  @return bool    TRUE if the string consist of the respective chars only, FALSE if not.
         */
        static public function alpha_dash($string, $utf8 = false){
            if(!$utf8){
                return (bool) preg_match("#^[a-zA-Z0-9\-\_]++$#iD", $string);
            }
            return (bool) preg_match("#^[\pL\pN\-\_]++$#uD", $string);
        }

        /*
         |  VALIDATE ALPHA-NUMERIC-DASH-COMMA
         |  @test   Tests for the following characters only 'a-z', 'A-Z', '0-9', '-', '_', ',', '.', ' '!
         |  @since  0.1.0
         |  @update 0.2.0
         |
         |  @param  string  The respective string to validate.
         |  @param  bool    TRUE to test for UTF8 characters, FALSE to do it not.
         |
         |  @return bool    TRUE if the string consist of the respective chars only, FALSE if not.
         */
        static public function alpha_comma($string, $utf8 = false){
            if(!$utf8){
                return (bool) preg_match("#^[a-zA-Z0-9\-\_\,\. ]++$#iD", $string);
            }
            return (bool) preg_match("#^[\pL\pN\-\_\,\. ]++$#uD", $string);
        }

        /*
         |  VALIDATE ALPHA-SPACE
         |  @test   Tests for the following characters only 'a-z', 'A-Z', ' '!
         |  @since  0.1.0
         |  @update 0.2.0
         |
         |  @param  string  The respective string to validate.
         |  @param  bool    TRUE to test for UTF8 characters, FALSE to do it not.
         |
         |  @return bool    TRUE if the string consist of the respective chars only, FALSE if not.
         */
        static public function alpha_space($string, $utf8 = false){
            if(!$utf8){
                return (bool) preg_match("#^[a-zA-Z\s]++$#iD", $string);
            }
            return (bool) preg_match("#^[\pL\s]++$#uD", $string);
        }

        /*
         |  VALIDATE ALPHA-NUMERIC-SPACE
         |  @test   Tests for the following characters only 'a-z', 'A-Z', '0-9', ' '!
         |  @since  0.1.0
         |  @update 0.2.0
         |
         |  @param  string  The respective string to validate.
         |  @param  bool    TRUE to test for UTF8 characters, FALSE to do it not.
         |
         |  @return bool    TRUE if the string consist of the respective chars only, FALSE if not.
         */
        static public function alphanum_space($string, $utf8 = false){
            if(!$utf8){
                return (bool) preg_match("#^[a-zA-Z0-9\s]++$#iD", $string);
            }
            return (bool) preg_match("#^[\pL\pN\s]++$#uD", $string);
        }

        /*
         |  VALIDATE SLUG
         |  @test   Tests for the following characters only 'a-z', '0-9', '-', '_', '.'!
         |  @since  0.1.0
         |  @update 0.2.0
         |
         |  @param  string  The respective string to validate.
         |  @param  bool    TRUE to test for UTF8 characters, FALSE to do it not.
         |
         |  @return bool    TRUE if the string consist of the respective chars only, FALSE if not.
         */
        static public function slug($string, $utf8 = false){
            if(!$utf8){
                return (bool) preg_match("#^[a-z0-9\-\_\.]++$#iD", $string);
            }
            return (bool) preg_match("#^[\pLl\pN\-\_\.]++$#uD", $string);
        }

        /*
         |  VALIDATE DIGITs
         |  @test   Tests for the following characters only '0-9'!
         |  @since  0.1.0
         |  @update 0.2.0
         |
         |  @param  string  The respective string to validate.
         |  @param  bool    TRUE to test for UTF8 characters, FALSE to do it not.
         |
         |  @return bool    TRUE if the string consist of the respective chars only, FALSE if not.
         */
        static public function digit($string, $utf8 = false){
            if(!$utf8){
                return ctype_digit($string);
            }
            return (bool) preg_match("#^[\pN]++$#uD", $string);
        }

        /*
         |  VALIDATE NUMERIC
         |  @test   Tests for the following characters only '0-9', '.', ','!
         |  @since  0.1.0
         |  @update 0.2.0
         |
         |  @param  string  The respective string to validate.
         |
         |  @return bool    TRUE if the string consist of the respective chars only, FALSE if not.
         */
        static public function numeric($string){
            return (bool) preg_match("#^[0-9\.\,]++$#D", $string);
        }

        /*
         |  VALIDATE RANGE
         |  @since  0.1.0
         |  @update 0.2.0
         |
         |  @param  string  The respective number to validate.
         |  @param  array   The respective range (min[, max[, step]]);
         |
         |  @return bool    TRUE if the number matches the passed range, FALSE if not.
         */
        static public function range($number, $range){
            if(is_numeric($number)){
                if(count($range) == 1){
                    if($number >= $range[0]){
                        return true;
                    }
                } else if(count($range) == 2){
                    if($number >= $range[0] && $number <= $range[1]){
                        return true;
                    }
                } else if(count($range) >= 3){
                    $range = call_user_func_array("range", $range);
                    if(in_array($number, $range)){
                        return true;
                    }
                }
            }
            return false;
        }

        /*
         |  VALIDATE DECIMAL
         |  @since  0.1.0
         |  @update 0.2.0
         |
         |  @param  string  The respective number to validate.
         |  @param  multi   The respective format of the decimal. Use NULL to check if the
         |                  number is decimwal only, Use a single integer to check the number
         |                  of decimal places (after the decimal), Use an array to check
         |                  (number of digits, number of decimal).
         |
         |  @return bool    TRUE if the number matches the passed format, FALSE if not.
         */
        static public function decimal($number, $format = NULL){
            if(is_numeric($format)){
                $format = array($format);
            }

            $pattern = "#^[0-9]%s\.[0-9]%s$#";
            if(is_array($format) && count($format) >= 1){
                if(count($format) == 1){
                    $pattern = sprintf($pattern, "+", "{".$format[0]."}");
                } else if(count($format) >= 2){
                    $pattern = sprintf($pattern, "{".$format[0]."}", "{".$format[1]."}");
                }
            } else {
                $pattern = sprintf($pattern, "+", "+");
            }
            return (bool) preg_match($pattern, (string) $number);
        }

        /*
         |  VALIDATE COLOR
         |  @since  0.1.0
         |  @update 0.2.0
         |
         |  @param  string  The respective color string to validate.
         |  @param  multi   The respective format of the color. Use NULL to check all formats,
         |                  or use a string or an array of strings with the formats.
         |
         |  @return bool    TRUE if the color is valid, FALSE if not.
         */
        static public function color($string, $format = NULL){
            if(is_string($format)){
                $format = array($format);
            }
            if(!is_array($format)){
                $format = array("hex", "rgb", "hsl");
            }

            // HEX
            if(in_array("hex", $format)){
                if(preg_match("/^\#?+[0-9a-fA-F]{3}(?:[0-9a-fA-F]{3})?$/iD", $string)){
                    return true;
                }
            }

            // RGB(A)
            if(strpos($string, "rgb") !== false && in_array("rgb", $format)){
                $string = trim(ltrim($string, "rgba"), "()");
                $number = array_map("floatval", explode(",", $string));

                if(count($number) >= 3 || count($number) <= 4){
                    for($i = 0; $i < count($number); $i++){
                        if($i == 3 && ($number[$i] < 0.0 || $number[$i] > 1.0)){
                            return false;
                        } else if($number[$i] < 0.0 || $number[$i] > 255.0){
                            return false;
                        }
                    }
                    return true;
                }
            }

            // HSL(A)
            if(strpos($string, "hsl") !== false && in_array("hsl", $format)){
                $string = trim(ltrim($string, "hsla"), "()");
                $number = array_map("floatval", explode(",", $string));

                if(count($number) >= 3 || count($number) <= 4){
                    if(count($number) < 3 || count($number) > 4){
                        return false;
                    }
                    if($number[0] < 0.0 || $number[0] > 360.0){
                        return false;
                    }
                    if($number[1] < 0.0 || $number[1] > 100.0 || $number[2] < 0.0 || $number[2] > 100.0){
                        return false;
                    }
                    if(count($number) == 4 && ($number[3] < 0.0 || $number[3] > 1.0)){
                        return false;
                    }
                    return true;
                }
            }

            // No Color Scheme found
            return false;
        }

        /*
         |  VALIDATE DIVIDENT AND DIVISOR
         |  @since  0.1.0
         |  @update 0.2.0
         |
         |  @param  int     The respective dividend.
         |  @param  int     The respective divisor.
         |
         |  @return bool   TRUE of the divisor is a multiple of the divient, FALSE if not.
         */
        static public function multiple($divident, $divisor){
            return !(bool) ((int) $divident % (int) $divisor);
        }

        /*
         |  VALIDATE POSTCODE
         |  @since  0.1.0
         |  @update 0.2.0
         |
         |  @param  string  The respective postcode string to validate.
         |  @param  string  The respective country code of the postcode.
         |
         |  @return multi   TRUE if the Postcode seems valid , FALSE if not.
         */
        static public function postcode($postcode, $country = "en"){
            if(!is_string($postcode) && !is_numeric($postcode)){
                return false;
            }
            if(!array_key_exists($country, self::$postcodes)){
                return false;
            }
            return (bool) preg_match("#".self::$postcodes[$country]."#", $postcode);
        }

        /*
         |  VALIDATE UTF8
         |  @since  0.1.0
         |  @update 0.2.0
         |
         |  @param  string  The respective string to validate.
         |
         |  @return multi   TRUE if the string is valid UTF-8, FALSE if not.
         */
        static public function valid_utf8($string){
            if(!is_string($string) && !is_numeric($string)){
                return false;
            }
            if(empty($string)){
                return true;
            }

            // Source: https://www.w3.org/International/questions/qa-forms-utf-8.en
            return (bool) preg_match('%^(?:
                  [\x09\x0A\x0D\x20-\x7E]            # ASCII
                | [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
                | \xE0[\xA0-\xBF][\x80-\xBF]         # excluding overlongs
                | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
                | \xED[\x80-\x9F][\x80-\xBF]         # excluding surrogates
                | \xF0[\x90-\xBF][\x80-\xBF]{2}      # planes 1-3
                | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
                | \xF4[\x80-\x8F][\x80-\xBF]{2}      # plane 16
            )*$%xs', $string);
        }
        static public function compliant_utf8($string){
            return self::valid_utf8($string);
        }


        /*
         |  CREDIT CARD NUMBERs
         */
        static public $cards = array(
            "default"           => array(
                "length"    => "13,14,15,16,17,18,19",
                "prefix"    => "",
                "luhn"      => true
            ),
            "american express"  => array(
                "length"    => "15",
                "prefix"    => "3[47]",
                "luhn"      => true
            ),
            "diners club"       => array(
                "length"    => "14,16",
                "prefix"    => "36|55|30[0-5]",
                "luhn"      => true
            ),
            "discover"          => array(
                "length"    => "16,18",
                "prefix"    => "6(?:5|011)",
                "luhn"      => true,
            ),
            "jcb"               => array(
                "length"    => "15,16",
                "prefix"    => "3|1800|2131",
                "luhn"      => true
            ),
            "maestro"           => array(
                "length"    => "16,18",
                "prefix"    => "50(?:20|38)|6(?:304|759)",
                "luhn"      => true
            ),
            "mastercard"        => array(
                "length"    => "16",
                "prefix"    => "5[1-5]",
                "luhn"      => true
            ),
            "visa"              => array(
                "length"    => "13,16",
                "prefix"    => "4",
                "luhn"      => true
            )
        );

        /*
         |  POSTCODES
         */
        static public $postcodes = array(
            "gb" => "GIR[ ]?0AA|((AB|AL|B|BA|BB|BD|BH|BL|BN|BR|BS|BT|CA|CB|CF|CH|CM|CO|CR|CT|CV|CW|DA|DD|DE|DG|DH|DL|DN|DT|DY|E|EC|EH|EN|EX|FK|FY|G|GL|GY|GU|HA|HD|HG|HP|HR|HS|HU|HX|IG|IM|IP|IV|JE|KA|KT|KW|KY|L|LA|LD|LE|LL|LN|LS|LU|M|ME|MK|ML|N|NE|NG|NN|NP|NR|NW|OL|OX|PA|PE|PH|PL|PO|PR|RG|RH|RM|S|SA|SE|SG|SK|SL|SM|SN|SO|SP|SR|SS|ST|SW|SY|TA|TD|TF|TN|TQ|TR|TS|TW|UB|W|WA|WC|WD|WF|WN|WR|WS|WV|YO|ZE)(\d[\dA-Z]?[]?\d[ABD-HJLN-UW-Z]{2}))|BFPO[ ]?\d{1,4}",
            "je" => "JE\d[\dA-Z]?[ ]?\d[ABD-HJLN-UW-Z]{2}",
            "gg" => "GY\d[\dA-Z]?[ ]?\d[ABD-HJLN-UW-Z]{2}",
            "im" => "IM\d[\dA-Z]?[ ]?\d[ABD-HJLN-UW-Z]{2}",
            "us" => "\d{5}([ \-]\d{4})?",
            "ca" => "[ABCEGHJKLMNPRSTVXY]\d[A-Z][ ]?\d[A-Z]\d",
            "de" => "\d{5}",
            "jp" => "\d{3}-\d{4}",
            "fr" => "\d{2}[ ]?\d{3}",
            "au" => "\d{4}",
            "it" => "\d{5}",
            "ch" => "\d{4}",
            "at" => "\d{4}",
            "es" => "\d{5}",
            "nl" => "\d{4}[ ]?[A-Z]{2}",
            "be" => "\d{4}",
            "dk" => "\d{4}",
            "se" => "\d{3}[ ]?\d{2}",
            "no" => "\d{4}",
            "br" => "\d{5}[\-]?\d{3}",
            "pt" => "\d{4}([\-]\d{3})?",
            "fi" => "\d{5}",
            "ax" => "22\d{3}",
            "kr" => "\d{3}[\-]\d{3}",
            "cn" => "\d{6}",
            "tw" => "\d{3}(\d{2})?",
            "sg" => "\d{6}",
            "dz" => "\d{5}",
            "ad" => "AD\d{3}",
            "ar" => "([A-HJ-NP-Z])?\d{4}([A-Z]{3})?",
            "am" => "(37)?\d{4}",
            "az" => "\d{4}",
            "bh" => "((1[0-2]|[2-9])\d{2})?",
            "bd" => "\d{4}",
            "bb" => "(BB\d{5})?",
            "by" => "\d{6}",
            "bm" => "[A-Z]{2}[ ]?[A-Z0-9]{2}",
            "ba" => "\d{5}",
            "io" => "BBND 1ZZ",
            "bn" => "[A-Z]{2}[ ]?\d{4}",
            "bg" => "\d{4}",
            "kh" => "\d{5}",
            "cv" => "\d{4}",
            "cl" => "\d{7}",
            "cr" => "\d{4,5}|\d{3}-\d{4}",
            "hr" => "\d{5}",
            "cy" => "\d{4}",
            "cz" => "\d{3}[ ]?\d{2}",
            "do" => "\d{5}",
            "ec" => "([A-Z]\d{4}[A-Z]|(?:[A-Z]{2})?\d{6})?",
            "eg" => "\d{5}",
            "ee" => "\d{5}",
            "fo" => "\d{3}",
            "ge" => "\d{4}",
            "gr" => "\d{3}[ ]?\d{2}",
            "gl" => "39\d{2}",
            "gt" => "\d{5}",
            "ht" => "\d{4}",
            "hn" => "(?:\d{5})?",
            "hu" => "\d{4}",
            "is" => "\d{3}",
            "in" => "\d{6}",
            "id" => "\d{5}",
            "ie" => "((D|DUBLIN)?([1-9]|6[wW]|1[0-8]|2[024]))?",
            "il" => "\d{5}",
            "jo" => "\d{5}",
            "kz" => "\d{6}",
            "ke" => "\d{5}",
            "kw" => "\d{5}",
            "la" => "\d{5}",
            "lv" => "\d{4}",
            "lb" => "(\d{4}([ ]?\d{4})?)?",
            "li" => "(948[5-9])|(949[0-7])",
            "lt" => "\d{5}",
            "lu" => "\d{4}",
            "mk" => "\d{4}",
            "my" => "\d{5}",
            "mv" => "\d{5}",
            "mt" => "[A-Z]{3}[ ]?\d{2,4}",
            "mu" => "(\d{3}[A-Z]{2}\d{3})?",
            "mx" => "\d{5}",
            "md" => "\d{4}",
            "mc" => "980\d{2}",
            "ma" => "\d{5}",
            "np" => "\d{5}",
            "nz" => "\d{4}",
            "ni" => "((\d{4}-)?\d{3}-\d{3}(-\d{1})?)?",
            "ng" => "(\d{6})?",
            "om" => "(PC )?\d{3}",
            "pk" => "\d{5}",
            "py" => "\d{4}",
            "ph" => "\d{4}",
            "pl" => "\d{2}-\d{3}",
            "pr" => "00[679]\d{2}([ \-]\d{4})?",
            "ro" => "\d{6}",
            "ru" => "\d{6}",
            "sm" => "4789\d",
            "sa" => "\d{5}",
            "sn" => "\d{5}",
            "sk" => "\d{3}[ ]?\d{2}",
            "si" => "\d{4}",
            "za" => "\d{4}",
            "lk" => "\d{5}",
            "tj" => "\d{6}",
            "th" => "\d{5}",
            "tn" => "\d{4}",
            "tr" => "\d{5}",
            "tm" => "\d{6}",
            "ua" => "\d{5}",
            "uy" => "\d{5}",
            "uz" => "\d{6}",
            "va" => "00120",
            "ve" => "\d{4}",
            "zm" => "\d{5}",
            "as" => "96799",
            "cc" => "6799",
            "ck" => "\d{4}",
            "rs" => "\d{6}",
            "me" => "8\d{4}",
            "cs" => "\d{5}",
            "yu" => "\d{5}",
            "cx" => "6798",
            "et" => "\d{4}",
            "fk" => "FIQQ 1ZZ",
            "nf" => "2899",
            "fm" => "(9694[1-4])([ \-]\d{4})?",
            "gf" => "9[78]3\d{2}",
            "gn" => "\d{3}",
            "gp" => "9[78][01]\d{2}",
            "gs" => "SIQQ 1ZZ",
            "gu" => "969[123]\d([ \-]\d{4})?",
            "gw" => "\d{4}",
            "hm" => "\d{4}",
            "iq" => "\d{5}",
            "kg" => "\d{6}",
            "lr" => "\d{4}",
            "ls" => "\d{3}",
            "mg" => "\d{3}",
            "mh" => "969[67]\d([ \-]\d{4})?",
            "mn" => "\d{6}",
            "mp" => "9695[012]([ \-]\d{4})?",
            "mq" => "9[78]2\d{2}",
            "nc" => "988\d{2}",
            "ne" => "\d{4}",
            "vi" => "008(([0-4]\d)|(5[01]))([ \-]\d{4})?",
            "pf" => "987\d{2}",
            "pg" => "\d{3}",
            "pm" => "9[78]5\d{2}",
            "pn" => "PCRN 1ZZ",
            "pw" => "96940",
            "re" => "9[78]4\d{2}",
            "sh" => "STHL 1ZZ",
            "sj" => "\d{4}",
            "so" => "\d{5}",
            "sz" => "[HLMS]\d{3}",
            "tc" => "TKCA 1ZZ",
            "wf" => "986\d{2}",
            "yt" => "976\d{2}"
        );

        /*
         |  IBAN CODEs
         */
        static public $ibans = array(
            "al" => 28,
            "ad" => 24,
            "at" => 20,
            "az" => 28,
            "bh" => 22,
            "be" => 16,
            "ba" => 20,
            "br" => 29,
            "bg" => 22,
            "cr" => 21,
            "hr" => 21,
            "cy" => 28,
            "cz" => 24,
            "dk" => 18,
            "do" => 28,
            "ee" => 20,
            "fo" => 18,
            "fi" => 18,
            "fr" => 27,
            "ge" => 22,
            "de" => 22,
            "gi" => 23,
            "gr" => 27,
            "gl" => 18,
            "gt" => 28,
            "hu" => 28,
            "is" => 26,
            "ie" => 22,
            "il" => 23,
            "it" => 27,
            "jo" => 30,
            "kz" => 20,
            "kw" => 30,
            "lv" => 21,
            "lb" => 28,
            "li" => 21,
            "lt" => 20,
            "lu" => 20,
            "mk" => 19,
            "mt" => 31,
            "mr" => 27,
            "mu" => 30,
            "mc" => 27,
            "md" => 24,
            "me" => 22,
            "nl" => 18,
            "no" => 15,
            "pk" => 24,
            "ps" => 29,
            "pl" => 28,
            "pt" => 25,
            "qa" => 29,
            "ro" => 24,
            "sm" => 27,
            "sa" => 24,
            "rs" => 22,
            "sk" => 24,
            "si" => 19,
            "es" => 24,
            "se" => 24,
            "ch" => 21,
            "tn" => 24,
            "tr" => 26,
            "ae" => 23,
            "gb" => 22,
            "vg" => 24
        );
    }
