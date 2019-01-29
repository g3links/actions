<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$search = '';
if (filter_input(INPUT_GET, 'search') !== null) 
    $search = filter_input(INPUT_GET, 'search');

$users = (new \model\user)->search($search, 5); // take first 5 records

$lexi = \model\lexi::getall('g3/project');
$data = [
    'lbl_title' => $lexi['sys053'],
    'th_col1' => $lexi['sys043'],
    'th_col2' => $lexi['sys029'],
    'users' => $users,
    'lbl_notfound' => count($users) === 0 ? $lexi['sys044'] : '',
];
\model\route::render('project/users/searchtoinvite.twig', $data);
