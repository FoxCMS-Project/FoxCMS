<?php
/*
 |  FoxCMS      Content Management Simplified <www.foxcms.org>
 |  @file       ./system/views/user/edit.php
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
<h1><?php _e(ucfirst($action) . " User"); ?></h1>

<form method="post" action="<?php echo ($action == "edit")? get_url("user/edit/{$user->id}"): get_url("user/add"); ?>">
    <input type="hidden" id="token" name="token" value="<?php echo $token; ?>" />

    <table class="fieldset" cellpadding="0" cellspacing="0" border="0">
        <tr>
            <td class="label"><label for="username"><?php _e("Username"); ?></label></td>
            <td class="field">
                <input type="text" id="username" name="user[username]" value="<?php echo $user->username; ?>"
                    class="textbox" size="40" maxlength="40" <?php readonly($action == "edit") ?> />
            </td>
            <td class="help"></td>
        </tr>
        <tr>
            <td class="label"><label for="name"><?php _e("Name"); ?></label></td>
            <td class="field">
                <input type="text" id="name" name="user[name]" value="<?php echo $user->name; ?>"
                    class="textbox" size="100" maxlength="100" />
            </td>
            <td class="help"></td>
        </tr>
        <tr>
            <td class="label"><label for="email"><?php _e("eMail Address"); ?></label></td>
            <td class="field">
                <input type="text" id="email" name="user[email]" value="<?php echo $user->email; ?>"
                    class="textbox" size="255" maxlength="255" />
            </td>
            <td class="help"></td>
        </tr>
        <tr>
            <td class="label"><label for="password"><?php _e("Password"); ?></label></td>
            <td class="field">
                <input type="password" id="password" name="user[password]" value="" class="textbox" />
            </td>
            <td class="help"></td>
        </tr>
        <tr>
            <td class="label"><label for="confirm"><?php _e("Confirm Password"); ?></label></td>
            <td class="field">
                <input type="password" id="confirm" name="user[confirm]" value="" class="textbox" />
            </td>
            <td class="help"></td>
        </tr>

        <?php if(AuthUser::hasPermission("user_edit")){ ?>
            <tr>
                <td class="label"><label for="roles"><?php _e("Roles"); ?></label></td>
                <td class="field">
                    <?php
                        $select = is_a($user, "User")? $user->roles(): array();
                        foreach($roles AS $role){
                            ?>
                                <input type="checkbox" id="user-role-<?php echo $role->id; ?>" name="user_role[<?php echo $role->name; ?>]" value="<?php echo $role->id; ?>"
                                    <?php checked($role->name, $select); ?>/><label for="user-role-<?php echo $role->id; ?>"><?php echo __(ucwords($role->name)); ?></label>
                            <?php
                        }
                    ?>
                </td>
                <td class="help"></td>
            </tr>
        <?php } ?>

        <tr>
            <td class="label"><label for="language"><?php _e("Language"); ?></label></td>
            <td class="field">
                <select id="language" name="user[language]" class="select">
                    <?php foreach(I18n::getLanguages() AS $code => $label){ ?>
                        <option value="<?php echo $code; ?>" <?php selected($code, $user->language); ?>><?php echo $label; ?></option>
                    <?php } ?>
                </select>
            </td>
            <td class="help"></td>
        </tr>
        <tr>
            <td class="label"><label for=""></label></td>
            <td class="field"></td>
            <td class="help"></td>
        </tr>
    </table>

    <?php Event::apply("user_edit_view_after_details", $user); ?>

    <p class="buttons">
        <input type="submit" name="commit" value="<?php _e("Save"); ?>" class="button" />
        <?php _e("or"); ?>
        <a href="<?php echo get_url("user"); ?>"><?php _e("Cancel"); ?></a>
    </p>
</form>

<!-- Wolf CMS Script -->
<script type="text/javascript">
    function setConfirmUnload(on, msg) {
        window.onbeforeunload = (on) ? unloadMessage : null;
        return true;
    }
    function unloadMessage() {
        return '<?php echo __('You have modified this page.  If you navigate away from this page without first saving your data, the changes will be lost.'); ?>';
    }
    $(document).ready(function() {
        // Prevent accidentally navigating away
        $(':input').bind('change', function() { setConfirmUnload(true); });
        $('form').submit(function() { setConfirmUnload(false); return true; });
    });
    Field.activate('user_name');
</script>
