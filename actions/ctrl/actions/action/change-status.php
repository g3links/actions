<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

// if changes status
$newidgate = 0;
if (filter_input(INPUT_GET, 'newidgate') !== null)
    $newidgate = (int)filter_input(INPUT_GET, 'newidgate');

(new \model\action(\model\env::session_src()))->setActionGate($newidgate, \model\env::session_idtaskselected(),'actions/*/statusaction.html');

require_once \model\route::script('style.php');

// restart Frame
\model\route::refresh('actions',['actions/index.php?idproject={0}', \model\env::session_idproject()], \model\env::session_idproject());
\model\route::close(\model\env::session_idproject(), 'action');
//\model\route::refresh('orderadmon', ['g3ext/market/order/index.php?idproject={0}', \model\env::session_idproject()], \model\env::session_idproject());
\model\route::refresh('start','start/index.php', \model\env::getUserIdProject());
