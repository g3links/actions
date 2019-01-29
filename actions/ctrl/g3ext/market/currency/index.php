<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$result = (new \model\ext\market\market(\model\env::session_src()))->getSessionCurrency();
if ($result->allowedit) {
    //required args
    $module = $result->module;
    $modulename = $result->modulename;
    require \model\route::script('project/links/projectservices.php');
}

require_once \model\route::script('style.php');
$data = [
    'loaddataroute' => \model\route::form('g3ext/market/currency/loaddata.php?idproject={0}', \model\env::session_idproject()),
];
\model\route::render('g3ext/market/currency/index.twig', $data);
