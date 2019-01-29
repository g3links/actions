<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$idtask = 0;
if (filter_input(INPUT_GET, 'idtask') !== null) 
    $idtask = (int)filter_input(INPUT_GET, 'idtask');

$actions = (new \model\action(\model\env::session_src()))->getActionsToAttach($idtask);

$sortfields = ['selected' => 'desc', 'title' => ''];
$actions = \model\utils::sorttakeList($actions, $sortfields, 0, 0);

$data = [
    'actions' => $actions,
];
\model\route::render('actions/action/taskstoattach.twig', $data);
