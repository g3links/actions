<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$result = (new \model\ext\market\bp(\model\env::session_src()))->getFiscalPeriodsSession();

$lexi = \model\lexi::getall('actions/bp');

require_once \model\route::script('style.php');
$data = [
    'fiscalperiods' => $result->fiscalperiods,
    'lbl_newfiscalperiod' => $lexi['sys113'],
    'th_col1' => $lexi['sys030'],
    'th_col2' => $lexi['sys031'],
    'th_col3' => $lexi['sys010'],
    'lbl_notfound' => $lexi['sys044'],
];
if($result->isrole) {
    $data += [
        'editfiscalperiodroute' => \model\route::form('g3ext/market/fiscalperiod/m_editfiscalperiod.php?idproject={0}&idfiscalperiod=[idfiscalperiod]', \model\env::session_idproject()),
    ];
}
\model\route::render('g3ext/market/fiscalperiod/index.twig', $data);
