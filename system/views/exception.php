<?php
/*
 |  FoxCMS      Content Management Simplified <www.foxcms.org>
 |  @file       ./system/views/exception.php
 |  @author     SamBrishes@pytesNET
 |  @version    0.8.4 [0.8.4] - Alpha
 |
 |  @license    GNU GPL v3
 |  @copyright  Copyright © 2015 - 2018 SamBrishes, pytesNET <pytes@gmx.net>
 |
 |  @history    Copyright © 2009 - 2015 Martijn van der Kleijn <martijn.niji@gmail.com>
 |              Copyright © 2008 - 2009 Philippe Archambault <philippe.archambault@gmail.com>
 */
    if(!defined("FOXCMS") || !defined("DEBUG_MODE")){ die(); }
    if(DEBUG_MODE !== true || !isset($error)){ die(); }

    /*
     |  DEBUG TABLE
     |  @since  0.8.4
     */
    function debug_table($array, $label, $key_label = "Variable", $value_label = "Value"){
        ?>
            <table>
                <thead>
                    <tr>
                        <th colspan="2"><?php echo $label; ?></th>
                    </tr>
                    <tr>
                        <td><?php echo $key_label; ?></td>
                        <td><?php echo $value_label; ?></td>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        foreach($array AS $key => $arg){
                            if(is_null($arg)){
                                $args[] = "NULL";
                            } else if(is_array($arg)){
                                $args[] = "array(". sizeof($arg) .")";
                            } else if(is_object($arg)){
                                $args[] = get_class($arg) . "Object";
                            } else if(is_bool($arg)){
                                $args[] = ($arg)? "true": "false";
                            } else if(is_integer($arg)){
                                $args[] = "(int) $arg";
                            } else if(is_float($arg)){
                                $args[] = "(float) $arg";
                            } else {
                                $arg = htmlspecialchars(substr($arg, 0, 112));
                                $arg = (strlen($arg) >= 112)? "$arg [...]": $arg;
                                $args[] = "(string) '{$arg}'";
                            }
                            ?>
                                <tr>
                                    <td width="30%"><code><?php echo $key; ?></code></td>
                                    <td width="70%"><code><?php echo $arg; ?></code></td>
                                </tr>
                            <?php
                        }
                    ?>
                </tbody>
            </table>
        <?php
    }

?><!DOCTYPE htmö>
<html>
    <head>
        <meta charset="utf-8" />
        <meta name="generator" content="FoxCMS-<?php echo FOX_VERSION ?>" />

        <title>Fox CMS | <?php _e("Framework Exception"); ?></title>

        <link href="https://fonts.googleapis.com/css?family=Roboto:400,500" rel="stylesheet" />
        <link href="https://fonts.googleapis.com/css?family=Inconsolata:400,700" rel="stylesheet" />
        <style type="text/css">
            *, *:before, *:after{
                box-sizing: border-box;
                -moz-box-sizing: border-box;
                -webkit-box-sizing: border-box;
            }
            html, body{
                color: #333;
                margin: 0;
                padding: 0;
                display: block;
                font-size: 14px;
                font-family: "Roboto", "Segoe UI", "Open Sans", Calibri, Arial, sans-serif;
                line-height: 22px;
                background-color: #efefef;
            }
            a{
                color: #509696;
                text-decoration: none;
            }
            a:hover{
                color: #64C85A;
            }
            h1, h2, h3, h4, h5, h6{
                margin: 15px 0;
                padding: 0;
                display: block;
                font-weight: normal;
            }
            h1{
                margin: 0 0 30px 0;
                padding: 10px 15px;
                font-size: 32px;
                line-height: 40px;
                border-bottom: 1px solid #d0d0d0;
            }
            h2{
                font-size: 28px;
                line-height: 36px;
            }
            h3{
                font-size: 24px;
                line-height: 32px;
            }
            h4{
                font-size: 20px;
                line-height: 28px;
            }
            h5{
                font-size: 16px;
                line-height: 24px;
            }
            h6{
                font-size: 16px;
                font-weight: bold;
                line-height: 24px;
            }
            p{
                margin: 30px 0;
                padding: 0;
                display: block;
            }
            pre, code{
                font-size: 14px;
                font-family: "Inconsolata", "Lucida Console", Monaco, monospace;
                line-height: 22px;
            }
            pre{
                margin: 15px 0;
                padding: 15px 30px;
                border-left: 4px solid #aaa;
                background-color: #f8f8f8;
                tab-size: 4;
                -o-tab-size: 4;
                -ms-tab-size: 4;
                -moz-tab-size: 4;
                -webkit-tab-size: 4;
            }
            table{
                width: 90%;
                margin: 20px auto;
                overflow: hidden;
                border-spacing: 0;
                border-collapse: separate;
                border: 1px solid #2a2520;
                border-radius: 3px;
                -webkit-border-radius: 3px;
            }
            table tr th{
                color: #fff;
                padding: 10px 15px;
                text-align: center;
                font-weight: normal;
                font-family: Verdana, Arial, sans-serif;
                background-color: #2a2520;
            }
            table tr td{
                padding: 10px 15px;
                font-family: Verdana;
                font-weight: lighter;
                vertical-align: top;
                border-bottom: 1px solid #d0d0d0;
            }
            table thead tr td{
                border-bottom-color: #2a2520;
            }
            table tbody tr:nth-child(odd) td{
                background: #ffffff;
            }
            table tbody tr:nth-child(even) td{
                background: #e8e8e8;
            }
            table tbody tr:last-child td{
                border-bottom: 0;
            }
            .container{
                width: 100%;
                margin: 0 auto;
                padding: 0 50px;
                display: block;
            }
            .page{
                width: 100%;
                margin: 0;
                padding: 30px 0;
                display: block;
                background-color: #fff;
            }
            .page .widget{
                width: 500px;
                margin: 0 auto;
                padding: 0;
                display: block;
                border: 1px solid #509696;
                border-radius: 5px;
                -webkit-border-radius: 5px;
                box-shadow: 0 1px 1px 1px rgba(80, 150, 150, 0.125);
            }
            .page .widget header{
                color: #ffffff;
                margin: 0;
                padding: 15px 20px;
                display: block;
                font-size: 18px;
                font-weight: 500;
                text-transform: uppercase;
                background-color: #509696;
            }
            .page .widget article{
                margin: 0;
                padding: 15px 20px;
                display: block;
            }
            .footer{
                width: 100%;
                margin: 0;
                padding: 50px 0;
                display: block;
                background-color: #efefef;
                border-top: 1px solid #d0d0d0;
                box-shadow: inset 0 1px 1px #dfdfdf;
                -webkit-box-shadow: inset 0 1px 1px #dfdfdf;
            }
            .footer .container{
                width: 1100px;
                display: table;
                border-spacing: 0;
                border-collapse: collapse;
            }
            .footer a{
                color: #777777;
                text-decoration: underline;
            }
            .footer a:hover{
                color: #64C85A;
            }
            .footer .footer-left,
            .footer .footer-right{
                color: #999;
                width: 50%;
                margin: 0;
                padding: 0;
                display: table-cell;
                font-size: 12px;
                line-height: 24px;
                vertical-align: top;
            }
            .footer .footer-right{
                text-align: right;
            }
        </style>
    </head>
    <body>
        <div class="page">
            <div class="container">
                <h1>Fox CMS - Exception Handler 0.2.0</h1>
                <ul>
                    <li>Fox Version: <?php echo FOX_VERSION; ?></li>
                    <li>Fox Status: <?php echo FOX_STATUS; ?></li>
                    <li>Fox Code: <?php echo FOX_CODE; ?></li>
                </ul>

                <h2>Error Message</h2>
                <p>
                    <?php
                        if(is_string($error)){
                            echo $error;
                        } else if(method_exists($error, "getMessage")){
                            echo $error->getMessage();
                        } else {
                            var_dump($error);
                        }
                    ?>
                </p>

                <h2>Error Location</h2>
                <p>
                    <?php
                        $file = "Unknown";
                        if(method_exists($error, "getFile")){
                            $file = $error->getFile();
                            $file = "." . DS . str_replace(BASE_ROOT, "", $file);
                        }

                        $line = "Unknown";
                        if(method_exists($error, "getLine")){
                            $line = $error->getLine();
                        }
                    ?>
                    The Exception thrown on Line <code><?php echo $line; ?></code> in File <code><?php echo $file; ?></code>.
                </p>

                <?php if(method_exists($error, "getTrace") && count($error->getTrace()) > 1){ ?>
                    <h2>Strack Trace</h2>
                    <pre><code><?php
                        $level = 0;
                        foreach(array_reverse($error->getTrace()) AS $trace){
                            $args = array();
                            if(!empty($trace["args"])){
                                foreach($trace["args"] AS $arg){
                                    if(is_null($arg)){
                                        $args[] = "NULL";
                                    } else if(is_array($arg)){
                                        $args[] = "array(". sizeof($arg) .")";
                                    } else if(is_object($arg)){
                                        $args[] = get_class($arg) . "Object";
                                    } else if(is_bool($arg)){
                                        $args[] = ($arg)? "true": "false";
                                    } else if(is_integer($arg)){
                                        $args[] = "(int) $arg";
                                    } else if(is_float($arg)){
                                        $args[] = "(float) $arg";
                                    } else {
                                        $arg = htmlspecialchars(substr($arg, 0, 112));
                                        $arg = (strlen($arg) >= 112)? "$arg...": $arg;
                                        $args[] = "(string) '{$arg}'";
                                    }
                                }
                            }
                            $file = "Unknown";
                            if(isset($trace["file"])){
                                $file = "." . DS . str_replace(BASE_ROOT, "", $trace["file"]);
                            }
                            $line = "Unknown";
                            if(isset($trace["line"])){
                                $line = $trace["line"];
                            }
                            $function = "";
                            if(isset($trace["class"])){
                                $function .= $trace["class"] . " &rarr; ";
                            }
                            if(isset($trace["function"])){
                                $function .= $trace["function"];
                            }

                            echo ($level > 0)? "\n\n": "";
                            echo ""   . str_repeat("\t", $level  ) . "<b>{$function}</b> (".implode(", ", $args).")";
                            echo "\n" . str_repeat("\t", $level  ) . " &#x21B3; on Line <b>{$line}</b>";
                            echo "\n" . str_repeat("\t", $level++) . " &#x21B3; in File <b>{$file}</b>";
                        }
                    ?></code></pre>
                <?php } ?>

                <?php if(class_exists("Dispatcher", false) && !empty(Dispatcher::getStatus())){ ?>
                    <h2>Dispatcher Status</h2>
                    <?php
                        $dispatcher_status["request method"] = request_method();
                        debug_table($dispatcher_status, "Dispatcher Status");
                    ?>
                <?php } ?>

                <h2>Globals</h2>
                <?php
                    $globals = array(
                        "_GET"  => $_GET, "_POST" => $_POST, "_COOKIE" => $_COOKIE, "_SERVER" => $_SERVER
                    );
                    foreach($globals AS $type => $global){
                        echo "<h3>{$type}</h3>";
                        if(!empty($global)){
                            debug_table($global, substr($type, 1));
                        } else {
                            echo "<p><i>Empty</i></p>";
                        }
                    }
                ?>
            </div>
        </div>

        <div class="footer">
            <div class="container">
                <div class="footer-left">
                    Copyright &copy; <?php echo date("Y"); ?> <?php echo Setting::get("site-title"); ?>
                </div>
                <div class="footer-right">
                    Powered by the <a href="https://www.foxcms.org" target="_blank">Fox CMS v.<?php echo FOX_VERSION; ?></a>
                </div>
            </div>
        </div>
    </body>
</html>
