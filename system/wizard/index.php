<?php
/*
 |  FoxCMS      Content Management Simplified <www.foxcms.org>
 |  @file       ./system/wizard/index.php
 |  @author     SamBrishes@pytesNET
 |  @version    0.8.4 [0.8.4] - Alpha
 |
 |  @license    GNU GPL v3
 |  @copyright  Copyright © 2015 - 2018 SamBrishes, pytesNET <pytes@gmx.net>
 |
 |  @history    Copyright © 2009 - 2015 Martijn van der Kleijn <martijn.niji@gmail.com>
 |              Copyright © 2008 - 2009 Philippe Archambault <philippe.archambault@gmail.com>
 */
    if(defined("FOX_CODE")){ die(); }

    /*
     |  DEFINE BASICs
     */
    define("START",         microtime(true));
    define("FOXCMS",        "wizard");

    define("DS",            DIRECTORY_SEPARATOR);
    define("BASE_DIR",      "../../");
    define("BASE_ROOT",     realpath(BASE_DIR) . DS);

    /*
     |  INIT
     */
    if(file_exists(BASE_ROOT . "config.php")){
        require_once(BASE_ROOT . "config.php");
    }
    require_once(BASE_ROOT . "defaults.php");
    require_once(BASE_ROOT . "init.php");
    require_once(SYSTEM_ROOT . "wizard" . DS . "wizard.php");
    require_once(SYSTEM_ROOT . "wizard" . DS . "wizard-install.php");
    require_once(SYSTEM_ROOT . "wizard" . DS . "wizard-upgrade.php");
    require_once(SYSTEM_ROOT . "wizard" . DS . "wizard-migrate.php");

    /*
     |  INIT WIZARD
     */
    global $wizard;
    $wizard = new Wizard();

?><!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8" />

		<title>Fox CMS Installer</title>

		<link type="text/css" rel="stylesheet" href="./css/font-awesome.min.css?ver=4.7.0" />
		<link type="text/css" rel="stylesheet" href="./css/wizard.css?ver=0.8.4" />

        <script type="text/javascript" src="./js/wizard.js?ver=0.8.4"></script>
	</head>
	<body>
		<div class="header">
			<div class="container">
				<div class="header-icon">
                    <img src="./images/foxcms-logo-white.png" />
                </div>
				<div class="header-title">
					<span class="title-main">Fox CMS v.<?php echo FOX_VERSION; ?> (<?php echo FOX_STATUS; ?>)</span>
					<span class="title-sub">Content Management Simplified</span>
				</div>
			</div>
		</div>

		<div class="content">
			<div class="content-header">
				<div class="container">
					<?php print($wizard->renderTabs()); ?>
				</div>
			</div>

            <?php foreach(array("errors", "success", "infos") AS $type){ ?>
                <?php if(!empty($wizard->{$type})){ ?>
        			<div class="content-status">
                        <div class="container">
                            <?php foreach($wizard->{$type} AS $data){ ?>
                                <div class="status status-<?php echo $type; ?>">
                                    <p><?php echo $data; ?></p>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                <?php } ?>
            <?php } ?>

			<div class="content-page">
    			<div class="container">
                    <?php
                        if($wizard->type() == "install"){
                            require_once(SYSTEM_ROOT . "wizard" . DS . "index-install.php");
                        } else if($wizard->type() == "upgrade"){
                            require_once(SYSTEM_ROOT . "wizard" . DS . "index-upgrade.php");
                        } else if($wizard->type() == "migrate"){
                            require_once(SYSTEM_ROOT . "wizard" . DS . "index-migrate.php");
                        } else {
                            ?>
                                <div class="content-panels">
                                    <div class="content-left">
                                        <div class="widget">
                                            <header>Install Fox CMS</header>
                                            <article>
                                                <?php $instance = new WizardInstall(); ?>
                                                <table style="margin-top:0;">
                                                    <?php foreach($instance->checkRequirements() AS $type => $data){ ?>
                                                        <tr>
                                                            <th><?php echo $data["title"]; ?></th>
                                                            <th width="1%" class="text-center">
                                                                <?php if($data["status"] == "error"){ ?>
                                                                    <span class="fa fa-times text-red"></span>
                                                                <?php } else { ?>
                                                                    <span class="fa fa-check text-green"></span>
                                                                <?php } ?>
                                                            </th>
                                                            <td><?php echo $data["string"]; ?></td>
                                                        </tr>
                                                    <?php } ?>
                                                </table>
                                                <div class="widget-icon"><span class="fa fa-plus"></span></div>
                                            </article>
                                            <footer>
                                                <?php if($instance->checkRequirements(true)){ ?>
                                                    <a href="?wizard=install&step=1" class="button">Start Installation</a>
                                                <?php } else { ?>
                                                    <a href="#" class="button disabled">You don't met all Requirements</a>
                                                <?php } ?>
                                            </footer>
                                        </div>
                                    </div>

                                    <div class="content-right">
                                        <div class="widget">
                                            <header>Migrate from Wolf CMS</header>
                                            <article>
                                                <?php $instance = new WizardMigrate(); ?>
                                                <table style="margin-top:0;">
                                                    <?php foreach($instance->checkRequirements() AS $type => $data){ ?>
                                                        <tr>
                                                            <th><?php echo $data["title"]; ?></th>
                                                            <th width="1%" class="text-center">
                                                                <?php if($data["status"] == "error"){ ?>
                                                                    <span class="fa fa-times text-red"></span>
                                                                <?php } else { ?>
                                                                    <span class="fa fa-check text-green"></span>
                                                                <?php } ?>
                                                            </th>
                                                            <td><?php echo $data["string"]; ?></td>
                                                        </tr>
                                                    <?php } ?>
                                                </table>
                                                <div class="widget-icon"><span class="fa fa-code-fork"></span></div>
                                            </article>
                                            <footer>
                                                <?php if($instance->checkRequirements(true)){ ?>
                                                    <a href="?wizard=migrate&step=1" class="button">Start Migration</a>
                                                <?php } else { ?>
                                                    <a href="#" class="button disabled">You don't met all Requirements</a>
                                                <?php } ?>
                                            </footer>
                                        </div>
                                    </div>
                                </div>
                            <?php
                        }
                    ?>
                </div>
            </div>

    		<div class="footer">
    			<div class="container">
    				<div class="footer-left">
    					Copyright &copy; 2015 - <?php echo date("Y"); ?> Fox CMS, pytesNET<br />
    					The Fox CMS is published under the GNU GPLv3!
    				</div>

    				<div class="footer-right">
    					The Fox CMS is a fork of the Wolf CMS.<br />
    					The Fox CMS is developed by pytesNET!
    				</div>
    			</div>
    		</div>
		</div>
    </body>
</html>
