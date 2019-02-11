<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3session.php';

$search = '';
if (filter_input(INPUT_GET, 'search') !== null) 
    $search = filter_input(INPUT_GET, 'search');

$limit = 25;

$lexi = \model\lexi::getall();
$data = [
    'lbl_title' => $lexi['prj046'],
    'th_col1' => $lexi['prj082'],
    'th_col2' => $lexi['prj084'],
    'lbl_notfound' => $lexi['prj044'],
    'projects' => (new \model\project)->getpublicprojects($search, $limit),
];
\model\route::render('project/tools/viewprojectsearch.twig', $data);
