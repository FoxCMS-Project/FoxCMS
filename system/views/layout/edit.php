<?php
/*
 |  FoxCMS      Content Management Simplified <www.foxcms.org>
 |  @file       ./system/views/layout/edit.php
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
<h1><?php _e(ucfirst($action) . " Layout"); ?></h1>
<form method="post" action="<?php echo ($action == "edit")? get_url("layout/edit/{$layout->id}"): get_url("layout/add"); ?>">
    <input type="hidden" id="token" name="token" value="<?php echo $token; ?>" />

    <div class="form-area">
        <p class="title">
            <label for="layout_name"><?php _e("Name"); ?></label>
            <input type="text" id="layout_name" name="layout[name]" value="<?php echo $layout->name; ?>"
                class="textbox" maxlength="100" size="100" />
        </p>

        <table class="fieldset" cellpadding="0" cellspacing="0" border="0">
            <tr>
                <td><label fir="layout_content_type"><?php _e("Content-Type"); ?></label></td>
                <td class="field">
                    <input type="text" id="layout_content_type" name="layout[content_type]" value="<?php echo $layout->content_type; ?>"
                        class="textbox" maxlength="40" size="40" />
                </td>
            </tr>
        </table>

        <p class="content">
            <label for="layout_content"><?php _e("Body"); ?></label>
            <textarea id="layout_content" name="layout[content]" class="textarea" rows="20"
                style="width:100%"><?php echo htmlspecialchars($layout->content, ENT_HTML5); ?></textarea>
        </p>

        <?php if(isset($layout->updated_on) && $layout->updated_on > date("Y-m-d H:i:s")){ ?>
            <p style="clear:left;"><small>
                <?php $date = date("D, h M Y", strtotime($layout->updated_on)); ?>
                <?php echo __("Last updated by")." {$layout->updated_by} ".__("on")." {$date}"; ?>
            </small></p>
        <?php } ?>
    </div>

    <p class="buttons">
        <?php if(($action == "add" && AuthUser::hasPermission("layout_add")) || ($action == "edit" && AuthUser::hasPermission("layout_edit"))){ ?>
            <input type="submit" name="commit" value="<?php _e("Save"); ?>" class="button" />
            <input type="submit" name="continue" value="<?php _e("Save and Continue Editing"); ?>" class="button" />
            <?php _e("or"); ?>
        <?php } else {
            echo ($action == "add")? __("You don't have the Permission to add Layouts!"): __("You don't have the Permission to edit this Layout!");
        } ?>
        <a href="<?php get_url("layout"); ?>"><?php _e("Cancel"); ?></a>
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
    document.getElementById('layout_name').focus();
</script>
