<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$idcart = 0;
if (filter_input(INPUT_GET, 'idcart') !== null)
    $idcart = (int) filter_input(INPUT_GET, 'idcart');

$data = [
    'lbl_hidehistory' => \model\lexi::get('g3ext/market','sys027'),
    'history' => (new \model\ext\market\market(\model\env::session_src()))->getCartHistory($idcart),
];
\model\route::render('g3ext/market/cart/history.twig', $data);
