<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$search = '';
if (filter_input(INPUT_GET, 'search') !== null) 
    $search = filter_input(INPUT_GET, 'search');

$limit = 10;
$projects = (new \model\project)->searchArchivedProjects($search, $limit); // take first $limit records

$lexi = \model\lexi::getall('g3/project');
$data = [
    'projects' => $projects,
    'lbl_title' => $lexi['sys053'],
    'lbl_tip' => $lexi['sys115'],
    'th_col1' => $lexi['sys043'],
    'th_col2' => $lexi['sys084'],
    'th_col3' => $lexi['sys039'],
    'lbl_notfound' => $lexi['sys044'],
];
\model\route::render('project/admon/projrestoresearch.twig', $data);
