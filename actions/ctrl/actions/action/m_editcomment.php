<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$isrole = \model\env::isUserAllow(\model\env::session_idproject(), \model\action::ROLE_ACTIONCOMMENT);

$lexi = \model\lexi::getall('actions');
$data = [
    'isrole' => $isrole,
    'updatecomment' => \model\route::form('actions/action/p_addcomment.php?idproject={0}&idtask={1}', \model\env::session_idproject(), \model\env::session_idtaskselected()),
    'lbl_title' => $lexi['sys011'],
    'lbl_submit' => $lexi['sys029'],
];
\model\route::render('actions/action/m_editcomment.twig', $data);
