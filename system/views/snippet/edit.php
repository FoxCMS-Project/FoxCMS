<?php
/*
 |  FoxCMS      Content Management Simplified <www.foxcms.org>
 |  @file       ./system/views/snippet/edit.php
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
<h1><?php _e(ucfirst($action) . " Snippet"); ?></h1>
<form method="post" action="<?php echo ($action == "edit")? get_url("snippet/edit/{$snippet->id}"): get_url("snippet/add"); ?>">
    <input type="hidden" id="token" name="token" value="<?php echo $token; ?>" />

    <div class="form-area">
        <h3><?php _e("Name"); ?></h3>
        <div id="meta-pages" class="pages">
            <p class="title">
                <input type="text" id="snippet_name" name="snippet[name]" value="<?php echo $snippet->name; ?>"
                    class="textbox" maxlength="100" size="255" />
            </p>
        </div>

        <h3><?php _e("Body"); ?></h3>
        <div id="pages" class="pages">
            <div class="page">
                <p>
                    <label for="snippet_filter_id"><?php _e("Filter"); ?></label>
                    <select id="snippet_filter_id" name="snippet[filter_id]" class="filter-selector">
                        <option value="" <?php selected("", $snippet->filter_id); ?>><?php _e("No Filter"); ?></option>
                        <?php foreach($filters AS $filter){ ?>
                            <?php
                                if(!isset(Filter::$filters[$filter]) || !Plugin::isEnabled($filter)){
                                    continue;
                                }
                            ?>
                            <option value="<?php echo $filter; ?>"
                                <?php selected($filter, $snippet->filter_id); ?>><?php echo Inflector::humanize($filter); ?></option>
                        <?php } ?>
                    </select>
                </p>

                <textarea id="snippet_content" name="snippet[content]" class="textarea" cols="40" rows="20"
                    style="width:100%"><?php echo htmlspecialchars($snippet->content, ENT_HTML5); ?></textarea>

                <?php if(isset($snippet->updated_on) && $snippet->updated_on > date("Y-m-d H:i:s")){ ?>
                    <p style="clear:left;"><small>
                        <?php $date = date("D, h M Y", strtotime($snippet->updated_on)); ?>
                        <?php echo __("Last updated by")." {$snippet->updated_by} ".__("on")." {$date}"; ?>
                    </small></p>
                <?php } ?>
            </div>
        </div>
    </div>

    <p class="buttons">
        <?php if(($action == "add" && AuthUser::hasPermission("snippet_add")) || ($action == "edit" && AuthUser::hasPermission("snippet_edit"))){ ?>
            <input type="submit" name="commit" value="<?php _e("Save"); ?>" class="button" />
            <input type="submit" name="continue" value="<?php _e("Save and Continue Editing"); ?>" class="button" />
            <?php _e("or"); ?>
        <?php } else {
            echo ($action == "add")? __("You don't have the Permission to add Snippets!"): __("You don't have the Permission to edit this Snippet!");
        } ?>
        <a href="<?php echo get_url("snippet"); ?>"><?php _e("Cancel"); ?></a>
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
  document.getElementById('snippet_name').focus();
</script>
