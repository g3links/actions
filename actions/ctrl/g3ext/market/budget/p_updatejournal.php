<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$idbb = 0;
if (filter_input(INPUT_GET, 'id') !== null)
    $idbb = (int) filter_input(INPUT_GET, 'id');

$idfiscalperiod = 0;
if (filter_input(INPUT_GET, 'idfiscal') !== null)
    $idfiscalperiod = (int)filter_input(INPUT_GET, 'idfiscal');

$idbg = 0;
if (filter_input(INPUT_POST, 'idbg') !== null)
    $idbg = (int) filter_input(INPUT_POST, 'idbg');

$idproduct = 0;
if (filter_input(INPUT_POST, 'idproduct') !== null)
    $idproduct = (int) filter_input(INPUT_POST, 'idproduct');

$args = filter_input_array(INPUT_POST);

(new \model\ext\market\bp(\model\env::session_src()))->setJournal($idbb, $idfiscalperiod, $idproduct, $args);

require_once \model\route::script('style.php');
\model\route::refresh('projsetup', ['g3ext/market/budget/index.php?idproject={0}&id={1}&idfiscal={2}', \model\env::session_idproject(), $idbg, $idfiscalperiod], \model\env::session_idproject());

