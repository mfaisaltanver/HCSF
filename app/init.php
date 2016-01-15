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

ini_set('display_errors', 1);
ini_set('xdebug.overload_var_dump', 0);
ini_set('html_errors', 0);
error_reporting(E_ALL);
//error_reporting(0);

mb_internal_encoding('UTF-8');
header("Content-Type: text/html; charset=UTF-8");

if (ini_get('session.auto_start') == 1) {
    die('Please disable session.autostart for this to work.');
}

require_once __DIR__.'/../vendor/autoload.php';

$AuraLoader = new \Aura\Autoload\Loader;
$AuraLoader->register();
$AuraLoader->addPrefix('\HaaseIT\HCSF', __DIR__.'/../src');

// PSR-7 Stuff
// Init request object
$request = \Zend\Diactoros\ServerRequestFactory::fromGlobals();

// cleanup request
$requesturi = $request->getRequestTarget();
$parsedrequesturi = \substr($requesturi, \strlen(\dirname($_SERVER['PHP_SELF'])));
if (substr($parsedrequesturi, 1, 1) != '/') {
    $parsedrequesturi = '/'.$parsedrequesturi;
}
$request = $request->withRequestTarget($parsedrequesturi);

use Symfony\Component\Yaml\Yaml;
use League\Glide\Signatures\SignatureFactory;
use League\Glide\Signatures\SignatureException;

function imgUR($file, $w = 0, $h =0) {
    global $C;
    $urlBuilder = League\Glide\Urls\UrlBuilderFactory::create('', $C['glide_signkey']);

    if ($w == 0 && $h == 0) return false;
    if ($w != 0) $param['w'] = $w;
    if ($h != 0) $param['h'] = $h;
    if ($w != 0 && $h != 0) $param['fit'] = 'stretch';

    //print_r($param);

    return $urlBuilder->getUrl($file, $param);
}

// Load core config
require_once __DIR__.'/config/constants.fixed.php';
$C = Yaml::parse(file_get_contents(__DIR__.'/config/config.core.yml'));
if (isset($C["debug"]) && $C["debug"]) HaaseIT\Tools::$bEnableDebug = true;

if ($C["enable_module_customer"] && isset($_COOKIE["acceptscookies"]) && $_COOKIE["acceptscookies"] == 'yes') {
// Session handling
// session.use_trans_sid wenn nötig aktivieren
    ini_set('session.use_only_cookies', 0); // TODO find another way to pass session when language detection == domain
    session_name('sid');
    if(ini_get('session.use_trans_sid') == 1) {
        ini_set('session.use_trans_sid', 0);
    }
// Session wenn nötig starten
    if (session_id() == '') {
        session_start();
    }

    // check if the stored ip and ua equals the clients, if not, reset. if not set at all, reset
    if (!empty($_SESSION['hijackprevention'])) {
        if (
            $_SESSION['hijackprevention']['remote_addr'] != $_SERVER['REMOTE_ADDR']
            ||
            $_SESSION['hijackprevention']['user_agent'] != $_SERVER['HTTP_USER_AGENT']
        ) {
            \session_regenerate_id();
            \session_unset();
        }
    } else {
        \session_regenerate_id();
        \session_unset();
        $_SESSION['hijackprevention']['remote_addr'] = $_SERVER['REMOTE_ADDR'];
        $_SESSION['hijackprevention']['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
    }
}

if ($C["enable_module_shop"]) $C["enable_module_customer"] = true;

$C = array_merge($C, Yaml::parse(file_get_contents(__DIR__.'/config/config.countries.yml')));
$C = array_merge($C, Yaml::parse(file_get_contents(__DIR__.'/config/config.scrts.yml')));
if ($C["enable_module_customer"]) $C = array_merge($C, Yaml::parse(file_get_contents(__DIR__.'/config/config.customer.yml')));
define("PATH_LOGS", __DIR__.'/../hcsflogs/');
if ($C["enable_module_shop"]) {
    define("FILE_PAYPALLOG", 'ipnlog.txt');
    $C = array_merge($C, Yaml::parse(file_get_contents(__DIR__.'/config/config.shop.yml')));
    if (isset($C["vat_disable"]) && $C["vat_disable"]) {
        $C["vat"] = array("full" => 0, "reduced" => 0);
    }
}


require_once PATH_BASEDIR.'src/functions.core.php';

date_default_timezone_set($C["defaulttimezone"]);

// ----------------------------------------------------------------------------
// Begin Twig loading and init
// ----------------------------------------------------------------------------

$loader = new Twig_Loader_Filesystem(array(__DIR__.'/../customviews', __DIR__.'/../src/views/'));
$twig_options = array(
    'autoescape' => false,
    'debug' => (isset($C["debug"]) && $C["debug"] ? true : false)
);
if (isset($C["templatecache_enable"]) && $C["templatecache_enable"] &&
    is_dir(PATH_TEMPLATECACHE) && is_writable(PATH_TEMPLATECACHE)) {
    $twig_options["cache"] = PATH_TEMPLATECACHE;
}
$twig = new Twig_Environment($loader, $twig_options);
if (isset($C["debug"]) && $C["debug"]) {
    $twig->addExtension(new Twig_Extension_Debug());
}
$twig->addFunction('T', new Twig_Function_Function('\HaaseIT\Textcat::T'));
$twig->addFunction('HT', new Twig_Function_Function('\HaaseIT\HCSF\HardcodedText::get'));
$twig->addFunction('gFF', new Twig_Function_Function('\HaaseIT\Tools::getFormField'));

// ----------------------------------------------------------------------------
// Begin language detection
// ----------------------------------------------------------------------------
if ($C["lang_detection_method"] == 'domain' && isset($C["lang_by_domain"]) && is_array($C["lang_by_domain"])) { // domain based language detection
    foreach ($C["lang_by_domain"] as $sKey => $sValue) {
        if ($_SERVER["HTTP_HOST"] == $sValue || $_SERVER["HTTP_HOST"] == 'www.'.$sValue) {
            $sLang = $sKey;
            break;
        }
    }
} elseif ($C["lang_detection_method"] == 'legacy') { // legacy language detection
    if (isset($_GET["language"]) && array_key_exists($_GET["language"], $C["lang_available"])) {
        $sLang = strtolower($_GET["language"]);
        setcookie('language', strtolower($_GET["language"]), 0, '/');
    } elseif (isset($_COOKIE["language"]) && array_key_exists($_COOKIE["language"], $C["lang_available"])) {
        $sLang = strtolower($_COOKIE["language"]);
    } elseif (isset($_SERVER["HTTP_ACCEPT_LANGUAGE"]) && array_key_exists(substr($_SERVER["HTTP_ACCEPT_LANGUAGE"], 0, 2), $C["lang_available"])) {
        $sLang = substr($_SERVER["HTTP_ACCEPT_LANGUAGE"], 0, 2);
    } else {
        $sLang = key($C["lang_available"]);
    }
}
if (!isset($sLang)) {
    $sLang = key($C["lang_available"]);
}

if (file_exists(PATH_BASEDIR.'src/hardcodedtextcats/'.$sLang.'.php')) {
    require PATH_BASEDIR.'src/hardcodedtextcats/'.$sLang.'.php';
} else {
    if (file_exists(PATH_BASEDIR.'src/hardcodedtextcats/'.key($C["lang_available"]).'.php')) {
        require PATH_BASEDIR.'src/hardcodedtextcats/'.key($C["lang_available"]).'.php';
    } else {
        require PATH_BASEDIR.'src/hardcodedtextcats/de.php';
    }
}
\HaaseIT\HCSF\HardcodedText::init($HT);

// ----------------------------------------------------------------------------
// Begin database init
// ----------------------------------------------------------------------------
$DB = new \PDO($C["db_type"].':host='.$C["db_server"].';dbname='.$C["db_name"], $C["db_user"], $C["db_password"], array( \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8', ));
$DB->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
$DB->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
$DB->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION); // ERRMODE_SILENT / ERRMODE_WARNING / ERRMODE_EXCEPTION

// ----------------------------------------------------------------------------
// more init stuff
// ----------------------------------------------------------------------------
\HaaseIT\Textcat::init($DB, $sLang, key($C["lang_available"]));

require_once __DIR__.'/config/config.navi.php';
if (isset($C["navstruct"]["admin"])) {
    unset($C["navstruct"]["admin"]);
}

if ($C["enable_module_customer"]) {
    require_once __DIR__.'/../src/customer/functions.customer.php';
}

$C["navstruct"]["admin"]["Admin Home"] = '/_admin/index.html';

if ($C["enable_module_shop"]) {
    require_once __DIR__ . '/../src/shop/Items.php';
    require_once __DIR__ . '/../src/shop/functions.shoppingcart.php';

    $oItem = new \HaaseIT\HCSF\Shop\Items($C, $DB, $sLang);

    $C["navstruct"]["admin"]["Bestellungen"] = '/_admin/shopadmin.html';
    $C["navstruct"]["admin"]["Artikel"] = '/_admin/itemadmin.html';
    $C["navstruct"]["admin"]["Artikelgruppen"] = '/_admin/itemgroupadmin.html';
} else {
    $oItem = '';
}

if ($C["enable_module_customer"]) {
    require_once __DIR__.'/../src/customer/functions.customer.php';
    $C["navstruct"]["admin"]["Kunden"] = '/_admin/customeradmin.html';
}

$C["navstruct"]["admin"]["Seiten"] = '/_admin/pageadmin.html';
$C["navstruct"]["admin"]["Textkataloge"] = '/_admin/textcatadmin.html';
$C["navstruct"]["admin"]["Templatecache leeren"] = '/_admin/cleartemplatecache.html';
//$C["navstruct"]["admin"]["Bildercache leeren"] = '/_admin/clearimagecache.html';
$C["navstruct"]["admin"]["PHPInfo"] = '/_admin/phpinfo.html';

// ----------------------------------------------------------------------------
// Begin routing
// ----------------------------------------------------------------------------

$router = new \HaaseIT\HCSF\Router($C, $DB, $sLang, $request, $twig, $oItem);
$P = $router->getPage();

if (1 == 3) {
    //$aPath = explode('/', $sPath);
    //HaaseIT\Tools::debug($aPath);

    if (1 == 2 && $aPath[1] == '_img') {
        $glideserver = League\Glide\ServerFactory::create([
            'source' => PATH_DOCROOT.$C['directory_images'].'/master',
            'cache' => PATH_GLIDECACHE,
            'max_image_size' => 2000*2000,
        ]);
        $glideserver->setBaseUrl('/'.$C['directory_images'].'/');
        // Generate a URL

        try {
            // Validate HTTP signature
            SignatureFactory::create($C['glide_signkey'])->validateRequest($sPath, $_GET);
            $glideserver->outputImage($sPath, $_GET);
            die();

        } catch (SignatureException $e) {
            $url = imgUR('/_img/test2.jpg', 100, 500);

            $P = new \HaaseIT\HCSF\CorePage($C, $sLang);
            $P->cb_pagetype = 'error';

            $P->oPayload->cl_html = '<a href="'.$url.'">Klick</a>';
            header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
        }

    }

}

