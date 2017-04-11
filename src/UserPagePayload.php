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

use HaaseIT\Toolbox\DBTools;
use Zend\ServiceManager\ServiceManager;

class UserPagePayload extends PagePayload
{
    public $cl_id, $cl_cb, $cl_lang, $purifier;

    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $dbal;

    public function __construct(ServiceManager $serviceManager, $iParentID, $bReturnRaw = false) {
        parent::__construct($serviceManager);

        $this->dbal = $this->serviceManager->get('dbal');

        if ($iParentID !== '/_misc/index.html') { // no need to fetch from db if this is the itemsearch page
            $sql = 'SELECT cl_id, cl_cb, cl_lang, cl_html, cl_keywords, cl_description, cl_title ';
            $sql .= 'FROM content_lang WHERE cl_cb = :ppkey AND cl_lang = :lang';

            /** @var \PDOStatement $hResult */
            $hResult = $this->serviceManager->get('db')->prepare($sql);

            // Try to get the payload in the current language
            $hResult->bindValue(':ppkey', $iParentID, \PDO::PARAM_STR);
            $hResult->bindValue(':lang', HelperConfig::$lang, \PDO::PARAM_STR);
            $hResult->setFetchMode(\PDO::FETCH_INTO, $this);
            $hResult->execute();

            if ($hResult->rowCount() == 1) {
                $hResult->fetch();
            } elseif (!$bReturnRaw) { // if raw data is required, don't try to fetch default lang data
                // if the current language data is not available, lets see if we can get the default languages data

                /** @var \PDOStatement $hResult */
                $hResult = $this->serviceManager->get('db')->prepare($sql);
                $hResult->bindValue(':ppkey', $iParentID, \PDO::PARAM_STR);
                $lang_available = HelperConfig::$core['lang_available'];
                $hResult->bindValue(':lang', key($lang_available), \PDO::PARAM_STR);
                $hResult->setFetchMode(\PDO::FETCH_INTO, $this);
                $hResult->execute();

                if ($hResult->rowCount() === 1) {
                    $hResult->fetch();
                }
            }
        }
    }

    public function write() {
        $aData = [
            'cl_html' => !empty($this->purifier) ? $this->purifier->purify($this->cl_html) : $this->cl_html,
            'cl_title' => filter_var($this->cl_title, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW),
            'cl_description' => filter_var($this->cl_description, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW),
            'cl_keywords' => filter_var($this->cl_keywords, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW),
            'cl_id' => $this->cl_id,
        ];
        $sql = DBTools::buildPSUpdateQuery($aData, 'content_lang', 'cl_id');

        $hResult = $this->serviceManager->get('db')->prepare($sql);
        foreach ($aData as $sKey => $sValue) {
            /** @var \PDOStatement $hResult */
            $hResult->bindValue(':'.$sKey, $sValue);
        }
        return $hResult->execute();
    }

    public function insert($iParentID) {
        $aData = [
            'cl_cb' => $iParentID,
            'cl_lang' => HelperConfig::$lang,
        ];
        $sql = DBTools::buildInsertQuery($aData, 'content_lang');
        $this->serviceManager->get('db')->exec($sql);
    }

    public function remove($sParentID) {
        $queryBuilder = $this->dbal->createQueryBuilder();
        $queryBuilder
            ->delete('content_lang')
            ->where('cl_cb = '.$queryBuilder->createNamedParameter($sParentID))
        ;

        return $queryBuilder->execute();
    }
}