<?php
/*
 |  FoxCMS      Content Management Simplified <www.foxcms.org>
 |  @file       ./system/views/page/part_edit.php
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
<div id="part-<?php echo $index; ?>-content" class="page">
    <div id="page-<?php echo $index; ?>" class="page">
        <div id="part-<?php echo $index; ?>" class="part">
            <input type="hidden" id="part_<?php echo ($index-1); ?>_name" name="part[<?php echo ($index-1); ?>][name]"
                value="<?php echo $part->name; ?>" />
            <?php if(isset($part->id)){ ?>
                <input type="hidden" id="part_<?php echo ($index-1); ?>_id" name="part[<?php echo ($index-1); ?>][id]"
                    value="<?php echo $part->id; ?>" />
            <?php } ?>

            <p>
                <label for="part_<?php echo ($index-1); ?>_filter_id"><?php _e("Filter"); ?></label>
                <select id="part_<?php echo ($index-1); ?>_filter_id" name="part[<?php echo ($index-1); ?>][filter_id]" class="filter-selector">
                    <option value="" <?php selected($part->filter_id == ""); ?>><?php _e("No Filter"); ?></option>
                    <?php foreach(Filter::findAll() AS $filter){ ?>
                        <?php
                            if(!isset(Filter::$filters[$filter]) || !Plugin::isEnabled($filter)){
                                continue;
                            }
                        ?>
                        <option value="<?php echo $filter; ?>" <?php selected($filter, $part->filter_id); ?>><?php echo Inflector::humanize($filter); ?></option>
                    <?php } ?>
                </select>
            </p>

            <div>
                <textarea id="part_<?php echo ($index-1); ?>_content" name="part[<?php echo ($index-1); ?>][content]"
                    class="textarea markitup <?php echo $part->filter_id; ?>" style="width: 100%" rows="20" cols="40"><?php echo htmlspecialchars($part->content, ENT_HTML5); ?></textarea>
            </div>
        </div>
    </div>
</div>
