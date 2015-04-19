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

$P = array(
    'base' => array(
        'cb_pagetype' => 'content',
        'cb_pageconfig' => '',
        'cb_subnav' => 'admin',
        'cb_customcontenttemplate' => 'pageadmin',
    ),
    'lang' => array(
        'cl_lang' => $sLang,
        'cl_html' => '',
    ),
);

if (isset($_REQUEST["action"]) && $_REQUEST["action"] == 'insert_lang') {
    $aPage = admin_getPage($_REQUEST["page_id"], $DB, $sLang);

    if (isset($aPage["base"]) && !isset($aPage["text"])) {
        $aData = array(
            DB_CONTENTTABLE_LANG_PARENTPKEY => $aPage["base"][DB_CONTENTTABLE_BASE_PKEY],
            DB_CONTENTFIELD_LANG => $sLang,
        );
        //HaaseIT\Tools::debug($aData);
        $sQ = \HaaseIT\DBTools::buildInsertQuery($aData, DB_CONTENTTABLE_LANG);
        //HaaseIT\Tools::debug($sQ);
        $DB->exec($sQ);
        header('Location: '.$_SERVER["PHP_SELF"]."?page_id=".$_REQUEST["page_id"].'&action=edit');
        die();
    }
    //HaaseIT\Tools::debug($aItemdata);
}

if (!isset($_REQUEST["action"])) {
    $P["base"]["cb_customdata"]["pageselect"] = showPageselect($DB, $C);
} elseif ($_REQUEST["action"] == 'edit' && isset($_REQUEST["page_id"]) && $_REQUEST["page_id"] != '') {
    if (admin_getPage($_REQUEST["page_id"], $DB, $sLang)) {
        if (isset($_REQUEST["action_a"]) && $_REQUEST["action_a"] == 'true') $P["base"]["cb_customdata"]["updated"] = updatePage($DB, $sLang);
        $P["base"]["cb_customdata"]["page"] = admin_getPage($_REQUEST["page_id"], $DB, $sLang);
        $P["base"]["cb_customdata"]["page"]["admin_page_types"] = $C["admin_page_types"];
        $P["base"]["cb_customdata"]["page"]["admin_page_groups"] = $C["admin_page_groups"];
        $aOptions = array('');
        foreach ($C["navstruct"] as $sKey => $aValue) $aOptions[] = $sKey;
        $P["base"]["cb_customdata"]["page"]["subnavarea_options"] = $aOptions;
        unset($aOptions);
    }
} elseif ($_REQUEST["action"] == 'addpage') {
    $aErr = array();
    if (isset($_POST["addpage"]) && $_POST["addpage"] == 'do') {
        if (mb_substr($_POST["pagekey"], 0, 2) == '/_') {
            $aErr["reservedpath"] = true;
        } elseif (strlen($_POST["pagekey"]) < 4) {
            $aErr["keytooshort"] = true;
        } else {
            $sQ = "SELECT ".DB_CONTENTFIELD_BASE_KEY." FROM ".DB_CONTENTTABLE_BASE." WHERE ".DB_CONTENTFIELD_BASE_KEY." = '";
            $sQ .= \trim(\filter_input(INPUT_POST, 'pagekey', FILTER_SANITIZE_SPECIAL_CHARS))."'";
            $hResult = $DB->query($sQ);
            $iRows = $hResult->rowCount();
            if ($iRows > 0) {
                $aErr["keyalreadyinuse"] = true;
            } else {
                $aData = array(DB_CONTENTFIELD_BASE_KEY => trim(\filter_input(INPUT_POST, 'pagekey', FILTER_SANITIZE_SPECIAL_CHARS)),);
                $sQ = \HaaseIT\DBTools::buildInsertQuery($aData, DB_CONTENTTABLE_BASE);
                //HaaseIT\Tools::debug($sQ);
                $hResult = $DB->exec($sQ);
                $iInsertID = $DB->lastInsertId();
                $sQ = "SELECT ".DB_CONTENTTABLE_BASE_PKEY." FROM ".DB_CONTENTTABLE_BASE." WHERE ".DB_CONTENTTABLE_BASE_PKEY." = '".$iInsertID."'";
                $hResult = $DB->query($sQ);
                $aRow = $hResult->fetch();
                header('Location: '.$_SERVER["PHP_SELF"].'?page_id='.$aRow[DB_CONTENTTABLE_BASE_PKEY].'&action=edit');
            }
        }
        $P["base"]["cb_customdata"]["err"] = $aErr;
        unset($aErr);
    }
    $P["base"]["cb_customdata"]["showaddform"] = true;
}

$aP = generatePage($C, $P, $sLang, $DB, $oItem);

echo $twig->render($C["template_base"], $aP);
