<?php

/*
    HCSF - A multilingual CMS and Shopsystem
    Copyright (C) 2014  Marcus Haase - mail@marcus.haase.name

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/*
14.9.2009
- moved update-queries to buildUpdateQuery()
- filtered input for all select queries
*/

//error_reporting(E_ALL);
/*
$P = array(
'head_scripts' => '<script type="text/javascript" src="/jquery.js"></script>
<script type="text/javascript" src="/_admin/_tinymce/tinymce.min.js"></script>
<script type="text/javascript">
tinymce.init({
selector: "textarea",
language : "de",
content_css: "/screen-global.css",
theme : "modern",
plugins: [
"advlist autolink link image lists charmap print preview hr anchor pagebreak spellchecker",
"searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking",
"save table contextmenu directionality emoticons template paste textcolor"
],
templates : [
{
title: "2-Spaltige Tabelle 50/50",
url: "/_admin/_tinymce/templates/table5050.html",
description: "2-Spaltige Tabelle 50/50"
}
]
});
</script>',
);
*/

require_once __DIR__.'/../../app/init.php';
require_once __DIR__.'/../../src/functions.admin.pages.php';

$P = new \HaaseIT\HCSF\CorePage($C, $sLang);
$P->cb_pagetype = 'content';
$P->cb_subnav = 'admin';
$P->cb_customcontenttemplate = 'pageadmin';

if (isset($_REQUEST["action"]) && $_REQUEST["action"] == 'insert_lang') {
    $aPage = admin_getPage($_REQUEST["page_id"], $DB, $sLang);

    if (isset($aPage["base"]) && !isset($aPage["text"])) {
        $aData = array(
            'cl_cb' => $aPage["base"]["cb_id"],
            'cl_lang' => $sLang,
        );
        //HaaseIT\Tools::debug($aData);
        $sQ = \HaaseIT\DBTools::buildInsertQuery($aData, 'content_lang');
        //HaaseIT\Tools::debug($sQ);
        $DB->exec($sQ);
        header('Location: '.$_SERVER["PHP_SELF"]."?page_id=".$_REQUEST["page_id"].'&action=edit');
        die();
    }
    //HaaseIT\Tools::debug($aItemdata);
}

if (!isset($_GET["action"])) {
    $P->cb_customdata["pageselect"] = showPageselect($DB, $C);
} elseif (($_GET["action"] == 'edit' || $_GET["action"] == 'delete') && isset($_REQUEST["page_id"]) && $_REQUEST["page_id"] != '') {
    if ($_GET["action"] == 'delete' && isset($_POST["delete"]) && $_POST["delete"] == 'do') {
        // delete and put message in customdata
        // delete children
        $sQ = "DELETE FROM content_lang WHERE cl_cb = '".\filter_var($_GET["page_id"], FILTER_SANITIZE_NUMBER_INT)."'";
        $DB->exec($sQ);

        // then delete base row
        $sQ = "DELETE FROM content_base WHERE cb_id = '".\filter_var($_GET["page_id"], FILTER_SANITIZE_NUMBER_INT)."'";
        $DB->exec($sQ);

        $P->cb_customdata["deleted"] = true;
    } else {
        if (admin_getPage($_REQUEST["page_id"], $DB, $sLang)) {
            if (isset($_REQUEST["action_a"]) && $_REQUEST["action_a"] == 'true') $P->cb_customdata["updated"] = updatePage($DB, $sLang);
            $P->cb_customdata["page"] = admin_getPage($_REQUEST["page_id"], $DB, $sLang);
            $P->cb_customdata["page"]["admin_page_types"] = $C["admin_page_types"];
            $P->cb_customdata["page"]["admin_page_groups"] = $C["admin_page_groups"];
            $aOptions = array('');
            foreach ($C["navstruct"] as $sKey => $aValue) {
                if ($sKey == 'admin') {
                    continue;
                }
                $aOptions[] = $sKey;
            }
            $P->cb_customdata["page"]["subnavarea_options"] = $aOptions;
            unset($aOptions);
        }
    }
} elseif ($_GET["action"] == 'addpage') {
    $aErr = array();
    if (isset($_POST["addpage"]) && $_POST["addpage"] == 'do') {
        if (mb_substr($_POST["pagekey"], 0, 2) == '/_') {
            $aErr["reservedpath"] = true;
        } elseif (strlen($_POST["pagekey"]) < 4) {
            $aErr["keytooshort"] = true;
        } else {
            $sQ = "SELECT cb_key FROM content_base WHERE cb_key = '";
            $sQ .= \trim(\filter_input(INPUT_POST, 'pagekey', FILTER_SANITIZE_SPECIAL_CHARS))."'";
            $hResult = $DB->query($sQ);
            $iRows = $hResult->rowCount();
            if ($iRows > 0) {
                $aErr["keyalreadyinuse"] = true;
            } else {
                $aData = array('cb_key' => trim(\filter_input(INPUT_POST, 'pagekey', FILTER_SANITIZE_SPECIAL_CHARS)),);
                $sQ = \HaaseIT\DBTools::buildInsertQuery($aData, 'content_base');
                //HaaseIT\Tools::debug($sQ);
                $hResult = $DB->exec($sQ);
                $iInsertID = $DB->lastInsertId();
                $sQ = "SELECT cb_id FROM content_base WHERE cb_id = '".$iInsertID."'";
                $hResult = $DB->query($sQ);
                $aRow = $hResult->fetch();
                header('Location: '.$_SERVER["PHP_SELF"].'?page_id='.$aRow["cb_id"].'&action=edit');
            }
        }
        $P->cb_customdata["err"] = $aErr;
        unset($aErr);
    }
    $P->cb_customdata["showaddform"] = true;
}

$aP = generatePage($C, $P, $sLang, $DB, $oItem);

echo $twig->render($C["template_base"], $aP);
