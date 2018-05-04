<?php
/*
 |  FoxCMS      Content Management Simplified <www.foxcms.org>
 |  @file       ./system/wizard/index-migrate.php
 |  @author     SamBrishes@pytesNET
 |  @version    0.8.4 [0.8.4] - Alpha
 |
 |  @license    GNU GPL v3
 |  @copyright  Copyright © 2015 - 2018 SamBrishes, pytesNET <pytes@gmx.net>
 |
 |  @history    Copyright © 2009 - 2015 Martijn van der Kleijn <martijn.niji@gmail.com>
 |              Copyright © 2008 - 2009 Philippe Archambault <philippe.archambault@gmail.com>
 */
    if(!defined("FOXCMS") || (defined("FOXCMS") && FOXCMS !== "wizard")){ die(); }

    global $wizard;
    if($wizard->type() !== "migrate"){
        die();
    }

    if($wizard->user() == 1){
        ?>
            <div class="content-panel">
                <div class="widget">
                    <header>Create a Backup</header>
                    <article>
                        <form method="post">
                            <input type="hidden" name="wizard" value="migrate" />
                            <input type="hidden" name="migrate" value="backup" />

                            <?php $files = $wizard->instance()->checkFiles(); ?>
                            <div style="padding: 0 30px;">
                                <table class="migrate-files">
                                    <?php
                                        foreach($files AS $id => $data){
                                            if(isset($data["error"])){
                                                ?>
                                                    <tr>
                                                        <td class="icon"><span class="fa fa-times fa-fw text-red"></span></td>
                                                        <td class="text" colspan="2">
                                                            Unable to check the <b><?php echo $id; ?></b> directory!
                                                            <div class="small">Any existing files will not be migrated!</div>
                                                        </td>
                                                    </tr>
                                                <?php
                                            }
                                            if(empty($data["files"])){
                                                ?>
                                                    <tr>
                                                        <td class="icon"><span class="fa fa-check fa-fw text-green"></span></td>
                                                        <td class="text" colspan="2">
                                                            No custom <b><?php echo $id; ?></b> found!
                                                            <div class="small">Yay, nothing found to migrate!</div>
                                                        </td>
                                                    </tr>
                                                <?php
                                            } else if($id == "images" && !empty($data["files"])){
                                                ?>
                                                    <tr>
                                                        <td class="icon"><span class="fa fa-circle fa-fw text-info"></span></td>
                                                        <td class="text">
                                                            <?php echo count($data["files"]); ?> custom <b><?php echo $id; ?></b> found!
                                                            <div class="small">All of your uploaded content.</div>
                                                        </td>
                                                        <td class="form">
                                                            <label><input type="checkbox" id="migrate-<?php echo $id; ?>" name="store[]"
                                                                value="<?php echo $id; ?>" /> Migrate Files</label>
                                                        </td>
                                                    </tr>
                                                <?php
                                            } else {
                                                ?>
                                                    <tr>
                                                        <td class="icon"><span class="fa fa-circle fa-fw text-info"></span></td>
                                                        <td class="text">
                                                            <?php echo count($data["files"]); ?> custom <b><?php echo $id; ?></b> found!
                                                            <div class="small"><?php echo implode(", ", $data["files"]); ?></div>
                                                        </td>
                                                        <td class="form">
                                                            <label><input type="checkbox" id="migrate-<?php echo $id; ?>" name="store[]"
                                                                value="<?php echo $id; ?>" /> Migrate Files</label>
                                                        </td>
                                                    </tr>
                                                <?php
                                            }
                                        }
                                    ?>
                                </table>
                            </div>

                            <?php if(!$wizard->instance()->loggedIn()){ ?>
                                <h1>Administrator Data</h1>
                                <table>
                                    <tr>
                                        <th width="25%"><label for="admin-user">Admin Username</label></th>
                                        <td width="30%">
                                            <input type="text" id="admin-user" name="admin-user" value="" placeholder="The Site-Admin Username"/>
                                        </td>
                                        <td width="45%">
                                            The Admin Username of your Wolf CMS Website.
                                        </td>
                                    </tr>

                                    <tr>
                                        <th width="25%"><label for="admin-pass">Admin Password</label></th>
                                        <td width="30%">
                                            <input type="password" id="admin-pass" name="admin-pass" value="" placeholder="The Site-Admin Password" />
                                        </td>
                                        <td width="45%">
                                            The Admin Password of your Wolf CMS Website.
                                        </td>
                                    </tr>
                                </table>
                            <?php } ?>

                            <p style="margin-top:50px;margin-left:530px;margin-right:0px;">
                                This step will backup all Wolf CMS folders and store them as .ZIP package within the
                                root folder. If this doesn't fail, all selected custom Wolf CMS data will be merged to
                                the Fox CMS folder structure!
                            </p>

                            <p class="text-right">
                                <button name="submit" value="submit">Create Backup and Merge Folders</button>
                            </p>
                        </form>
                    </article>
                </div>
            </div>
        <?php
    } else if($wizard->user() == 2){
        $u = bin2hex(openssl_random_pseudo_bytes(5));
        $c = array_merge(array(
            "fox-public"    => URL_PUBLIC,
            "https-mode"    => (USE_HTTPS)? "always": 0,
            "admin-dir"     => ADMIN_DIR,
            "url-suffix"    => URL_SUFFIX,
            "fox-id"        => "{$u}",
            "session-key"   => "{$u}_s",
            "cookie-key"    => "{$u}_c",
            "mod-rewrite"   => file_exists(BASE_ROOT . ".htaccess")? 1: 0,
            "debug-mode"    => FOX_STATUS != "Stable"? 1: 0,
        ), $_POST);

        ?>
            <div class="content-panel">
                <div class="widget">
                    <header>Migrate and Remove</header>
                    <article>
                        <form method="post" action="">
                            <input type="hidden" name="wizard" value="migrate" />
                            <input type="hidden" name="migrate" value="tothefox" />

                            <h1>General Settings</h1>
                            <table>
                                <tr>
                                    <th width="25%"><label for="fox-public">Public URL</label></th>
                                    <td width="30%">
                                        <input type="text" id="fox-public" name="fox-public" value="<?php echo $c["fox-public"]; ?>" />
                                    </td>
                                    <td width="45%">The public URL to your foxcms installation.</td>
                                </tr>

                                <tr>
                                    <th width="25%"><label for="admin-dir">Admin Dir</label></th>
                                    <td width="30%">
                                        <input type="text" id="admin-dir" name="admin-dir" value="<?php echo $c["admin-dir"]; ?>" />
                                    </td>
                                    <td width="45%">The virtual directory to the backend.</td>
                                </tr>

                                <tr>
                                    <th width="25%"><label for="url-suffix">URL Suffix</label></th>
                                    <td width="30%">
                                        <input type="text" id="url-suffix" name="url-suffix" value="<?php echo $c["url-suffix"]; ?>" />
                                    </td>
                                    <td width="45%">An URL suffix to simulate static pages.</td>
                                </tr>

                                <tr>
                                    <th width="25%"><label for="debug-mode">Debug Mode</label></th>
                                    <td width="30%">
                                        <select id="debug-mode" name="debug-mode">
                                            <option value="1" <?php selected($c["debug-mode"], "1"); ?>>Enable</option>
                                            <option value="0" <?php selected($c["debug-mode"], "0"); ?>>Disable</option>
                                        </select>
                                    </td>
                                    <td width="45%">Enable / Disable the debug mode. (Disable on productive systems!)</td>
                                </tr>
                            </table>

                            <h1>Security Settings</h1>
                            <table>
                                <tr>
                                    <th width="25%"><label for="https-mode">HTTPS Mode</label></th>
                                    <td width="30%">
                                        <select id="https-mode" name="https-mode">
                                            <option value="0" <?php selected($c["https-mode"], "0"); ?>>Disable</option>
                                            <option value="backend" <?php selected($c["https-mode"], "backend"); ?>>Use on Backend</option>
                                            <option value="frontend" <?php selected($c["https-mode"], "frontend"); ?>>Use on Frontend</option>
                                            <option value="always" <?php selected($c["https-mode"], "always"); ?>>Use Always</option>
                                        </select>
                                    </td>
                                    <td width="45%">Enable or Disable the TLS-encrypted HTTP<b>S</b> protocol.</td>
                                </tr>

                                <tr>
                                    <th width="25%"><label for="fox-id">Fox ID</label></th>
                                    <td width="30%">
                                        <input type="text" id="fox-id" name="fox-id" value="<?php echo $c["fox-id"]; ?>" />
                                    </td>
                                    <td width="45%">A unique Fox ID, which is used for many site-related actions.</td>
                                </tr>

                                <tr>
                                    <th width="25%"><label for="session-key">Session Key</label></th>
                                    <td width="30%">
                                        <input type="text" id="session-key" name="session-key" value="<?php echo $c["session-key"]; ?>" />
                                    </td>
                                    <td width="45%">A unique Session key for login and user actions.</td>
                                </tr>

                                <tr>
                                    <th width="25%"><label for="cookie-key">Cookie Key</label></th>
                                    <td width="30%">
                                        <input type="text" id="cookie-key" name="cookie-key" value="<?php echo $c["cookie-key"]; ?>" />
                                    </td>
                                    <td width="45%">A unique Cookie key for login and user actions.</td>
                                </tr>

                                <tr>
                                    <th width="25%"><label for="mod-rewrite">Mod Rewrite</label></th>
                                    <td width="30%">
                                        <select id="mod-rewrite" name="mod-rewrite">
                                            <option value="1" <?php selected($c["mod-rewrite"], "1"); ?>>Enable</option>
                                            <option value="0" <?php selected($c["mod-rewrite"], "0"); ?>>Disable</option>
                                        </select>
                                    </td>
                                    <td width="45%">Create clean URLs. (Check if your server supports mod_rewrite!)</td>
                                </tr>
                            </table>

                            <p style="margin-top:50px;margin-left:530px;margin-right:0px;">
                                This step creates the Fox CMS config file within the root directory AND
                                migrates the Wolf CMS database to the Fox CMS structure. Please be smart and
                                create a Database backup BEFORE you start this option!
                            </p>

                            <p class="text-right">
                                <button name="submit" value="submit">Migrate to Fox CMS and <b>Remove Wolf CMS</b></button>
                            </p>
                        </form>
                    </article>
                </div>
            </div>
        <?php
    } else if($wizard->user() == 3){
        ?>
            <div class="content-panel">
                <div class="widget">
                    <header>Thanks for testing the Fox CMS</header>
                    <article>
                        <p class="text-green" style="font-size: 24px;line-height:50px;text-align:center;">
                            The Fox CMS is now successfully migrated!
                        </p>
                        <p style="margin-left:15px;margin-right:15px;">
                            The Fox CMS has been successfully installed and can now be used! The following
                            links guides you to the Frontend as well as the Backend of your Webseite. Please
                            Note: You may need to adapt the CSS and JS links of the used theme to the new
                            Fox CMS environment. Visit the Backend, use your usual Username and Password,
                            to edit the respective Layout!
                        </p>
                        <ul>
                            <li><a href="<?php echo FOX_PUBLIC; ?>">Your Website (Frontend)</a></li>
                            <li><a href="<?php echo FOX_PUBLIC . "/" . ADMIN_DIR; ?>">Your Administration (Backend)</a></li>
                            <li><a href="https://www.github.com/FoxCMS/FoxCMS">The Fox CMS on GitHub</a></li>
                            <li><a href="https://www.twitter.com/FoxCMS_Fork">The Fox CMS on Twitter</a></li>
                        </ul>
                        <p style="margin-top:30px;margin-left:15px;margin-right:15px;">
                            Thanks for testing the Fox CMS, have Fun! &lt;3 <br /><br />
                            Sincerely,<br />
                            SamBrishes.
                        </p>
                    </article>
                </div>
            </div>
        <?php
    } else {
        ?>
            <div class="content-panel">
                <div class="widget">
                    <header>Check Migration</header>
                    <article>
                        <?php $requirements = $wizard->instance()->checkRequirements(); ?>
                        <table style="width:500px;margin:0 auto 15px auto;">
                            <?php foreach($requirements AS $type => $data){ ?>
                                <tr>
                                    <th><?php echo $data["title"]; ?></th>
                                    <th width="1%" class="text-center">
                                        <?php if($data["status"] == "error"){ ?>
                                            <span class="fa fa-times text-red"></span>
                                        <?php } else { ?>
                                            <span class="fa fa-check text-green"></span>
                                        <?php } ?>
                                    </th>
                                    <td><?php echo $data["string"]; ?></td>
                                </tr>
                            <?php } ?>
                        </table>
                        <div class="widget-icon"><span class="fa fa-plus"></span></div>

                        <p class="text-right">
                            <?php if($wizard->instance()->checkRequirements(true)){ ?>
                                <a href="?wizard=migrate&step=1" class="button">Start Migration</a>
                            <?php } else { ?>
                                <a href="#" class="button disabled">You don't met all Requirements</a>
                            <?php } ?>
                        </p>
                    </article>
                </div>
            </div>
        <?php
    }
