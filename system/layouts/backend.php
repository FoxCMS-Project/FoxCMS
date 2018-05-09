<?php
/*
 |  FoxCMS      Content Management Simplified <www.foxcms.org>
 |  @file       ./system/layouts/backend.php
 |  @author     SamBrishes@pytesNET
 |  @version    0.8.4 [0.8.4] - Alpha
 |
 |  @license    GNU GPL v3
 |  @copyright  Copyright © 2015 - 2018 SamBrishes, pytesNET <pytes@gmx.net>
 |
 |  @history    Copyright © 2009 - 2015 Martijn van der Kleijn <martijn.niji@gmail.com>
 |              Copyright © 2008 - 2009 Philippe Archambault <philippe.archambault@gmail.com>
 */
    if(!defined("FOXCMS") || get_called_class() !== "View"){ die(); }
    if(!AuthUser::hasPermission("admin_view")){
        redirect("Location: " . PUBLIC_URL);
    }

?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo DEFAULT_LANGUAGE; ?>" lang="<?php echo DEFAULT_LANGUAGE; ?>">
    <head>
        <meta charset="utf-8" />
        <title><?php echo $this->getTitle(); ?> | Fox CMS Administration</title>

        <style type="text/css">
            .placeholder{
                color: #363636;
                height: 2.4em;
                line-height: 1.2em;
                border: 1px solid #fcefa1;
                background-color: #fbf9ee;
            }
        </style>
        <link type="text/css" rel="stylesheet" href="<?php echo get_theme_path("../../css/admin.css"); ?>" media="screen" />
        <link type="text/css" rel="stylesheet" href="<?php echo get_theme_path("../../markitup/slins/simple/style.css"); ?>" media="screen" />
        <link type="text/css" rel="stylesheet" href="<?php echo get_theme_path("styles.css"); ?>" media="screen" />
        <?php
            $style = '<link type="text/css" rel="stylesheet" href="%s" media="screen" />';
            foreach(Plugin::$plugins AS $plugin_id => $plugin){
                if(file_exists(PLUGINS_ROOT . $plugin_id . DS . $plugin_id . ".css")){
                    printf($style, PLUGINS_HTML . $plugin_id . "/" . $plugin_id . ".css");
                }
            }
            foreach(Plugin::$stylesheets AS $plugin_id => $file){
                printf($style, PLUGINS_HTML . $plugin_id . "/" . $file);
            }
        ?>

        <script type="text/javascript">
            var FoxCMS = {
                "root": {
                    "plugins": "<?php echo PLUGINS_HTML; ?>"
                },
                "url": {
                    "plugins": "<?php echo PLUGINS_URL; ?>"
                }
            };
        </script>
        <script type="text/javascript" src="<?php echo get_theme_path("../../js/cp-datepicker.js"); ?>" charset="UTF-8"></script>
        <script type="text/javascript" src="<?php echo get_theme_path("../../js/wolf.js"); ?>" charset="UTF-8"></script>
        <script type="text/javascript" src="<?php echo get_theme_path("../../js/jquery-1.8.3.min.js"); ?>" charset="UTF-8"></script>
        <script type="text/javascript" src="<?php echo get_theme_path("../../js/jquery-ui-1.10.3.min.js"); ?>" charset="UTF-8"></script>
        <script type="text/javascript" src="<?php echo get_theme_path("../../js/jquery.ui.nestedSortable.js"); ?>" charset="UTF-8"></script>
        <script type="text/javascript" src="<?php echo get_theme_path("../../markitup/jquery.markitup.js"); ?>"></script>
        <?php
            $script = '<script type="text/javascript" src="%s" charset="UTF-8"></script>';
            foreach(Plugin::$plugins AS $plugin_id => $plugin){
                if(file_exists(PLUGINS_ROOT . $plugin_id . DS . $plugin_id . ".js")){
                    printf($script, PLUGINS_HTML . $plugin_id . "/" . $plugin_id . ".js");
                }
            }
            foreach(Plugin::$javascripts AS $plugin_id => $file){
                printf($script, PLUGINS_HTML . $plugin_id . "/" . $file);
            }
        ?>
        <script type="text/javascript">
            // A Wolf CMS Script
            $(document).ready(function() {
                (function showMessages(e) {
                    e.fadeIn('slow')
                     .animate({opacity: 1.0}, Math.min(5000, parseInt(e.text().length * 50)))
                     .fadeOut('slow', function() {
                        if ($(this).next().attr('class') == 'message') {
                            showMessages($(this).next());
                        }
                        $(this).remove();
                     })
                })( $(".message:first") );

                // Get the initial values and activate filter
                $('.filter-selector').each(function() {
                    var $this = $(this);
                    $this.data('oldValue', $this.val());

                    if ($this.val() == '') {
                        return true;
                    }
                    var elemId = $this.attr('id').slice(0, -10);
                    var elem = $('#'+elemId+'_content');
                    $this.trigger('wolfSwitchFilterIn', [$this.val(), elem]);
                });

                $('.filter-selector').live('change',function(){
                    var $this = $(this);
                    var newFilter = $this.val();
                    var oldFilter = $this.data('oldValue');
                    $this.data('oldValue', newFilter);
                    var elemId = $this.attr('id').slice(0, -10);
                    var elem = $('#'+elemId+'_content');
                    $(this).trigger('wolfSwitchFilterOut', [oldFilter, elem]);
                    $(this).trigger('wolfSwitchFilterIn', [newFilter, elem]);
                });
            });
        </script>

        <?php Event::apply("view_backend_layout_head"); ?>
    </head>
    <body id="<?php echo$this->getID(); ?>" class="<?php echo $this->getClass(); ?>">
        <div id="mask"><!-- MODAL DIALOGS --></div>

        <div id="header">
            <div id="site-title">
                <a href="<?php echo get_url(); ?>"><?php echo Setting::get("site-title"); ?></a>
            </div>

            <div id="mainTabs">
                <ul class="main-navi">
                    <!--
                    <li id="dashboard-plugin" class="navi-item plugin">
                        <a href="<?php/* echo get_url(); */?>" class="item-link <?php/* echo $this->isCurrent(); */?>">
                            <?php/* echo __("Dashboard"); */?>
                        </a>
                    </li>
                    -->
                    <li id="page-plugin" class="navi-item plugin">
                        <a href="<?php echo get_url("page"); ?>" class="item-link <?php echo $this->isCurrent("page"); ?>">
                            <?php echo __("Pages"); ?>
                        </a>
                    </li>
                    <?php if(AuthUser::hasPermission("snippet_view")){ ?>
                        <li id="snippet-plugin" class="navi-item plugin">
                            <a href="<?php echo get_url("snippet"); ?>" class="item-link <?php echo $this->isCurrent("snippet"); ?>">
                                <?php echo __("MSG_SNIPPETS"); ?>
                            </a>
                        </li>
                    <?php } ?>
                    <?php if(AuthUser::hasPermission("layout_view")){ ?>
                        <li id="layout-plugin" class="navi-item plugin">
                            <a href="<?php echo get_url("layout"); ?>" class="item-link <?php echo $this->isCurrent("layout"); ?>">
                                <?php echo __("Layouts"); ?>
                            </a>
                        </li>
                    <?php } ?>

                    <?php
                        foreach(Plugin::$controllers AS $plugin_id => $plugin){
                            if(!$plugin->show_tab || !AuthUser::hasPermission($plugin->permissions)){
                                continue;
                            }
                            Event::apply("view_backend_list_plugin", $plugin_id, $plugin);
                            ?>
                                <li id="<?php echo $plugin_id; ?>-plugin" class="navi-item plugin">
                                    <a href="<?php echo get_url("plugin/{$plugin_id}"); ?>" class="item-link <?php echo $this->isCurrent("plugin/{$plugin_id}"); ?>">
                                        <?php echo $plugin->label; ?>
                                    </a>
                                </li>
                            <?php
                        }
                    ?>

                    <?php if(AuthUser::hasPermission("admin_edit")){ ?>
                        <li id="setting-plugin" class="navi-item right">
                            <a href="<?php echo get_url("setting"); ?>" class="item-link <?php echo $this->isCurrent("setting"); ?>">
                                <?php echo __("Administration"); ?>
                            </a>
                        </li>
                    <?php } ?>
                    <?php if(AuthUser::hasPermission("user_view")){ ?>
                        <li id="user-plugin" class="navi-item right">
                            <a href="<?php echo get_url("user"); ?>" class="item-link <?php echo $this->isCurrent("user"); ?>">
                                <?php echo __("Users"); ?>
                            </a>
                        </li>
                    <?php } ?>
                </ul>
            </div>
        </div>

        <?php
            foreach(array("error", "warning", "success", "info") AS $type){
                if(!empty(Flash::get($type))){
                    ?>
                        <div id="<?php echo $type; ?>" class="message">
                            <?php print(Flash::get($type)); ?>
                        </div>
                    <?php
                }
            }
        ?>

        <div id="main">
            <div id="content-wrapper">
                <div id="content">
                    <?php
                        if(isset($content_for_layout)){
                            if(is_object($content_for_layout) && method_exists($content_for_layout, "render")){
                                print($content_for_layout->render());
                            } else {
                                print($content_for_layout);
                            }
                        }
                    ?>
                </div>
            </div>
            <?php if(isset($sidebar)){ ?>
                <div id="sidebar-wrapper">
                    <div id="sidebar">
                        <?php echo $sidebar; ?>
                    </div>
                </div>
            <?php } ?>
        </div>

        <div id="footer">
            <p id="footer-links" class="footer-copyright footer-links">
                <?php echo __('Thank you for using'); ?> <a href="https://www.foxcms.org/" target="_blank">Fox CMS <?php echo FOX_VERSION; ?></a>
                <span class="separator"> | </span>
                <a href="https://www.github.com/SamBrishes/FoxCMS" target="_blank">GitHub</a>
                <span class="separator"> | </span>
                <a href="https://www.twitter.com/FoxCMS_Fork" target="_blank">Twitter</a>
            </p>

            <p id="stats" class="footer-stats stats">
                <?php echo __("Page rendered in")." ".execution_time()." ".__("seconds"); ?>
                <span class="separator"> | </span>
                <?php echo __("Memory usage:")." ".memory_usage(); ?>
            </p>

            <?php $token = SecureToken::generateToken("login/logout"); ?>
            <p id="site-links" class="footer-links links">
                <span class="site-thanks"><?php _e("You are currently logged in as"); ?></span>
                <a id="site-user-link" href="<?php echo get_url("user/edit/".AuthUser::getId()); ?>"><?php echo AuthUser::getUser()->name; ?></a>
                <span class="separator"> | </span>
                <a id="site-lgout-link" href="<?php echo get_url("login/logout/?token={$token}"); ?>"><?php _e("Logout"); ?></a>
                <span class="separator"> | </span>
                <a id="site-view-link" href="<?php echo PUBLIC_URL; ?>" target="_blank"><?php _e("View Site"); ?></a>
            </p>
        </div>
    </body>
</html>
