<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$idnote = 0;
if(filter_input(INPUT_GET, 'idnote') !== null)
    $idnote = filter_input(INPUT_GET, 'idnote');

(new \model\action(\model\env::session_src()))->archiveNote($idnote);
