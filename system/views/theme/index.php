<?php
/*
 |  FoxCMS      Content Management Simplified <www.foxcms.org>
 |  @file       ./system/views/theme/index.php
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
?>
<h1><?php _e("Themes"); ?></h1>
<table class="list-table list-themes">
    <?php foreach(array("thead", "tfoot") AS $tag){ ?>
        <<?php echo $tag; ?>>
            <tr>
                <th class="th-theme-title"><?php _e("Theme Title"); ?></th>
                <th class="th-theme-type"><?php _e("Type"); ?></th>
            </tr>
        </<?php echo $tag; ?>>
    <?php } ?>

    <tbody>
        <?php foreach($themes AS $theme){ ?>
            <?php $token = SecureToken::generateToken("theme/delete/" . $theme["id"]); ?>
            <tr>
                <td class="td-theme-title">
                    <a href="<?php echo get_url("/theme/edit/" . $theme["id"]); ?>" class="theme-edit-link"><?php echo $theme["title"]; ?></a>
                    <div class="list-actiona">
                        <a href="<?php echo get_url("theme/edit/" . $theme["id"]); ?>"><?php _e("Edit"); ?></a> |
                        <a href="<?php echo get_url("theme/delete/" . $theme["id"]) . "?token={$token}"; ?>"><?php _e("Delete"); ?></a>
                    </div>
                </td>
                <td class="td-theme-type">
                    <?php echo $theme["type"]; ?>
                </td>
            </tr>
        <?php } ?>
    </tbody>
</table>
