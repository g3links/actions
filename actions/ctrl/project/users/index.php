<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$result = (new \model\project())->getUserSession(\model\env::session_idproject());

$lexi = \model\lexi::getall();
require_once \model\route::script('style.php');
$data = [
    'projectusers' => $result->projectusers,
    'projectinvitations' => $result->projectinvitations,
    'roles' => $result->roles,
    'lbl_notfound' => count($result->projectusers) === 0 ? $lexi['prj044'] : '',
    'searchemailroute' => \model\route::form('project/users/searchtoinvite.php?search=[search]'),
    'th_col1' => $lexi['prj043'],
    'th_col2' => $lexi['prj048'],
    'th_col3' => $lexi['prj029'],
    'lbl_invite' => $lexi['prj036'],
    'lbl_invitetip' => $lexi['prj113'],
    'lbl_register' => $lexi['prj130'],
    'lbl_registertip' => $lexi['prj131'],
    'lbl_search' => $lexi['prj049'],
    'lbl_securityr' => $lexi['prj132'],
    'lbl_securityi' => $lexi['prj054'],
    'lbl_name' => $lexi['prj043'],
    'lbl_email' => $lexi['prj029'],
    'lbl_role' => $lexi['prj048'],
    'lbl_submit' => $lexi['prj062'],
    'lbl_submitinvite' => $lexi['prj056'],
    'lbl_send' => $lexi['prj057'],
    'lbl_username' => $lexi['prj041'],
    'lbl_sender' => $lexi['prj055'],
    'lbl_useremail' => $lexi['prj029'],
];
if ($result->isrole) {
    $data += [
        'editprojectuserroleroute' => \model\route::form('project/users/m_editprojectuserrole.php?idproject={0}&iduser=[iduser]', \model\env::session_idproject()),
        'projregisteruserroute' => \model\route::form('project/users/p_updateprojectrole.php?idproject={0}', \model\env::session_idproject()),
        'projinviteroute' => \model\route::form('project/users/p_projinv_email.php?idproject={0}', \model\env::session_idproject()),
        'removeinvitationroute' => \model\route::form('project/users/p_deleteinvitation.php?idproject={0}&idprojectinv=[idprojectinv]', \model\env::session_idproject()),
        'lbl_removeinvitation' => $lexi['prj047'],
    ];
}
\model\route::render('project/users/index.twig', $data);