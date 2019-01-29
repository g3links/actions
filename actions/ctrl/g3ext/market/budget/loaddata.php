<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$idbg = 0;
if (filter_input(INPUT_GET, 'id') !== null) {
    $idbg = (int) filter_input(INPUT_GET, 'id');
}

$idfiscalperiod = 0;
if (filter_input(INPUT_GET, 'idfiscal') !== null) {
    $idfiscalperiod = (int)filter_input(INPUT_GET, 'idfiscal');
}

$result = (new \model\ext\market\bp(\model\env::session_src()))->getBudgetGridData($idbg, $idfiscalperiod);

$lexi = \model\lexi::getall('actions/bp');

$budget_header = [];
$budget_header[] = $lexi['sys005'];
$budget_header[] = $lexi['sys030'];
foreach ($result->periods as $period) {
    $budget_header[] = $period->name;
}

$rows = \model\utils::filter($result->budget_rows, '!$v->hidden');

if ($result->budgetgroupreport === \model\ext\market\bp::BUDGET_BOOK) {
    $data = [
        'fiscalperiodname' => $result->fiscalperiodname,
        'idfiscalperiod' => $idfiscalperiod,
        'budget_header' => $budget_header,
        'budget_rows' => $rows,
    ];
    if ($result->isrole) {
        $data += [
            'budgetjournalroute' => \model\route::form('g3ext/market/budget/m_editjournal.php?idproject={0}&idfiscal={1}&idbg={2}&id=[id]&idproduct=[idproduct]', \model\env::session_idproject(), $idfiscalperiod, $idbg),
        ];
    }
    \model\route::render('g3ext/market/budget/listbook.twig', $data);
}
if ($result->budgetgroupreport === \model\ext\market\bp::BUDGET_PROFIT) {
    $data = [
        'fiscalperiodname' => $result->fiscalperiodname,
        'idfiscalperiod' => $idfiscalperiod,
        'budget_header' => $budget_header,
        'budget_rows' => $rows,
        'th_col1' => $lexi['sys024'],
        'th_col2' => $lexi['sys121'],
    ];
    \model\route::render('g3ext/market/budget/profit.twig', $data);
}
