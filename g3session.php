<?php

if (\strtolower(filter_input(INPUT_SERVER, 'SERVER_NAME')) !== 'localhost') {
    $secureserver = filter_input(INPUT_SERVER, 'HTTPS');
    if (!isset($secureserver) || $secureserver !== "on") {
        $url = "https://" . filter_input(INPUT_SERVER, 'SERVER_NAME') . filter_input(INPUT_SERVER, 'REQUEST_URI');
        header("Location: $url");
        die();
    }
}

$rootpath = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT');

$rootpathvendor = $rootpath . '/vendor/autoload.php';
if (!is_file($rootpathvendor)) {
    echo 'missing folder or files at vendor. Please run composer update.';
    die();
}
require_once $rootpathvendor;

if (!defined('DIR_APP')) {
    $customdefinitions = $rootpath . '/g3definitions.php';
    if (!is_file($customdefinitions)) {
        // build definitions
        $loaddefinitions = $rootpath . '/install/index.php';
        require $loaddefinitions;
        die();
    }

    require $customdefinitions;

    define('LOGINSRV', 'g3');  // logon service id
    define('LOGINSRVNAME', 'G3 Links');  // logon service name

    $shost = filter_input(INPUT_SERVER, 'HTTP_HOST');
    $sserver = filter_input(INPUT_SERVER, 'HTTPS');
    define('G3TOKEN', \str_replace('.', '', \str_replace(':', '', $shost)));
    define('WEB_HOST', (isset($sserver) && $sserver === 'on' ? 'https' : 'http') . '://' . $shost);
    define('ROOT_APP', WEB_HOST . WEB_APP . '/');
    define('DIR_APP', $rootpath . WEB_APP . '/');
}

require_once DIR_APP . '/autoload.php';

(new \model\env)->getUserSession();
