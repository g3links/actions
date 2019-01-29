<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$idcart = 0;
if (filter_input(INPUT_GET, 'idcart') !== null)
    $idcart = (int) filter_input(INPUT_GET, 'idcart');

if (filter_input(INPUT_POST, 'idcart') !== null)
    $idcart = (int) filter_input(INPUT_POST, 'idcart');

$idgate = 2;
if (filter_input(INPUT_GET, 'idgate') !== null)
    $idgate = (int) filter_input(INPUT_GET, 'idgate');

if (filter_input(INPUT_POST, 'idgate') !== null)
    $idgate = (int) filter_input(INPUT_POST, 'idgate');

$updatecart = new stdClass();
$updatecart->idcart = $idcart;
$updatecart->toidgate = $idgate;

(new \model\ext\market\market(\model\env::session_src()))->setUpdateCartGate($updatecart);

//refresh
require_once \model\route::script('style.php');
\model\route::refresh('marketadmon', ['g3ext/market/cart/index.php?idproject={0}', \model\env::session_idproject()], \model\env::session_idproject());
\model\route::refresh('start', 'start/index.php', \model\env::getUserIdProject());
