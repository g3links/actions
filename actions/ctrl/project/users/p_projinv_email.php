<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

//if (filter_input(INPUT_POST, 'sendemailinvitation') !== null) {
    $memberrole = 0;
    if (filter_input(INPUT_POST, 'role') !== null)
        $memberrole = (int) filter_input(INPUT_POST, 'role');

    $targetiduser = 0;
    if (filter_input(INPUT_POST, 'targetiduser') !== null)
        $targetiduser = (int) filter_input(INPUT_POST, 'targetiduser');

    (new \model\project)->registerinvitation(\model\env::session_idproject(), \model\env::getUserName(), $targetiduser, $memberrole, 'g3/*/invitation.html');
//}

require \model\route::script('project/users/index.php');
