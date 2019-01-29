<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$idproduct = 0;
if (filter_input(INPUT_GET, 'idproduct') !== null)
    $idproduct = (int) filter_input(INPUT_GET, 'idproduct');

$result = (new \model\ext\market\market(\model\env::session_src()))->getProductDetail($idproduct);

$lexi = \model\lexi::getall('g3ext/market');
$data = [
    'product' => $result->product,
    'currencies' => $result->currencies,
    'lbl_edit' => $lexi['sys049'],
    'lbl_in' => $lexi['sys032'],
    'lbl_image' => $lexi['sys050'],
    'lbl_name' => $lexi['sys011'],
    'lbl_keycode' => $lexi['sys012'],
    'lbl_inactive' => $lexi['sys048'],
    'lbl_issales' => $lexi['sys051'],
    'lbl_ispurchase' => $lexi['sys052'],
    'lbl_productdescription' => $lexi['sys013'],
    'lbl_productmanifest' => $lexi['sys014'],
    'lbl_currency' => $lexi['sys015'],
    'lbl_price' => $lexi['sys006'],
    'lbl_metatag' => $lexi['sys057'],
];
if ($result->isrole) {
    $data += [
        'updateproductroute' => \model\route::form('g3ext/market/product/p_editproduct.php?idproject={0}&id={1}', \model\env::session_idproject(),$idproduct),
        'lbl_submit' => $lexi['sys016'],
        'lbl_submitdelete' => $lexi['sys017'],
    ];
}
\model\route::render('g3ext/market/product/editproduct.twig', $data);
