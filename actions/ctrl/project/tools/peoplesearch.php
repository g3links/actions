<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3session.php';

$search = '';
if (filter_input(INPUT_GET, 'search') !== null) 
    $search = filter_input(INPUT_GET, 'search');

$lexi = \model\lexi::getall('g3/project');
require_once \model\route::script('style.php');
$data = [
    'searchpeopleroute' => \model\route::form('project/tools/viewpeoplesearch.php?search=[search]'),
    'lbl_search' => $lexi['sys049'],
    'search' => $search,
    'lbl_tip' => $lexi['sys088'],
];
\model\route::render('project/tools/peoplesearch.twig', $data);

