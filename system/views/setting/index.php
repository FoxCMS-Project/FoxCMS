<?php
/*
 |  FoxCMS      Content Management Simplified <www.foxcms.org>
 |  @file       ./system/views/setting/index.php
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
<h1><?php _e("Administration"); ?></h1>
<div id="admin-area" clas="form-area">
    <div class="content tabs">
        <ul class="tabNavigation">
            <li class="tab"><a href="#plugins"><?php _e("Plugins"); ?></a><li>
            <li class="tab"><a href="#settings"><?php _e("Settings"); ?></a><li>
        </ul>

        <div class="pages">
            <div id="plugins" class="page">
                <table class="index">
                    <thead>
                        <tr>
                            <th class="plugin"><?php _e("Plugin"); ?></th>
                            <th class="pluginSetting"><?php _e("Settings"); ?></th>
                            <th class="website"><?php _e("Website"); ?></th>
                            <th class="version"><?php _e("Version"); ?></th>
                            <th class="latest"><?php _e("Latest"); ?></th>
                            <th class="enabled"><?php _e("Enabled"); ?></th>
                            <th class="uninstall"><?php _e("Uninstall"); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            $string  = __("This plugin CANNOT be enabled!");
                            $plugins = Plugin::$plugins;
                            $filters = Filter::$filters;
                            foreach(Plugin::findAll() AS $plugin){
                                $errors = array();
                                $disabled = !Plugin::hasPrerequisites($plugin, $errors);
                                ?>
                                    <tr class="<?php echo ($disabled)? "disabled": ""; ?>">
                                        <td class="plugin">
                                            <h4><?php
                                                if(isset($plugins[$plugin->id]) && Plugin::hasDocumentationPage($plugin->id)){
                                                    ?><a href="<?php echo get_url("plugin/{$plugin->id}/documentation"); ?> ?>"><?php echo $plugin->title; ?></a><?php
                                                } else {
                                                    echo $plugin->title;
                                                }
                                            ?></h4>
                                            <p><?php echo $plugin->description . ($disabled? '<span class="notes">'.$string." ".implode("<br />", $errors).'</span>': ''); ?></p>
                                        </td>
                                        <td class="pluginSetting">
                                            <?php
                                                if(isset($plugins[$plugin->id]) && Plugin::hasSettingsPage($plugin->id)){
                                                    ?><a href="<?php echo get_url("plugin/{$plugin->id}/settings"); ?>"><?php _e("Settings"); ?></a><?php
                                                }
                                            ?>
                                        </td>
                                        <td class="website">
                                            <a href="<?php echo $plugin->website; ?>" target="_blank"><?php _e("Website"); ?></a>
                                        </td>
                                        <td class="version"><?php echo $plugin->version; ?></td>
                                        <td class="latest"><?php echo Plugin::checkLatest($plugin); ?></td>
                                        <td class="enabled">
                                            <input type="checkbox" name="enabled_<?php echo $plugin->id; ?>" value="<?php echo $plugin->id; ?>"
                                                <?php checked(isset($plugins[$plugin->id])); ?> <?php disabled($disabled); ?> />
                                        </td>
                                        <td class="uninstall">
                                            <a href="<?php echo get_url("setting"); ?>" name="uninstall_<?php echo $plugin->id; ?>"><?php _e("Uninstall"); ?></a>
                                        </td>
                                    </tr>
                                <?php
                            }
                        ?>
                    </tbody>
                </table>
            </div>

            <div id="settings" class="page">
                <form method="post" action="<?php echo get_url("setting"); ?>#settings">
                    <input type="hidden" id="token" name="token" value="<?php echo $token; ?>" />

                    <table class="fieldset">
                        <tr>
                            <td class="label"><label for="site-title"><?php _e("Site Title"); ?></label></td>
                            <td class="field">
                                <input type="text" id="site-title" name="setting[site-title]" value="<?php echo $config["site-title"]; ?>"
                                    class="textbox" maxlength="255" size="255" />
                            </td>
                            <td class="help"></td>
                        </tr>
                            <td class="label"><label for="site-slogan"><?php _e("Site Slogan"); ?></label></td>
                            <td class="field">
                                <input type="text" id="site-slogan" name="setting[site-slogan]" value="<?php echo $config["site-slogan"]; ?>"
                                    class="textbox" maxlength="255" size="255" />
                            </td>
                            <td class="help"></td>
                        </tr>
                        <tr>
                            <td class="label"><label for="site-email"><?php _e("Site eMail"); ?></label></td>
                            <td class="field">
                                <input type="text" id="site-email" name="setting[site-email]" value="<?php echo $config["site-email"]; ?>"
                                    class="textbox" maxlength="255" size="255" />
                            </td>
                            <td class="help"></td>
                        </tr>
                        <tr>
                            <td class="label"><label for="site-keywords"><?php _e("Site Meta Keywords"); ?></label></td>
                            <td class="field">
                                <input type="text" id="site-keywords" name="setting[site-keywords]" value="<?php echo $config["site-keywords"]; ?>"
                                    class="textbox" maxlength="255" size="255" />
                            </td>
                            <td class="help"></td>
                        </tr>
                        <tr>
                            <td class="label"><label for="site-description"><?php _e("Site Meta Description"); ?></label></td>
                            <td class="field">
                                <input type="text" id="site-description" name="setting[site-description]" value="<?php echo $config["site-description"]; ?>"
                                    class="textbox" maxlength="255" size="255" />
                            </td>
                            <td class="help"></td>
                        </tr>
                        <tr>
                            <td class="label"><label for="default-language"><?php _e("Default Language"); ?></label></td>
                            <td class="field">
                                <select id="default-language" name="setting[default-language]" class="select">
                                    <?php
                                        foreach(I18n::getAvailableLanguages() AS $key => $string){
                                            ?>
                                                <option value="<?php echo $key; ?>"
                                                        <?php selected($key, $config["default-language"]); ?>><?php echo $string; ?></option>
                                            <?php
                                        }
                                    ?>
                                </select>
                            </td>
                            <td class="help"></td>
                        </tr>
                        <tr>
                            <td class="label"><label for="backend-theme"><?php _e("Backend Theme"); ?></label></td>
                            <td class="field">
                                <select id="backend-theme" name="setting[backend-theme]" class="select">
                                    <?php
                                        foreach(Setting::getThemes() AS $key => $string){
                                            ?>
                                                <option value="<?php echo $key; ?>"
                                                        <?php selected($key, $config["backend-theme"]); ?>><?php echo $string; ?></option>
                                            <?php
                                        }
                                    ?>
                                </select>
                            </td>
                            <td class="help"></td>
                        </tr>
                        <tr>
                            <td class="label"><label for="default-tab"><?php _e("Default Tab"); ?></label></td>
                            <td class="field">
                                <select id="default-tab" name="setting[default-tab]" class="select">
                                    <option value="page" <?php selected("page", $config["default-tab"]) ?>><?php _e("Pages"); ?></option>
                                    <option value="snippet" <?php selected("snippet", $config["default-tab"]) ?>><?php _e("MSG_SNIPPETS"); ?></option>
                                    <option value="layout" <?php selected("layout", $config["default-tab"]) ?>><?php _e("Layouts"); ?></option>
                                    <option value="users" <?php selected("users", $config["default-tab"]) ?>><?php _e("Users"); ?></option>
                                    <option value="setting" <?php selected("setting", $config["default-tab"]) ?>><?php _e("Administration"); ?></option>
                                    <?php
                                        foreach(Plugin::$controllers AS $key => $controller){
                                            if(Plugin::isEnabled($key) && $controller->show_tab){
                                                ?>
                                                    <option value="plugin/<?php echo $key; ?>"
                                                            <?php selected("plugin/{$key}", $config["default-tab"]); ?>><?php echo $controller->label; ?></option>
                                                <?php
                                            }
                                        }
                                    ?>
                                </select>
                            </td>
                            <td class="help"></td>
                        </tr>
                    </table>

                    <h3>Page Settings</h3>
                    <table class="fieldset">
                        <tr>
                            <td class="label"><label for="allow-html-on"><?php _e("HTML in Page Title"); ?></label></td>
                            <td class="field">
                                <input type="radio" id="allow-html-on" name="setting[default-allow-html]" value="on"
                                    <?php checked($config["default-allow-html"], "on"); ?>/><label for="allow-html-on"><?php _e("Enable"); ?></label><br />
                                <input type="radio" id="allow-html-off" name="setting[default-allow-html]" value="off"
                                    <?php checked($config["default-allow-html"], "off"); ?>/><label for="allow-html-off"><?php _e("Disable"); ?></label>
                            </td>
                            <td class="help"></td>
                        </tr>
                        <tr>
                            <td class="label"><label for="default-status-draft"><?php _e("Default Status"); ?></label></td>
                            <td class="field">
                                <input type="radio" id="default-status-draft" name="setting[default-status]" value="1"
                                    <?php checked($config["default-status"], "1"); ?>/><label for="default-status-draft"><?php _e("Draft"); ?></label><br />
                                <input type="radio" id="default-status-published" name="setting[default-status]" value="100"
                                    <?php checked($config["default-status"], "100"); ?>/><label for="default-status-published"><?php _e("Published"); ?></label>
                            </td>
                            <td class="help"></td>
                        </tr>
                        <tr>
                            <td class="label"><label for="default-filter"><?php _e("Default Filter"); ?></label></td>
                            <td class="field">
                                <select id="default-filter" name="setting[default-filter]">
                                    <option value="" <?php selected($config["default-filter"] == ""); ?>><?php _e("No Filter"); ?></option>
                                    <?php
                                        foreach(Filter::findAll() AS $filter){
                                            if(!isset($filters[$filter]) || !Plugin::isEnabled($filter)){
                                                continue;
                                            }
                                            $string = Inflector::humanize($filter);
                                            ?>
                                                <option value="<?php echo $filter; ?>" <?php selected($filter, $config["default-filter"]); ?>><?php echo $string; ?></option>
                                            <?php
                                        }
                                    ?>
                                </select>
                            </td>
                            <td class="help"></td>
                        </tr>
                    </table>

                    <p class="buttons">
                        <input type="submit" name="commit" value="<?php _e("Save Settings"); ?>" class="button" />
                    </p>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Wolf CMS Script -->
<script type="text/javascript">
    function toSentenceCase(s) {
      return s.toLowerCase().replace(/^(.)|\s(.)/g,
              function($1) { return $1.toUpperCase(); });
    }
    function toLabelCase(s) {
      return s.toLowerCase().replace(/^(.)|\s(.)|_(.)/g,
              function($1) { return $1.toUpperCase(); });
    }
    $(document).ready(function() {

        // Setup tabs
        $(function () {
            var tabContainers = $('div.tabs > div.pages > div');

            $('div.tabs ul.tabNavigation a').click(function () {
                tabContainers.hide().filter(this.hash).show();

                $('div.tabs ul.tabNavigation a').removeClass('here');
                $(this).addClass('here');

                return false;
            }).filter(':first').click();
        });

        // Dynamically change enabled state
        $('.enabled input').change(function() {
            $.get('<?php echo get_url('setting'); ?>'+(this.checked ? '/activate_plugin/':'/deactivate_plugin/')+this.value, function(){
                location.reload(true);
            });
        });

        // Dynamically uninstall
        $('.uninstall a').click(function(e) {
            if (confirm('<?php echo escape(__('Are you sure you wish to uninstall this plugin?'), "javascript"); ?>')) {
                var pluginId = this.name.replace('uninstall_', '');
                $.get('<?php echo get_url('setting/uninstall_plugin'); ?>/'+pluginId, function() {
                    location.reload(true);
                });
            }
            e.preventDefault();
        });
    });
</script>
