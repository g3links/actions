<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$idtask = 0;
if (filter_input(INPUT_GET, 'idtask') !== null) 
    $idtask = (int) filter_input(INPUT_GET, 'idtask');

$result = (new \model\action(\model\env::session_src()))->getUsersAndGroups($idtask);

$data = [
    'users' => $result->users,
    'groups' => $result->groups,
];
\model\route::render('project/users/m_userstofilter.twig', $data);
