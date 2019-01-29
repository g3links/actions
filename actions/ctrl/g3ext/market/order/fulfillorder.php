<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$idorder = 0;
if (filter_input(INPUT_GET, 'id') !== null) {
    $idorder = (INT)filter_input(INPUT_GET, 'id');
}

$downloadfileroute = \model\route::form('g3ext/market/product/p_downloadfile.php?idproject=[idproject]&filename=[attachedfile]');
$result = (new \model\ext\market\market(\model\env::session_src()))->getOrder($idorder, $downloadfileroute);

$lexi = \model\lexi::getall('g3ext/market');
$data = [
    'order' => $result->order,
    'orderdetails' => $result->orderdetails,
    'lbl_title' => $lexi['sys033'],
    'lbl_submit' => $lexi['sys038'],
    'th_col1' => $lexi['sys005'],
    'th_col2' => $lexi['sys007'],
    'th_col3' => $lexi['sys031'],
    'th_col4' => $lexi['sys034'],
    'lbl_deletefulfill' => $lexi['sys035'],
];
if($result->isrole) {
    $data += [
        'editfullfilroute' => \model\route::form('g3ext/market/order/p_editfulfill.php?idproject={0}&id={1}', \model\env::session_idproject(), $idorder),
        'lbl_requiredfull' => $lexi['sys042'],
    ];
}
\model\route::render('g3ext/market/order/fulfillorder.twig', $data);
