<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$commenttext = '';
if(filter_input(INPUT_POST, 'commenttext') !== null)
    $commenttext = filter_input(INPUT_POST, 'commenttext');

(new \model\action(\model\env::session_src()))->insertcomment(\model\env::session_idtaskselected(), $commenttext);

require \model\route::script('actions/action/index.php');
