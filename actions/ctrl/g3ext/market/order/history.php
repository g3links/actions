<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$idorder = 0;
if (filter_input(INPUT_GET, 'idorder') !== null)
    $idorder = (int) filter_input(INPUT_GET, 'idorder');

$data = [
    'lbl_hidehistory' => \model\lexi::get('g3ext/market','sys027'),
    'history' => (new \model\ext\market\market(\model\env::session_src()))->getOrderHistory($idorder),
];
\model\route::render('g3ext/market/order/history.twig', $data);
