<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$result = (new \model\action(\model\env::session_src()))->getUsersAndGroups(\model\env::session_idtaskselected());

$lexi = \model\lexi::getall('actions');
$data = [
    'idproject' => \model\env::session_idproject(),
    'assignedusersroute' => \model\route::form('actions/action/p_updatedassignedusers.php?idproject={0}&idtask={1}', \model\env::session_idproject(), \model\env::session_idtaskselected()),
    'projectgroupsroute' => \model\route::window('projupdate',['project/groups/index.php?idproject={0}', \model\env::session_idproject()], \model\env::session_idproject(),\model\lexi::get('g3/project','sys103')),
    'lbl_title' => $lexi['sys047'],
    'lbl_submit' => $lexi['sys029'],
    'lbl_setupgroups' => $lexi['sys050'],
    'users' => $result->users,
    'groups' => $result->groups,
    'inc_users' => 'project/users/m_userstofilter.twig',
];
\model\route::render('actions/action/m_taskuser.twig', $data);

