<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';


$seccode = '';
if(filter_input(INPUT_GET, 'seccode') !== null)
    $seccode = filter_input(INPUT_GET, 'seccode');

$idrole = 0;
if(filter_input(INPUT_POST, 'idrole') !== null)
    $idrole = (int) filter_input(INPUT_POST, 'idrole');

$defaultidrole = 0;
if(filter_input(INPUT_POST, 'defaultidrole') !== null)
    $defaultidrole = (int) filter_input(INPUT_POST, 'defaultidrole');

(new \model\action(\model\env::session_src()))->updateprojectsecurityusers($seccode, $idrole, $defaultidrole, $args);

require \model\route::script('project/security/index.php?idproject={0}', \model\env::session_idproject());
