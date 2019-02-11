<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3session.php';

$search = '';
if (filter_input(INPUT_GET, 'search') !== null) {
    $search = filter_input(INPUT_GET, 'search');
}

$isauthorized = \model\env::isauthorized();

$lexi = \model\lexi::getall();
require_once \model\route::script('style.php');
$data = [
    'search' => $search,
    'isauthorized' => $isauthorized,
    'searchprojectroute' => \model\route::form('project/tools/viewprojectsearch.php?search=[search]'),
    'joinprojectroute' => \model\route::form('project/tools/p_joinproject.php'),
    'lbl_search' => $lexi['prj049'],
    'lbl_join' => $lexi['prj081'],
    'lbl_desc' => $lexi['prj082'],
    'submit' => $lexi['prj081'],
];
\model\route::render('project/tools/projectsearch.twig', $data);
