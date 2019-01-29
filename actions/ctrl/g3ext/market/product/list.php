<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$navpage = 0;
if (filter_input(INPUT_GET, 'navpage') !== null)
    $navpage = (int) filter_input(INPUT_GET, 'navpage');

$searchtext = '';
if (filter_input(INPUT_GET, 'search') !== null)
    $searchtext = filter_input(INPUT_GET, 'search');

$idtag = '';
if (filter_input(INPUT_GET, 'tag') !== null)
    $idtag = filter_input(INPUT_GET, 'tag');

$ignoreshared = false;
if (filter_input(INPUT_GET, 'ignoreshared') !== null)
    $ignoreshared = true;

$downloadfileroute = \model\route::form('g3ext/market/product/p_downloadfile.php?idproject=[idproject]&filename=[attachedfile]');
$result = (new \model\ext\market\market(\model\env::session_src()))->searchProducts($searchtext, $idtag, $navpage, $ignoreshared, $downloadfileroute);

// page
$total_records = $result->total_records;
$max_records = $result->max_records;
require \model\route::script('g3/footpage.php');

$lexi = \model\lexi::getall('g3ext/market');

$lbl_filter = '';
if (!empty($searchtext)) {
    $lbl_filter = \model\utils::format($lexi['sys058'], $searchtext);
} else {
    if (!empty($idtag)) {
        $lbl_filter = \model\utils::format($lexi['sys058'], $idtag);
    }
}

$data = [
    'products' => $result->products,
    'lbl_filter' => $lbl_filter,
    'th_col1' => '',
    'th_col2' => $lexi['sys005'],
    'th_col3' => $lexi['sys006'],
    'lbl_notfound' => $lexi['sys004'],
    'lbl_inactive' => $lexi['sys048'],
];
\model\route::render('g3ext/market/product/list.twig', $data);
