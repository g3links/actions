<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$project = (new \model\project)->getproject(\model\env::session_idproject());

$lexi = \model\lexi::getall();
$data = [
    'getfilteredusersroute' => \model\route::form('project/users/m_userstofilter.php?idproject={0}', \model\env::session_idproject()),
    'addnoteroute' => \model\route::form('actions/note/addnote.php?idproject={0}', \model\env::session_idproject()),
    'lbl_note' => $lexi['prj104'],
    'lbl_to' => $lexi['prj041'],
    'lbl_from' => $lexi['prj135'],
    'lbl_projtitle' => $project->title ?? '',
    'actionid' => \model\action::NOTE_PROJECT,
];
\model\route::render('project/admon/m_projectnote.twig', $data);

