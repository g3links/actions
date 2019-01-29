<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$idcartdetail = 0;
if (filter_input(INPUT_GET, 'id') !== null) {
    $idcartdetail = (int) filter_input(INPUT_GET, 'id');
}

$result = (new \model\ext\market\market(\model\env::session_src()))->getCartProduct($idcartdetail);

$lexi = \model\lexi::getall('g3ext/market');
$data = [
    'product' => $result->product,
    'lbl_title' => $lexi['sys010'],
    'lbl_name' => $lexi['sys011'],
    'lbl_keycode' => $lexi['sys012'],
    'lbl_description' => $lexi['sys013'],
    'lbl_manifest' => $lexi['sys014'],
    'th_col1' => $lexi['sys015'],
    'th_col2' => $lexi['sys011'],
    'th_col3' => $lexi['sys006'],
    'th_col4' => $lexi['sys007'],
    'lbl_missing_currency' => $lexi['sys015'],
    'lbl_save' => $lexi['sys016'],
    'lbl_submit' => $lexi['sys017'],
];
if ($result->isrole) {
    $data += [
        'updateproductroute' => \model\route::form('g3ext/market/cart/p_editcartproduct.php?idproject={0}&id={1}', \model\env::session_idproject(), $idcartdetail),
    ];
}
\model\route::render('g3ext/market/cart/m_carteditproduct.twig', $data);

