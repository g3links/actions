<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$modelcore = new \model\project();
$modeltask = new \model\action(\model\env::session_src());

if (\model\env::session_idproject() === 0) {
    $users = $modelcore->geActiveUsersAllProjects();
    $groups = $modeltask->geActiveGroups();
} else {
    $users = $modelcore->getactiveusersinproject(\model\env::session_idproject());
    $groups = $modeltask->getprojectactivegroupsusers();
}

$data = [
    'users' => $users,
    'groups' => $groups,
];
\model\route::render('project/users/m_userstofilter.twig', $data);
