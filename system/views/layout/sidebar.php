<?php
/*
 |  FoxCMS      Content Management Simplified <www.foxcms.org>
 |  @file       ./system/views/layout/sidebar.php
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
    if(Dispatcher::getAction() != "index"){
        return;
    }
?>
<?php if(AuthUser::hasPermission("layout_add")){ ?>
<p class="button">
    <a href="<?php echo get_url("layout/add"); ?>">
        <img src="<?php echo get_theme_url("../../images/layout.png"); ?>" align="middle" alt="Layout Icon" />
        <?php _e("New Layout"); ?>
    </a>
</p>
<?php } ?>
<div class="box">
    <h2><?php _e("What is a Layout?"); ?></h2>
    <p>
        <?php _e("Use layouts to apply a visual look to a Web page. Layouts can contain special tags to include page content and other elements such as the header or footer. Click on a layout name below to edit it or click <strong>Remove</strong> to delete it."); ?>
    </p>
</div>
