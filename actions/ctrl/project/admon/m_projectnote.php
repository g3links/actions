<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$project = (new \model\project)->getproject(\model\env::session_idproject());

$lexi = \model\lexi::getall('g3/project');
$data = [
    'getfilteredusersroute' => \model\route::form('project/users/m_userstofilter.php?idproject={0}', \model\env::session_idproject()),
    'addnoteroute' => \model\route::form('actions/note/addnote.php?idproject={0}', \model\env::session_idproject()),
    'lbl_note' => $lexi['sys104'],
    'lbl_to' => $lexi['sys041'],
    'lbl_from' => $lexi['sys135'],
    'lbl_projtitle' => $project->title ?? '',
    'actionid' => \model\action::NOTE_PROJECT,
];
\model\route::render('project/admon/m_projectnote.twig', $data);

