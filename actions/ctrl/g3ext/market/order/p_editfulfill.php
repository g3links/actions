<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$args = filter_input_array(INPUT_POST);

$idorder = 0;
if (filter_input(INPUT_GET, 'id') !== null) 
    $idorder = (INT) filter_input(INPUT_GET, 'id');

$fullrequired = false;
if (filter_input(INPUT_POST, 'fullrequired') !== null) 
    $fullrequired = true;

$updateorder = new stdClass;
$updateorder->idorder = $idorder;
$updateorder->fullrequired = $fullrequired;

(new \model\ext\market\market(\model\env::session_src()))->setOrderFulfill($updateorder, $args);

require \model\route::script('g3ext/market/order/index.php?idproject={0}',\model\env::session_idproject());
