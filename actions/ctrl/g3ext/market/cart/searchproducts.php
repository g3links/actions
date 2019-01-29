<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$idcart = 0;
if (filter_input(INPUT_GET, 'idcart') !== null)
    $idcart = (int) filter_input(INPUT_GET, 'idcart');

$tags = (new \model\ext\market\market(\model\env::session_src()))->getSearchTags();

$lexi = \model\lexi::getall('g3ext/market');
$data = [
    'tags' => $tags,
    'lbl_title' => $lexi['sys022'],
    'lbl_searchwarning' => \model\utils::format($lexi['sys021'], 3),
    'listproductsroute' => \model\route::form('g3ext/market/cart/listproducts.php?idproject={0}&idcart={1}&search=[search]&tag=[tag]', \model\env::session_idproject(), $idcart),
];
\model\route::render('g3ext/market/cart/searchproducts.twig', $data);
