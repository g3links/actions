<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

//$idcart = 0;
//if (filter_input(INPUT_GET, 'idcart') !== null)
//    $idcart = (int) filter_input(INPUT_GET, 'idcart');

$searchtext = '';
if (filter_input(INPUT_GET, 'search') !== null)
    $searchtext = filter_input(INPUT_GET, 'search');

$idtag = '';
if (filter_input(INPUT_GET, 'tag') !== null)
    $idtag = filter_input(INPUT_GET, 'tag');

$downloadfileroute = \model\route::form('g3ext/market/product/p_downloadfile.php?idproject=[idproject]&filename=[attachedfile]');
$result = (new \model\ext\market\market(\model\env::session_src()))->searchCartProducts($searchtext, $idtag, $downloadfileroute);

$lexi = \model\lexi::getall('g3ext/market');
$data = [
    'products' => $result->products,
    'th_col1' => $lexi['sys005'],
    'th_col2' => $lexi['sys006'],
    'lbl_notfound' => $lexi['sys004'],
    'lbl_addtocart' =>  $lexi['sys023'],
];
if($result->isrole) {
    $data += [
        'addtocartroute' => \model\route::form('g3ext/market/cart/p_addtocart.php?idproject={0}&idcart=[idcart]&id=[id]&qty=[qty]', \model\env::session_idproject()),
    ];
}
\model\route::render('g3ext/market/cart/listproducts.twig', $data);
