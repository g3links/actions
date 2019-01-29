<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$idorder = 0;
if (filter_input(INPUT_GET, 'idorder') !== null)
    $idorder = (int) filter_input(INPUT_GET, 'idorder');

if (filter_input(INPUT_POST, 'idorder') !== null)
    $idorder = (int) filter_input(INPUT_POST, 'idorder');

$idgate = 2;
if (filter_input(INPUT_GET, 'idgate') !== null)
    $idgate = (int) filter_input(INPUT_GET, 'idgate');

if (filter_input(INPUT_POST, 'idgate') !== null)
    $idgate = (int) filter_input(INPUT_POST, 'idgate');

$updateorder = new stdClass();
$updateorder->idorder = $idorder;
$updateorder->toidgate = $idgate;

(new \model\ext\market\market(\model\env::session_src()))->setUpdateOrderGate($updateorder);

//refresh
require_once \model\route::script('style.php');
\model\route::refresh('orderadmon', ['g3ext/market/order/index.php?idproject={0}', \model\env::session_idproject()], \model\env::session_idproject());
\model\route::refresh('start', 'start/index.php', \model\env::getUserIdProject());
