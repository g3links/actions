<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$search = '';
if (filter_input(INPUT_GET, 'search') !== null) 
    $search = filter_input(INPUT_GET, 'search');

$limit = 10;
$projects = (new \model\project)->searchArchivedProjects($search, $limit); // take first $limit records

$lexi = \model\lexi::getall();
$data = [
    'projects' => $projects,
    'lbl_title' => $lexi['prj053'],
    'lbl_tip' => $lexi['prj115'],
    'th_col1' => $lexi['prj043'],
    'th_col2' => $lexi['prj084'],
    'th_col3' => $lexi['prj039'],
    'lbl_notfound' => $lexi['prj044'],
];
\model\route::render('project/admon/projrestoresearch.twig', $data);
