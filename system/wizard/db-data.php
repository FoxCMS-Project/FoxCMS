<?php
/*
 |  FoxCMS      Content Management Simplified <www.foxcms.org>
 |  @file       ./system/wizard/db-mysql.php
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
    if(!defined("FOX_WIZARD_DATA")){ die(); }

    /*
     |  INSERT DATA
     |  @since  0.8.4
     */
    function wizard_data_install($db, $data){
        global $Fox;
        $pdo = $db::getConnection();
        $time = time();

        // Config Table
        $stmt = $pdo->prepare("INSERT INTO ".DB_PREFIX."config (name, value, type) VALUES (:n, :v, :g);");
        $stmt->execute(array(":n" => "fox-version", ":v" => FOX_VERSION, ":g" => "fox"));
        $stmt->execute(array(":n" => "fox-status", ":v" => FOX_STATUS, ":g" => "fox"));
        $stmt->execute(array(":n" => "cms-version", ":v" => CMS_VERSION, ":g" => "fox"));
        $stmt->execute(array(":n" => "site-title", ":v" => $data['site-title'], ":g" => "core"));
        $stmt->execute(array(":n" => "site-slogan", ":v" => "My awesome Fox CMS based Website!", ":g" => "core"));
        $stmt->execute(array(":n" => "site-email", ":v" => $data['site-email'], ":g" => "core"));
        $stmt->execute(array(":n" => "site-keywords", ":v" => "foxcms, content, management, simplified", ":g" => "core"));
        $stmt->execute(array(":n" => "site-description", ":v" => "A small description about your FoxCMS website.", ":g" => "core"));
        $stmt->execute(array(":n" => "site-plugins", ":v" => serialize(array()), ":g" => "core"));
        $stmt->execute(array(":n" => "site-cronjob", ":v" => 1, ":g" => "core"));
        $stmt->execute(array(":n" => "default-language", ":v" => DEFAULT_LANGUAGE, ":g" => "core"));
        $stmt->execute(array(":n" => "default-status", ":v" => 1, ":g" => "core"));
        $stmt->execute(array(":n" => "default-filter", ":v" => "", ":g" => "core"));
        $stmt->execute(array(":n" => "default-allow-html", ":v" => "on", ":g" => "core"));
        $stmt->execute(array(":n" => "default-tab", ":v" => "page", ":g" => "core"));
        $stmt->execute(array(":n" => "backend-theme", ":v" => "fox", ":g" => "core"));

        // User (+ Role) Table
        $db::insert("user", array(
            "username"      => ":username",
            "name"          => ":name",
            "email"         => ":mail",
            "password"      => AuthUser::hashPassword($data["admin-password"]),
            "salt"          => "fox", /* Unused in the Fox CMS */
            "language"      => $data["admin-language"],
            "created_on"    => date("Y-m-d H:i:s"),
            "created_by"    => 0
        ), array(
            ":username"     => $data["admin-username"],
            ":name"         => $data["admin-name"],
            ":mail"         => $data["admin-email"]
        ));
        $db::insert("user_role", array(
            "user_id"   => 1,
            "role_id"   => 1
        ));

        // Permission Table
        $db::insert("permission", array("name" => "admin_view"));
        $db::insert("permission", array("name" => "admin_edit"));
        $db::insert("permission", array("name" => "user_view"));
        $db::insert("permission", array("name" => "user_add"));
        $db::insert("permission", array("name" => "user_edit"));
        $db::insert("permission", array("name" => "user_delete"));
        $db::insert("permission", array("name" => "layout_view"));
        $db::insert("permission", array("name" => "layout_add"));
        $db::insert("permission", array("name" => "layout_edit"));
        $db::insert("permission", array("name" => "layout_delete"));
        $db::insert("permission", array("name" => "snippet_view"));
        $db::insert("permission", array("name" => "snippet_add"));
        $db::insert("permission", array("name" => "snippet_edit"));
        $db::insert("permission", array("name" => "snippet_delete"));
        $db::insert("permission", array("name" => "page_view"));
        $db::insert("permission", array("name" => "page_add"));
        $db::insert("permission", array("name" => "page_edit"));
        $db::insert("permission", array("name" => "page_delete"));
        $db::insert("permission", array("name" => "file_manager_view"));
        $db::insert("permission", array("name" => "file_manager_upload"));
        $db::insert("permission", array("name" => "file_manager_mkdir"));
        $db::insert("permission", array("name" => "file_manager_mkfile"));
        $db::insert("permission", array("name" => "file_manager_rename"));
        $db::insert("permission", array("name" => "file_manager_chmod"));
        $db::insert("permission", array("name" => "file_manager_delete"));

        // Role Table
        $db::insert("role", array("name" => "administrator"));
        $db::insert("role", array("name" => "developer"));
        $db::insert("role", array("name" => "editor"));

        // Permission -> Role Table
        $db::insert("role_permission", array("role_id" => 1, "permission_id" =>  1));
        $db::insert("role_permission", array("role_id" => 1, "permission_id" =>  2));
        $db::insert("role_permission", array("role_id" => 1, "permission_id" =>  3));
        $db::insert("role_permission", array("role_id" => 1, "permission_id" =>  4));
        $db::insert("role_permission", array("role_id" => 1, "permission_id" =>  5));
        $db::insert("role_permission", array("role_id" => 1, "permission_id" =>  6));
        $db::insert("role_permission", array("role_id" => 1, "permission_id" =>  7));
        $db::insert("role_permission", array("role_id" => 1, "permission_id" =>  8));
        $db::insert("role_permission", array("role_id" => 1, "permission_id" =>  9));
        $db::insert("role_permission", array("role_id" => 1, "permission_id" => 10));
        $db::insert("role_permission", array("role_id" => 1, "permission_id" => 11));
        $db::insert("role_permission", array("role_id" => 1, "permission_id" => 12));
        $db::insert("role_permission", array("role_id" => 1, "permission_id" => 13));
        $db::insert("role_permission", array("role_id" => 1, "permission_id" => 14));
        $db::insert("role_permission", array("role_id" => 1, "permission_id" => 15));
        $db::insert("role_permission", array("role_id" => 1, "permission_id" => 16));
        $db::insert("role_permission", array("role_id" => 1, "permission_id" => 17));
        $db::insert("role_permission", array("role_id" => 1, "permission_id" => 18));
        $db::insert("role_permission", array("role_id" => 1, "permission_id" => 19));
        $db::insert("role_permission", array("role_id" => 1, "permission_id" => 20));
        $db::insert("role_permission", array("role_id" => 1, "permission_id" => 21));
        $db::insert("role_permission", array("role_id" => 1, "permission_id" => 22));
        $db::insert("role_permission", array("role_id" => 1, "permission_id" => 23));
        $db::insert("role_permission", array("role_id" => 1, "permission_id" => 24));
        $db::insert("role_permission", array("role_id" => 1, "permission_id" => 25));

        $db::insert("role_permission", array("role_id" => 2, "permission_id" =>  1));
        $db::insert("role_permission", array("role_id" => 2, "permission_id" =>  7));
        $db::insert("role_permission", array("role_id" => 2, "permission_id" =>  8));
        $db::insert("role_permission", array("role_id" => 2, "permission_id" =>  9));
        $db::insert("role_permission", array("role_id" => 2, "permission_id" => 10));
        $db::insert("role_permission", array("role_id" => 2, "permission_id" => 11));
        $db::insert("role_permission", array("role_id" => 2, "permission_id" => 12));
        $db::insert("role_permission", array("role_id" => 2, "permission_id" => 13));
        $db::insert("role_permission", array("role_id" => 2, "permission_id" => 14));
        $db::insert("role_permission", array("role_id" => 2, "permission_id" => 15));
        $db::insert("role_permission", array("role_id" => 2, "permission_id" => 16));
        $db::insert("role_permission", array("role_id" => 2, "permission_id" => 17));
        $db::insert("role_permission", array("role_id" => 2, "permission_id" => 18));
        $db::insert("role_permission", array("role_id" => 2, "permission_id" => 19));
        $db::insert("role_permission", array("role_id" => 2, "permission_id" => 20));
        $db::insert("role_permission", array("role_id" => 2, "permission_id" => 21));
        $db::insert("role_permission", array("role_id" => 2, "permission_id" => 22));
        $db::insert("role_permission", array("role_id" => 2, "permission_id" => 23));
        $db::insert("role_permission", array("role_id" => 2, "permission_id" => 24));
        $db::insert("role_permission", array("role_id" => 2, "permission_id" => 25));

        $db::insert("role_permission", array("role_id" => 3, "permission_id" =>  1));
        $db::insert("role_permission", array("role_id" => 3, "permission_id" => 15));
        $db::insert("role_permission", array("role_id" => 3, "permission_id" => 16));
        $db::insert("role_permission", array("role_id" => 3, "permission_id" => 17));
        $db::insert("role_permission", array("role_id" => 3, "permission_id" => 18));
        $db::insert("role_permission", array("role_id" => 3, "permission_id" => 19));
        $db::insert("role_permission", array("role_id" => 3, "permission_id" => 20));
        $db::insert("role_permission", array("role_id" => 3, "permission_id" => 21));
        $db::insert("role_permission", array("role_id" => 3, "permission_id" => 22));
        $db::insert("role_permission", array("role_id" => 3, "permission_id" => 23));
        $db::insert("role_permission", array("role_id" => 3, "permission_id" => 24));
        $db::insert("role_permission", array("role_id" => 3, "permission_id" => 25));

        // Layout Table
        $db::insert("layout", array(
            "name"          => "none",
            "content"       => base64_decode("PD9waHAgZWNobyAkdGhpcy0+Y29udGVudCgpOyA/Pg=="),
            "content_type"  => "text/html",
            "created_on"    => date("Y-m-d H:i:s", $time+=15),
            "created_by"    => 1
        ));

        $string = "PCFET0NUWVBFIGh0bWw+DQo8aHRtbCB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94aHRtbCI
        geG1sOmxhbmc9Ijw/cGhwIGVjaG8gREVGQVVMVF9MQU5HVUFHRTsgPz4iPg0KICAgIDxoZWFkPg0KICAgICAgICA8b
        WV0YSBjaGFyc2V0PSJ1dGYtOCIgLz4NCg0KICAgICAgICA8dGl0bGU+PD9waHAgZWNobyAkdGhpcy0+dGl0bGUoKTs
        gPz48L3RpdGxlPg0KDQogICAgICAgIDxtZXRhIG5hbWU9ImF1dGhvciIgY29udGVudD0iPD9waHAgZWNobyBVc2VyO
        jpmaW5kQnlJZCgxKS0+bmFtZTsgPz4iIC8+DQogICAgICAgIDxtZXRhIG5hbWU9ImtleXdvcmRzIiBjb250ZW50PSI
        8P3BocCBlY2hvIGdldF9rZXl3b3JkcygkdGhpcyk7ID8+IiAvPg0KICAgICAgICA8bWV0YSBuYW1lPSJkZXNjcmlwd
        GlvbiIgY29udGVudD0iPD9waHAgZWNobyBnZXRfZGVzY3JpcHRpb24oJHRoaXMpOyA/PiIgLz4NCiAgICAgICAgPG1
        ldGEgbmFtZT0icm9ib3RzIiBjb250ZW50PSJpbmRleCwgZm9sbG93IiAvPg0KDQogICAgICAgIDxsaW5rIHR5cGU9I
        nRleHQvY3NzIiByZWw9InN0eWxlc2hlZXQiIGhyZWY9Ijw/cGhwIGVjaG8gZ2V0X3RoZW1lX3BhdGgoIi9zY3JlZW4
        uY3NzIiwgImZveGJhc2ljIik7ID8+IiBtZWRpYT0ic2NyZWVuIiAvPg0KICAgICAgICA8bGluayB0eXBlPSJ0ZXh0L
        2NzcyIgcmVsPSJzdHlsZXNoZWV0IiBocmVmPSI8P3BocCBlY2hvIGdldF90aGVtZV9wYXRoKCIvcHJpbnQuY3NzIiw
        gImZveGJhc2ljIik7ID8+IiBtZWRpYT0icHJpbnQiIC8+DQogICAgICAgIDxsaW5rIHR5cGU9ImFwcGxpY2F0aW9uL
        3Jzcyt4bWwiIHJlbD0iYWx0ZXJuYXRlIiB0aXRsZT0iPD9waHAgZWNobyBnZXRfdGl0bGUoKTsgPz4gLSBSU1MgRmV
        lZCIgaHJlZj0iPD9waHAgZWNobyBnZXRfdXJsKCIvcnNzLnhtbCIpOyA/PiIgLz4NCiAgICA8L2hlYWQ+DQogICAgP
        GJvZHk+DQogICAgICAgIDxkaXYgY2xhc3M9ImhlYWRlciI+DQogICAgICAgICAgICA8ZGl2IGNsYXNzPSJjb250YWl
        uZXIiPg0KICAgICAgICAgICAgICAgIDxhIGhyZWY9Ijw/cGhwIGVjaG8gZ2V0X3VybCgpOyA/PiIgdGl0bGU9Ijw/c
        GhwIGVjaG8gZ2V0X3RpdGxlKCk7ID8+IiBjbGFzcz0iaGVhZGVyLXRpdGxlIj4NCiAgICAgICAgICAgICAgICAgICA
        gPD9waHAgZWNobyBTZXR0aW5nOjpnZXQoInNpdGUtdGl0bGUiKTsgPz4NCiAgICAgICAgICAgICAgICAgICAgPD9wa
        HAgaWYoKCRzdWJ0aXRsZSA9IFNldHRpbmc6OmdldCgic2l0ZS1zbG9nYW4iKSkgIT0gZmFsc2UpeyA/Pg0KICAgICA
        gICAgICAgICAgICAgICAgICAgPHNwYW4gY2xhc3M9ImhlYWRlci1zdWJ0aXRsZSI+PD9waHAgZWNobyAkc3VidGl0b
        GU7ID8+PC9zcGFuPg0KICAgICAgICAgICAgICAgICAgICA8P3BocCB9ID8+DQogICAgICAgICAgICAgICAgPC9hPg0
        KICAgICAgICAgICAgPC9kaXY+DQogICAgICAgIDwvZGl2Pg0KICAgICAgICANCiAgICAgICAgPGRpdiBjbGFzcz0ib
        mF2aWdhdGlvbiI+DQogICAgICAgICAgICA8ZGl2IGNsYXNzPSJjb250YWluZXIiPg0KICAgICAgICAgICAgICAgIDx
        1bCBjbGFzcz0ibmF2aSI+DQogICAgICAgICAgICAgICAgICAgICAgICA8bGkgY2xhc3M9Im5hdmktaXRlbSA8P3Boc
        CBlY2hvIHVybF9tYXRjaCgiLyIpPyAiY3VycmVudCI6ICJub25lIjsgPz4iPg0KICAgICAgICAgICAgICAgICAgICA
        gICAgPGEgaHJlZj0iPD9waHAgZWNobyBnZXRfdXJsKCk7ID8+Ij4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgI
        CBIb21lDQogICAgICAgICAgICAgICAgICAgICAgICA8L2E+DQogICAgICAgICAgICAgICAgICAgIDwvbGk+DQogICA
        gICAgICAgICAgICAgICAgIDw/cGhwIGZvcmVhY2goJHRoaXMtPmZpbmQoIi8iKS0+Y2hpbGRyZW4oKSBBUyAkaXRlb
        Sl7ID8+DQogICAgICAgICAgICAgICAgICAgICAgICA8bGkgY2xhc3M9Im5hdmktaXRlbSA8P3BocCBlY2hvIHVybF9
        tYXRjaCgkaXRlbS0+c2x1Zyk/ICJjdXJyZW50IjogIm5vbmUiOyA/PiI+DQogICAgICAgICAgICAgICAgICAgICAgI
        CAgICAgPGEgaHJlZj0iPD9waHAgZWNobyAkaXRlbS0+dXJsKCk7ID8+IiB0aXRsZT0iPD9waHAgZWNobyAkaXRlbS0
        +dGl0bGUoKTsgPz4iPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8P3BocCBlY2hvICRpdGVtLT50a
        XRsZSgpOyA/Pg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgIDwvYT4NCiAgICAgICAgICAgICAgICAgICAgICA
        gIDwvbGk+DQogICAgICAgICAgICAgICAgICAgIDw/cGhwIH0gPz4NCiAgICAgICAgICAgICAgICA8L3VsPg0KICAgI
        CAgICAgICAgPC9kaXY+DQogICAgICAgIDwvZGl2Pg0KICAgICAgICANCiAgICAgICAgPGRpdiBjbGFzcz0iY29udGV
        udCI+DQogICAgICAgICAgICA8ZGl2IGNsYXNzPSJjb250YWluZXIiPg0KICAgICAgICAgICAgICAgIDxkaXYgY2xhc
        3M9ImNvbnRlbnQtbWFpbiI+DQogICAgICAgICAgICAgICAgICAgIDxkaXYgY2xhc3M9ImNvbnRlbnQtcGFnZSI+DQo
        gICAgICAgICAgICAgICAgICAgICAgICA8ZGl2IGNsYXNzPSJwYWdlLXRpdGxlIj48c3Bhbj48P3BocCBlY2hvICR0a
        GlzLT50aXRsZSgpOyA/Pjwvc3Bhbj48L2Rpdj4NCiAgICAgICAgICAgICAgICAgICAgICAgIDxkaXYgY2xhc3M9InB
        hZ2UtY29udGVudCI+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgPD9waHANCiAgICAgICAgICAgICAgICAgI
        CAgICAgICAgICAgICAgZWNobyAkdGhpcy0+Y29udGVudCgpOw0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICA
        gICBpZigkdGhpcy0+aGFzQ29udGVudCgiZXh0ZW5kZWQiKSl7DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgI
        CAgICAgICBlY2hvICR0aGlzLT5jb250ZW50KCJleHRlbmRlZCIpOw0KICAgICAgICAgICAgICAgICAgICAgICAgICA
        gICAgICB9DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgPz4NCiAgICAgICAgICAgICAgICAgICAgICAgIDwvZ
        Gl2Pg0KICAgICAgICAgICAgICAgICAgICA8L2Rpdj4NCiAgICAgICAgICAgICAgICA8L2Rpdj4NCiAgICAgICAgICA
        gICAgICA8ZGl2IGNsYXNzPSJjb250ZW50LWFzaWRlIj4NCiAgICAgICAgICAgICAgICAgICAgPD9waHANCiAgICAgI
        CAgICAgICAgICAgICAgICAgIGlmKCR0aGlzLT5sZXZlbCgpID4gMCAmJiAoJHBhcmVudCA9ICR0aGlzLT5wYXJlbnQ
        oKSkgIT09IGZhbHNlKXsNCiAgICAgICAgICAgICAgICAgICAgICAgICAgICBpZigkcGFyZW50LT5jaGlsZHJlbkNvd
        W50KCkgPiAwICYmICRwYXJlbnQtPnNsdWcoKSAhPT0gImFydGljbGVzIil7DQogICAgICAgICAgICAgICAgICAgICA
        gICAgICAgICAgID8+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8ZGl2IGNsYXNzPSJzaWRlY
        mFyIj4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8aDM+PD9waHAgZWNobyAkcGFyZW5
        0LT50aXRsZSgpOyA/PiBNZW51PC9oMz4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8d
        WwgY2xhc3M9Imxpc3QtbWVudSI+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDw
        /cGhwIGZvcmVhY2goJHBhcmVudC0+Y2hpbGRyZW4oKSBBUyAkaXRlbSl7ID8+DQogICAgICAgICAgICAgICAgICAgI
        CAgICAgICAgICAgICAgICAgICAgICAgICAgICA8bGkgY2xhc3M9Imxpc3QtaXRlbSA8P3BocCBlY2hvIHVybF9tYXR
        jaCgkaXRlbS0+c2x1Zyk/ICJjdXJyZW50IjogIm5vbmUiOyA/PiI+DQogICAgICAgICAgICAgICAgICAgICAgICAgI
        CAgICAgICAgICAgICAgICAgICAgICAgICAgPGEgaHJlZj0iPD9waHAgZWNobyAkaXRlbS0+dXJsKCk7ID8+IiB0aXR
        sZT0iPD9waHAgZWNobyAkaXRlbS0+dGl0bGUoKTsgPz4iPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgI
        CAgICAgICAgICAgICAgICAgICAgICAgICA8P3BocCBlY2hvICRpdGVtLT50aXRsZSgpOyA/Pg0KICAgICAgICAgICA
        gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDwvYT4NCiAgICAgICAgICAgICAgICAgICAgI
        CAgICAgICAgICAgICAgICAgICAgICAgICAgIDwvbGk+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA
        gICAgICAgICAgIDw/cGhwIH0gPz4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8L3VsP
        g0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPC9kaXY+DQogICAgICAgICAgICAgICAgICAgICA
        gICAgICAgICAgIDw/cGhwDQogICAgICAgICAgICAgICAgICAgICAgICAgICAgfQ0KICAgICAgICAgICAgICAgICAgI
        CAgICAgfQ0KICAgICAgICAgICAgICAgICAgICAgICAgDQogICAgICAgICAgICAgICAgICAgICAgICBpZigkdGhpcy0
        +cGFydEV4aXN0cygic2lkZWJhciIsIHRydWUpKXsNCiAgICAgICAgICAgICAgICAgICAgICAgICAgICA/PjxkaXYgY
        2xhc3M9InNpZGViYXIiPjw/cGhwDQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGVjaG8gJHRoaXMtPmN
        vbnRlbnQoInNpZGViYXIiLCB0cnVlKTsNCiAgICAgICAgICAgICAgICAgICAgICAgICAgICA/PjwvZGl2Pjw/cGhwD
        QogICAgICAgICAgICAgICAgICAgICAgICB9DQogICAgICAgICAgICAgICAgICAgID8+DQogICAgICAgICAgICAgICA
        gPC9kaXY+DQogICAgICAgICAgICA8L2Rpdj4NCiAgICAgICAgPC9kaXY+DQoNCiAgICAgICAgPGRpdiBjbGFzcz0iZ
        m9vdGVyIj4NCiAgICAgICAgICAgIDxkaXYgY2xhc3M9ImNvbnRhaW5lciI+DQogICAgICAgICAgICAgICAgPGRpdiB
        jbGFzcz0iZm9vdGVyLWNvcHlyaWdodCI+DQogICAgICAgICAgICAgICAgICAgIDxkaXYgY2xhc3M9ImZvb3Rlci1sZ
        WZ0Ij4NCiAgICAgICAgICAgICAgICAgICAgICAgIENvcHlyaWdodCAmY29weTsgPD9waHAgZWNobyBkYXRlKCJZIik
        7ID8+IC0gPGEgaHJlZj0iPD9waHAgZWNobyBnZXRfdXJsKCk7ID8+IiB0aXRsZT0iPD9waHAgZWNobyBTZXR0aW5nO
        jpnZXQoInNpdGUtdGl0bGUiKTsgPz4iPjw/cGhwIGVjaG8gU2V0dGluZzo6Z2V0KCJzaXRlLXRpdGxlIik7ID8+PC9
        hPjxiciAvPg0KICAgICAgICAgICAgICAgICAgICAgICAgQWxsIHJpZ2h0cyByZXNlcnZlZCENCiAgICAgICAgICAgI
        CAgICAgICAgPC9kaXY+DQogICAgICAgICAgICAgICAgICAgIDxkaXYgY2xhc3M9ImZvb3Rlci1yaWdodCI+DQogICA
        gICAgICAgICAgICAgICAgICAgICBCdWlsZCB3aXRoIHRoZSA8YSBocmVmPSJodHRwczovL3d3dy5mb3hjbXMub3JnI
        iB0aXRsZT0iQnVpbGQgd2l0aCBGb3ggQ01TIj5Gb3ggQ01TIHYuPD9waHAgZWNobyBGT1hfVkVSU0lPTjsgPz48L2E
        +IChhIFdvbGYgQ01TIEZvcmspPGJyIC8+DQogICAgICAgICAgICAgICAgICAgICAgICBCYXNpYyBGb3ggRGVzaWduI
        HdyaXR0ZW4gYnkgdGhlIDxhIGhyZWY9Imh0dHBzOi8vd3d3LmZveGNtcy5vcmciIHRpdGxlPSJCdWlsZCB3aXRoIEZ
        veCBDTVMiPkZveCBDb3JlIFRlYW08L2E+DQogICAgICAgICAgICAgICAgICAgIDwvZGl2Pg0KICAgICAgICAgICAgI
        CAgIDwvZGl2Pg0KICAgICAgICAgICAgPC9kaXY+DQogICAgICAgIDwvZGl2Pg0KICAgIDwvYm9keT4NCjwvaHRtbD4=";
        $db::insert("layout", array(
            "name"          => "FoxBasic",
            "content"       => base64_decode($string),
            "content_type"  => "text/html",
            "created_on"    => date("Y-m-d H:i:s", $time+=15),
            "created_by"    => 1
        ));

        $string = "PCFET0NUWVBFIGh0bWw+DQo8aHRtbCB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94aHRtbCI
        geG1sOmxhbmc9Ijw/cGhwIGVjaG8gREVGQVVMVF9MQU5HVUFHRTsgPz4iPg0KICAgIDxoZWFkPg0KICAgICAgICA8b
        WV0YSBjaGFyc2V0PSJ1dGYtOCIgLz4NCg0KICAgICAgICA8dGl0bGU+PD9waHAgZWNobyAkdGhpcy0+dGl0bGUoKTs
        gPz48L3RpdGxlPg0KDQogICAgICAgIDxtZXRhIG5hbWU9ImF1dGhvciIgY29udGVudD0iPD9waHAgZWNobyBVc2VyO
        jpmaW5kQnlJZCgxKS0+bmFtZTsgPz4iIC8+DQogICAgICAgIDxtZXRhIG5hbWU9ImtleXdvcmRzIiBjb250ZW50PSI
        8P3BocCBlY2hvIGVtcHR5KCR0aGlzLT5rZXl3b3JkcygpKT8gU2V0dGluZzo6Z2V0KCJzaXRlLWtleXdvcmRzIik6I
        CR0aGlzLT5rZXl3b3JkcygpOyA/PiIgLz4NCiAgICAgICAgPG1ldGEgbmFtZT0iZGVzY3JpcHRpb24iIGNvbnRlbnQ
        9Ijw/cGhwIGVjaG8gZW1wdHkoJHRoaXMtPmRlc2NyaXB0aW9uKCkpPyBTZXR0aW5nOjpnZXQoInNpdGUtZGVzY3Jpc
        HRpb24iKTogJHRoaXMtPmRlc2NyaXB0aW9uKCk7ID8+IiAvPg0KICAgICAgICA8bWV0YSBuYW1lPSJyb2JvdHMiIGN
        vbnRlbnQ9ImluZGV4LCBmb2xsb3ciIC8+DQoNCiAgICAgICAgPGxpbmsgdHlwZT0idGV4dC9jc3MiIHJlbD0ic3R5b
        GVzaGVldCIgaHJlZj0iPD9waHAgZWNobyB0aGVtZV9wYXRoKCIvc2NyZWVuLmNzcyIsICJzaW1wbGUiKTsgPz4iIG1
        lZGlhPSJzY3JlZW4iIC8+DQogICAgICAgIDxsaW5rIHR5cGU9InRleHQvY3NzIiByZWw9InN0eWxlc2hlZXQiIGhyZ
        WY9Ijw/cGhwIGVjaG8gdGhlbWVfcGF0aCgiL3ByaW50LmNzcyIsICJzaW1wbGUiKTsgPz4iIG1lZGlhPSJwcmludCI
        gLz4NCiAgICAgICAgPGxpbmsgdHlwZT0iYXBwbGljYXRpb24vcnNzK3htbCIgcmVsPSJhbHRlcm5hdGUiIHRpdGxlP
        SI8P3BocCBlY2hvIGdldF90aXRsZSgpOyA/PiAtIFJTUyBGZWVkIiBocmVmPSI8P3BocCBlY2hvIGdldF91cmwoIi9
        yc3MueG1sIik7ID8+IiAvPg0KICAgIDwvaGVhZD4NCiAgICA8Ym9keT4NCiAgICAgICAgPGRpdiBpZD0icGFnZSI+D
        QogICAgICAgICAgICA8P3BocCAkdGhpcy0+aW5jbHVkZVNuaXBwZXQoImhlYWRlciIpOyA/Pg0KICAgICAgICAgICA
        gPGRpdiBpZD0iY29udGVudCI+DQogICAgICAgICAgICAgICAgPGgyPjw/cGhwIGVjaG8gJHRoaXMtPnRpdGxlKCk7I
        D8+PC9oMj4NCiAgICAgICAgICAgICAgICA8P3BocCANCiAgICAgICAgICAgICAgICAgICAgZWNobyAkdGhpcy0+Y29
        udGVudCgpOyANCiAgICAgICAgICAgICAgICAgICAgaWYoJHRoaXMtPmhhc0NvbnRlbnQoImV4dGVuZGVkIikpew0KI
        CAgICAgICAgICAgICAgICAgICAgICAgZWNobyAkdGhpcy0+Y29udGVudCgiZXh0ZW5kZWQiKTsNCiAgICAgICAgICA
        gICAgICAgICAgfQ0KICAgICAgICAgICAgICAgID8+DQogICAgICAgICAgICA8L2Rpdj4NCiAgICAgICAgICAgIDxka
        XYgaWQ9InNpZGViYXIiPg0KICAgICAgICAgICAgICAgIDw/cGhwIGVjaG8gJHRoaXMtPmNvbnRlbnQoInNpZGViYXI
        iLCB0cnVlKTsgPz4NCiAgICAgICAgICAgIDwvZGl2Pg0KICAgICAgICAgICAgPD9waHAgJHRoaXMtPmluY2x1ZGVTb
        mlwcGV0KCJmb290ZXIiKTsgPz4NCiAgICAgICAgPC9kaXY+DQogICAgPC9ib2R5Pg0KPC9odG1sPg==";
        $db::insert("layout", array(
            "name"          => "Simple",
            "content"       => base64_decode($string),
            "content_type"  => "text/html",
            "created_on"    => date("Y-m-d H:i:s", $time+=15),
            "created_by"    => 1
        ));
        $db::insert("layout", array(
            "name"          => "RSS Feed",
            "content"       => base64_decode("PD9waHAgZWNobyAkdGhpcy0+Y29udGVudCgpOyA/Pg=="),
            "content_type"  => "application/rss+xml",
            "created_on"    => date("Y-m-d H:i:s", $time+=15),
            "created_by"    => 1
        ));

        // Page Table
        $db::insert("page", array(
            "slug"          => "",
            "title"         => "Home Page",
            "breadcrumb"    => "Home Page",
            "position"      => 0,
            "parent_id"     => 0,
            "layout_id"     => 2,
            "status_id"     => 100,
            "is_protected"  => 1,
            "needs_login"   => 0,
            "created_on"    => date("Y-m-d H:i:s", $time+=15),
            "created_by"    => 1,
            "published_on"  => date("Y-m-d H:i:s", $time+=15),
            "published_by"  => 1
        ));
        $db::insert("page", array(
            "slug"          => "rss.xml",
            "title"         => "RSS Feed",
            "breadcrumb"    => "RSS Feed",
            "position"      => 2,
            "parent_id"     => 1,
            "layout_id"     => 4,
            "status_id"     => 101,
            "is_protected"  => 1,
            "needs_login"   => 0,
            "created_on"    => date("Y-m-d H:i:s", $time+=15),
            "created_by"    => 1,
            "published_on"  => date("Y-m-d H:i:s", $time+=15),
            "published_by"  => 1
        ));
        $db::insert("page", array(
            "slug"          => "about-us",
            "breadcrumb"    => "About Us",
            "title"         => "About Us",
            "position"      => 0,
            "parent_id"     => 1,
            "layout_id"     => 0,
            "status_id"     => 100,
            "is_protected"  => 0,
            "needs_login"   => 2,
            "created_on"    => date("Y-m-d H:i:s", $time+=15),
            "created_by"    => 1,
            "published_on"  => date("Y-m-d H:i:s", $time+=15),
            "published_by"  => 1
        ));
        $db::insert("page", array(
            "slug"          => "articles",
            "breadcrumb"    => "Articles",
            "title"         => "Articles",
            "position"      => 1,
            "parent_id"     => 1,
            "layout_id"     => 0,
            "status_id"     => 100,
            "behavior_id"   => "archive",
            "is_protected"  => 1,
            "needs_login"   => 2,
            "created_on"    => date("Y-m-d H:i:s", $time+=15),
            "created_by"    => 1,
            "published_on"  => date("Y-m-d H:i:s", $time+=15),
            "published_by"  => 1
        ));
        $db::insert("page", array(
            "slug"          => "my-first-article",
            "breadcrumb"    => "My first Article",
            "title"         => "My first Article",
            "position"      => 0,
            "parent_id"     => 4,
            "layout_id"     => 0,
            "status_id"     => 100,
            "is_protected"  => 0,
            "needs_login"   => 2,
            "created_on"    => date("Y-m-d H:i:s", $time+=15),
            "created_by"    => 1,
            "published_on"  => date("Y-m-d H:i:s", $time+=15),
            "published_by"  => 1
        ));
        $db::insert("page", array(
            "slug"          => "my-second-article",
            "breadcrumb"    => "My second Article",
            "title"         => "My second Article",
            "position"      => 1,
            "parent_id"     => 4,
            "layout_id"     => 0,
            "status_id"     => 100,
            "is_protected"  => 0,
            "needs_login"   => 2,
            "created_on"    => date("Y-m-d H:i:s", $time+=15),
            "created_by"    => 1,
            "published_on"  => date("Y-m-d H:i:s", $time+=15),
            "published_by"  => 1
        ));

        // Page-Part Table
        $string = "PD9waHANCiAgICAkYXJ0aWNsZSA9ICR0aGlzLT5maW5kKCIvYXJ0aWNsZXMvIik7DQogICAgaWYoJGF
        ydGljbGUtPmNoaWxkcmVuQ291bnQoKSA+IDApew0KICAgICAgICAkY2hpbGRyZW4gPSAkYXJ0aWNsZS0+Y2hpbGRyZ
        W4oYXJyYXkoIm9yZGVyIiA9PiAicGFnZS5jcmVhdGVkX29uIERFU0MiLCAibGltaXQiID0+IDUpKTsNCiAgICAgICA
        gZm9yZWFjaCgkY2hpbGRyZW4gQVMgJGNoaWxkKXsNCiAgICAgICAgICAgID8+DQogICAgICAgICAgICAgICAgPGRpd
        iBjbGFzcz0iPD9waHAgaXNzZXQoJGZpcnN0KT8gIiI6ICJmaXJzdCI7ID8+IGVudHJ5Ij4NCiAgICAgICAgICAgICA
        gICAgICAgPGgzPjw/cGhwIGVjaG8gJGNoaWxkLT5saW5rKCk7ID8+PC9oMz4NCiAgICAgICAgICAgICAgICAgICAgP
        D9waHAgDQogICAgICAgICAgICAgICAgICAgICAgICBlY2hvICRjaGlsZC0+Y29udGVudCgpOw0KICAgICAgICAgICA
        gICAgICAgICAgICAgaWYoJGNoaWxkLT5oYXNDb250ZW50KCJleHRlbmRlZCIpKXsNCiAgICAgICAgICAgICAgICAgI
        CAgICAgICAgICBlY2hvICRjaGlsZC0+bGluaygiQ29udGludWUgUmVhZGluZyDigKYiKTsNCiAgICAgICAgICAgICA
        gICAgICAgICAgIH0NCiAgICAgICAgICAgICAgICAgICAgPz4NCiAgICAgICAgICAgICAgICAgICAgPHAgY2xhc3M9I
        mluZm8iPg0KICAgICAgICAgICAgICAgICAgICAgICAgUG9zdGVkIGJ5IDw/cGhwIGVjaG8gJGNoaWxkLT5hdXRob3I
        oKTsgPz4gb24gPD9waHAgZWNobyAkY2hpbGQtPmRhdGUoKTsgPz4NCiAgICAgICAgICAgICAgICAgICAgPC9wPg0KI
        CAgICAgICAgICAgICAgIDwvZGl2Pg0KICAgICAgICAgICAgPD9waHANCiAgICAgICAgICAgICRmaXJzdCA9IHRydWU
        7DQogICAgICAgIH0NCiAgICB9DQo/Pg==";
        $db::insert("page_part", array(
            "name"          => "body",
            "page_id"       => 1,
            "filter_id"     => "",
            "content"       => base64_decode($string),
            "content_html"  => base64_decode($string)
        ));

        $string = "PD9waHAgZWNobyAnPD8nOyA/PnhtbCB2ZXJzaW9uPSIxLjAiIGVuY29kaW5nPSI8P3BocCBlY2hvIER
        FRkFVTFRfQ0hBUlNFVDsgPz4iPD9waHAgZWNobyAnPz4nOyA/Pg0KPHJzcyB2ZXJzaW9uPSIyLjAiIHhtbG5zOmF0b
        209Imh0dHA6Ly93d3cudzMub3JnLzIwMDUvQXRvbSI+DQogICAgPGNoYW5uZWw+DQogICAgICAgIDx0aXRsZT48P3B
        ocCBlY2hvIGdldF90aXRsZSgpOyA/PiAtIFJTUyBGZWVkPC90aXRsZT4NCiAgICAgICAgPGxpbms+PD9waHAgZWNob
        yBnZXRfdXJsKCk7ID8+PC9saW5rPg0KICAgICAgICA8YXRvbTpsaW5rIGhyZWY9Ijw/cGhwIGVjaG8gZ2V0X3VybCg
        pID8+cnNzLnhtbCIgcmVsPSJzZWxmIiB0eXBlPSJhcHBsaWNhdGlvbi9yc3MreG1sIiAvPg0KICAgICAgICA8bGFuZ
        3VhZ2U+PD9waHAgZWNobyBERUZBVUxUX0xBTkdVQUdFOyA/PjwvbGFuZ3VhZ2U+DQogICAgICAgIDxjb3B5cmlnaHQ
        +PD9waHAgZWNobyBkYXRlKCJZIik7ID8+IDw/cGhwIGVjaG8gZ2V0X3VybCgpOyA/PjwvY29weXJpZ2h0Pg0KICAgI
        CAgICA8cHViRGF0ZT48P3BocCBlY2hvIGRhdGUoInIiKTsgPz48L3B1YkRhdGU+DQogICAgICAgIDxsYXN0QnVpbGR
        EYXRlPjw/cGhwIGVjaG8gZGF0ZSgiciIpOyA/PjwvbGFzdEJ1aWxkRGF0ZT4NCiAgICAgICAgPGNhdGVnb3J5PmFue
        TwvY2F0ZWdvcnk+DQogICAgICAgIDxnZW5lcmF0b3I+Rm94IENNUyA8P3BocCBlY2hvIEZPWF9WRVJTSU9OID8+PC9
        nZW5lcmF0b3I+DQogICAgICAgIDxkZXNjcmlwdGlvbj5UaGUgbWFpbiBuZXdzIGZlZWQgZnJvbSB0aGUgRm94IENNU
        y48L2Rlc2NyaXB0aW9uPg0KICAgICAgICA8ZG9jcz5odHRwOi8vd3d3LnJzc2JvYXJkLm9yZy9yc3Mtc3BlY2lmaWN
        hdGlvbjwvZG9jcz4NCiAgICAgICAgPD9waHANCiAgICAgICAgICAgIGlmKCRhcnRpY2xlcyA9ICR0aGlzLT5maW5kK
        CIvYXJ0aWNsZXMiKSl7DQogICAgICAgICAgICAgICAgZm9yZWFjaCgkYXJ0aWNsZXMtPmNoaWxkcmVuKGFycmF5KCJ
        vcmRlciIgPT4gInBhZ2UuY3JlYXRlZF9vbiBERVNDIiwgImxpbWl0IiA9PiAxMCkpIEFTICRjaGlsZCl7DQogICAgI
        CAgICAgICAgICAgICAgID8+DQogICAgICAgICAgICAgICAgICAgICAgICA8aXRlbT4NCiAgICAgICAgICAgICAgICA
        gICAgICAgICAgICA8dGl0bGU+PD9waHAgZWNobyAkY2hpbGQtPnRpdGxlKCk7ID8+PC90aXRsZT4NCiAgICAgICAgI
        CAgICAgICAgICAgICAgICAgICA8ZGVzY3JpcHRpb24+PD9waHAgDQogICAgICAgICAgICAgICAgICAgICAgICAgICA
        gICAgIGlmKCRjaGlsZC0+aGFzQ29udGVudCgic3VtbWFyeSIpKXsNCiAgICAgICAgICAgICAgICAgICAgICAgICAgI
        CAgICAgICAgIGVjaG8gc3RyaXBfdGFncygkY2hpbGQtPmNvbnRlbnQoInN1bW1hcnkiKSk7DQogICAgICAgICAgICA
        gICAgICAgICAgICAgICAgICAgIH0gZWxzZSB7DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBlY
        2hvIHN0cmlwX3RhZ3MoJGNoaWxkLT5jb250ZW50KCkpOw0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB
        9DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgPz48L2Rlc2NyaXB0aW9uPg0KICAgICAgICAgICAgICAgICAgI
        CAgICAgICAgIDxwdWJEYXRlPjw/cGhwIGVjaG8gJGNoaWxkLT5kYXRldGltZSgiciIpOyA/PjwvcHViRGF0ZT4NCiA
        gICAgICAgICAgICAgICAgICAgICAgICAgICA8bGluaz48P3BocCBlY2hvICRjaGlsZC0+dXJsKCk7ID8+PC9saW5rP
        g0KICAgICAgICAgICAgICAgICAgICAgICAgICAgIDxndWlkPjw/cGhwIGVjaG8gJGNoaWxkLT5wYXRoKCk7ID8+PC9
        ndWlkPg0KICAgICAgICAgICAgICAgICAgICAgICAgPC9pdGVtPg0KICAgICAgICAgICAgICAgICAgICA8P3BocA0KI
        CAgICAgICAgICAgICAgIH0NCiAgICAgICAgICAgIH0NCiAgICAgICAgPz4NCiAgICA8L2NoYW5uZWw+DQo8L3Jzcz4=";
        $db::insert("page_part", array(
            "name"          => "body",
            "page_id"       => 2,
            "filter_id"     => "",
            "content"       => base64_decode($string),
            "content_html"  => base64_decode($string),
        ));

        $db::insert("page_part", array(
            "name"          => "body",
            "page_id"       => 3,
            "filter_id"     => "textile",
            "content"       => "This is my site. I live in this city ... I do some nice things, like this and that ...",
            "content_html"  => "<p>This is my site. I live in this city &#8230; I do some nice things, like this and that &#8230;</p>",
        ));

        $string = "PD9waHAgDQogICAgZm9yZWFjaCgkdGhpcy0+Y2hpbGRyZW4oYXJyYXkoIm9yZGVyIiA9PiAicGFnZS5
        jcmVhdGVkX29uIERFU0MiLCAibGltaXQiID0+IDUpKSBBUyAkY2hpbGQpew0KICAgICAgICA/Pg0KICAgICAgICAgI
        CAgPGRpdiBjbGFzcz0iZW50cnkiPg0KICAgICAgICAgICAgICAgIDxoMz48P3BocCBlY2hvICRjaGlsZC0+bGluayg
        pOyA/PjwvaDM+DQogICAgICAgICAgICAgICAgPD9waHAgZWNobyAkY2hpbGQtPmNvbnRlbnQoKTsgPz4NCiAgICAgI
        CAgICAgICAgICA8cCBjbGFzcz0iaW5mbyI+DQogICAgICAgICAgICAgICAgICAgIFBvc3RlZCBieSA8P3BocCBlY2h
        vICRjaGlsZC0+YXV0aG9yKCk7ID8+IG9uIDw/cGhwIGVjaG8gJGNoaWxkLT5kYXRlKCk7ID8+PGJyIC8+DQogICAgI
        CAgICAgICAgICAgICAgIFRhZ3M6IDw/cGhwIGVjaG8gaW1wbG9kZSgiLCAiLCAkY2hpbGQtPnRhZ3MoKSk7ID8+DQo
        gICAgICAgICAgICAgICAgPC9wPg0KICAgICAgICAgICAgPC9kaXY+DQogICAgICAgIDw/cGhwDQogICAgfQ0KPz4=";
        $db::insert("page_part", array(
            "name"          => "body",
            "page_id"       => 4,
            "filter_id"     => "",
            "content"       => base64_decode($string),
            "content_html"  => base64_decode($string),
        ));

        $db::insert("page_part", array(
            "name"          => "body",
            "page_id"       => 5,
            "filter_id"     => "markdown",
            "content"       => "My **first** test of my first article that uses *Markdown*.",
            "content_html"  => "<p>My <strong>first</strong> test of my first article that uses <em>Markdown</em>.</p>",
        ));

        $db::insert("page_part", array(
            "name"          => "body",
            "page_id"       => 6,
            "filter_id"     => "markdown",
            "content"       => "This is my second article.",
            "content_html"  => "<p>This is my second article.</p>",
        ));

        $string = "PGgzPkFib3V0IE1lPC9oMz4NCjxwPg0KICAgIEknbSBqdXN0IGEgZGVtb25zdHJhdGlvbiBvZiBob3c
        gZWFzeSBpdCBpcyB0byB1c2UgV29sZiBDTVMgdG8gcG93ZXIgYSBibG9nLg0KICAgIDxhIGhyZWY9Ijw/cGhwIGVja
        G8gZ2V0X3VybCgiL2Fib3V0LXVzIik7ID8+Ij5SZWFkbW9yZS4uLjwvYT4NCjwvcD4NCg0KPGgzPkZhdm9yaXRlIFN
        pdGVzPC9oMz4NCjx1bD4NCiAgICA8bGk+PGEgaHJlZj0iaHR0cHM6Ly93d3cuZm94Y21zLm9yZyIgdGl0bGU9IkZve
        CBDTVMiPkZveCBDTVM8L2E+PC9saT4NCjwvdWw+DQoNCjw/cGhwIGlmKHVybF9tYXRjaCgiLyIpKXsgPz4NCiAgICA
        8aDM+UmVjZW50IEVudHJpZXM8L2gzPg0KICAgIDx1bD4NCiAgICAgICAgPD9waHAgZm9yZWFjaCgkdGhpcy0+ZmluZ
        CgiL2FydGljbGVzIiktPmNoaWxkcmVuKGFycmF5KCJvcmRlciIgPT4gInBhZ2UuY3JlYXRlZF9vbiBERVNDIiwgImx
        pbWl0IiA9PiAxMCkpIEFTICRjaGlsZCl7ID8+DQogICAgICAgICAgICA8bGk+PD9waHAgZWNobyAkY2hpbGQtPmxpb
        msoKTsgPz48L2xpPg0KICAgICAgICA8P3BocCB9ID8+DQogICAgPC91bD4NCiAgICA8cD48YSBocmVmPSI8P3BocCB
        lY2hvIGdldF91cmwoIi9hcnRpY2xlcyIpOyA/PiI+QXJjaGl2ZXM8L2E+PC9wPg0KPD9waHAgfSA/Pg0KDQo8aDM+U
        3luZGljYXRlPC9oMz4NCjxwPjxhIGhyZWY9Ijw/cGhwIGVjaG8gZ2V0X3VybCgiL3Jzcy54bWwiKTsgPz4iPlJTUyB
        GZWVkPC9hPjwvcD4=";
        $db::insert("page_part", array(
            "name"          => "sidebar",
            "page_id"       => 1,
            "filter_id"     => "",
            "content"       => base64_decode($string),
            "content_html"  => base64_decode($string),
        ));

        $string = "PD9waHAgJGFyY2hpdmVzID0gJHRoaXMtPmZpbmQoImFydGljbGVzIiktPmFyY2hpdmUtPmFyY2hpdmV
        zQnlNb250aCgpOyA/Pg0KPGgzPkFyY2hpdmVzIEJ5IE1vbnRoPC9oMz4NCjx1bD4NCiAgICA8P3BocCBmb3JlYWNoK
        CRhcmNoaXZlcyBBUyAkZGF0ZSl7ID8+DQogICAgICAgIDxsaT48YSBocmVmPSI8P3BocCBlY2hvICR0aGlzLT51cmw
        oZmFsc2UpIC4iL3skZGF0ZX0iIC4gVVJMX1NVRkZJWDsgPz4iPg0KICAgICAgICAgICAgPD9waHAgZWNobyBzdHJmd
        GltZSgiJUIgJVkiLCBzdHJ0b3RpbWUoc3RydHIoJGRhdGUsICIvIiwgIi0iKSkpOz8+DQogICAgICAgIDwvYT48L2x
        pPg0KICAgIDw/cGhwIH0gPz4NCjwvdWw+";
        $db::insert("page_part", array(
            "name"          => "sidebar",
            "page_id"       => 4,
            "filter_id"     => "",
            "content"       => base64_decode($string),
            "content_html"  => base64_decode($string),
        ));

        // Snippet Table
        $string = "PGRpdiBpZD0iaGVhZGVyIj4NCiAgICA8aDE+DQogICAgICAgIDxhIGhyZWY9Ijw/cGhwIGVjaG8gZ2V
        0X3VybCgpOyA/PiI+PD9waHAgZWNobyBnZXRfdGl0bGUoKTsgPz48L2E+DQogICAgICAgIDxzcGFuPmNvbnRlbnQgb
        WFuYWdlbWVudCBzaW1wbGlmaWVkPC9zcGFuPg0KICAgIDwvaDE+DQogICAgPGRpdiBpZD0ibmF2Ij4NCiAgICAgICA
        gPHVsPg0KICAgICAgICAgICAgPGxpPjxhIGhyZWY9Ijw/cGhwIGVjaG8gZ2V0X3VybCgpOyA/PiIgY2xhc3M9Ijw/c
        GhwIGVjaG8gdXJsX21hdGNoKCIvIik/ICJjdXJyZW50IjogIiI7ID8+Ij5Ib21lPC9hPjwvbGk+DQogICAgICAgICA
        gICA8P3BocCBmb3JlYWNoKCR0aGlzLT5maW5kKCIvIiktPmNoaWxkcmVuKCkgQVMgJG1lbnUpeyA/Pg0KICAgICAgI
        CAgICAgICAgIDxsaT4NCiAgICAgICAgICAgICAgICAgICAgPD9waHAgZWNobyAkbWVudS0+bGluaygkbWVudS0+dGl
        0bGUsIHVybF9tYXRjaCgkbWVudS0+c2x1Zyk/ICdjbGFzcz0iY3VycmVudCInOiBOVUxMKTsgPz4NCiAgICAgICAgI
        CAgICAgICA8L2xpPg0KICAgICAgICAgICAgPD9waHAgfSA/Pg0KICAgICAgICA8L3VsPg0KICAgIDwvZGl2Pg0KPC9
        kaXY+";
        $db::insert("snippet", array(
            "name"          => "header",
            "filter_id"     => "",
            "content"       => base64_decode($string),
            "content_html"  => base64_decode($string),
            "created_on"    => date("Y-m-d H:i:s"),
            "created_by"    => 1
        ));

        $string = "PGRpdiBpZD0iZm9vdGVyIj4NCiAgICA8ZGl2IGlkPSJmb290ZXItaW5uZXIiPg0KICAgICAgICA8cD4
        NCiAgICAgICAgICAgIENvcHlyaWdodCAmY29weTsgPD9waHAgZWNobyBkYXRlKCJZIik7ID8+IDxhIGhyZWY9Ijw/c
        GhwIGVjaG8gZ2V0X3VybCgpOyA/PiIgdGl0bGU9Ijw/cGhwIGVjaG8gZ2V0X3RpdGxlKCk7ID8+Ij48P3BocCBlY2h
        vIGdldF90aXRsZSgpOyA/PjwvYT48YnIgLz4NCiAgICAgICAgICAgIDxhIGhyZWY9Imh0dHBzOi8vd3d3LmZveGNtc
        y5vcmciIHRpdGxlPSJCdWlsZCB3aXRoIEZveCBDTVMiPkZveCBDTVMgPD9waHAgZWNobyBGT1hfVkVSU0lPTjsgPz4
        8YT4gSW5zaWRlLg0KICAgICAgICA8L3A+DQogICAgPC9kaXY+DQo8L2Rpdj4=";
        $db::insert("snippet", array(
            "name"          => "footer",
            "filter_id"     => "",
            "content"       => base64_decode($string),
            "content_html"  => base64_decode($string),
            "created_on"    => date("Y-m-d H:i:s"),
            "created_by"    => 1
        ));
    }

    /*
     |  UPGRADE Data
     |  @since  0.8.4
     */
    function wizard_data_upgrade($db){
        // Future Stuff :3
    }

    /*
     |  MIGRATE DATA
     |  @since  0.8.4
     */
    function wizard_data_migrate($pdo){
        $prefix = TABLE_PREFIX;

        // Get old Configurations
        $data = array();
        $unknown = array();
        foreach($pdo->query("SELECT name, value FROM {$prefix}setting;") AS $conf){
            $keys = array(
                "admin_title"       => "site-title",
                "admin_email"       => "site-email",
                "language"          => "default-language",
                "theme"             => "backend-theme",
                "default_status_id" => "default-status",
                "default_filter_id" => "default-filter",
                "default_tab"       => "default-tab",
                "allow_html_title"  => "default-allow-html",
                "plugins"           => "site-plugins"
            );
            if(array_key_exists($conf["name"], $keys)){
                $data[$keys[$conf["name"]]] = $conf["value"];
            } else {
                $unknown[$conf["name"]] = $conf["value"];
            }
        }

        // Config Table
        $stmt = $pdo->prepare("INSERT INTO {$prefix}config (name, value, type) VALUES (:n, :v, :g);");
        $stmt->execute(array(":n" => "fox-version", ":v" => FOX_VERSION, ":g" => "fox"));
        $stmt->execute(array(":n" => "fox-status", ":v" => FOX_STATUS, ":g" => "fox"));
        $stmt->execute(array(":n" => "cms-version", ":v" => CMS_VERSION, ":g" => "fox"));
        $stmt->execute(array(":n" => "site-title", ":v" => $data['site-title'], ":g" => "core"));
        $stmt->execute(array(":n" => "site-slogan", ":v" => "My awesome Fox CMS based Website!", ":g" => "core"));
        $stmt->execute(array(":n" => "site-email", ":v" => $data['site-email'], ":g" => "core"));
        $stmt->execute(array(":n" => "site-keywords", ":v" => "foxcms, content, management, simplified", ":g" => "core"));
        $stmt->execute(array(":n" => "site-description", ":v" => "A small description about your FoxCMS website.", ":g" => "core"));
        $stmt->execute(array(":n" => "site-plugins", ":v" => $data["site-plugins"], ":g" => "core"));
        $stmt->execute(array(":n" => "site-cronjob", ":v" => 1, ":g" => "core"));
        $stmt->execute(array(":n" => "default-language", ":v" => $data["default-language"], ":g" => "core"));
        $stmt->execute(array(":n" => "default-status", ":v" => $data["default-status"], ":g" => "core"));
        $stmt->execute(array(":n" => "default-filter", ":v" => $data["default-filter"], ":g" => "core"));
        $stmt->execute(array(":n" => "default-allow-html", ":v" => $data["default-allow-html"], ":g" => "core"));
        $stmt->execute(array(":n" => "default-tab", ":v" => $data["default-tab"], ":g" => "core"));
        $stmt->execute(array(":n" => "backend-theme", ":v" => "fox", ":g" => "core"));

        // Merge Unknown Configuration
        if(!empty($unknown)){
            $stmt = $pdo->prepare("INSERT INTO {$prefix}config (name, value, type) VALUES (:n, :v, 'unknown');");
            foreach($unknown AS $key => $value){
                $stmt->execute(array(":n" => $key, ":v" => $value));
            }
        }

        // Merge Plugin Configuration
        $query = $pdo->query("SELECT name, value, plugin_id AS 'type' FROM {$prefix}plugin_settings;");
        if($query){
            $stmt = $pdo->prepare("INSERT INTO {$prefix}config (name, value, type) VALUES (:n, :v, :g);");
            foreach($query AS $conf){
                $stmt->execute(array(":n" => $conf["name"], ":v" => $conf["value"], ":g" => $conf["type"] . "_plugin"));
            }
        }

        // Drop old tables
        $pdo->exec("DROP TABLE {$prefix}cron;");
        $pdo->exec("DROP TABLE {$prefix}secure_token;");
        $pdo->exec("DROP TABLE {$prefix}setting;");
        $pdo->exec("DROP TABLE {$prefix}plugin_settings;");
    }
