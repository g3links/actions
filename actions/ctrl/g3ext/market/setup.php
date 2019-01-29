<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$hasproducts = (new \model\ext\market\market(\model\env::session_src()))->hasProductService();

$lexi = \model\lexi::getall('actions/bp');

$modules = [];

$module = new \stdClass();
$module->isshared = (new \model\action(\model\env::session_src()))->isDataShared(\model\env::MODULE_PRODUCTS);
$module->approute = \model\route::window('projsetup', ['g3ext/market/product/index.php?idproject={0}', \model\env::session_idproject()], \model\env::session_idproject(), $lexi['sys051']);
$module->imagesymbol = 'imgProduct';
$module->moduleid = 'b02';
$modules[] = $module;

if ($hasproducts) {
    $module = new \stdClass();
    $module->approute = \model\route::window('projsetup', ['g3ext/market/currency/index.php?idproject={0}', \model\env::session_idproject()], \model\env::session_idproject(), $lexi['sys047']);
    $module->imagesymbol = 'imgCurrency';
    $module->moduleid = 'b01';
    $modules[] = $module;

    $module = new \stdClass();
    $module->approute = \model\route::window('projsetup', ['g3ext/market/financial/index.php?idproject={0}', \model\env::session_idproject()], \model\env::session_idproject(), $lexi['sys083']);
    $module->imagesymbol = 'imgFinance';
    $module->moduleid = 'b03';
    $modules[] = $module;

    $module = new \stdClass();
    $module->approute = \model\route::window('projsetup', ['g3ext/market/fiscalperiod/index.php?idproject={0}', \model\env::session_idproject()], \model\env::session_idproject(), $lexi['sys003']);
    $module->imagesymbol = 'imgFiscal';
    $module->moduleid = 'b04';
    $modules[] = $module;

    $module = new \stdClass();
    $module->approute = \model\route::window('projsetup', ['g3ext/market/period/index.php?idproject={0}', \model\env::session_idproject()], \model\env::session_idproject(), $lexi['sys085']);
    $module->imagesymbol = 'imgPeriod';
    $module->moduleid = 'b06';
    $modules[] = $module;

    $module = new \stdClass();
    $module->title = $lexi['sys004'];
    $module->imagesymbol = 'imgSales';
    $modules[] = $module;
}

$data = [
    'lbl_title' => $lexi['sys152'],
    'lbl_shared' => $t_shared,
    'idproject' => \model\env::session_idproject(),
    'modules' => $modules,
];
\model\route::render('project/setup/setup.twig', $data);

// special Reports        
if ($hasproducts) {
    $modelbp = new \model\ext\market\bp(\model\env::session_src());
    $budgetgroups = $modelbp->getBudgetGroups();
    $data = [
        'budgetreportroute' => \model\route::window('projsetup', ['g3ext/market/budget/index.php?idproject={0}&id=[ssid]&idfiscal=', \model\env::session_idproject()], \model\env::session_idproject(), ''),
        'budgetgroups' => $budgetgroups,
    ];
    \model\route::render('g3ext/market/budget/menu_cashflow.twig', $data);
}
