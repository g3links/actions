<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$lastviewgate = 0;
if (filter_input(INPUT_GET, 'idgate') !== null)
    $lastviewgate = (int) filter_input(INPUT_GET, 'idgate');

$navpage = 0;
if (filter_input(INPUT_GET, 'navpage') !== null)
    $navpage = (int) filter_input(INPUT_GET, 'navpage');

////sort ************************
//$sortname = '';
//if (filter_input(INPUT_GET, 'sort') !== null)
//    $sortname = (string) filter_input(INPUT_GET, 'sort');
//
//$prev_sortdirection = '';
//if (filter_input(INPUT_GET, 'sortdirection') !== null) 
//    $prev_sortdirection = (string) filter_input(INPUT_GET, 'sortdirection');
//
//$sortdirection = \model\utils::getSortDirection($sortname, $prev_sortdirection);
//
//$_sort = $sortdirection ? 'desc' : '';
//$_imgsort = $sortdirection ? 'sortDsc' : 'sortAsc';
//
////set images to default none
//$s_col1 = '';
//$s_col2 = '';
//$s_col3 = '';
//$s_col4 = '';
//$s_col5 = '';
//$s_col6 = '';
//$s_col8 = '';
//
//$sortfields = ['idpriority' => $_sort, 'idorder' => '']; //default
//if ($sortname === '') {
//    $s_col1 = $_imgsort;
//}
//if ($sortname === 'PRDCODE') {
//    $s_col2 = $_imgsort;
//    $sortfields = ['productcode' => $_sort, 'productname' => '', 'idorder' => ''];
//}
//if ($sortname === 'PRD') {
//    $s_col3 = $_imgsort;
//    $sortfields = ['productname' => $_sort, 'idorder' => ''];
//}
//if ($sortname === 'QTY') {
//    $s_col4 = $_imgsort;
//    $sortfields = ['orderquantity' => $_sort, 'productcode' => '', 'idorder' => ''];
//}
//if ($sortname === 'QTYF') {
//    $s_col5 = $_imgsort;
//    $sortfields = ['qtyfulfill' => $_sort, 'productcode' => '', 'idorder' => ''];
//}
//if ($sortname === 'PRICE') {
//    $s_col6 = $_imgsort;
//    $sortfields = ['idcurrencyto' => $_sort, 'saleprice' => $_sort, 'productcode' => '', 'idorder' => ''];
//}
//if ($sortname === 'DATE') {
//    $s_col8 = $_imgsort;
//    $sortfields = ['createdon' => $_sort, 'idorder' => ''];
//}

$result = (new \model\ext\market\market(\model\env::session_src()))->getOrders($lastviewgate, $navpage);

// page
$total_records = $result->total_records;
$max_records = $result->max_records;
require \model\route::script('g3/footpage.php');

$lexi = \model\lexi::getall('g3ext/market');
$data = [
    'orders' => $result->orders,
    'Gates' => $result->Gates,
    'lastviewgate' => $result->idgate,
    'orderhistoryroute' => \model\route::form('g3ext/market/order/history.php?idproject={0}&idorder=[idorder]', \model\env::session_idproject()),
    'viewtaskroute' => \model\route::window('action', ['actions/action/index.php?idproject={0}&idtask=[idtask]', \model\env::session_idproject()], \model\env::session_idproject(), \model\lexi::get('actions', 'sys067'), ''),
    'historyactionroute' => \model\route::form('g3ext/market/order/actionhistory.php?idproject={0}&idorder=[idorder]', \model\env::session_idproject()),
    'th_col2' => $lexi['sys012'],
    'th_col3' => $lexi['sys005'],
    'th_col4' => $lexi['sys007'],
    'th_col5' => $lexi['sys031'],
    'th_col6' => $lexi['sys006'],
    'th_col8' => $lexi['sys009'],
    'lbl_notfound' => $lexi['sys004'],
    'lbl_viewhistory' => $lexi['sys026'],
    'lbl_historyaction' => $lexi['sys045'],
//    'sortdirection' => $sortname . '_' . ($sortdirection ? 'desc' : ''),
//    'th_col1' => $lexi['sys008'],
//    's_col1' => $s_col1,
//    's_col2' => $s_col2,
//    's_col3' => $s_col3,
//    's_col4' => $s_col4,
//    's_col5' => $s_col5,
//    's_col6' => $s_col6,
//    'th_col7' => $lexi['sys009'],
//    's_col8' => $s_col8,
];
if ($result->isrole) {
    $data += [
        'updateordergateroute' => \model\route::form('g3ext/market/order/change-status.php?idproject={0}&idorder=[idcart]&idgate=[idgate]', \model\env::session_idproject()),
        'updateorderdetailroute' => \model\route::form('g3ext/market/order/fulfillorder.php?idproject={0}&id=[id]', \model\env::session_idproject()),
        'lbl_cancel' => $lexi['sys003'],
        'lbl_statusgate' => $lexi['sys002'],
        'lbl_update' => $lexi['sys038'],
        'lbl_warningopen' => $lexi['sys039'],
            //       'warningclose' => !$result->allowedit ? $lexi['sys029'] : '',
    ];
}
if ($result->isroleaction) {
    $data += [
        'newactionroute' => \model\route::window('newaction', ['g3ext/market/order/newaction.php?idproject={0}&idorder=[idorder]', \model\env::session_idproject()], \model\env::session_idproject(), \model\lexi::get('g3/project', 'sys009')),
    ];
}
\model\route::render('g3ext/market/order/list.twig', $data);
