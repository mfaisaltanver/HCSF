<?php

require_once __DIR__.'/../src/functions.admin.pages.php';

$P = new \HaaseIT\HCSF\CorePage($C, $sLang);
$P->cb_pagetype = 'content';
$P->cb_subnav = 'admin';
$P->cb_customcontenttemplate = 'pageadmin';

function showPageselect($DB, $C) {
    $sQ = "SELECT * FROM content_base ORDER BY cb_key";
    $hResult = $DB->query($sQ);
    foreach ($C["admin_page_groups"] as $sValue) {
        $TMP = explode('|', $sValue);
        $aGroupkeys[] = $TMP[0];
    }
    unset($TMP);

    while ($aResult = $hResult->fetch()) {
        $bGrouped = false;
        foreach ($aGroupkeys as $sValue) {
            if ($aResult["cb_group"] == $sValue) {
                $aTree[$sValue][] = $aResult;
                $bGrouped = true;
            }
        }
        if (!$bGrouped) $aTree["_"][] = $aResult;
    }

    foreach ($C["admin_page_groups"] as $sValue) {
        $TMP = explode('|', $sValue);
        if (isset ($aTree[$TMP[0]]) && count($aTree[$TMP[0]]) >= 1) {
            $aOptions_g[] = $TMP[0].'|'.$TMP[1];
        }
    }
    unset($TMP);

    $aSData = array(
        'options_groups' => isset($aOptions_g) ? $aOptions_g : array(),
        'tree' => isset($aTree) ? $aTree : array(),
    );

    return $aSData;
}

// adding language to page here
if (isset($_REQUEST["action"]) && $_REQUEST["action"] == 'insert_lang') {
    $Ptoinsertlang = new \HaaseIT\HCSF\UserPage($C, $sLang, $DB, $_REQUEST["page_key"], true);

    if ($Ptoinsertlang->cb_id != NULL && $Ptoinsertlang->oPayload->cl_id == NULL) {
        $Ptoinsertlang->oPayload->insert($Ptoinsertlang->cb_id);
        header('Location: '.$_SERVER["PHP_SELF"]."?page_key=".$Ptoinsertlang->cb_key.'&action=edit');
        die();
    } else {
        die('Could not insert language data.');
    }
}

if (!isset($_GET["action"])) {
    $P->cb_customdata["pageselect"] = showPageselect($DB, $C);
} elseif (($_GET["action"] == 'edit' || $_GET["action"] == 'delete') && isset($_REQUEST["page_key"]) && $_REQUEST["page_key"] != '') {
    if ($_GET["action"] == 'delete' && isset($_POST["delete"]) && $_POST["delete"] == 'do') {
        // delete and put message in customdata
        $Ptodelete = new \HaaseIT\HCSF\UserPage($C, $sLang, $DB, $_GET["page_key"], true);
        if ($Ptodelete->cb_id != NULL) {
            $Ptodelete->remove();
        } else {
            die('Page to delete not found error.');
        }
        $P->cb_customdata["deleted"] = true;
    } else { // edit or update page
        if (isset($_REQUEST["page_key"]) && $Ptoedit = new \HaaseIT\HCSF\UserPage($C, $sLang, $DB, $_REQUEST["page_key"], true)) {
            if (isset($_REQUEST["action_a"]) && $_REQUEST["action_a"] == 'true') {

                $Ptoedit->cb_pagetype = $_POST['page_type'];
                $Ptoedit->cb_group = $_POST['page_group'];
                $Ptoedit->cb_pageconfig = $_POST['page_config'];
                $Ptoedit->cb_subnav = $_POST['page_subnav'];
                $bBaseupdated = $Ptoedit->write();

                if ($Ptoedit->oPayload->cl_id != NULL) {
                    $Ptoedit->oPayload->cl_html = $_POST['page_html'];
                    $Ptoedit->oPayload->cl_title = $_POST['page_title'];
                    $Ptoedit->oPayload->cl_description = $_POST['page_description'];
                    $Ptoedit->oPayload->cl_keywords = $_POST['page_keywords'];
                    $bPayloadupdated = $Ptoedit->oPayload->write();
                }

                $P->cb_customdata["updated"] = true;
            }
            $P->cb_customdata["page"] = $Ptoedit;
            $P->cb_customdata["admin_page_types"] = $C["admin_page_types"];
            $P->cb_customdata["admin_page_groups"] = $C["admin_page_groups"];
            $aOptions = array('');
            foreach ($C["navstruct"] as $sKey => $aValue) {
                if ($sKey == 'admin') {
                    continue;
                }
                $aOptions[] = $sKey;
            }
            $P->cb_customdata["subnavarea_options"] = $aOptions;
            unset($aOptions);
        } else {
            die('Page selected not found error.');
        }
    }
} elseif ($_GET["action"] == 'addpage') {
    $aErr = array();
    if (isset($_POST["addpage"]) && $_POST["addpage"] == 'do') {
        $sPagekeytoadd = \trim(\filter_input(INPUT_POST, 'pagekey', FILTER_SANITIZE_SPECIAL_CHARS));

        if (mb_substr($sPagekeytoadd, 0, 2) == '/_') {
            $aErr["reservedpath"] = true;
        } elseif (strlen($sPagekeytoadd) < 4) {
            $aErr["keytooshort"] = true;
        } else {
            $Ptoadd = new \HaaseIT\HCSF\UserPage($C, $sLang, $DB, $sPagekeytoadd, true);
            if ($Ptoadd->cb_id == NULL) {
                if ($Ptoadd->insert($sPagekeytoadd)) {
                    header('Location: '.$_SERVER["PHP_SELF"].'?page_key='.$sPagekeytoadd.'&action=edit');
                    die();
                } else {
                    die('Could not insert error.');
                }
            } else {
                $aErr["keyalreadyinuse"] = true;
            }
        }
        $P->cb_customdata["err"] = $aErr;
        unset($aErr);
    }
    $P->cb_customdata["showaddform"] = true;
}