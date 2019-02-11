<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$results = (new \model\action(\model\env::session_src()))->getNotes();
$total_new_notes = $results[1];

$lexi = \model\lexi::getall();
$data = [
    'lbl_title' => $lexi['sys048'],
    'total_new_notes' => $total_new_notes,
    'archivenoteroute' => \model\route::form('actions/note/archivenote.php?idproject=[idproject]&idnote=[idnote]'),
    'markasreadroute' => \model\route::form('actions/note/markasread.php?idproject=[idproject]&idnote=[idnote]'),
    'lbl_nonotes' => $lexi['sys049'],
    'notes' => $results[0],
    'lbl_flag' => $lexi['sys050'],
    'lbl_reply' => $lexi['sys047'],
    'lbl_remove' => $lexi['sys051'],
    'lbl_open' => $lexi['sys052'],
    'iduser' => \model\env::getIdUser(),
];
\model\route::render('actions/note/listnotes.twig', $data);
