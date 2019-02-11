<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3session.php';

if (filter_input(INPUT_GET, 'search') !== null) {
    $search = filter_input(INPUT_GET, 'search');
    $limit = 25;
    $contacts = (new \model\user)->getpeople($search, $limit);
}


$lexi = \model\lexi::getall();
$data = [
    'lbl_title' => $lexi['prj087'],
    'th_col1' => $lexi['prj043'],
    'th_col2' => $lexi['prj029'],
    'lbl_notfound' => $lexi['prj044'],
    'contacts' => $contacts ?? [],
];
\model\route::render('project/tools/viewpeoplesearch.twig', $data);
