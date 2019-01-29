<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$currencies = (new \model\ext\market\market(\model\env::session_src()))->getCurrencies(true);

$lexi = \model\lexi::getall('g3ext/market');
$data = [
    'currencies' => $currencies,
    'th_col1' => $lexi['sys059'],
    'th_col2' => $lexi['sys060'],
    'th_col3' => $lexi['sys061'],
    'lbl_notfound' => $lexi['sys004'],
];
\model\route::render('g3ext/market/currency/loaddata.twig', $data);
