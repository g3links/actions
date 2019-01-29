<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$idfiscalperiod = 0;
if (filter_input(INPUT_GET, 'idfiscal') !== null) 
    $idfiscalperiod = (int)filter_input(INPUT_GET, 'idfiscal');

$idbg = '';
if (filter_input(INPUT_GET, 'id') !== null) 
    $idbg = (int)filter_input(INPUT_GET, 'id');

(new \model\ext\market\bp(\model\env::session_src()))->deleteJournalGroup($idbg, $idfiscalperiod);

require_once \model\route::script('style.php');
\model\route::refresh('projsetup',['g3ext/market/budget/index.php?idproject={0}&id={1}&idfiscal={2}', \model\env::session_idproject(), $idbg, $idfiscalperiod], \model\env::session_idproject());
