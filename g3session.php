<?php

$rootpath = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT');
require_once $rootpath . '/vendor/autoload.php';

$customdefinitions = $rootpath . '/g3definitions.php';
if(!is_file($customdefinitions)) {
    // build definitions
    $loaddefinitions = $rootpath . '/setup/index.php';
    require  $loaddefinitions;
    die();
}

//// CUSTOMIZE SEETINGS ******************
//define('WEB_APP', '/test'); //  define app folder
//define('DATA_PATH', '/home/gus/NetBeansProjects/g3linksdata');  // define data location for db, config, attach and log folders
//define('PAGETITLE', 'G3 Links Actions');
//define('WELCOMEPAGE', 'https://g3links.com/wp');
////**************************************
require $customdefinitions;

define('LOGINSRV', 'g3');  // logon service id
define('LOGINSRVNAME', 'G3 Links');  // logon service name

$shost = filter_input(INPUT_SERVER, 'HTTP_HOST');
$sserver = filter_input(INPUT_SERVER, 'HTTPS');
define('G3TOKEN', \str_replace('.', '', \str_replace(':', '', $shost)));
define('WEB_HOST', (isset($sserver) && $sserver === 'on' ? 'https' : 'http') . '://' . $shost);
define('ROOT_APP', WEB_HOST . WEB_APP . '/');
define('DIR_APP', $rootpath . WEB_APP . '/');

require_once DIR_APP . '/autoload.php';

(new \model\login)->getUserCredentials();
