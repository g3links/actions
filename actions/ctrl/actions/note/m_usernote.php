<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$project = (new \model\project)->getproject(\model\env::session_idproject());

$idnote = 0;
if(filter_input(INPUT_GET, 'idnote') !== null) 
    $idnote = (int) filter_input(INPUT_GET, 'idnote');
        
$lexi = \model\lexi::getall();
$data = [
    'idnote' => $idnote,
    'lbl_action' => \model\action::NOTE_REPLY,
    'addnoteroute' => \model\route::form('actions/note/addnote.php?idproject={0}', \model\env::session_idproject()),
    'submitnote' => $lexi['sys046'],
    'lbl_title' => $lexi['sys047'] . ': ' . ($project->title ?? ''),
];
\model\route::render('actions/note/m_usernote.twig', $data);
