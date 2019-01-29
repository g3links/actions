<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$periods = (new \model\ext\market\bp(\model\env::session_src()))->getPeriods(true);

$lexi = \model\lexi::getall('actions/bp');
$data = [
    'periods' => $periods,
    'th_col1' => $lexi['sys030'],
    'th_col2' => $lexi['sys031'],
    'th_col3' => $lexi['sys111'],
    'th_col4' => $lexi['sys112'],
    'lbl_notfound' => $lexi['sys044'],
];
\model\route::render('g3ext/market/period/loaddata.twig', $data);
