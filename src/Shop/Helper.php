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

namespace HaaseIT\HCSF\Shop;


use HaaseIT\HCSF\HelperConfig;
use HaaseIT\HCSF\Shop\Items;
use Zend\ServiceManager\ServiceManager;

class Helper
{
    public static function showOrderStatusText(\HaaseIT\Textcat $textcats, $sStatusShort)
    {
        $mapping = [
            'y' => 'order_status_completed',
            'n' => 'order_status_open',
            'i' => 'order_status_inwork',
            's' => 'order_status_canceled',
            'd' => 'order_status_deleted',
        ];

        if (!empty($mapping[$sStatusShort])) {
            return $textcats->T($mapping[$sStatusShort]);
        }

        return '';
    }

    public static function calculateTotalFromDB($aOrder)
    {
        $fGesamtnetto = $aOrder["o_sumnettoall"];
        $fVoll = $aOrder["o_sumvoll"];
        $fSteuererm = $aOrder["o_taxerm"];

        if ($aOrder["o_mindermenge"] > 0) {
            $fVoll += $aOrder["o_mindermenge"];
            $fGesamtnetto += $aOrder["o_mindermenge"];
        }
        if ($aOrder["o_shippingcost"] > 0) {
            $fVoll += $aOrder["o_shippingcost"];
            $fGesamtnetto += $aOrder["o_shippingcost"];
        }

        $fSteuervoll = ($fVoll * $aOrder["o_vatfull"] / 100);
        $fGesamtbrutto = $fGesamtnetto + $fSteuervoll + $fSteuererm;

        return $fGesamtbrutto;
    }

    public static function addAdditionalCostsToItems($aSumme, $iVATfull, $iVATreduced)
    {
        $fGesamtnetto = $aSumme["sumvoll"] + $aSumme["sumerm"];
        $fSteuervoll = $aSumme["sumvoll"] * $iVATfull / 100;
        $fSteuererm = $aSumme["sumerm"] * $iVATreduced / 100;
        $fGesamtbrutto = $fGesamtnetto + $fSteuervoll + $fSteuererm;

        $aOrder = [
            'sumvoll' => $aSumme["sumvoll"],
            'sumerm' => $aSumme["sumerm"],
            'sumnettoall' => $fGesamtnetto,
            'taxvoll' => $fSteuervoll,
            'taxerm' => $fSteuererm,
            'sumbruttoall' => $fGesamtbrutto,
        ];

        $fGesamtnettoitems = $aOrder["sumnettoall"];
        $aOrder["fVoll"] = $aOrder["sumvoll"];
        $aOrder["fErm"] = $aOrder["sumerm"];
        $aOrder["fGesamtnetto"] = $aOrder["sumnettoall"];
        $aOrder["fSteuervoll"] = $aOrder["taxvoll"];
        $aOrder["fSteuererm"] = $aOrder["taxerm"];
        $aOrder["fGesamtbrutto"] = $aOrder["sumbruttoall"];

        $aOrder["bMindesterreicht"] = true;
        $aOrder["fMindergebuehr"] = 0;
        $aOrder["iMindergebuehr_id"] = 0;
        if ($fGesamtnettoitems < HelperConfig::$shop["minimumorderamountnet"]) {
            $aOrder["bMindesterreicht"] = false;
            $aOrder["iMindergebuehr_id"] = 0;
        } elseif ($fGesamtnettoitems < HelperConfig::$shop["reducedorderamountnet1"]) {
            $aOrder["iMindergebuehr_id"] = 1;

        } elseif ($fGesamtnettoitems < HelperConfig::$shop["reducedorderamountnet2"]) {
            $aOrder["iMindergebuehr_id"] = 2;
        }

        if ($aOrder["iMindergebuehr_id"] > 0) {
            $aOrder["fVoll"] += HelperConfig::$shop["reducedorderamountfee" . $aOrder["iMindergebuehr_id"]];
            $aOrder["fGesamtnetto"] += HelperConfig::$shop["reducedorderamountfee" . $aOrder["iMindergebuehr_id"]];
            $aOrder["fSteuervoll"] = $aOrder["fVoll"] * $iVATfull / 100;
            $aOrder["fGesamtbrutto"] = $aOrder["fGesamtnetto"] + $aOrder["fSteuervoll"] + $aOrder["fSteuererm"];
            $aOrder["fMindergebuehr"] = HelperConfig::$shop["reducedorderamountfee" . $aOrder["iMindergebuehr_id"]];
        }

        $aOrder["fVersandkosten"] = 0;
        if (
            isset(HelperConfig::$shop["shippingcoststandardrate"])
            && HelperConfig::$shop["shippingcoststandardrate"] != 0
            &&
            (
                (
                    !isset(HelperConfig::$shop["mindestbetragversandfrei"])
                    || !HelperConfig::$shop["mindestbetragversandfrei"]
                )
                || $fGesamtnettoitems < HelperConfig::$shop["mindestbetragversandfrei"]
            )
        ) {
            $aOrder["fVersandkostennetto"] = self::getShippingcost();
            $aOrder["fVersandkostenvat"] = $aOrder["fVersandkostennetto"] * $iVATfull / 100;
            $aOrder["fVersandkostenbrutto"] = $aOrder["fVersandkostennetto"] + $aOrder["fVersandkostenvat"];

            $aOrder["fSteuervoll"] = ($aOrder["fVoll"] * $iVATfull / 100) + $aOrder["fVersandkostenvat"];
            $aOrder["fVoll"] += $aOrder["fVersandkostennetto"];
            $aOrder["fGesamtnetto"] += $aOrder["fVersandkostennetto"];
            $aOrder["fGesamtbrutto"] = $aOrder["fGesamtnetto"] + $aOrder["fSteuervoll"] + $aOrder["fSteuererm"];
        }

        return $aOrder;
    }

    public static function getShippingcost()
    {
        $fShippingcost = HelperConfig::$shop["shippingcoststandardrate"];

        if (isset($_SESSION["user"]["cust_country"])) {
            $sCountry = $_SESSION["user"]["cust_country"];
        } elseif (isset($_POST["doCheckout"]) && $_POST["doCheckout"] == 'yes' && isset($_POST["country"])) {
            $sCountry = trim(\HaaseIT\Tools::getFormfield("country"));
        } elseif (isset($_SESSION["formsave_addrform"]["country"])) {
            $sCountry = $_SESSION["formsave_addrform"]["country"];
        } else {
            $sCountry = \HaaseIT\HCSF\Customer\Helper::getDefaultCountryByConfig(HelperConfig::$lang);
        }

        foreach (HelperConfig::$shop["shippingcosts"] as $aValue) {
            if (isset($aValue["countries"][$sCountry])) {
                $fShippingcost = $aValue["cost"];
                break;
            }
        }

        return $fShippingcost;
    }

    public static function calculateCartItems($aCart)
    {
        $fErm = 0;
        $fVoll = 0;
        $fTaxErm = 0;
        $fTaxVoll = 0;
        foreach ($aCart as $aValue) {
            // Hmmmkay, so, if vat is not disabled and there is no vat id or none as vat id set to this item, then
            // use the full vat as default. Only use reduced if it is set. Gotta use something as default or item
            // will not add up to total price
            if ($aValue["vat"] != "reduced") {
                $fVoll += ($aValue["amount"] * $aValue["price"]["netto_use"]);
                $fTaxVoll += ($aValue["amount"] * $aValue["price"]["netto_use"] * (HelperConfig::$shop["vat"]["full"] / 100));
            } else {
                $fErm += ($aValue["amount"] * $aValue["price"]["netto_use"]);
                $fTaxErm += ($aValue["amount"] * $aValue["price"]["netto_use"] * (HelperConfig::$shop["vat"]["reduced"] / 100));
            }
        }
        $aSumme = ['sumvoll' => $fVoll, 'sumerm' => $fErm, 'taxvoll' => $fTaxVoll, 'taxerm' => $fTaxErm];

        return $aSumme;
    }

    public static function refreshCartItems(ServiceManager $serviceManager) // bei login/logout ändern sich ggf die preise, shoppingcart neu berechnen
    {
        if (isset($_SESSION["cart"]) && is_array($_SESSION["cart"])) {
            foreach ($_SESSION["cart"] as $sKey => $aValue) {
                if (!isset(HelperConfig::$shop["custom_order_fields"])) {
                    $sItemkey = $sKey;
                } else {
                    $TMP = explode('|', $sKey);
                    $sItemkey = $TMP[0];
                    unset($TMP);
                }
                $aData = $serviceManager->get('oItem')->sortItems('', $sItemkey);
                $_SESSION["cart"][$sKey]["price"] = $aData["item"][$sItemkey]["pricedata"];
            }
        }
    }

    public static function buildShoppingCartTable($aCart, $bReadonly = false, $sCustomergroup = '', $aErr = '', $iVATfull = '', $iVATreduced = '')
    {
        if ($iVATfull == '' && $iVATreduced == '') {
            $iVATfull = HelperConfig::$shop["vat"]["full"];
            $iVATreduced = HelperConfig::$shop["vat"]["reduced"];
        }
        $aSumme = self::calculateCartItems($aCart);
        $aData["shoppingcart"] = [
            'readonly' => $bReadonly,
            'customergroup' => $sCustomergroup,
            'cart' => $aCart,
            'rebategroups' => HelperConfig::$shop["rebate_groups"],
            'additionalcoststoitems' => self::addAdditionalCostsToItems($aSumme, $iVATfull, $iVATreduced),
            'minimumorderamountnet' => HelperConfig::$shop["minimumorderamountnet"],
            'reducedorderamountnet1' => HelperConfig::$shop["reducedorderamountnet1"],
            'reducedorderamountnet2' => HelperConfig::$shop["reducedorderamountnet2"],
            'reducedorderamountfee1' => HelperConfig::$shop["reducedorderamountfee1"],
            'reducedorderamountfee2' => HelperConfig::$shop["reducedorderamountfee2"],
            'minimumamountforfreeshipping' => HelperConfig::$shop["minimumamountforfreeshipping"],
        ];

        if (!$bReadonly) {
            $aCartpricesums = $aData["shoppingcart"]["additionalcoststoitems"];
            $_SESSION["cartpricesums"] = $aCartpricesums;
        }

        if ($aData["shoppingcart"]["additionalcoststoitems"]["bMindesterreicht"] && !$bReadonly) {
            $aData["customerform"] = \HaaseIT\HCSF\Customer\Helper::buildCustomerForm(HelperConfig::$lang, 'shoppingcart', $aErr);
        }

        return $aData;
    }

    public static function getShoppingcartData()
    {
        $aCartinfo = [
            'numberofitems' => 0,
            'cartsums' => [],
            'cartsumnetto' => 0,
            'cartsumbrutto' => 0,
        ];
        if ((!HelperConfig::$shop["show_pricesonlytologgedin"] || \HaaseIT\HCSF\Customer\Helper::getUserData()) && isset($_SESSION["cart"]) && count($_SESSION["cart"])) {
            $aCartsums = \HaaseIT\HCSF\Shop\Helper::calculateCartItems($_SESSION["cart"]);
            $aCartinfo = [
                'numberofitems' => count($_SESSION["cart"]),
                'cartsums' => $aCartsums,
                'cartsumnetto' => $aCartsums["sumvoll"] + $aCartsums["sumerm"],
                'cartsumbrutto' => $aCartsums["sumvoll"] + $aCartsums["sumerm"] + $aCartsums["taxerm"] + $aCartsums["taxvoll"],
            ];
            unset($aCartsums);
            foreach ($_SESSION["cart"] as $sKey => $aValue) {
                $aCartinfo["cartitems"][$sKey] = [
                    'cartkey' => $sKey,
                    'name' => $aValue["name"],
                    'amount' => $aValue["amount"],
                    'img' => $aValue["img"],
                    'price' => $aValue["price"],
                ];
            }
        }

        return $aCartinfo;
    }

    /**
     * @param Items $oItem
     * @param array $aPossibleSuggestions
     * @param string $sSetSuggestions
     * @param string $sCurrentitem
     * @param string|array $mItemindex
     * @param array $aItemindexpathtreeforsuggestions
     * @return array
     */
    public static function getItemSuggestions(
        Items $oItem,
        $aPossibleSuggestions,
        $sSetSuggestions,
        $sCurrentitem,
        $mItemindex,
        $aItemindexpathtreeforsuggestions
    )
    {
        //$aPossibleSuggestions = $aP["items"]["item"]; // put all possible suggestions that are already loaded into this array
        unset($aPossibleSuggestions[$sCurrentitem]); // remove the currently shown item from this list, we do not want to show it as a suggestion

        $suggestions = static::prepareSuggestions($sSetSuggestions, $aPossibleSuggestions, $oItem);

        $suggestions = static::fillSuggestions($suggestions);

        foreach ($suggestions as $aSuggestionsKey => $aSuggestionsValue) { // build the paths to the suggested items
            if (mb_strpos($aSuggestionsValue["itm_index"], '|') !== false) { // check if the suggestions itemindex contains multiple indexes, if so explode an array
                $aSuggestionIndexes = explode('|', $aSuggestionsValue["itm_index"]);

                // iterate through these indexes
                foreach ($aSuggestionIndexes as $sSuggestionIndexesValue) {
                    // check if there is an index configured on this page
                    if (isset($mItemindex)) {
                        // check if it is an array and if the suggestions index is in that array, set path to empty string
                        if (is_array($mItemindex) && in_array($sSuggestionIndexesValue, $mItemindex)) {
                            $suggestions[$aSuggestionsKey]["path"] = '';
                            // path to suggestion set, continue with next suggestion
                            continue 2;
                        // if the suggestion index is on this page, set path to empty string
                        } elseif ($mItemindex == $sSuggestionIndexesValue) {
                            $suggestions[$aSuggestionsKey]["path"] = '';
                            continue 2; // path to suggestion set, continue with next suggestion
                        }
                    }
                    if (isset($aItemindexpathtreeforsuggestions[$sSuggestionIndexesValue])) {
                        $suggestions[$aSuggestionsKey]["path"] = $aItemindexpathtreeforsuggestions[$sSuggestionIndexesValue];
                        continue 2;
                    }
                }
            } elseif (isset($aItemindexpathtreeforsuggestions[$aSuggestionsValue["itm_index"]])) {
                $suggestions[$aSuggestionsKey]["path"] = $aItemindexpathtreeforsuggestions[$aSuggestionsValue["itm_index"]];
            }
        }

        return shuffle($suggestions);
    }

    /**
     * @param string $sSetSuggestions
     * @param array $aPossibleSuggestions
     * @param Items $oItem
     * @return array
     */
    public static function prepareSuggestions($sSetSuggestions, $aPossibleSuggestions, Items $oItem)
    {
        $sSetSuggestions = trim($sSetSuggestions);

        $aDefinedSuggestions = [];
        if (!empty($sSetSuggestions)) {
            $aDefinedSuggestions = explode('|', $sSetSuggestions);
        }

        $aSuggestionsToLoad = [];
        // iterate all defined suggestions and put those not loaded yet into array
        foreach ($aDefinedSuggestions as $aDefinedSuggestionsValue) {
            if (!isset($aPossibleSuggestions[$aDefinedSuggestionsValue])) {
                $aSuggestionsToLoad[] = $aDefinedSuggestionsValue;
            }
        }

        // if there are not yet loaded suggestions, load them
        if (isset($aSuggestionsToLoad)) {
            $aItemsNotInCategory = $oItem->sortItems('', $aSuggestionsToLoad, false);

            // merge loaded and newly loaded items
            if (isset($aItemsNotInCategory)) {
                $aPossibleSuggestions = array_merge($aPossibleSuggestions, $aItemsNotInCategory["item"]);
            }
        }

        $suggestions = [
            'default' => [],
            'additional' => [],
        ];
        foreach ($aPossibleSuggestions as $aPossibleSuggestionsKey => $aPossibleSuggestionsValue) { // iterate through all possible suggestions
            if (in_array($aPossibleSuggestionsKey, $aDefinedSuggestions)) { // if this suggestion is a defined one, put into this array
                $suggestions['default'][$aPossibleSuggestionsKey] = $aPossibleSuggestionsValue;
                continue;
            }
            // if not, put into this one
            $suggestions['additional'][$aPossibleSuggestionsKey] = $aPossibleSuggestionsValue;
        }

        return $suggestions;
    }

    /**
     * @param array $suggestions
     * @return array
     */
    public static function fillSuggestions($suggestions)
    {
        $iNumberOfSuggestions = count($suggestions['default']);
        if ($iNumberOfSuggestions > HelperConfig::$shop["itemdetail_suggestions"]) { // if there are more suggestions than should be displayed, randomly pick as many as to be shown
            $aKeysSuggestions = array_rand($suggestions['default'], HelperConfig::$shop["itemdetail_suggestions"]); // get the array keys that will stay
            foreach ($suggestions['default'] as $aSuggestionsKey => $aSuggestionsValue) { // iterate suggestions and remove those that which will not be kept
                if (!in_array($aSuggestionsKey, $aKeysSuggestions)) {
                    unset($suggestions['default'][$aSuggestionsKey]);
                }
            }

            return $suggestions['default'];
        }

        // if less or equal continue here
        $iNumberOfAdditionalSuggestions = count($suggestions['additional']);
        if (
            $iNumberOfSuggestions < HelperConfig::$shop["itemdetail_suggestions"]
            && $iNumberOfAdditionalSuggestions > 0
        ) { // if there are less suggestions than should be displayed and there are additional available
            // how many more are needed?
            $iAdditionalSuggestionsRequired = HelperConfig::$shop["itemdetail_suggestions"] - $iNumberOfSuggestions;
            // see if there are more available than required, if so, pick as many as needed
            if ($iNumberOfAdditionalSuggestions > $iAdditionalSuggestionsRequired) {
                // since array_rand returns a string and no array if there is only one row picked, we have to do this awkward dance
                $aKeysAdditionalSuggestions = array_rand($suggestions['additional'], $iAdditionalSuggestionsRequired);
                if (is_string($aKeysAdditionalSuggestions)) {
                    $aKeysAdditionalSuggestions = [$aKeysAdditionalSuggestions];
                }

                // iterate suggestions and remove those that which will not be kept
                foreach ($suggestions['additional'] as $aAdditionalSuggestionsKey => $aAdditionalSuggestionsValue) {
                    if (!in_array($aAdditionalSuggestionsKey, $aKeysAdditionalSuggestions)) {
                        unset($suggestions['additional'][$aAdditionalSuggestionsKey]);
                    }
                }
                unset($aKeysAdditionalSuggestions);
            }
            return array_merge($suggestions['default'], $suggestions['additional']); // merge
        }

        // if the number of default suggestions is not larger than configured and also not smaller, then it equals the
        // configured amount, so lets return this then.
        return $suggestions['default'];
    }

    static function handleItemPage(ServiceManager $serviceManager, $P, $aP)
    {
        $mItemIndex = '';
        if (isset($P->cb_pageconfig->itemindex)) {
            $mItemIndex = $P->cb_pageconfig->itemindex;
        }

        $oItem = $serviceManager->get('oItem');

        $aP["items"] = $oItem->sortItems($mItemIndex, '', ($aP["pagetype"] == 'itemoverviewgrpd' ? true : false));
        if ($aP["pagetype"] == 'itemdetail') {

            $aP["itemindexpathtreeforsuggestions"] = $oItem->getItemPathTree();

            if (isset($aP["pageconfig"]->itemindex)) {
                if (is_array($aP["pageconfig"]->itemindex)) {
                    foreach ($aP["pageconfig"]->itemindex as $sItemIndexValue) {
                        $aP["itemindexpathtreeforsuggestions"][$sItemIndexValue] = '';
                    }
                } else {
                    $aP["itemindexpathtreeforsuggestions"][$aP["pageconfig"]->itemindex] = '';
                }
            }

            // Change pagetype to itemoverview, will be changed back to itemdetail once the item is found
            // if it is not found, we will show the overview
            $aP["pagetype"] = 'itemoverview';
            if (count($aP["items"]["item"])) {
                foreach ($aP["items"]["item"] as $sKey => $aValue) {
                    if ($aValue['itm_no'] != $P->cb_pageconfig->itemno) {
                        continue;
                    }

                    $aP["pagetype"] = 'itemdetail';
                    $aP["item"]["data"] = $aValue;
                    $aP["item"]["key"] = $sKey;

                    $iPositionInItems = array_search($sKey, $aP["items"]["itemkeys"]);
                    $aP["item"]["currentitem"] = $iPositionInItems + 1;

                    $aP["item"]["previtem"] = $aP["items"]["itemkeys"][$iPositionInItems - 1];
                    if ($iPositionInItems == 0) {
                        $aP["item"]["previtem"] = $aP["items"]["itemkeys"][$aP["items"]["totalitems"] - 1];
                    }

                    $aP["item"]["nextitem"] = $aP["items"]["itemkeys"][$iPositionInItems + 1];
                    if ($iPositionInItems == $aP["items"]["totalitems"] - 1) {
                        $aP["item"]["nextitem"] = $aP["items"]["itemkeys"][0];
                    }

                    // build item suggestions if needed
                    if (HelperConfig::$shop["itemdetail_suggestions"] > 0) {
                        $aP["item"]["suggestions"] = self::getItemSuggestions(
                            $oItem,
                            $aP["items"]["item"],
                            (!empty($aValue['itm_data']["suggestions"]) ? $aValue['itm_data']["suggestions"] : ''),
                            $sKey,
                            (!empty($aP["pageconfig"]->itemindex) ? $aP["pageconfig"]->itemindex : ''),
                            (!empty($aP["itemindexpathtreeforsuggestions"]) ? $aP["itemindexpathtreeforsuggestions"] : [])
                        );
                    }
                    // Wenn der Artikel gefunden wurde können wir das Ausführen der Suche beenden.
                    break;
                }
            }
        }

        return $aP;
    }
}