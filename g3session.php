<?php
// CUSTOMIZE SEETINGS ******************
define('WEB_APP', '/actions/'); // define root app folder
define('DATA_PATH', '<here real data folder path>');  // define data location for db, config, attach and log folders
define('PAGETITLE', 'G3 Links Actions');
$welcomepage = 'https://g3links.com/wp';
//**************************************

define('LOGINSRV', 'g3');  // logon service id
define('LOGINSRVNAME', 'G3 Links');  // logon service name

define('WELCOMEPAGE', filter_input(INPUT_SERVER, 'SERVER_NAME') === 'localhost' ? '' : $welcomepage);

$shost = filter_input(INPUT_SERVER, 'HTTP_HOST');
$sserver = filter_input(INPUT_SERVER, 'HTTPS');
define('G3TOKEN', \str_replace('.', '', \str_replace(':', '', $shost)));
define('WEB_HOST', (isset($sserver) && $sserver === 'on' ? 'https' : 'http') . '://' . $shost);
define('ROOT_APP', WEB_HOST . WEB_APP . '/');
define('DIR_APP', filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . WEB_APP . '/');

require_once DIR_APP . '/autoload.php';
require_once DIR_APP . '/vendor/autoload.php';

(new \model\login)->getUserCredentials();
