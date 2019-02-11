<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$lexi = \model\lexi::getall();
$data = [
    'getfilteredusersroute' => \model\route::form('project/users/m_userstofilter.php?idproject={0}', \model\env::session_idproject()),
    'filteredroute' => \model\route::form('project/users/p_filtered.php?idproject={0}', \model\env::session_idproject()),
    'lbl_submit' => $lexi['prj137'],
    'lbl_filter' => $lexi['prj138'],
];
\model\route::render('project/users/m_filteruser.twig', $data);
