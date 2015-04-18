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

if (getUserData()) {
    $P = array(
        'base' => array(
            'cb_pagetype' => 'content',
            'cb_pageconfig' => '',
            'cb_subnav' => '',
        ),
        'lang' => array(
            'cl_lang' => $sLang,
            'cl_html' => \HaaseIT\Textcat::T("denied_default"),
        ),
    );
} else {
    function handleResetpasswordPage($C, $sLang, $DB) {
        $P = array(
            'base' => array(
                'cb_pagetype' => 'content',
                'cb_pageconfig' => '',
                'cb_subnav' => '',
            ),
            'lang' => array(
                'cl_lang' => $sLang,
                'cl_html' => '',
            ),
        );

        if (!isset($_GET["key"]) || !isset($_GET["email"]) || trim($_GET["key"]) == '' || trim($_GET["email"]) == '' || !\filter_var($_GET["email"], FILTER_VALIDATE_EMAIL)) {
            $P["lang"]["cl_html"] = \HaaseIT\Textcat::T("denied_default");
        } else {
            $sQ = "SELECT * FROM ".DB_CUSTOMERTABLE." WHERE ".DB_CUSTOMERFIELD_EMAIL." = :email AND ".DB_CUSTOMERFIELD_PWRESETCODE." = :pwresetcode AND ".DB_CUSTOMERFIELD_PWRESETCODE." != ''";
            $hResult = $DB->prepare($sQ);
            $hResult->bindValue(':email', $_GET["email"], PDO::PARAM_STR);
            $hResult->bindValue(':pwresetcode', $_GET["key"], PDO::PARAM_STR);
            $hResult->execute();
            if ($hResult->rowCount() != 1) {
                $P["lang"]["cl_html"] = \HaaseIT\Textcat::T("denied_default");
            } else {
                $aErr = array();
                $aResult = $hResult->fetch();
                $iTimestamp = time();
                if ($aResult[DB_CUSTOMERFIELD_PWRESETTIMESTAMP] < $iTimestamp - DAY) {
                    $P["lang"]["cl_html"] = \HaaseIT\Textcat::T("pwreset_error_expired");
                } else {
                    $P["base"]["cb_customcontenttemplate"] = 'customer/resetpassword';
                    $P["base"]["cb_customdata"]["pwreset"]["minpwlength"] = $C["minimum_length_password"];
                    if (isset($_POST["doSend"]) && $_POST["doSend"] == 'yes') {
                        $aErr = handlePasswordReset($DB, $C, $aErr, $aResult[DB_CUSTOMERTABLE_PKEY]);
                        if (count($aErr) == 0) {
                            $P["base"]["cb_customdata"]["pwreset"]["showsuccessmessage"] = true;
                        } else {
                            $P["base"]["cb_customdata"]["pwreset"]["errors"] = $aErr;
                        }
                    }
                }
            }
        }

        return $P;
    }

    $P = handleResetpasswordPage($C, $sLang, $DB);
}