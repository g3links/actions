<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$idfiscalperiod = 0;
$fiscalname = '';
$seq = 0;
$isclosed = false;

if (filter_input(INPUT_POST, 'save') !== null) {
    $updatefiscalperiod = new stdClass();
    $updatefiscalperiod->idfiscalperiod = 0;
    $updatefiscalperiod->fiscalname = '';
    $updatefiscalperiod->seq = 0;
    $updatefiscalperiod->isclosed = false;

    $idfiscalperiod = (int) filter_input(INPUT_POST, 'idfiscalperiod');

    $updatefiscalperiod->idfiscalperiod = $idfiscalperiod;
    $updatefiscalperiod->fiscalname = filter_input(INPUT_POST, 'fiscalname');
    $updatefiscalperiod->seq = (int) filter_input(INPUT_POST, 'seq');
    $updatefiscalperiod->isclosed = (bool) filter_input(INPUT_POST, 'isclosed');

    (new \model\ext\market\bp(\model\env::session_src()))->setFiscalPeriod($idfiscalperiod, $updatefiscalperiod);
}

if (filter_input(INPUT_POST, 'delete') !== null) {
    $idfiscalperiod = (int) filter_input(INPUT_POST, 'idfiscalperiod');

    (new \model\ext\market\bp(\model\env::session_src()))->deleteFiscalPeriod($idfiscalperiod);
}

require \model\route::script('g3ext/market/fiscalperiod/index.php?idproject={0}', \model\env::session_idproject());



