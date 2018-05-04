<?php
/*
 |  FoxCMS      Content Management Simplified <www.foxcms.org>
 |  @file       ./system/views/layout/index.php
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
<h1><?php _e("Layouts"); ?></h1>
<div id="site-map-def" class="index-def">
    <div class="layout">
        <?php _e("Layout"); ?> (<a href="#" id="reorder-toggle"><?php _e("reorder"); ?></a>)
    </div>
    <div class="modify"><?php _e("Modify"); ?></div>
</div>
<ul id="layouts" class="index">
    <?php foreach($layouts AS $layout){ ?>
        <?php $token = SecureToken::generateToken("layout/delete/{$layout->id}"); ?>

        <li id="layout_<?php echo $layout->id; ?>" class="layout node <?php echo odd_even(); ?>">
            <img src="<?php echo get_theme_url("../../images/layout.png"); ?>" align="middle" alt="layout-icon" />
            <a href="<?php echo get_url("layout/edit/{$layout->id}"); ?>"><?php echo $layout->name; ?></a>
            <img src="<?php echo get_theme_url("../../images/drag.gif"); ?>" align="middle" alt="<?php _e("Drag and Drop"); ?>" class="handle" />
            <div class="remove"><a href="<?php echo get_url("layout/delete/{$layout->id}?token={$token}"); ?>"
                    onclick="return confirm('<?php echo __("Are you sure you with to delete")." ".$layout->name."?"; ?>')">
                <img src="<?php echo get_theme_url("../../images/icon-remove.gif"); ?>" alt="Delete Layout Icon" title="<?php _e("Delete Layout"); ?>" />
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
                var order = $(ui.item.parent()).sortable('serialize', {key: 'layouts[]'});
                $.post('<?php echo get_url('layout/reorder/'); ?>', {data : order});
            }
        })
        .disableSelection();

        return this;
    };

    $(document).ready(function() {
        $('ul#layouts').sortableSetup();
        $('#reorder-toggle').toggle(
            function(){
                $('ul#layouts').sortable('option', 'disabled', false);
                $('.handle').show();
                $('#reorder-toggle').text('<?php echo __('disable reorder');?>');
            },
            function() {
                $('ul#layouts').sortable('option', 'disabled', true);
                $('.handle').hide();
                $('#reorder-toggle').text('<?php echo __('reorder');?>');
            }
        )
    });
</script>
