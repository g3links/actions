<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$result = (new \model\project())->getUserSession(\model\env::session_idproject());

$lexi = \model\lexi::getall('g3/project');
require_once \model\route::script('style.php');
$data = [
    'projectusers' => $result->projectusers,
    'projectinvitations' => $result->projectinvitations,
    'roles' => $result->roles,
    'lbl_notfound' => count($result->projectusers) === 0 ? $lexi['sys044'] : '',
    'searchemailroute' => \model\route::form('project/users/searchtoinvite.php?search=[search]'),
    'th_col1' => $lexi['sys043'],
    'th_col2' => $lexi['sys048'],
    'th_col3' => $lexi['sys029'],
    'lbl_invite' => $lexi['sys036'],
    'lbl_invitetip' => $lexi['sys113'],
    'lbl_register' => $lexi['sys130'],
    'lbl_registertip' => $lexi['sys131'],
    'lbl_search' => $lexi['sys049'],
    'lbl_securityr' => $lexi['sys132'],
    'lbl_securityi' => $lexi['sys054'],
    'lbl_name' => $lexi['sys043'],
    'lbl_email' => $lexi['sys029'],
    'lbl_role' => $lexi['sys048'],
    'lbl_submit' => $lexi['sys062'],
    'lbl_submitinvite' => $lexi['sys056'],
    'lbl_send' => $lexi['sys057'],
    'lbl_username' => $lexi['sys041'],
    'lbl_sender' => $lexi['sys055'],
    'lbl_useremail' => $lexi['sys029'],
];
if ($result->isrole) {
    $data += [
        'editprojectuserroleroute' => \model\route::form('project/users/m_editprojectuserrole.php?idproject={0}&iduser=[iduser]', \model\env::session_idproject()),
        'projregisteruserroute' => \model\route::form('project/users/p_updateprojectrole.php?idproject={0}', \model\env::session_idproject()),
        'projinviteroute' => \model\route::form('project/users/p_projinv_email.php?idproject={0}', \model\env::session_idproject()),
        'removeinvitationroute' => \model\route::form('project/users/p_deleteinvitation.php?idproject={0}&idprojectinv=[idprojectinv]', \model\env::session_idproject()),
        'lbl_removeinvitation' => $lexi['sys047'],
    ];
}
\model\route::render('project/users/index.twig', $data);