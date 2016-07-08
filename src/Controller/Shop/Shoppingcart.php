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

namespace HaaseIT\HCSF\Controller\Shop;

class Shoppingcart extends Base
{
    public function preparePage()
    {
        $this->P = new \HaaseIT\HCSF\CorePage($this->container);
        $this->P->cb_pagetype = 'contentnosubnav';

        if ($this->container['conf']["show_pricesonlytologgedin"] && !\HaaseIT\HCSF\Customer\Helper::getUserData()) {
            $this->P->oPayload->cl_html = \HaaseIT\Textcat::T("denied_notloggedin");
        } else {
            $this->P->cb_customcontenttemplate = 'shop/shoppingcart';
            $this->P->oPayload->cl_html = '';

            // ----------------------------------------------------------------------------
            // Check if there is a message to display above the shoppingcart
            // ----------------------------------------------------------------------------
            $this->P->oPayload->cl_html = $this->getNotification();

            // ----------------------------------------------------------------------------
            // Display the shoppingcart
            // ----------------------------------------------------------------------------
            if (isset($_SESSION["cart"]) && count($_SESSION["cart"]) >= 1) {
                $aErr = [];
                if (isset($_POST["doCheckout"]) && $_POST["doCheckout"] == 'yes') {
                    $aErr = \HaaseIT\HCSF\Customer\Helper::validateCustomerForm($this->container['conf'], $this->container['lang'], $aErr, true);
                    if (!\HaaseIT\HCSF\Customer\Helper::getUserData() && (!isset($_POST["tos"]) || $_POST["tos"] != 'y')) {
                        $aErr["tos"] = true;
                    }
                    if (!\HaaseIT\HCSF\Customer\Helper::getUserData() && (!isset($_POST["cancellationdisclaimer"]) || $_POST["cancellationdisclaimer"] != 'y')) {
                        $aErr["cancellationdisclaimer"] = true;
                    }
                    if (!isset($_POST["paymentmethod"]) || array_search($_POST["paymentmethod"], $this->container['conf']["paymentmethods"]) === false) {
                        $aErr["paymentmethod"] = true;
                    }
                }
                $aShoppingcart = \HaaseIT\HCSF\Shop\Helper::buildShoppingCartTable($_SESSION["cart"], $this->container, false, '', $aErr);
            }

            // ----------------------------------------------------------------------------
            // Checkout
            // ----------------------------------------------------------------------------
            if (!isset($aShoppingcart)) {
                $this->P->oPayload->cl_html .= \HaaseIT\Textcat::T("shoppingcart_empty");
            } else {
                if (isset($_POST["doCheckout"]) && $_POST["doCheckout"] == 'yes') {
                    if (count($aErr) == 0) {
                        $this->doCheckout();
                    }
                } // endif $_POST["doCheckout"] == 'yes'
            }

            if (isset($aShoppingcart)) {
                $this->P->cb_customdata = $aShoppingcart;
            }
        }
    }

    private function getItemImage($aV)
    {
        // base64 encode img and prepare for db
        // image/png image/jpeg image/gif
        // data:{mimetype};base64,XXXX

        $aImagesToSend = [];
        $base64Img = false;
        $binImg = false;

        if ($this->container['conf']['email_orderconfirmation_embed_itemimages_method'] == 'glide') {
            $sPathToImage = '/'.$this->container['conf']['directory_images'].'/'.$this->container['conf']['directory_images_items'].'/';
            $sImageroot = PATH_BASEDIR . $this->container['conf']['directory_glide_master'];

            if (
                is_file($sImageroot.substr($sPathToImage.$aV["img"], strlen($this->container['conf']['directory_images']) + 1))
                && $aImgInfo = getimagesize($sImageroot.substr($sPathToImage.$aV["img"], strlen($this->container['conf']['directory_images']) + 1))
            ) {
                $glideserver = \League\Glide\ServerFactory::create([
                    'source' => $sImageroot,
                    'cache' => PATH_GLIDECACHE,
                    'max_image_size' => $this->container['conf']['glide_max_imagesize'],
                ]);
                $glideserver->setBaseUrl('/' . $this->container['conf']['directory_images'] . '/');
                $base64Img = $glideserver->getImageAsBase64($sPathToImage.$aV["img"], $this->container['conf']['email_orderconfirmation_embed_itemimages_glideparams']);
                $TMP = explode(',', $base64Img);
                $binImg = base64_decode($TMP[1]);
                unset($TMP);
            }
        } else {
            $sPathToImage = PATH_DOCROOT.$this->container['conf']['directory_images'].'/'.$this->container['conf']['directory_images_items'].'/'.$this->container['conf']['directory_images_items_email'].'/';
            if ($aImgInfo = getimagesize($sPathToImage.$aV["img"])) {
                $binImg = file_get_contents($sPathToImage.$aV["img"]);
                $base64Img = 'data:' . $aImgInfo["mime"] . ';base64,';
                $base64Img .= base64_encode($binImg);
            }
        }
        if (isset($this->container['conf']["email_orderconfirmation_embed_itemimages"]) && $this->container['conf']["email_orderconfirmation_embed_itemimages"]) {
            $aImagesToSend['binimg'] = $binImg;
        }
        if ($base64Img) {
            $aImagesToSend['base64img'] = $base64Img;
        }
        return $aImagesToSend;
    }

    private function prepareDataOrder()
    {
        return [
            'o_custno' => filter_var(trim(\HaaseIT\Tools::getFormfield("custno")), FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW),
            'o_email' => filter_var(trim(\HaaseIT\Tools::getFormfield("email")), FILTER_SANITIZE_EMAIL),
            'o_corpname' => filter_var(trim(\HaaseIT\Tools::getFormfield("corpname")), FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW),
            'o_name' => filter_var(trim(\HaaseIT\Tools::getFormfield("name")), FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW),
            'o_street' => filter_var(trim(\HaaseIT\Tools::getFormfield("street")), FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW),
            'o_zip' => filter_var(trim(\HaaseIT\Tools::getFormfield("zip")), FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW),
            'o_town' => filter_var(trim(\HaaseIT\Tools::getFormfield("town")), FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW),
            'o_phone' => filter_var(trim(\HaaseIT\Tools::getFormfield("phone")), FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW),
            'o_cellphone' => filter_var(trim(\HaaseIT\Tools::getFormfield("cellphone")), FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW),
            'o_fax' => filter_var(trim(\HaaseIT\Tools::getFormfield("fax")), FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW),
            'o_country' => filter_var(trim(\HaaseIT\Tools::getFormfield("country")), FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW),
            'o_group' => trim(\HaaseIT\HCSF\Customer\Helper::getUserData('cust_group')),
            'o_remarks' => filter_var(trim(\HaaseIT\Tools::getFormfield("remarks")), FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW),
            'o_tos' => ((isset($_POST["tos"]) && $_POST["tos"] == 'y' || \HaaseIT\HCSF\Customer\Helper::getUserData()) ? 'y' : 'n'),
            'o_cancellationdisclaimer' => ((isset($_POST["cancellationdisclaimer"]) && $_POST["cancellationdisclaimer"] == 'y' || \HaaseIT\HCSF\Customer\Helper::getUserData()) ? 'y' : 'n'),
            'o_paymentmethod' => filter_var(trim(\HaaseIT\Tools::getFormfield("paymentmethod")), FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW),
            'o_sumvoll' => $_SESSION["cartpricesums"]["sumvoll"],
            'o_sumerm' => $_SESSION["cartpricesums"]["sumerm"],
            'o_sumnettoall' => $_SESSION["cartpricesums"]["sumnettoall"],
            'o_taxvoll' => $_SESSION["cartpricesums"]["taxvoll"],
            'o_taxerm' => $_SESSION["cartpricesums"]["taxerm"],
            'o_sumbruttoall' => $_SESSION["cartpricesums"]["sumbruttoall"],
            'o_mindermenge' => (isset($_SESSION["cartpricesums"]["mindergebuehr"]) ? $_SESSION["cartpricesums"]["mindergebuehr"] : ''),
            'o_shippingcost' => \HaaseIT\HCSF\Shop\Helper::getShippingcost($this->container),
            'o_orderdate' => date("Y-m-d"),
            'o_ordertimestamp' => time(),
            'o_authed' => ((\HaaseIT\HCSF\Customer\Helper::getUserData()) ? 'y' : 'n'),
            'o_sessiondata' => serialize($_SESSION),
            'o_postdata' => serialize($_POST),
            'o_remote_address' => $_SERVER["REMOTE_ADDR"],
            'o_ordercompleted' => 'n',
            'o_paymentcompleted' => 'n',
            'o_srv_hostname' => $_SERVER["SERVER_NAME"],
            'o_vatfull' => $this->container['conf']["vat"]["full"],
            'o_vatreduced' => $this->container['conf']["vat"]["reduced"],
        ];
    }

    private function doCheckout()
    {
        if (empty($_SESSION["cart"])) {
            return false;
        }
        try {
            $this->container['db']->beginTransaction();

            $aDataOrder = $this->prepareDataOrder();
            $sql = \HaaseIT\DBTools::buildPSInsertQuery($aDataOrder, 'orders');
            $hResult = $this->container['db']->prepare($sql);
            foreach ($aDataOrder as $sKey => $sValue) {
                $hResult->bindValue(':' . $sKey, $sValue);
            }
            $hResult->execute();
            $iInsertID = $this->container['db']->lastInsertId();

            $aDataOrderItems = [];
            $aImagesToSend = [];
            foreach ($_SESSION["cart"] as $sK => $aV) {

                $aImagesToSend[$aV["img"]] = $this->getItemImage($aV);

                $aDataOrderItems[] = [
                    'oi_o_id' => $iInsertID,
                    'oi_cartkey' => $sK,
                    'oi_amount' => $aV["amount"],
                    'oi_price_netto_list' => $aV["price"]["netto_list"],
                    'oi_price_netto_use' => $aV["price"]["netto_use"],
                    'oi_price_brutto_use' => $aV["price"]["brutto_use"],
                    'oi_price_netto_sale' => isset($aV["price"]["netto_sale"]) ? $aV["price"]["netto_sale"] : '',
                    'oi_price_netto_rebated' => isset($aV["price"]["netto_rebated"]) ? $aV["price"]["netto_rebated"] : '',
                    'oi_vat' => $this->container['conf']["vat"][$aV["vat"]],
                    'oi_rg' => $aV["rg"],
                    'oi_rg_rebate' => isset($this->container['conf']["rebate_groups"][$aV["rg"]][trim(\HaaseIT\HCSF\Customer\Helper::getUserData('cust_group'))]) ? $this->container['conf']["rebate_groups"][$aV["rg"]][trim(\HaaseIT\HCSF\Customer\Helper::getUserData('cust_group'))] : '',
                    'oi_itemname' => $aV["name"],
                    'oi_img' => $aImagesToSend[$aV["img"]]['base64img'],
                ];
            }
            foreach ($aDataOrderItems as $aV) {
                $sql = \HaaseIT\DBTools::buildPSInsertQuery($aV, 'orders_items');
                $hResult = $this->container['db']->prepare($sql);
                foreach ($aV as $sKey => $sValue) {
                    $hResult->bindValue(':' . $sKey, $sValue);
                }
                $hResult->execute();
            }
            $this->container['db']->commit();
        } catch (Exception $e) {
            // If something raised an exception in our transaction block of statements,
            // roll back any work performed in the transaction
            print '<p>Unable to complete transaction!</p>';
            print $e;
            $this->container['db']->rollBack();
        }
        $sMailbody_us = $this->buildOrderMailBody(false, $iInsertID);
        $sMailbody_they = $this->buildOrderMailBody(true, $iInsertID);

        // write to file
        $this->writeCheckoutToFile($sMailbody_us);

        // Send Mails
        $this->sendCheckoutMails($iInsertID, $sMailbody_us, $sMailbody_they, $aImagesToSend);

        if (isset($_SESSION["cart"])) unset($_SESSION["cart"]);
        if (isset($_SESSION["cartpricesums"])) unset($_SESSION["cartpricesums"]);
        if (isset($_SESSION["sondercart"])) unset($_SESSION["sondercart"]);

        if (isset($_POST["paymentmethod"]) && $_POST["paymentmethod"] == 'paypal' && array_search('paypal', $this->container['conf']["paymentmethods"]) !== false && isset($this->container['conf']["paypal_interactive"]) && $this->container['conf']["paypal_interactive"]) {
            header('Location: /_misc/paypal.html?id=' . $iInsertID);
        } elseif (isset($_POST["paymentmethod"]) && $_POST["paymentmethod"] == 'sofortueberweisung' && array_search('sofortueberweisung', $this->container['conf']["paymentmethods"]) !== false) {
            header('Location: /_misc/sofortueberweisung.html?id=' . $iInsertID);
        } else {
            header('Location: /_misc/checkedout.html?id=' . $iInsertID);
        }
        die();
    }

    private function sendCheckoutMails($iInsertID, $sMailbody_us, $sMailbody_they, $aImagesToSend)
    {
        if (isset($this->container['conf']["email_orderconfirmation_attachment_cancellationform_" . $this->container['lang']]) && file_exists(PATH_DOCROOT.$this->container['conf']['directory_emailattachments'].'/'.$this->container['conf']["email_orderconfirmation_attachment_cancellationform_".$this->container['lang']])) {
            $aFilesToSend[] = PATH_DOCROOT.$this->container['conf']['directory_emailattachments'].'/'.$this->container['conf']["email_orderconfirmation_attachment_cancellationform_".$this->container['lang']];
        } else $aFilesToSend = [];

        \HaaseIT\HCSF\Helper::mailWrapper($this->container['conf'], $_POST["email"], \HaaseIT\Textcat::T("shoppingcart_mail_subject") . ' ' . $iInsertID, $sMailbody_they, $aImagesToSend, $aFilesToSend);
        \HaaseIT\HCSF\Helper::mailWrapper($this->container['conf'], $this->container['conf']["email_sender"], 'Bestellung im Webshop Nr: ' . $iInsertID, $sMailbody_us, $aImagesToSend);
    }

    private function writeCheckoutToFile($sMailbody_us)
    {
        $fp = fopen(PATH_LOGS . 'shoplog_' . date("Y-m-d") . '.html', 'a');
        // Write $somecontent to our opened file.
        fwrite($fp, $sMailbody_us . "\n\n-------------------------------------------------------------------------\n\n");
        fclose($fp);
    }

    private function getPostValue($field)
    {
        return (isset($_POST[$field]) && trim($_POST[$field]) != '' ? $_POST[$field] : '');
    }

    private function buildOrderMailBody($bCust = true, $iId = 0)
    {
        $aSHC = \HaaseIT\HCSF\Shop\Helper::buildShoppingCartTable($_SESSION["cart"], $this->container, true);

        $aData = [
            'customerversion' => $bCust,
            //'shc_css' => file_get_contents(PATH_DOCROOT.'screen-shc.css'),
            'datetime' => date("d.m.Y - H:i"),
            'custno' => (isset($_POST["custno"]) && strlen(trim($_POST["custno"])) >= $this->container['conf']["minimum_length_custno"] ? $_POST["custno"] : ''),
            'corpname' => $this->getPostValue('corpname'),
            'name' => $this->getPostValue('name'),
            'street' => $this->getPostValue('street'),
            'zip' => $this->getPostValue('zip'),
            'town' => $this->getPostValue('town'),
            'phone' => $this->getPostValue('phone'),
            'cellphone' => $this->getPostValue('cellphone'),
            'fax' => $this->getPostValue('fax'),
            'email' => $this->getPostValue('email'),
            'country' => (isset($_POST["country"]) && trim($_POST["country"]) != '' ?
                (isset($this->container['conf']["countries_".$this->container['lang']][$_POST["country"]]) ? $this->container['conf']["countries_".$this->container['lang']][$_POST["country"]] : $_POST["country"]) : ''
            ),
            'remarks' => $this->getPostValue('remarks'),
            'tos' => $this->getPostValue('tos'),
            'cancellationdisclaimer' => $this->getPostValue('cancellationdisclaimer'),
            'paymentmethod' => $this->getPostValue('paymentmethod'),
            'shippingcost' => (!isset($_SESSION["shippingcost"]) || $_SESSION["shippingcost"] == 0 ? false : $_SESSION["shippingcost"]),
            'paypallink' => (isset($_POST["paymentmethod"]) && $_POST["paymentmethod"] == 'paypal' ?  $_SERVER["SERVER_NAME"].'/_misc/paypal.html?id='.$iId : ''),
            'sofortueberweisunglink' => (isset($_POST["paymentmethod"]) && $_POST["paymentmethod"] == 'sofortueberweisung' ?  $_SERVER["SERVER_NAME"].'/_misc/sofortueberweisung.html?id='.$iId : ''),
            'SESSION' => (!$bCust ? \HaaseIT\Tools::debug($_SESSION, '$_SESSION', true, true) : ''),
            'POST' => (!$bCust ? \HaaseIT\Tools::debug($_POST, '$_POST', true, true) : ''),
            'orderid' => $iId,
        ];

        $aM["customdata"] = $aSHC;
        $aM['currency'] = $this->container['conf']["waehrungssymbol"];
        if (isset($this->container['conf']["custom_order_fields"])) $aM["custom_order_fields"] = $this->container['conf']["custom_order_fields"];
        $aM["customdata"]["mail"] = $aData;

        return $this->container['twig']->render('shop/mail-order-html.twig', $aM);

    }

    private function getNotification()
    {
        $return = '';
        if (isset($_GET["msg"]) && trim($_GET["msg"]) != '') {
            if (
                ($_GET["msg"] == 'updated' && isset($_GET["cartkey"]) && isset($_GET["amount"]))
                || ($_GET["msg"] == 'removed')
                && isset($_GET["cartkey"])
            ) {
                $return .= \HaaseIT\Textcat::T("shoppingcart_msg_" . $_GET["msg"] . "_1") . ' ';
                if (isset($this->container['conf']["custom_order_fields"]) && mb_strpos($_GET["cartkey"], '|') !== false) {
                    $mCartkeys = explode('|', $_GET["cartkey"]);
                    foreach ($mCartkeys as $sKey => $sValue) {
                        if ($sKey == 0) {
                            $return .= $sValue . ', ';
                        } else {
                            $TMP = explode(':', $sValue);
                            $return .= \HaaseIT\Textcat::T("shoppingcart_item_" . $TMP[0]) . ' ' . $TMP[1] . ', ';
                            unset($TMP);
                        }
                    }
                    $return = \HaaseIT\Tools::cutStringend($this->P->oPayload->cl_html, 2);
                } else {
                    $return .= $_GET["cartkey"];
                }
                $return.= ' ' . \HaaseIT\Textcat::T("shoppingcart_msg_" . $_GET["msg"] . "_2");
                if ($_GET["msg"] == 'updated') {
                    $return .= ' ' . $_GET["amount"];
                }
                $return .= '<br><br>';
            }
        }

        return $return;
    }

}