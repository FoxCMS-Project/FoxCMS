<?php
/*
 |  FoxCMS      Content Management Simplified <www.foxcms.org>
 |  @file       ./system/views/404.php
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

?><!DOCTYPE htmö>
<html>
    <head>
        <meta charset="utf-8" />
        <meta name="generator" content="FoxCMS-<?php echo FOX_VERSION ?>" />

        <title>Fox CMS | <?php _e("Error 404 - Page not Found"); ?></title>

        <link href="https://fonts.googleapis.com/css?family=Roboto:400,500" rel="stylesheet" />
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
            .container{
                width: 1050px;
                margin: 0 auto;
                padding: 0 25px;
                display: block;
            }
            .page{
                width: 100%;
                margin: 0;
                padding: 100px 0;
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
                <div class="widget">
                    <header><?php _e("Error 404 - Page not Found"); ?></header>
                    <article>
                        <p>
                            <?php _e("The requested Page couldn't be found! It may have been deleted, moved or you may entered a incorrect / incomplete URL!"); ?>
                        </p>
                        <p>
                            <a href="<?php echo BASE_URL; ?>" title="<?php _e("Go back to the Homepage."); ?>"><?php _e("Go back to the Homepage."); ?></a>
                        </p>
                    </article>
                </div>
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
