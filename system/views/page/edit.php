<?php
/*
 |  FoxCMS      Content Management Simplified <www.foxcms.org>
 |  @file       ./system/views/page/edit.php
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

    $temp = array(
        "page"  => Flash::get("page"),
        "parts" => Flash::get("page_parts"),
        "tags"  => Flash::get("page_tag")
    );
    $page  = !empty($temp["page"])?  $temp["page"]:  $page;
    $parts = !empty($temp["parts"])? $temp["parts"]: $parts;
    $tags  = !empty($temp["tags"])?  $temp["tags"]:  $tags;

    if($action == "edit"){
        ?>
            <span style="float:right;">
                <a href="<?php echo $page->url(); ?>" id="site-view-page" target="_blank"><?php _e("View this Page"); ?></a>
            </span>
        <?php
    }
?>
<h1><?php _e(ucfirst($action) . " Page"); ?></h1>

<form id="page_edit_form" method="post" action="<?php echo ($action == "edit")? get_url("page/edit/{$page->id}"): get_url("page/add"); ?>">
    <input type="hidden" id="token" name="token" value="<?php echo $token; ?>" />
    <input type="hidden" id="parent_id" name="page[parent_id]" value="<?php echo $page->parent_id; ?>" />

    <div class="form-area">
        <div id="metainfo-tabs" class="content tabs">
            <ul class="tabNavigation">
                <li class="tab"><a href="#pagetitle"><?php _e("Page Title"); ?></a></li>
                <li class="tab"><a href="#metadata"><?php _e("Metadata"); ?></a></li>
                <li class="tab"><a href="#settings"><?php _e("Settings"); ?></a></li>
                <?php Event::apply("view_page_edit_tab_links", $page, $parts, $action); ?>
            </ul>
        </div>
        <div id="metainfo-content" class="pages">
            <div id="pagetitle" class="page">
                <div id="div-title" class="title" title="<?php _e("Page title"); ?>">
                    <input type="text" id="page_title" name="page[title]" value="<?php echo $page->title; ?>"
                        class="textbox" maxlength="255" size="255" />
                </div>
            </div>

            <div id="metadata" class="page">
                <div id="div-metadata" title="<?php _e("Metadata"); ?>">
                    <table cellpadding="0" cellspacing="0" border="0">
                        <?php if($page->parent_id !== 0){ ?>
                        <tr>
                            <td class="label"><label for="slug"><?php _e("Slug"); ?></label></td>
                            <td class="field">
                                <input type="text" id="slug" name="page[slug]" value="<?php echo $page->slug; ?>" class="textbox" maxlength="100" size="100" />
                            </td>
                        </tr>
                        <?php } ?>
                        <tr>
                            <td class="label"><label for="breadcrumb"><?php _e("Breadcrumb"); ?></label></td>
                            <td class="field">
                                <input type="text" id="breadcrumb" name="page[breadcrumb]" value="<?php echo htmlentities($page->breadcrumb, ENT_COMPAT, DEFAULT_CHARSET); ?>"
                                    class="textbox" maxlength="100" size="100" />
                            </td>
                        </tr>
                        <tr>
                            <td class="label"><label for="keywords"><?php _e("Keywords"); ?></label></td>
                            <td class="field">
                                <input type="text" id="keywords" name="page[keywords]" value="<?php echo $page->keywords; ?>" class="textbox" maxlength="100" size="100" />
                            </td>
                        </tr>
                        <tr>
                            <td class="label"><label for="description"><?php _e("Description"); ?></label></td>
                            <td class="field">
                                <input type="text" id="description" name="page[description]" value="<?php echo $page->description; ?>" class="textbox" maxlength="100" size="100" />
                            </td>
                        </tr>
                        <tr>
                            <td class="label"><label for="tags"><?php _e("Tags"); ?></label></td>
                            <td class="field">
                                <input type="text" id="tags" name="page_tag[tags]" value="<?php echo implode(", ", $page->tags()); ?>" class="textbox" maxlength="100" size="100" />
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <div id="settings" class="page">
                <div id="dic-settings" title="<?php _e("Settings"); ?>">
                    <table cellpadding="0" cellspacing="0" border="0">
                        <?php if($page->parent_id !== 0){ ?>
                        <tr>
                            <td class="label"><label for="page-id"><?php _e("Page ID"); ?></label></td>
                            <td class="field">
                                <input type="text" id="page-id" name="unused" value="<?php echo $page->id; ?>" maxlength="100" size="100" disabled="disabled" />
                            </td>
                        </tr>
                        <?php } ?>
                        <tr>
                            <td class="label"><label for="layout-id"><?php _e("Layout"); ?></label></td>
                            <td class="field">
                                <select id="layout-id" name="page[layout_id]">
                                    <option value="0" <?php selected($page->layout_id == ""); ?>><?php _e("Inherit"); ?></option>
                                    <?php foreach($layouts AS $layout){ ?>
                                        <option value="<?php echo $layout->id; ?>" <?php selected($page->layout_id, $layout->id); ?>><?php echo $layout->name; ?></option>
                                    <?php } ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td class="label"><label for="behavior-id"><?php _e("Page Type"); ?></label></td>
                            <td class="field">
                                <select id="behavior-id" name="page[behavior_id]">
                                    <option value="0" <?php selected($page->behavior_id == ""); ?>><?php _e("None"); ?></option>
                                    <?php foreach($behaviors AS $behavior){ ?>
                                        <option value="<?php echo $behavior; ?>" <?php selected($page->behavior_id, $behavior); ?>><?php echo Inflector::humanize($behavior); ?></option>
                                    <?php } ?>
                                </select>
                            </td>
                        </tr>
                        <?php if(isset($page->created_on)){ ?>
                        <tr>
                            <td class="label"><label for="created-on"><?php _e("Created on"); ?></label></td>
                            <td class="field">
                                <input type="text" id="created-on" name="page[created_on]" value="<?php echo substr($page->created_on, 0, 10); ?>" />
                                <img src="<?php echo get_theme_path("../../images/icon_cal.gif"); ?>" alt="Calendar" class="datepicker" onlick="displayDatePicker('page[created_on]');" />
                                <input type="text" id="created-on-time" name="page[created_on_time]" value="<?php echo substr($page->created_on, 11); ?>" />
                            </td>
                        </tr>
                        <?php } ?>
                        <?php if(isset($page->published_on)){ ?>
                        <tr>
                            <td class="label"><label for="published-on"><?php _e("Published on"); ?></label></td>
                            <td class="field">
                                <input type="text" id="published-on" name="page[published_on]" value="<?php echo substr($page->published_on, 0, 10); ?>" />
                                <img src="<?php echo get_theme_path("../../images/icon_cal.gif"); ?>" alt="Calendar" class="datepicker" onlick="displayDatePicker('page[published_on]');" />
                                <input type="text" id="published-on-time" name="page[published_on_time]" value="<?php echo substr($page->published_on, 11); ?>" />
                            </td>
                        </tr>
                        <?php } ?>
                        <?php if(isset($page->valid_until)){ ?>
                        <tr>
                            <td class="label"><label for="vaild-until"><?php _e("Valid Until"); ?></label></td>
                            <td class="field">
                                <input type="text" id="valid-until" name="page[valid_until]" value="<?php echo substr($page->valid_until, 0, 10); ?>" />
                                <img src="<?php echo get_theme_path("../../images/icon_cal.gif"); ?>" alt="Calendar" class="datepicker" onlick="displayDatePicker('page[valid_until]');" />
                                <input type="text" id="valid-until-time" name="page[valid_until_time]" value="<?php echo substr($page->valid_until, 11); ?>" />
                            </td>
                        </tr>
                        <?php } ?>
                        <?php if(AuthUser::hasPermission("page_edit")){ ?>
                        <tr>
                            <td class="label"><label for="needs-loin"><?php _e("Needs Login"); ?></label></td>
                            <td class="field">
                                <select id="needs-login" name="page[needs_login]">
                                    <option value="2" <?php selected($page->needs_login, "2"); ?>><?php _e("Inherit"); ?></option>
                                    <option value="1" <?php selected($page->needs_login, "1"); ?>><?php _e("Required"); ?></option>
                                    <option value="0" <?php selected($page->needs_login, "0"); ?>><?php _e("Not Required"); ?></option>
                                </select>
                                <input type="checkbox" id="is-protected" name="page[is_protected]" value="1" class="checkbox"
                                    <?php checked($page->is_protected == 1); ?> <?php disabled(!AuthUser::hasPermission("admin_edit")); ?> />
                                <label for="is-protected"><?php _e("Protect Page"); ?></label>
                            </td>
                        </tr>
                        <?php } ?>
                    </table>
                </div>
            </div>
            <?php Event::apply("view_page_edit_tabs", $page, $parts, $action); ?>
        </div>

        <div id="part-tabs" class="content-tabs">
            <div id="tab-toolbar" class="tab_toolbar">
                <a href="#" id="add-part" title="<?php _e("Add Tab"); ?>"><img src="<?php echo get_theme_path("../../images/plus.png"); ?>" alt="add tab icon" /></a>
                <a href="#" id="delete-part" title="<?php _e("Remove Tab"); ?>"><img src="<?php echo get_theme_path("../../images/minus.png"); ?>" alt="remove tab icon" /></a>
            </div>
            <ul class="tabNavigation">
                <?php foreach($parts AS $key => $part){ ?>
                    <li id="part-<?php echo $key+1; ?>-tab" class="tab">
                        <a href="#part-<?php echo $key+1; ?>-content"><?php echo $part->name; ?></a>
                    </li>
                <?php } ?>
            </ul>
        </div>
        <div id="part-content" class="pages">
            <?php
                $index = 0;
                foreach($parts AS $part){
                    $view = new View("page/part_edit", array("index" => ++$index, "part" => $part));
                    $view->display();
                }
            ?>
        </div>
        <?php Event::apply("view_page_after_edit_tabs", $page, $parts, $action); ?>

        <div class="row">
            <?php if(!isset($page->id) || $page->id != 1){ ?>
                <div>
                    <label for="status-id"><?php _e("Status"); ?></label>
                    <select id="status-id" name="page[status_id]">
                        <option value="1" <?php selected($page->status_id, 1); ?>><?php _e("Draft"); ?></option>
                        <option value="10" <?php selected($page->status_id, 10); ?>><?php _e("Preview"); ?></option>
                        <option value="100" <?php selected($page->status_id, 100); ?>><?php _e("Published"); ?></option>
                        <option value="101" <?php selected($page->status_id, 101); ?>><?php _e("Hidden"); ?></option>
                        <option value="200" <?php selected($page->status_id, 200); ?>><?php _e("Archived"); ?></option>
                    </select>
                </div>
            <?php } ?>
            <?php Event::apply("view_page_edit_plugins", $page, $parts, $action); ?>
        </div>

        <p><small><?php
            _e("Last updated by :user on :date", array(":user" => $page->updated_by, ":date" => $page->datetime("D, j M Y", "updated")));
        ?></small></p>
    </div>

    <p class="buttons">
        <input type="submit" name="commit" value="<?php _e("Save and Close"); ?>" class="button" />
        <input type="submit" name="continue" value="<?php _e("Save and Continue Editing"); ?>" class="button" />
        <?php _e("or"); ?>
        <a href="<?php echo get_url("page"); ?>"><?php _e("Cancel"); ?></a>
    </p>
</form>

<div id="boxes">
    <div id="add-part-dialog" class="window">
        <div class="titlebar">
            <div id="busy" class="busy" style="display: none;">
                <img src="<?php echo get_theme_path("../../images/spinner.gif"); ?>" alt="spinner" />
            </div>
            <?php _e("Add Part"); ?>
            <a href="#" class="close">[x]</a>
        </div>
        <div class="content">
            <form method="post" action="">
                <div>
                    <input type="hidden" id="part-index-field" name="part[index]" value="<?php echo ++$index; ?>" />
                    <input type="text" id="part-name-field" name="part[name]" value="" />
                    <input type="submit" id="add-part-button" name="commit" value="<?php _e("Add"); ?>" />
                </div>
            </form>
        </div>
    </div>

    <?php Event::apply("view_page_edit_popup", $page, $parts, $action); ?>
</div>

<!-- Wolf CMS Script -->
<script type="text/javascript">
    function setConfirmUnload(on, msg) {
        window.onbeforeunload = (on) ? unloadMessage : null;
        return true;
    }
    function unloadMessage() {
        return '<?php echo __('You have modified this page.  If you navigate away from this page without first saving your data, the changes will be lost.'); ?>';
    }
    jQuery.fn.spinnerSetup = function spinnerSetup() {
        this.each(function() {
            var pid = $(this).attr('id')
            $('#'+pid).hide()  // hide it initially
            .ajaxStop(function() {
                $('#'+pid).hide();
            });
        });
        return this;
    };

    $(document).ready(function() {
        $(".busy").spinnerSetup();
        var editAction = '<?php echo $action; ?>';
        if (editAction == 'add') {
            $('#page_title').change(function (){
                $('#page_slug').val(toSlug(this.value));
                $('#page_breadcrumb').val(this.value);
            });
        }

        // Store PHP value for later reference
        var partIndex = <?php echo $index; ?>;

        // Prevent accidentally navigating away
        $('form#page_edit_form :input').bind('change', function() { setConfirmUnload(true); });
        $('form#page_edit_form').submit(function() { setConfirmUnload(false); return true; });

        // Do the metainfo tab thing
        $('div#metainfo-tabs ul.tabNavigation li a').bind('click', function(event){
            $('div#metainfo-content > div.page').hide().filter(this.hash).show();
            /* Get index and current page id*/
            var i = $(this).parent('li').index();
            var pageID = page_id();

            $('div#metainfo-tabs ul.tabNavigation a.here').removeClass('here');
            $(this).addClass('here');

            $(this).trigger('metaInfoTabFocus', [ i, this.hash ]);
            document.cookie = "meta_tab=" + pageID + ':' + i;
            return false;
        });

        // Do the parts tab thing
        $('div#part-tabs ul.tabNavigation a').live('click', function(event) {
            $('div#part-content > div.page').hide().filter(this.hash).show();
            /* Get index and current page id */
            var i = $(this).parent('li').index();
            var pageID = page_id();

            $('div#part-tabs ul.tabNavigation a.here').removeClass('here');
            $(this).addClass('here');

            document.cookie = "page_tab=" + pageID + ':' + i;
            $(this).trigger('pageTabFocus', [ i , this.hash ] );
            return false;
        });

        (function(){
            var id, metaTab, pageTab,
                pageId = page_id(),
                meta = document.cookie.match(/meta_tab=(\d+):(\d+);/),
                part = document.cookie.match(/page_tab=(\d+):(\d+);/);

            if(meta && pageId == meta[1]) {
                metaTab = (meta[2]) ? meta[2] : 0 ;
            } else { metaTab = 0; }

            if(part && pageId == part[1]) {
                pageTab = (part[2]) ? part[2] : 0 ;
            } else { pageTab = 0; }

            $('div#metainfo-content > div.page').hide();
            $('div#metainfo-tabs ul.tabNavigation li a').eq(metaTab).click();

            $('div#part-content > div.page').hide();
            $('div#part-tabs ul.tabNavigation li a').eq(pageTab).click();
        })();

        // Do the add part button thing
        $('#add-part').click(function() {
            var id = 'div#boxes div#add-part-dialog';
            $('div#add-part-dialog div.content form input#part-name-field').val('');

            //Get the screen height and width
            var maskHeight = $(document).height();
            var maskWidth = $(window).width();

            //Set height and width to mask to fill up the whole screen
            $('#mask').css({'width':maskWidth,'height':maskHeight,'top':0,'left':0});

            //transition effect
            $('#mask').show();
            $('#mask').fadeTo("fast",0.5);

            //Get the window height and width
            var winH = $(window).height();
            var winW = $(window).width();

            //Set the popup window to center
            $(id).css('top',  winH/2-$(id).height()/2);
            $(id).css('left', winW/2-$(id).width()/2);

            //transition effect
            $(id).fadeIn("fast"); //2000

            $(id+" :input:visible:enabled:first").focus();
            // END show popup
        });
        // Do the submit add part window thing
        $('div#add-part-dialog div.content form').submit(function(e) {
            e.preventDefault();

            if (valid_part_name($('div#add-part-dialog div.content form input#part-name-field').val())) {
                $('div#part-tabs ul.tabNavigation').append('<li id="part-'+partIndex+'-tab" class="tab">\n\
                                                             <a href="#part-'+partIndex+'-content">'+$('div#add-part-dialog div.content form input#part-name-field').val()+'</a></li>');
                $('div#part-tabs ul.tabNavigation li#part-'+partIndex+'-tab a').click();
                $('div#add-part-dialog div.content form input#part-index-field').val(partIndex);
                $('#busy').show();

                $.post('<?php echo get_url('page/addPart'); ?>',
                        $('div#add-part-dialog div.content form').serialize(),
                        function(data) {
                                $('div#part-content').append(data);
                                $('#busy').hide();
                            });

                partIndex++;

                // Make sure users save changes
                setConfirmUnload(true);
           }
           $('#mask, .window').hide();
           return false;
        });

        // Do the delete part button thing
        $('#delete-part').click(function() {
            // Delete the tab
            var partRegEx = /part-(\d+)-tab/i;
            var myRegEx = new RegExp(partRegEx);
            var matched = myRegEx.exec($('div#part-tabs ul.tabNavigation li.tab a.here').parent().attr('id'));
            var removePart = matched[1];
            if (!confirm('<?php echo __('Delete the current tab?'); ?>')) {
                return;
            }

            $('div#part-tabs ul.tabNavigation li.tab a.here').remove();
            $('div#part-tabs ul.tabNavigation a').filter(':first').click();
            // Delete the content section
            $('div#part-'+removePart+'-content').remove();
            // Make sure users save changes
            setConfirmUnload(true);
        });

        // Make all modal dialogs draggable
        $("#boxes .window").draggable({
            addClasses: false,
            containment: 'window',
            scroll: false,
            handle: '.titlebar'
        })

        //if close button is clicked
        $('#boxes .window .close').click(function (e) {
            //Cancel the link behavior
            e.preventDefault();
            $('#mask, .window').hide();
        });
    });
</script>
