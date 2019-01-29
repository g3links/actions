<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$iduser = 0;
if (filter_input(INPUT_GET, 'iduser') !== null) 
    $iduser = (int) filter_input(INPUT_GET, 'iduser');

$result = (new \model\project)->getprojectbyuser(\model\env::session_idproject(), $iduser);

$lexi = \model\lexi::getall('g3/project');
$data = [
    'projectuser' => $result->projectuser,
    'roles' => $result->roles,
    'noeditaccessid' => $result->noeditaccessid,
    'lbl_title' => $lexi['sys062'],
    'lbl_role' => $lexi['sys048'],
    'lbl_activeaccount' => $lexi['sys033'],
    'lbl_access' => $lexi['sys133'],
    'lbl_accesstip' => $lexi['sys134'],
    'lbl_submit' => $lexi['sys060'],
    'lbl_accesslocal' => $lexi['sys007'],
    'lbl_accesspublic' => $lexi['sys008'],
    'lbl_accessguest' => $lexi['sys011'],
];
if($result->isrole) {
    $data += [
        'editprojectroleroute' => \model\route::form('project/users/p_updateprojectrole.php?idproject={0}', \model\env::session_idproject()),
    ];
}
\model\route::render('project/users/m_editprojectuserrole.twig', $data);
