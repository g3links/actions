<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$idbg = 0;
if (filter_input(INPUT_GET, 'id') !== null) 
    $idbg = (int) filter_input(INPUT_GET, 'id');

$idfiscalperiod = 0;
if (filter_input(INPUT_GET, 'idfiscal') !== null) 
    $idfiscalperiod = (int)filter_input(INPUT_GET, 'idfiscal');

$result = (new \model\ext\market\bp(\model\env::session_src()))->getBudgetSession($idbg, $idfiscalperiod);

$lexi = \model\lexi::getall('actions/bp');

require_once \model\route::script('style.php');
$data = [
    'fiscalperiods' => $result->fiscalperiods,
    'fiscalperiodname' => $result->fiscalperiodname,
    'idfiscalperiod' => $idfiscalperiod,
    'loaddataroute' => \model\route::form('g3ext/market/budget/loaddata.php?idproject={0}&id={1}&idfiscal=[idfiscal]',  \model\env::session_idproject(), $idbg),
    'lbl_title' => $lexi['sys082'],
    'lbl_deletejournal' => $lexi['sys081'],
    'lbl_cancel' => $lexi['sys021'],
    'lbl_group' => $lexi['sys092'],
];
if($result->isrole) {
    $data += [
        'deletejournalgrouproute' => \model\route::form('g3ext/market/budget/p_deletegroup.php?idproject={0}&id={1}&idfiscal=[idfiscal]', \model\env::session_idproject(), $idbg),
    ];
}
\model\route::render('g3ext/market/budget/index.twig', $data);
