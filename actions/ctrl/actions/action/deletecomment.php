<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$idcomment = 0;
if (filter_input(INPUT_GET, 'idcomment') !== null) 
    $idcomment = (int) filter_input(INPUT_GET, 'idcomment');

(new \model\action(\model\env::session_src()))->updatecomment($idcomment, 1);

require_once \model\route::script('style.php');
\model\route::refresh('action',['actions/action/index.php?idproject={0}&idtask={1}', \model\env::session_idproject(), \model\env::session_idtaskselected()], \model\env::session_idproject());
