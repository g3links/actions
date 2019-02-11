<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$data = [
    'totalunreadmessages' => (new \model\project)->getTotalWaitingMessages(),
    'noteroute' => \model\route::window('message','actions/note/index.php',\model\lexi::get('','sys017')),
];
\model\route::render('actions/actions/menumessage.twig', $data);