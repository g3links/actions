<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$search = '';
if (filter_input(INPUT_GET, 'search') !== null) 
    $search = filter_input(INPUT_GET, 'search');

$users = (new \model\user)->search($search, 5); // take first 5 records

$lexi = \model\lexi::getall();
$data = [
    'lbl_title' => $lexi['prj053'],
    'th_col1' => $lexi['prj043'],
    'th_col2' => $lexi['prj029'],
    'users' => $users,
    'lbl_notfound' => count($users) === 0 ? $lexi['prj044'] : '',
];
\model\route::render('project/users/searchtoinvite.twig', $data);
