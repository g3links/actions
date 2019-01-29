<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$iduser = 0;
if(filter_input(INPUT_GET, 'iduser') !== null)
    $iduser = (int) filter_input(INPUT_GET, 'iduser');

(new \model\action(\model\env::session_src()))->setprojectownerdelete($iduser);


require \model\route::script('project/admon/index.php?idproject={0}', \model\env::session_idproject());

