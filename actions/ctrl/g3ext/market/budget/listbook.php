<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$data = [
    'isrole' => $isrole,
    'idfiscalperiod' => $idfiscalperiod,
    'fiscalperiodname' => $fiscalperiodname,
    'budget_header' => $budget_header,
    'budget_rows' => $budget_rows,
    'budgetjournalroute' => \model\route::form('g3ext/market/budget/m_editjournal.php?idproject={0}&idfiscal={1}&idbg={2}&id=[id]&idproduct=[idproduct]', \model\env::session_idproject(), $idfiscalperiod, $idbg),
];
\model\route::render('g3ext/market/budget/listbook.twig', $data);
