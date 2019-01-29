<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$targetiduser = 0;
if (filter_input(INPUT_POST, 'targetiduser') !== null) 
    $targetiduser = (int)filter_input(INPUT_POST, 'targetiduser');

$idrole = 99;
if (filter_input(INPUT_POST, 'idrole') !== null) 
    $idrole = (int) filter_input(INPUT_POST, 'idrole');

if (isset($targetiduser) & $idrole !== 99) {
    if (filter_input(INPUT_POST, 'update') !== null) {
        $inactive = false;
        if (filter_input(INPUT_POST, 'inactive') !== null) {
            $inactive = true;
        }
        $idaccess = 1;
        if (filter_input(INPUT_POST, 'idaccess') !== null) {
            $idaccess = (int) filter_input(INPUT_POST, 'idaccess');
            if($idaccess < 1 | $idaccess > 3) {
                $idaccess = null;
            }
        }

        (new \model\project)->updateprojectrole(\model\env::session_idproject(), $targetiduser, $idrole, $inactive, $idaccess);
    }

    if (filter_input(INPUT_POST, 'insert') !== null) 
        (new \model\project)->insertprojectrole(\model\env::session_idproject(), $targetiduser, $idrole, 0, 1);
}

require \model\route::script('project/users/index.php?idproject={0}', \model\env::session_idproject());
