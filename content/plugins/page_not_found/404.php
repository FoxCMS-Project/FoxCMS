<?php
/*
 |  FoxCMS Page not Found Plugin
 |  @file       ./page_not_found/404.php
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

?><!DOCTYPE HTML>
<html>
    <head>
        <meta charset="utf-8" />
        <title><?php _e("Error 404 - Page not Found"); ?> | <?php echo Setting::get("site-title"); ?></title>

        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:400,700" />
        <style type="text/css">
            *, *:before, *:after{
                box-sizing: border-box;
                -moz-box-sizing: border-box;
                -webkit-box-sizing: border-box;
            }
            html, body{
                margin: 0;
                padding: 0;
                display: block;
                font-size: 14px;
                line-height: 20px;
                font-family: "Roboto", "Open Sans", Calibri, Arial, sans-serif;
                background-color: rgba(250, 240, 240);
            }
            .error{
                width: 90%;
                max-width: 600px;
                margin: 35px auto;
                padding: 0;
                display: block;
            }
            .error header{
                color: #fff;
                padding: 10px 15px;
                font-size: 18px;
                font-weight: 700;
                line-height: 28px;
                background-color: rgb(230, 50, 50);
                border-radius: 5px 5px 0 0;
                -webkit-border-radius: 5px 5px 0 0;
            }
            .error article{
                color: #333;
                padding: 10px 15px;
                font-size: 14px;
                line-height: 22px;
                border-width: 1px;
                border-style: solid;
                border-color: rgba(210, 30, 30) rgba(0, 0, 0, 0.3) rgba(0, 0, 0, 0.3) rgba(0, 0, 0, 0.3);
                background-color: rgb(255, 255, 255);
                border-radius: 0 0 5px 5px;
                -webkit-border-radius: 0 0 5px 5px;
            }
            .error article p{
                margin: 5px 0;
                padding: 0 5px;
            }
            .error footer{
                padding: 0px 15px;
            }
            .error footer a{
                color: #fff;
                margin: 10px 0 0 0;
                padding: 5px 10px;
                display: inline-block;
                font-size: 14px;
                line-height: 20px;
                text-decoration: none;
                background-color: rgb(45, 65, 65);
                border-radius: 3px;
                -webkit-border-radius: 3px;
            }
            .error footer a:hover{
                background-color: rgb(65, 85, 85);
            }
        </style>
    </head>
    <body>
        <div class="error">
            <header>Error 404 - Page not Found</header>
            <article>
                <p>
                    <b>Sorry, but the requested Page couldn't be found!</b><br />
                    The link you followed is probably broken or the page has been removed by the Author.
                </p>
            </article>
            <footer>
                <p>
                    <a href="<?php echo PUBLIC_URL; ?>" title="<?php echo Setting::get("site-title"); ?>">Return to the Homepage</a>
                </p>
            </footer>
        </div>
    </body>
</html>
