<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$idfiscalperiod = 0;
if (filter_input(INPUT_GET, 'idfiscalperiod') !== null) 
    $idfiscalperiod = (int) filter_input(INPUT_GET, 'idfiscalperiod');

$isrole = \model\env::isUserAllow(\model\env::session_idproject(), \model\action::ROLE_FINANCIALS);

if ($idfiscalperiod !== 0) {
    $fiscalperiod = (new \model\ext\market\bp(\model\env::session_src()))->getFiscalPeriod($idfiscalperiod);
} else {
    $fiscalperiod = new \stdClass();
    $fiscalperiod->fiscalname = '';
    $fiscalperiod->idfiscalperiod = 0;
    $fiscalperiod->seq = 0;
}

$lexi = \model\lexi::getall('actions/bp');
$data = [
    'fiscalperiod' => $fiscalperiod,
    'lbl_title' => $lexi['sys003'],
    'lbl_fiscalname' => $lexi['sys030'],
    'lbl_seq' => $lexi['sys031'],
    'lbl_closed' => $lexi['sys010'],
    'lbl_submit' => $lexi['sys029'],
    'lbl_submitdelete' => $lexi['sys032'],
];
if($isrole) {
    $data += [
        'updatefiscalperiodroute' => \model\route::form('g3ext/market/fiscalperiod/p_updateperiod.php?idproject={0}', \model\env::session_idproject()),
    ];
}
\model\route::render('g3ext/market/fiscalperiod/m_editfiscalperiod.twig', $data);
