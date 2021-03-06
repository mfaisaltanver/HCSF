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

namespace HaaseIT\HCSF\Controller\Admin\Shop;


use HaaseIT\HCSF\HelperConfig;
use HaaseIT\Toolbox\Tools;
use Zend\ServiceManager\ServiceManager;

/**
 * Class Itemgroupadmin
 * @package HaaseIT\HCSF\Controller\Admin\Shop
 */
class Itemgroupadmin extends Base
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    private $dbal;

    /**
     * @var \HaaseIT\HCSF\HardcodedText
     */
    private $hardcodedtextcats;

    /**
     * Itemgroupadmin constructor.
     * @param ServiceManager $serviceManager
     */
    public function __construct(ServiceManager $serviceManager)
    {
        parent::__construct($serviceManager);
        $this->dbal = $serviceManager->get('dbal');
        $this->hardcodedtextcats = $serviceManager->get('hardcodedtextcats');
    }

    /**
     *
     */
    public function preparePage()
    {
        $this->P = new \HaaseIT\HCSF\CorePage($this->serviceManager);
        $this->P->cb_pagetype = 'content';
        $this->P->cb_subnav = 'admin';

        $this->P->cb_customcontenttemplate = 'shop/itemgroupadmin';

        $return = '';
        $iGID = filter_input(INPUT_GET, 'gid', FILTER_SANITIZE_NUMBER_INT);
        $getaction = filter_input(INPUT_GET, 'action');
        if ($getaction === 'insert_lang') {
            $querybuilder = $this->dbal->createQueryBuilder();
            $querybuilder
                ->select('itmg_id')
                ->from('itemgroups_base')
                ->where('itmg_id = ?')
                ->setParameter(0, $iGID)
            ;
            $stmt = $querybuilder->execute();

            $iNumRowsBasis = $stmt->rowCount();

            $querybuilder = $this->dbal->createQueryBuilder();
            $querybuilder
                ->select('itmgt_id')
                ->from('itemgroups_text')
                ->where('itmgt_pid = ? AND itmgt_lang = ?')
                ->setParameter(0, $iGID)
                ->setParameter(1, $this->config->getLang())
            ;
            $stmt = $querybuilder->execute();

            $iNumRowsLang = $stmt->rowCount();

            if ($iNumRowsBasis === 1 && $iNumRowsLang === 0) {
                $querybuilder = $this->dbal->createQueryBuilder();
                $querybuilder
                    ->insert('itemgroups_text')
                    ->setValue('itmgt_pid', '?')
                    ->setValue('itmgt_lang', '?')
                    ->setParameter(0, $iGID)
                    ->setParameter(1, $this->config->getLang())
                ;
                $querybuilder->execute();
                $this->helper->redirectToPage('/_admin/itemgroupadmin.html?gid='.$iGID.'&action=editgroup');
            }
        }

        $postdo = filter_input(INPUT_POST, 'do');
        if ($getaction === 'editgroup') {
            if ($postdo === 'true') {
                $this->P->cb_customdata['updatestatus'] = $this->updateGroup();
            }

            $aGroup = $this->getItemgroups($iGID);
            if (filter_input(INPUT_GET, 'added') !== null) {
                $this->P->cb_customdata['groupjustadded'] = true;
            }
            $this->P->cb_customdata['showform'] = 'edit';
            $this->P->cb_customdata['group'] = $this->prepareGroup('edit', $aGroup[0]);
        } elseif ($getaction === 'addgroup') {
            $aErr = [];
            if ($postdo === 'true') {
                $sName = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);
                $sGNo = filter_input(INPUT_POST, 'no', FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);
                $sImg = filter_input(INPUT_POST, 'img', FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);

                if (strlen($sName) < 3) {
                    $aErr['nametooshort'] = true;
                }
                if (strlen($sGNo) < 3) {
                    $aErr['grouptooshort'] = true;
                }
                if (count($aErr) == 0) {
                    $querybuilder = $this->dbal->createQueryBuilder();
                    $querybuilder
                        ->select('itmg_no')
                        ->from('itemgroups_base')
                        ->where('itmg_no = ?')
                        ->setParameter(0, $sGNo)
                    ;
                    $stmt = $querybuilder->execute();

                    if ($stmt->rowCount() > 0) {
                        $aErr['duplicateno'] = true;
                    }
                }
                if (count($aErr) === 0) {
                    $querybuilder = $this->dbal->createQueryBuilder();
                    $querybuilder
                        ->insert('itemgroups_base')
                        ->setValue('itmg_name', '?')
                        ->setValue('itmg_no', '?')
                        ->setValue('itmg_img', '?')
                        ->setParameter(0, $sName)
                        ->setParameter(1, $sGNo)
                        ->setParameter(2, $sImg)
                    ;
                    $querybuilder->execute();
                    $iLastInsertID = $this->dbal->lastInsertId();
                    $this->helper->redirectToPage('/_admin/itemgroupadmin.html?action=editgroup&added&gid='.$iLastInsertID);
                } else {
                    $this->P->cb_customdata['err'] = $aErr;
                    $this->P->cb_customdata['showform'] = 'add';
                    $this->P->cb_customdata['group'] = $this->prepareGroup('add');
                }
            } else {
                $this->P->cb_customdata['showform'] = 'add';
                $this->P->cb_customdata['group'] = $this->prepareGroup('add');
            }
        } else {
            if (!$return .= $this->showItemgroups($this->getItemgroups(''))) {
                $this->P->cb_customdata['err']['nogroupsavaliable'] = true;
            }
        }
        $this->P->oPayload->cl_html = $return;
    }

    /**
     * @return string
     */
    private function updateGroup()
    {
        $purifier = false;
        if ($this->config->getShop('itemgrouptext_enable_purifier')) {
            $purifier = $this->helper->getPurifier('itemgroup');
        }

        $iGID = filter_input(INPUT_GET, 'gid', FILTER_SANITIZE_NUMBER_INT);
        $sGNo = filter_input(INPUT_POST, 'no', FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);

        $querybuilder = $this->dbal->createQueryBuilder();
        $querybuilder
            ->select('*')
            ->from('itemgroups_base')
            ->where('itmg_id != ? AND itmg_no = ?')
            ->setParameter(0, $iGID)
            ->setParameter(1, $sGNo)
        ;
        $stmt = $querybuilder->execute();

        if ($stmt->rowCount() > 0) {
            return 'duplicateno';
        }

        $querybuilder = $this->dbal->createQueryBuilder();
        $querybuilder
            ->update('itemgroups_base')
            ->set('itmg_name', '?')
            ->set('itmg_no', '?')
            ->set('itmg_img', '?')
            ->where('itmg_id = ?')
            ->setParameter(0, filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW))
            ->setParameter(1, $sGNo)
            ->setParameter(2, filter_input(INPUT_POST, 'img', FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW))
            ->setParameter(3, $iGID)
        ;
        $querybuilder->execute();

        $querybuilder = $this->dbal->createQueryBuilder();
        $querybuilder
            ->select('itmgt_id')
            ->from('itemgroups_text')
            ->where('itmgt_pid = ? AND itmgt_lang = ?')
            ->setParameter(0, $iGID)
            ->setParameter(1, $this->config->getLang())
        ;
        $stmt = $querybuilder->execute();

        if ($stmt->rowCount() === 1) {
            $shorttext = filter_input(INPUT_POST, 'shorttext', FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW);
            $details = filter_input(INPUT_POST, 'details', FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW);

            $aRow = $stmt->fetch();
            $querybuilder = $this->dbal->createQueryBuilder();
            $querybuilder
                ->update('itemgroups_text')
                ->set('itmgt_shorttext', '?')
                ->set('itmgt_details', '?')
                ->where('itmgt_id = ?')
                ->setParameter(0, !empty($this->purifier) ? $purifier->purify($shorttext) : $shorttext)
                ->setParameter(1, !empty($this->purifier) ? $purifier->purify($details) : $details)
                ->setParameter(2, $aRow['itmgt_id'])
            ;
            $querybuilder->execute();
        }

        return 'success';
    }

    /**
     * @param string $sPurpose
     * @param array $aData
     * @return array
     */
    private function prepareGroup($sPurpose = 'none', $aData = [])
    {
        $aGData = [
            'formaction' => Tools::makeLinkHRefWithAddedGetVars('/_admin/itemgroupadmin.html'),
            'id' => isset($aData['itmg_id']) ? $aData['itmg_id'] : '',
            'name' => isset($aData['itmg_name']) ? $aData['itmg_name'] : '',
            'no' => isset($aData['itmg_no']) ? $aData['itmg_no'] : '',
            'img' => isset($aData['itmg_img']) ? $aData['itmg_img'] : '',
        ];

        if ($sPurpose === 'edit') {
            if ($aData['itmgt_id'] != '') {
                $aGData['lang'] = [
                    'shorttext' => isset($aData['itmgt_shorttext']) ? $aData['itmgt_shorttext'] : '',
                    'details' => isset($aData['itmgt_details']) ? $aData['itmgt_details'] : '',
                ];
            }
        }

        return $aGData;
    }

    /**
     * @param string $iGID
     * @return mixed
     */
    private function getItemgroups($iGID = '')
    {
        $querybuilder = $this->dbal->createQueryBuilder();
        $querybuilder
            ->select('*')
            ->from('itemgroups_base', 'b')
            ->leftJoin('b', 'itemgroups_text', 't', 'b.itmg_id = t.itmgt_pid AND t.itmgt_lang = ?')
            ->setParameter(0, $this->config->getLang())
            ->orderBy('itmg_no')
        ;

        if ($iGID != '') {
            $querybuilder
                ->where('itmg_id = ?')
                ->setParameter(1, $iGID)
            ;
        }
        $stmt = $querybuilder->execute();

        return $stmt->fetchAll();
    }

    /**
     * @param $aGroups
     * @return bool|mixed
     */
    private function showItemgroups($aGroups)
    {
        $aList = [
            ['title' => $this->hardcodedtextcats->get('itemgroupadmin_list_no'), 'key' => 'gno', 'width' => 80, 'linked' => false, 'style-data' => 'padding: 5px 0;'],
            ['title' => $this->hardcodedtextcats->get('itemgroupadmin_list_name'), 'key' => 'gname', 'width' => 350, 'linked' => false, 'style-data' => 'padding: 5px 0;'],
            ['title' => $this->hardcodedtextcats->get('itemgroupadmin_list_edit'), 'key' => 'gid', 'width' => 30, 'linked' => true, 'ltarget' => '/_admin/itemgroupadmin.html', 'lkeyname' => 'gid', 'lgetvars' => ['action' => 'editgroup'], 'style-data' => 'padding: 5px 0;'],
        ];
        if (count($aGroups) > 0) {
            $aData = [];
            foreach ($aGroups as $aValue) {
                $aData[] = [
                    'gid' => $aValue['itmg_id'],
                    'gno' => $aValue['itmg_no'],
                    'gname' => $aValue['itmg_name'],
                ];
            }
            return Tools::makeListtable($aList, $aData, $this->serviceManager->get('twig'));
        }

        return false;
    }
}
