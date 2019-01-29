<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$lastviewgate = 0;
if (filter_input(INPUT_GET, 'idgate') !== null)
    $lastviewgate = (int) filter_input(INPUT_GET, 'idgate');

$navpage = 0;
if (filter_input(INPUT_GET, 'navpage') !== null)
    $navpage = (int) filter_input(INPUT_GET, 'navpage');


$downloadfileroute = \model\route::form('g3ext/market/product/p_downloadfile.php?idproject=[idproject]&filename=[attachedfile]');
$result = (new \model\ext\market\market(\model\env::session_src()))->getCarts($lastviewgate, $navpage, $downloadfileroute);

// pages
$total_records = $result->total_records;
$max_records = $result->max_records;
require \model\route::script('g3/footpage.php');

$lexi = \model\lexi::getall('g3ext/market');
$data = [
    'carts' => $result->carts,
    'lastviewgate' => $result->lastviewgate,
    'Gates' => (new \model\action(\model\env::session_src()))->getGates(),
    'carthistoryroute' => \model\route::form('g3ext/market/cart/history.php?idproject={0}&idcart=[idcart]', \model\env::session_idproject()),
    'lbl_viewhistory' => $lexi['sys026'],
    'th_col2' => $lexi['sys005'],
    'th_col3' => $lexi['sys007'],
    'th_col4' => $lexi['sys006'],
    'th_col5' => $lexi['sys009'],
    'lbl_notfound' => $lexi['sys004'],
    'lbl_order' => $lexi['sys030'],
];
if ($result->isrole) {
    $data += [
        'updatecartgateroute' => \model\route::form('g3ext/market/cart/change-status.php?idproject={0}&idcart=[idcart]&idgate=[idgate]', \model\env::session_idproject()),
        'lbl_cancel' => $lexi['sys003'],
        'lbl_statusgate' => $lexi['sys002'],
        'lbl_warningopen' => $lexi['sys028'],
//        'lbl_warningclose' => $lexi['sys029'],
//        'lbl_warningdelete' => $lexi['sys043'],
    ];
}
if ($result->isrole & $result->allowedit) {
    $data += [
        'editcartdetailroute' => \model\route::form('g3ext/market/cart/m_carteditproduct.php?idproject={0}&id=[id]', \model\env::session_idproject()),
        'searchproductsroute' => \model\route::form('g3ext/market/cart/searchproducts.php?idproject={0}', \model\env::session_idproject()),
        'lbl_newcartdetail' => $lexi['sys020'],
    ];
}
\model\route::render('g3ext/market/cart/list.twig', $data);
