<?php
/*
 |  FoxCMS      Content Management Simplified <www.foxcms.org>
 |  @file       ./system/views/theme/sidebar.php
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
<?php if(AuthUser::hasPermission("theme_add")){ ?>
<p class="button">
    <a href="<?php echo get_url("theme/add"); ?>"> <?php _e("New Theme"); ?></a>
</p>
<?php } ?>
