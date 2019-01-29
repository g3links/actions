<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$searchtext = '';
if (filter_input(INPUT_GET, 'search') !== null) 
    $searchtext = filter_input(INPUT_GET, 'search');

$type = 'txt';
if (filter_input(INPUT_GET, 'type') !== null) 
    $type = filter_input(INPUT_GET, 'type');


require_once \model\route::script('style.php');
$data = [
    'searchtext' => $searchtext,
    'loaddataroute' => \model\route::form('actions/search/loaddata.php?idproject={0}&search={1}&type={2}&idgate={3}',  \model\env::session_idproject(), $searchtext, $type, \model\env::session_lastviewgate()),
    'lbl_title' => \model\lexi::get('actions','sys154'),
];
\model\route::render('actions/search/index.twig', $data);
