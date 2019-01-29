<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$isrole = \model\env::isUserAllow(\model\env::session_idproject(), \model\action::ROLE_ACTIONFILES);

$lexi = \model\lexi::getall('actions');
$data = [
    'idproject' => \model\env::session_idproject(),
    'isrole' => $isrole,
    'uploadfileroute' => \model\route::form('actions/action/p_uploadfile.php'),
    'lbl_title' => $lexi['sys055'],
    'lbl_submit' => $lexi['sys055'],
    'lbl_tip' => $lexi['sys056'],
];
\model\route::render('actions/action/m_fileattach.twig', $data);
