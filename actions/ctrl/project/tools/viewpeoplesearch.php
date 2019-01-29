<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3session.php';

if (filter_input(INPUT_GET, 'search') !== null) {
    $search = filter_input(INPUT_GET, 'search');
    $limit = 25;
    $contacts = (new \model\user)->getpeople($search, $limit);
}


$lexi = \model\lexi::getall('g3/project');
$data = [
    'lbl_title' => $lexi['sys087'],
    'th_col1' => $lexi['sys043'],
    'th_col2' => $lexi['sys029'],
    'lbl_notfound' => $lexi['sys044'],
    'contacts' => $contacts ?? [],
];
\model\route::render('project/tools/viewpeoplesearch.twig', $data);
