<?php
/*
 |  FoxCMS      Content Management Simplified <www.foxcms.org>
 |  @file       ./system/wizard/wizard-install.php
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
    if($wizard->type() !== "install"){
        die();
    }

    if($wizard->user() == 1){
        $base = str_replace("\\", "/", $_SERVER["DOCUMENT_ROOT"]);
        $base = str_replace($base, "", str_replace("\\", "/", BASE_ROOT));
        $https = (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == 'on');

        $d = PDO::getAvailableDrivers();
        $u = bin2hex(openssl_random_pseudo_bytes(5));
        $c = array_merge(array(
            "db-type"       => "mysql",
            "db-host"       => "",
            "db-port"       => "",
            "db-socket"     => "",
            "db-user"       => "",
            "db-pass"       => "",
            "db-name"       => "",
            "db-prefix"     => "",

            "fox-public"    => "http" . ($https? "s": "") . "://" . $_SERVER["SERVER_NAME"] . $base,
            "https-mode"    => ($https)? "always": 0,
            "admin-dir"     => "admin",
            "url-suffix"    => ".html",

            "fox-id"        => "{$u}",
            "session-key"   => "{$u}_s",
            "cookie-key"    => "{$u}_c",
            "mod-rewrite"   => file_exists(BASE_ROOT . ".htaccess")? 1: 0,
            "debug-mode"    => FOX_STATUS != "Stable"? 1: 0,
        ), $_POST);
        if(isset($_SESSION["wizard_form_config"]) && is_array($_SESSION["wizard_form_config"])){
            $c = array_merge($c, $_SESSION["wizard_form_config"]);
            unset($_SESSION["wizard_form_config"]);
        }

        ?>
            <div class="content-panel">
                <div class="widget">
                    <header>Configure your Fox CMS Installation</header>
                    <article>
                        <form method="post">
                            <input type="hidden" name="wizard" value="install" />
                            <input type="hidden" name="install" value="config" />

                            <table>
                                <tr>
                                    <th width="25%"><label for="db-type">Database Type</label></th>
                                    <td width="30%">
                                        <select id="db-type" name="db-type">
                                            <?php if(in_array("mysql", $d)){ ?><option value="mysql" <?php selected($c["db-type"], "mysql"); ?>>PDO :: MySQL</option><?php } ?>
                                            <?php if(in_array("pgsql", $d)){ ?><option value="pgsql" <?php selected($c["db-type"], "pgsql"); ?>>PDO :: PostgreSQL</option><?php } ?>
                                            <?php if(in_array("sqlite", $d)){ ?><option value="sqlite" <?php selected($c["db-type"], "sqlite"); ?>>PDO :: SQLite3</option><?php } ?>
                                        </select>
                                    </td>
                                </tr>

                                <tr data-db="mysql|pgsql">
                                    <th width="25%"><label for="db-host">Database Host</label></th>
                                    <td width="30%">
                                        <input type="text" id="db-host" name="db-host" value="<?php echo $c["db-host"]; ?>" placeholder="localhost" />
                                    </td>
                                    <td width="45%">Your database host name. (Default: localhost)</td>
                                </tr>

                                <tr data-db="mysql|pgsql">
                                    <th width="25%"><label for="db-port">Database Port</label></th>
                                    <td width="30%">
                                        <input type="text" id="db-port" name="db-port" value="<?php echo $c["db-port"]; ?>" placeholder="3306" />
                                    </td>
                                    <td width="45%">Your database host port. (MySQL: 3306, PostgreSQL: 5432)</td>
                                </tr>

                                <tr data-db="mysql|pgsql">
                                    <th width="25%"><label for="db-socket">Database Unix Socket</label></th>
                                    <td width="30%">
                                        <input type="text" id="db-socket" name="db-socket" value="<?php echo $c["db-socket"]; ?>" placeholder="/path/to/socket" />
                                    </td>
                                    <td width="45%">Ignores the host and the port, when filled!</td>
                                </tr>

                                <tr data-db="mysql|pgsql">
                                    <th width="25%"><label for="db-user">Database User</label></th>
                                    <td width="30%">
                                        <input type="text" id="db-user" name="db-user" value="<?php echo $c["db-user"]; ?>" placeholder="root" />
                                    </td>
                                    <td width="45%">The database user name. (Don't use 'root' on productive sytems!)</td>
                                </tr>

                                <tr data-db="mysql|pgsql">
                                    <th width="25%"><label for="db-pass">Database Password</label></th>
                                    <td width="30%">
                                        <input type="password" id="db-pass" name="db-pass" value="<?php echo $c["db-pass"]; ?>" placeholder="Pa$$w0rd" />
                                    </td>
                                    <td width="45%">The database password.</td>
                                </tr>

                                <tr data-db="mysql|pgsql|sqlite">
                                    <th width="25%"><label for="db-name">
                                        <span data-db="sqlite">Database File</span>
                                        <span data-db="mysql|pgsql">Database Name</span>
                                    </label></th>
                                    <td width="30%">
                                        <input type="text" id="db-name" name="db-name" value="<?php echo $c["db-name"]; ?>" placeholder="foxcms" />
                                    </td>
                                    <td width="45%">
                                        <span data-db="sqlite">
                                            The absolute path to your database file.<br />
                                            It's recommended to keep your SQLite file outside the Fox CMS directories
                                            and at best also outside of your webserver's root!
                                        </span>
                                        <span data-db="mysql|pgsql">The database name.</span>
                                    </td>
                                </tr>

                                <tr data-db="mysql|pgsql">
                                    <th width="25%"><label for="db-prefix">Database Table Prefix</label></th>
                                    <td width="30%">
                                        <input type="text" id="db-prefix" name="db-prefix" value="<?php echo $c["db-prefix"]; ?>" placeholder="fox_" />
                                    </td>
                                    <td width="45%">The database table prefix. (Default: 'fox_')</td>
                                </tr>
                            </table>

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

                            <p class="text-right">
                                <?php if($wizard->step() > $wizard->user()){ ?>
                                    <button name="submit" value="submit" class="button">Overwrite Config File</button>
                                <?php } else { ?>
                                    <button name="submit" value="submit" class="button">Write Config File</button>
                                <?php } ?>
                            </p>
                        </form>
                    </article>
                </div>
            </div>
        <?php
    } else if($wizard->user() == 2){
        $c = array_merge(array(
            "site-title"        => "My foxy Website",
            "site-email"        => "no-reply@{$_SERVER["HTTP_HOST"]}" . ((strpos($_SERVER["HTTP_HOST"], ".") === false)? ".vs": ""),
            "site-language"     => "en",
            "admin-username"    => "",
            "admin-email"       => "",
            "admin-language"    => "en",
            "admin-password"    => "",
            "admin-password2"   => ""
        ), $_POST);
        if(isset($_SESSION["wizard_form_database"]) && is_array($_SESSION["wizard_form_database"])){
            $c = array_merge($c, $_SESSION["wizard_form_database"]);
            unset($_SESSION["wizard_form_database"]);
        }
        use_helper("I18n");
        ?>
            <div class="content-panel">
                <div class="widget">
                    <header>Install your Fox Website / Create your Admin Account</header>
                    <article>
                        <form method="post">
                            <input type="hidden" name="wizard" value="install" />
                            <input type="hidden" name="install" value="database" />

                            <table>
                                <tr>
                                    <th width="25%"><label for="site-title">Site Title</label></th>
                                    <td width="30%">
                                        <input type="text" id="site-title" name="site-title" value="<?php echo $c["site-title"]; ?>" placeholder="The Title of your Website" />
                                    </td>
                                    <td width="45%"></td>
                                </tr>

                                <tr>
                                    <th width="25%"><label for="site-email">Site eMail</label></th>
                                    <td width="30%">
                                        <input type="email" id="site-email" name="site-email" value="<?php echo $c["site-email"]; ?>" placeholder="The main eMail Address of your Website" />
                                    </td>
                                    <td width="45%"></td>
                                </tr>

                                <tr>
                                    <th width="25%"><label for="site-language">Site Language</label></th>
                                    <td width="30%">
                                        <select id="site-language" name="site-language">
                                            <?php foreach(I18n::getAvailableLanguages() AS $key => $lang){ ?>
                                                <option value="<?php echo $key; ?>" <?php selected($key, $c["site-language"]); ?>><?php echo $lang; ?></option>
                                            <?php } ?>
                                        </select>
                                    </td>
                                    <td width="45%"></td>
                                </tr>
                            </table>

                            <h1>Admin Account</h1>
                            <table>
                                <tr>
                                    <th width="25%"><label for="admin-username">Your Username</label></th>
                                    <td width="30%">
                                        <input type="text" id="admin-username" name="admin-username" value="<?php echo $c["admin-username"]; ?>" placeholder="Your Admin Username" />
                                    </td>
                                    <td width="45%"></td>
                                </tr>

                                <tr>
                                    <th width="25%"><label for="admin-email">Your eMail Address</label></th>
                                    <td width="30%">
                                        <input type="email" id="admin-email" name="admin-email" value="<?php echo $c["admin-email"]; ?>" placeholder="Your Admin eMail Address" />
                                    </td>
                                    <td width="45%"></td>
                                </tr>

                                <tr>
                                    <th width="25%"><label for="admin-language">Your Language</label></th>
                                    <td width="30%">
                                        <select id="admin-language" name="admin-language">
                                            <?php foreach(I18n::getLanguages() AS $key => $lang){ ?>
                                                <option value="<?php echo $key; ?>" <?php selected($key, $c["admin-language"]); ?>><?php echo $lang; ?></option>
                                            <?php } ?>
                                        </select>
                                    </td>
                                    <td width="45%"></td>
                                </tr>

                                <tr>
                                    <th width="25%"><label for="admin-password">Your Password</label></th>
                                    <td width="30%">
                                        <input type="password" id="admin-password" name="admin-password" value="" placeholder="Your Admin Password" />
                                    </td>
                                    <td width="45%"></td>
                                </tr>

                                <tr>
                                    <th width="25%"><label for="admin-password2">Repeat your Password</label></th>
                                    <td width="30%">
                                        <input type="password" id="admin-password2" name="admin-password2" value="" placeholder="Repeat your Admin Password" />
                                    </td>
                                    <td width="45%"></td>
                                </tr>
                            </table>

                            <p class="text-right">
                                <button name="submit" value="submit">Install Database</button>
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
                            The Fox CMS is now successfully installed!
                        </p>
                        <p style="margin-left:15px;margin-right:15px;">
                            The Fox CMS has been successfully installed and can now be used! The following
                            links guides you to the Frontend as well as the Backend of your Webseite. Please
                            Note: The FoxCMS is the first Alpha Version as Fork of the Wolf CMS, which means
                            that this Version may contains some bugs, errors and inconsistencies! But you
                            can help us by reporting each bug and requesting each missing feature on our GitHub,
                            thanks!
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
                    <header>Check Requirements</header>
                    <article>
                        <?php $requirements = $wizard->instance()->checkRequirements(); ?>
                        <table style="width:500px;margin:0 auto 15px auto;">
                            <?php foreach($requirements AS $type => $data){ ?>
                                <tr>
                                    <th><?php echo $data["title"]; ?></th>
                                    <th width="1%" class="text-center">
                                        <?php if($data["status"] == "error"){ ?>
                                            <span class="fa fa-times fa-fw text-red"></span>
                                        <?php } else { ?>
                                            <span class="fa fa-check fa-fw text-green"></span>
                                        <?php } ?>
                                    </th>
                                    <td><?php echo $data["string"]; ?></td>
                                </tr>
                            <?php } ?>
                        </table>
                        <div class="widget-icon"><span class="fa fa-plus"></span></div>

                        <p class="text-right">
                            <?php if($wizard->instance()->checkRequirements(true)){ ?>
                                <a href="?wizard=install&step=1" class="button">Start Installation</a>
                            <?php } else { ?>
                                <a href="#" class="button disabled">You don't met all Requirements</a>
                            <?php } ?>
                        </p>
                    </article>
                </div>
            </div>
        <?php
    }
