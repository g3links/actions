<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$users = (new \model\project)->getprojectusers(\model\env::session_idproject());

$lexi = \model\lexi::getall('g3/project');
$data = [
    'users' => $users,
    'addprojectownerroute' => \model\route::form('project/security/p_projectowner.php?idproject={0}', \model\env::session_idproject()),
    'lbl_title' => $lexi['sys085'],
    'lbl_users' => $lexi['sys053'],
    'lbl_save' => $lexi['sys124'],
    'thisuser' => \model\env::getIdUser(),
];
\model\route::render('project/security/m_projectowner.twig', $data);

