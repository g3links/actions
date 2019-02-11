<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$iduser = 0;
if (filter_input(INPUT_GET, 'iduser') !== null) 
    $iduser = (int) filter_input(INPUT_GET, 'iduser');

$result = (new \model\project)->getprojectbyuser(\model\env::session_idproject(), $iduser);

$lexi = \model\lexi::getall();
$data = [
    'projectuser' => $result->projectuser,
    'roles' => $result->roles,
    'noeditaccessid' => $result->noeditaccessid,
    'lbl_title' => $lexi['prj062'],
    'lbl_role' => $lexi['prj048'],
    'lbl_activeaccount' => $lexi['prj033'],
    'lbl_access' => $lexi['prj133'],
    'lbl_accesstip' => $lexi['prj134'],
    'lbl_submit' => $lexi['prj060'],
    'lbl_accesslocal' => $lexi['prj007'],
    'lbl_accesspublic' => $lexi['prj008'],
    'lbl_accessguest' => $lexi['prj011'],
];
if($result->isrole) {
    $data += [
        'editprojectroleroute' => \model\route::form('project/users/p_updateprojectrole.php?idproject={0}', \model\env::session_idproject()),
    ];
}
\model\route::render('project/users/m_editprojectuserrole.twig', $data);
