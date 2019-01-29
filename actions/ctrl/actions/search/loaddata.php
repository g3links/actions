<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$searchtext = '';
if (filter_input(INPUT_GET, 'search') !== null) 
    $searchtext = filter_input(INPUT_GET, 'search');

$type = 'txt';
if (filter_input(INPUT_GET, 'type') !== null) 
    $type = filter_input(INPUT_GET, 'type');

$navpage = 0;
if (filter_input(INPUT_GET, 'navpage') !== null) 
    $navpage = (int) filter_input(INPUT_GET, 'navpage');

$result = (new \model\action(\model\env::session_src()))->searchAllActions($searchtext, $type, \model\env::session_lastviewgate(), \model\env::session_idproject(), $navpage);

$total_records = $result->total_records;
$max_records = $result->max_records;
require \model\route::script('g3/footpage.php');

$data = [
    'listactions' => $result->actions,
    'searchactionsroute' => \model\route::form('actions/search/loaddata.php?idproject={0}&searchtext={1}&navpage={2}&type={3}&idgate={4}',  \model\env::session_idproject(), $searchtext, $navpage, $type, \model\env::session_lastviewgate()),
    'viewtaskroute' => \model\route::window('action',['actions/action/index.php?idproject={0}&idtask={1}', '[idproject]', '[idtask]'],'', \model\lexi::get('actions', 'sys067'),''),
    'lbl_notfound' => \model\lexi::get('actions','sys044'),
];
\model\route::render('actions/search/loaddata.twig', $data);
