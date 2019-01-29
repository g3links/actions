<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3session.php';

$lang = 'en';
if(filter_input(INPUT_POST, 'updatelang') !== null) {
    $lang = filter_input(INPUT_POST, 'updatelang');
    \model\env::setLang($lang);
    
   require \model\route::script('restart.php');
}

\model\route::script('g3/editlanguage.php');
die();
