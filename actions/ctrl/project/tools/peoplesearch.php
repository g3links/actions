<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3session.php';

$search = '';
if (filter_input(INPUT_GET, 'search') !== null) 
    $search = filter_input(INPUT_GET, 'search');

$lexi = \model\lexi::getall();
require_once \model\route::script('style.php');
$data = [
    'searchpeopleroute' => \model\route::form('project/tools/viewpeoplesearch.php?search=[search]'),
    'lbl_search' => $lexi['prj049'],
    'search' => $search,
    'lbl_tip' => $lexi['prj088'],
];
\model\route::render('project/tools/peoplesearch.twig', $data);

