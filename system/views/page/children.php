<?php
/*
 |  FoxCMS      Content Management Simplified <www.foxcms.org>
 |  @file       ./system/views/page/children.php
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
<ul id="site-map<?php echo($level > 1)? "-{$level}": ""; ?>" class="<?php echo ($level > 1)? "sortable child": "sortable tree-root"; ?>">
    <?php foreach($children AS $child){ ?>
    <?php
        switch($child->status_id){
            case Page::STATUS_DRAFT:
                $status = array("draft" => __("Draft"));
                break;
            case Page::STATUS_PREVIEW:
                $status = array("preview" => __("Preview"));
                break;
            case Page::STATUS_PUBLISHED:
                $status = array("published" => __("Published"));
                break;
            case Page::STATUS_HIDDEN:
                $status = array("hidden" => __("Hidden"));
                break;
            case Page::STATUS_ARCHIVED:
                $status = array("archived" => __("Archived"));
                break;
            default:
                $status = array("unknown" => __("Unknown"));
                break;
        }
    ?>
    <li id="page_<?php echo $child->id; ?>" class="node level-<?php echo $level . (!$child->hasChildren? " no-children": ($child->is_expanded? " children-visible": " children-hidden")); ?>">
        <div class="content-children">
            <div class="page">
                <span class="w1">
                    <?php if($child->hasChildren){ ?>
                        <img src="<?php echo get_theme_path("../../images/" . ($child->is_expanded? "collapse": "expand") . ".png"); ?>"
                            align="middle" alt="toggle children" class="expander <?php echo($child->is_expanded)? "expanded": ""; ?>" />
                    <?php } ?>
                    <?php if(!AuthUser::hasPermission("page_edit") || (!AuthUser::hasPermission("admin_edit") && $child->is_protected)){ ?>
                        <img src="<?php echo get_theme_path("../../images/page.png"); ?>" align="middle" alt="page icon" class="icon" />
                        <span class="title protected"><?php echo $child->title; ?></span>
                        <img src="<?php echo get_theme_path("../../images/drag_to_sort.gif"); ?>" align="middle" alt="drag and drop" class="handle_reorder" />
                    <?php } else { ?>
                        <a href="<?php echo get_url("page/edit/{$child->id}"); ?>" title="<?php echo $child->id." | ".$child->slug; ?>" class="edit-link">
                            <img src="<?php echo get_theme_path("../../images/page.png"); ?>" align="middle" alt="page icon" class="icon" />
                            <span class="title protected"><?php echo $child->title; ?></span>
                            <img src="<?php echo get_theme_path("../../images/drag_to_sort.gif"); ?>" align="middle" alt="drag and drop" class="handle_reorder" />
                            <img src="<?php echo get_theme_path("../../images/drag_to_copy.gif"); ?>" align="middle" alt="drag to copy" class="handle_copy" />
                        </a>
                    <?php } ?>
                    <?php if(!empty($child->bevavior_id)){ ?>
                        <small class="info">(<?php echo Inflector::humanize($child->behavior); ?>)</small>
                    <?php } ?>
                    <img src="<?php echo get_theme_path("../../images/spinner.gif"); ?>" id="busy-<?php echo $child->id; ?>" align="middle" class="busy" />
                </span>
            </div>
            <div class="page-layout">
                <?php
                    $layout = Layout::findById($child->layout_id);
                    if(isset($layout->name)){
                        echo htmlspecialchars($layout->name);
                    } else {
                        _e("Inherit");
                    }
                ?>
            </div>
            <div class="status <?php echo array_keys($status)[0] ?>-status">
                <?php echo array_values($status)[0]; ?>
            </div>
            <div class="view view-page">
                <a href="<?php echo $child->url(); ?>" class="view-link" target="_blank">
                    <img src="<?php echo get_theme_path("../../images/magnify.png"); ?>" align="middle" alt="view page" title="<?php _e("View Page"); ?>" />
                </a>
            </div>
            <div class="modify">
                <a href="<?php echo get_url("page/add/{$child->id}"); ?>" class="add-child-link">
                    <img src="<?php echo get_theme_path("../../images/plus.png"); ?>" align="middle" alt="add child" title="<?php _e("Add Child"); ?>" />
                </a>
                <?php if(!$child->is_protected || AuthUser::hasPermission("page_delete")){ ?>
                    <?php $token = SecureToken::generateToken("page/delete/{$child->id}"); ?>
                    <a href="<?php echo get_url("page/delete/{$child->id}"); ?>?token=<?php echo $token; ?>" class="remove"
                        onclick="return confirm('<?php echo __("Are you sure you with to delete")." ".$child->title."?"; ?>')">
                        <img src="<?php echo get_theme_path("../../images/icon-remove.gif"); ?>" align="middle" alt="remove page" title="<?php _e("Remove Page"); ?>" />
                    </a>
                <?php } ?>
                <a href="#" id="copy-<?php echo $child->id; ?>" class="copy-page">
                    <img src="<?php echo get_theme_path("../../images/copy.png"); ?>" align="middle" alt="copy page" title="<?php _e("Copy Page"); ?>" />
                </a>
            </div>
        </div>
        <?php if($child->is_expanded){ echo $child->children_rows; } ?>
    </li>
    <?php } ?>
</ul>
