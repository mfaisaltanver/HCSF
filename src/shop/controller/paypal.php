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

require_once PATH_BASEDIR . 'src/shop/functions.shoppingcart.php';

$P = new \HaaseIT\HCSF\CorePage($C, $sLang);
$P->cb_pagetype = 'content';

$iId = \filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
$sQ = "SELECT * FROM " . DB_ORDERTABLE . " ";
$sQ .= "WHERE o_id = :id AND o_paymentmethod = 'paypal' AND o_paymentcompleted = 'n'";

$hResult = $DB->prepare($sQ);
$hResult->bindValue(':id', $iId, PDO::PARAM_INT);

$hResult->execute();

if ($hResult->rowCount() == 1) {
    $aOrder = $hResult->fetch();
    //\HaaseIT\Tools::debug($aOrder);
    $fGesamtbrutto = calculateTotalFromDB($aOrder);

    $sPaypalURL = $C["paypal"]["url"] . '?cmd=_xclick&rm=2&custom=' . $iId . '&business=' . $C["paypal"]["business"];
    $sPaypalURL .= '&notify_url=http://' . $_SERVER["HTTP_HOST"] . '/_misc/paypal_notify.html&item_name=' . \HaaseIT\Textcat::T("misc_paypaypal_paypaltitle") . ' ' . $iId;
    $sPaypalURL .= '&currency_code=' . $C["paypal"]["currency_id"] . '&amount=' . str_replace(',', '.', number_format($fGesamtbrutto, 2, '.', ''));
    if (isset($C["interactive_paymentmethods_redirect_immediately"]) && $C["interactive_paymentmethods_redirect_immediately"]) {
        header('Location: '.$sPaypalURL);
    }

    $P->oPayload->cl_html = \HaaseIT\Textcat::T("misc_paypaypal_greeting") . '<br><br>';
    $P->oPayload->cl_html .= '<a href="' . $sPaypalURL . '">' . \HaaseIT\Textcat::T("misc_paypaypal") . '</a>';
} else {
    $P->oPayload->cl_html = \HaaseIT\Textcat::T("misc_paypaypal_paymentnotavailable");
}