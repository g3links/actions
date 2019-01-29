<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$idorder = 0;
if (filter_input(INPUT_GET, 'idorder') !== null)
    $idorder = (int) filter_input(INPUT_GET, 'idorder');

$actionshistory = (new \model\ext\market\market(\model\env::session_src()))->getOrderActionHistory($idorder);

$data = [
    'actionshistory' => $actionshistory,
    'lbl_historyaction' => \model\lexi::get('g3ext/market','sys045'),
];
\model\route::render('g3ext/market/order/actionhistory.twig', $data);
