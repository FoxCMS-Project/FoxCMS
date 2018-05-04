<?php
/*
 |  FoxCMS      Content Management Simplified <www.foxcms.org>
 |  @file       ./system/views/snippet/sidebar.php
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
<?php if(AuthUser::hasPermission("snippet_add")){ ?>
<p class="button">
    <a href="<?php echo get_url("snippet/add"); ?>">
        <img src="<?php echo get_theme_url("../../images/snippet.png"); ?>" align="middle" alt="Snippet Icon" />
        <?php _e("New Snippet"); ?>
    </a>
</p>
<?php } ?>
<div class="box">
    <h2><?php _e("What is a Snippet?"); ?></h2>
    <p>
        <?php _e("Snippets are generally small pieces of content which are included in other pages or layouts."); ?>
    </p>
</div>
<div class="box">
    <h2><?php _e("How to use Snippets?"); ?></h2>
    <p>
        <?php _e("Just replace <b>snippet</b> by the snippet name you want to include."); ?>
    </p>
    <p>
        <code>&lt;?php $this->includeSnippet('snippet'); ?&gt;</code>
    </p>
</div>
