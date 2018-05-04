<?php
/*
 |  FoxCMS      Content Management Simplified <www.foxcms.org>
 |  @file       ./system/views/user/sidebar.php
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
<?php if(AuthUser::hasPermission("user_add")){ ?>
<p class="button">
    <a href="<?php echo get_url("user/add"); ?>">
        <img src="<?php echo get_theme_url("../../images/user.png"); ?>" align="middle" alt="ucer idon" />
        <?php _e("New User"); ?>
    </a>
</p>
<?php } ?>
<div class="box">
    <h2><?php _e("Where do the Avatars come from?"); ?></h2>
    <p>
        <?php _e("The avatars are automatically linked for those with a :gravatar (a free service) account.", array(
            ":gravatar" => '<a href="http://www.gravatar.com" target=":blank">Gravatar</a>'
        )); ?>
    </p>
</div>
