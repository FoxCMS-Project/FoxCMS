<?php
/*
 |  FoxCMS      Content Management Simplified <www.foxcms.org>
 |  @file       ./system/views/user/index.php
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
    use_helper("Gravatar");
?>
<h1><?php _e("Users"); ?></h1>

<table id="users" class="index" cellpadding="0" cellspacing="0" border="0">
    <thead>
        <tr>
            <th class="th-user"><?php _e("User / Name"); ?></th>
            <th class="th-email"><?php _e("eMail"); ?></th>
            <th class="th-roles"><?php _e("Roles"); ?></th>
            <th class="th-modify"><?php _e("Modify"); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($users AS $user){ ?>
            <tr class="node <?php echo odd_even(); ?>">
                <td class="td-user user">
                    <?php echo Gravatar::img($user->email, array(
                        "alt"   => "user icon",
                        "align" => "middle",
                    ), "32", get_theme_url("../../images/user.png"). "g", in_array(HTTPS_MODE, array("always", "backend"))); ?>
                    <a href="<?php echo get_url("user/edit/{$user->id}"); ?>" title="<?php _e("Edit this User"); ?>"><?php echo $user->name; ?></a>
                    <small><?php echo $user->username; ?></small>
                </td>
                <td class="td-email"><?php echo $user->email; ?></td>
                <td class="td-roles"><?php echo implode(", ", $user->roles()); ?></td>
                <td class="td-modify">
                    <?php if($user->id > 1 && AuthUser::hasPermission("user_delete")){ ?>
                        <?php $token = SecureToken::generateToken("user/delete/{$user->id}"); ?>
                        <a href="<?php echo get_url("user/delete/{$user->id}"); ?>?token=<?php echo $token; ?>"
                            onclick="return confirm('<?php echo __("Are you sure you with to delete")." ".$user->username."?"; ?>')">
                            <img src="<?php echo get_theme_url("../../images/icon-remove.gif"); ?>" alt="delete user icon" title="<?php _e("Delete User"); ?>" />
                        </a>
                    <?php } ?>
                </td>
            </tr>
        <?php } ?>
    </tbody>
</table>
