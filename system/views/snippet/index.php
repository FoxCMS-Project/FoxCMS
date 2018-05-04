<?php
/*
 |  FoxCMS      Content Management Simplified <www.foxcms.org>
 |  @file       ./system/views/snippet/index.php
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
<h1><?php _e("MSG_SNIPPETS"); ?></h1>
<div id="site-map-def" class="index-def">
    <div class="snippet">
        <?php _e("Snippet"); ?> (<a href="#" id="reorder-toggle"><?php _e("reorder"); ?></a>)
    </div>
    <div class="modify"><?php _e("Modify"); ?></div>
</div>
<ul id="snippets" class="index">
    <?php foreach($snippets AS $snippet){ ?>
        <?php $token = SecureToken::generateToken("snippet/delete/{$snippet->id}"); ?>

        <li id="snippet_<?php echo $snippet->id; ?>" class="snippet node <?php echo odd_even(); ?>">
            <img src="<?php echo get_theme_url("../../images/snippet.png"); ?>" align="middle" alt="snippet-icon" />
            <a href="<?php echo get_url("snippet/edit/{$snippet->id}"); ?>"><?php echo $snippet->name; ?></a>
            <img src="<?php echo get_theme_url("../../images/drag.gif"); ?>" align="middle" alt="<?php _e("Drag and Drop"); ?>" class="handle" />
            <div class="remove"><a href="<?php echo get_url("snippet/delete/{$snippet->id}?token={$token}"); ?>"
                    onclick="return confirm('<?php echo __("Are you sure you with to delete")." ".$snippet->name."?"; ?>')">
                <img src="<?php echo get_theme_url("../../images/icon-remove.gif"); ?>" alt="Delete Snippet Icon" title="<?php _e("Delete Snippet"); ?>" />
            </a></div>
        </li>
    <?php } ?>
</ul>

<!-- Wolf CMS Script -->
<script type="text/javascript">
    jQuery.fn.sortableSetup = function sortableSetup() {
        this.sortable({
            disabled:true,
            tolerance:'intersect',
       		containment:'#main',
       		placeholder:'placeholder',
       		revert: true,
            handle: '.handle',
            cursor:'crosshair',
       		distance:'15',
            stop: function(event, ui) {
                var order = $(ui.item.parent()).sortable('serialize', {key: 'snippets[]'});
                $.post('<?php echo get_url('snippet/reorder/'); ?>', {data : order});
            }
        })
        .disableSelection();

        return this;
    };

    $(document).ready(function() {
        $('ul#snippets').sortableSetup();
        $('#reorder-toggle').toggle(
            function(){
                $('ul#snippets').sortable('option', 'disabled', false);
                $('.handle').show();
                $('#reorder-toggle').text('<?php echo __('disable reorder');?>');
            },
            function() {
                $('ul#snippets').sortable('option', 'disabled', true);
                $('.handle').hide();
                $('#reorder-toggle').text('<?php echo __('reorder');?>');
            }
        )
    });
</script>
