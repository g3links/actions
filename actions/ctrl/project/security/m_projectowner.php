<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$users = (new \model\project)->getprojectusers(\model\env::session_idproject());

$lexi = \model\lexi::getall();
$data = [
    'users' => $users,
    'addprojectownerroute' => \model\route::form('project/security/p_projectowner.php?idproject={0}', \model\env::session_idproject()),
    'lbl_title' => $lexi['prj085'],
    'lbl_users' => $lexi['prj053'],
    'lbl_save' => $lexi['prj124'],
    'thisuser' => \model\env::getIdUser(),
];
\model\route::render('project/security/m_projectowner.twig', $data);

