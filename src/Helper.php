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

namespace HaaseIT\HCSF;


class Helper
{
    public static function getSignedGlideURL($file, $width = 0, $height =0)
    {
        $urlBuilder = \League\Glide\Urls\UrlBuilderFactory::create('', GLIDE_SIGNATURE_KEY);

        if ($width == 0 && $height == 0) return false;
        if ($width != 0) $param['w'] = $width;
        if ($height != 0) $param['h'] = $height;
        if ($width != 0 && $height != 0) $param['fit'] = 'stretch';

        return $urlBuilder->getUrl($file, $param);
    }

    public static function mailWrapper($C, $to, $subject = '(No subject)', $message = '', $aImagesToEmbed = [], $aFilesToAttach = []) {
        $mail = new \PHPMailer;
        $mail->CharSet = 'UTF-8';

        $mail->isMail();
        if ($C['mail_method'] == 'sendmail') {
            $mail->isSendmail();
        } elseif ($C['mail_method'] == 'smtp') {
            $mail->isSMTP();
            $mail->Host = $C['mail_smtp_server'];
            $mail->Port = $C['mail_smtp_port'];
            if ($C['mail_smtp_auth'] == true) {
                $mail->SMTPAuth = true;
                $mail->Username = $C['mail_smtp_auth_user'];
                $mail->Password = $C['mail_smtp_auth_pwd'];
                if ($C['mail_smtp_secure']) {
                    $mail->SMTPSecure = 'tls';
                    if ($C['mail_smtp_secure_method'] == 'ssl') {
                        $mail->SMTPSecure = 'ssl';
                    }
                }
            }
        }

        $mail->From = $C["email_sender"];
        $mail->FromName = $C["email_sendername"];
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $message;

        if (is_array($aImagesToEmbed) && count($aImagesToEmbed)) {
            foreach ($aImagesToEmbed as $sKey => $imgdata) {
                $imginfo = getimagesizefromstring($imgdata['binimg']);
                $mail->AddStringEmbeddedImage($imgdata['binimg'], $sKey, $sKey, 'base64', $imginfo['mime']);
            }
        }

        if (is_array($aFilesToAttach) && count($aFilesToAttach)) {
            foreach ($aFilesToAttach as $sValue) {
                if (file_exists($sValue)) {
                    $mail->AddAttachment($sValue);
                }
            }
        }

        //$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
        return $mail->send();
    }

    // don't remove this, this is the fallback for unavailable twig functions
    public static function reachThrough($string) {
        return $string;
    }

    public static function generatePage($container, $P, $sLang, $oItem, $requesturi)
    {
        $aP = [
            'language' => $sLang,
            'pageconfig' => $P->cb_pageconfig,
            'pagetype' => $P->cb_pagetype,
            'subnavkey' => $P->cb_subnav,
            'requesturi' => $requesturi,
            'requesturiarray' => parse_url($requesturi),
            'locale_format_date' => $container['conf']['locale_format_date'],
            'locale_format_date_time' => $container['conf']['locale_format_date_time'],
            'maintenancemode' => $container['conf']['maintenancemode'],
            'numberformat_decimals' => $container['conf']['numberformat_decimals'],
            'numberformat_decimal_point' => $container['conf']['numberformat_decimal_point'],
            'numberformat_thousands_seperator' => $container['conf']['numberformat_thousands_seperator'],
        ];
        if ($container['conf']["enable_module_customer"]) {
            $aP["isloggedin"] = \HaaseIT\HCSF\Customer\Helper::getUserData();
            $aP["enable_module_customer"] = true;
        }
        if ($container['conf']["enable_module_shop"]) {
            $aP["currency"] = $container['conf']["waehrungssymbol"];
            $aP["orderamounts"] = $container['conf']["orderamounts"];
            if (isset($container['conf']["vat"]["full"])) $aP["vatfull"] = $container['conf']["vat"]["full"];
            if (isset($container['conf']["vat"]["reduced"])) $aP["vatreduced"] = $container['conf']["vat"]["reduced"];
            if (isset($container['conf']["custom_order_fields"])) $aP["custom_order_fields"] = $container['conf']["custom_order_fields"];
            $aP["enable_module_shop"] = true;
        }
        if (isset($P->cb_key)) $aP["path"] = pathinfo($P->cb_key);
        else $aP["path"] = pathinfo($aP["requesturi"]);
        if ($P->cb_customcontenttemplate != NULL) $aP["customcontenttemplate"] = $P->cb_customcontenttemplate;
        if ($P->cb_customdata != NULL) $aP["customdata"] = $P->cb_customdata;
        if (isset($_SERVER["HTTP_REFERER"])) $aP["referer"] = $_SERVER["HTTP_REFERER"];

        // if there is no subnav defined but there is a default subnav defined, use it
        // subnavkey can be used in the templates to find out, where we are
        if ((!isset($aP["subnavkey"]) || $aP["subnavkey"] == '') && $container['conf']["subnav_default"] != '') {
            $aP["subnavkey"] = $container['conf']["subnav_default"];
            $P->cb_subnav = $container['conf']["subnav_default"];
        }
        if ($P->cb_subnav != NULL && isset($container['navstruct'][$P->cb_subnav])) $aP["subnav"] = $container['navstruct'][$P->cb_subnav];

        // Get page title, meta-keywords, meta-description
        $aP["pagetitle"] = $P->oPayload->getTitle();
        $aP["keywords"] = $P->oPayload->cl_keywords;
        $aP["description"] = $P->oPayload->cl_description;

        // TODO: Add head scripts to DB
        //if (isset($P["head_scripts"]) && $P["head_scripts"] != '') $aP["head_scripts"] = $P["head_scripts"];

        // Language selector
        // TODO: move content of langselector out of php script
        if (count($container['conf']["lang_available"]) > 1) {
            $aP["langselector"] = self::getLangSelector($container['conf'], $sLang);
        }

        // Shopping cart infos
        if ($container['conf']["enable_module_shop"]) {
            $aP["cartinfo"] = \HaaseIT\HCSF\Shop\Helper::getShoppingcartData($container['conf']);
        }

        $aP["countrylist"][] = ' | ';
        foreach ($container['conf']["countries_".$sLang] as $sKey => $sValue) {
            $aP["countrylist"][] = $sKey.'|'.$sValue;
        }

        if ($container['conf']["enable_module_shop"] && ($aP["pagetype"] == 'itemoverview' || $aP["pagetype"] == 'itemoverviewgrpd' || $aP["pagetype"] == 'itemdetail')) {
            $aP = \HaaseIT\HCSF\Shop\Helper::handleItemPage($container['conf'], $oItem, $P, $aP);
        }

        $aP["content"] = $P->oPayload->cl_html;

        $aP["content"] = str_replace("@", "&#064;", $aP["content"]); // Change @ to HTML Entity -> maybe less spam mails

        if ($container['conf']['debug']) {
            self::getDebug($aP, $P);
        }

        $aP["debugdata"] = \HaaseIT\Tools::$sDebug;

        return $aP;
    }

    private static function getDebug($aP, $P)
    {
        if (!empty($_POST)) {
            \HaaseIT\Tools::debug($_POST, '$_POST');
        } elseif (!empty($_REQUEST)) {
            \HaaseIT\Tools::debug($_REQUEST, '$_REQUEST');
        }
        if (!empty($_SESSION)) {
            \HaaseIT\Tools::debug($_SESSION, '$_SESSION');
        }
        \HaaseIT\Tools::debug($aP, '$aP');
        \HaaseIT\Tools::debug($P, '$P');
    }

    private static function getLangSelector($C, $sLang)
    {
        $sLangselector = '';
        if ($C["lang_detection_method"] == 'domain') {
            $aSessionGetVarsForLangSelector = [];
            if (session_status() == PHP_SESSION_ACTIVE) {
                $aSessionGetVarsForLangSelector[session_name()] = session_id();
            }
            $aRequestURL = parse_url($_SERVER["REQUEST_URI"]);
        }
        foreach ($C["lang_available"] as $sKey => $sValue) {
            if ($sLang != $sKey) {
                if ($C["lang_detection_method"] == 'domain') {
                    $sLangselector .= '<a href="//www.' . $C["lang_by_domain"][$sKey] . $aRequestURL["path"] . \HaaseIT\Tools::makeLinkHRefWithAddedGetVars('', $aSessionGetVarsForLangSelector) . '">' . \HaaseIT\Textcat::T("misc_language_" . $sKey) . '</a> ';
                } else {
                    $sLangselector .= '<a href="' . \HaaseIT\Tools::makeLinkHRefWithAddedGetVars('', ['language' => $sKey]) . '">' . \HaaseIT\Textcat::T("misc_language_" . $sKey) . '</a> ';
                }
            }
        }
        $sLangselector = \HaaseIT\Tools::cutStringend($sLangselector, 1);

        return $sLangselector;
    }

    public static function getLanguage($C)
    {
        if ($C["lang_detection_method"] == 'domain' && isset($C["lang_by_domain"]) && is_array($C["lang_by_domain"])) { // domain based language detection
            foreach ($C["lang_by_domain"] as $sKey => $sValue) {
                if ($_SERVER["SERVER_NAME"] == $sValue || $_SERVER["SERVER_NAME"] == 'www.'.$sValue) {
                    $sLang = $sKey;
                    break;
                }
            }
        } elseif ($C["lang_detection_method"] == 'legacy') { // legacy language detection
            $sLang = key($C["lang_available"]);
            if (isset($_GET["language"]) && array_key_exists($_GET["language"], $C["lang_available"])) {
                $sLang = strtolower($_GET["language"]);
                setcookie('language', strtolower($_GET["language"]), 0, '/');
            } elseif (isset($_COOKIE["language"]) && array_key_exists($_COOKIE["language"], $C["lang_available"])) {
                $sLang = strtolower($_COOKIE["language"]);
            } elseif (isset($_SERVER["HTTP_ACCEPT_LANGUAGE"]) && array_key_exists(substr($_SERVER["HTTP_ACCEPT_LANGUAGE"], 0, 2), $C["lang_available"])) {
                $sLang = substr($_SERVER["HTTP_ACCEPT_LANGUAGE"], 0, 2);
            }
        }
        if (!isset($sLang)) {
            $sLang = key($C["lang_available"]);
        }

        return $sLang;
    }

    public static function getPurifier($C, $purpose)
    {
        $purifier_config = \HTMLPurifier_Config::createDefault();
        $purifier_config->set('Core.Encoding', 'UTF-8');
        $purifier_config->set('Cache.SerializerPath', PATH_PURIFIERCACHE);
        $purifier_config->set('HTML.Doctype', $C['purifier_doctype']);

        if ($purpose == 'textcat') {
            $configkey = 'textcat';
        } elseif ($purpose == 'page') {
            $configkey = 'pagetext';
        } elseif ($purpose == 'item') {
            $configkey = 'itemtext';
        } elseif ($purpose == 'itemgroup') {
            $configkey = 'itemgrouptext';
        } else {
            return false;
        }

        if (isset($C[$configkey.'_unsafe_html_whitelist']) && trim($C[$configkey.'_unsafe_html_whitelist']) != '') {
            $purifier_config->set('HTML.Allowed', $C[$configkey.'_unsafe_html_whitelist']);
        }
        if (isset($C[$configkey.'_loose_filtering']) && $C[$configkey.'_loose_filtering']) {
            $purifier_config->set('HTML.Trusted', true);
            $purifier_config->set('Attr.EnableID', true);
            $purifier_config->set('Attr.AllowedFrameTargets', ['_blank', '_self', '_parent', '_top']);
        }

        return new \HTMLPurifier($purifier_config);
    }
}