<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$filename = '';
if (filter_input(INPUT_GET,'filename') !== null) 
    $filename = filter_input(INPUT_GET,'filename');

(new \model\action(\model\env::session_src()))->deleteActionAttachedFile(\model\env::session_idtaskselected(), $filename);

require \model\route::script('actions/action/index.php');