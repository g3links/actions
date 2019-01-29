<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$result = (new \model\action(\model\env::session_src()))->getStartupActions();

if (count($result->holdactions) > 0) {
    $data = [
        'todoshold' => $result->holdactions,
        'lbl_title' => \model\lexi::get('actions', 'sys001'),
    ];
    \model\route::render('actions/actions/listtasks_hold.twig', $data);
}

if (count($result->openactions) > 0) {
    $data = [
        'lbl_title' => \model\lexi::get('actions', 'sys079'),
        'todosopen' => $result->openactions,
    ];
    \model\route::render('start/services/listopenactions.twig', $data);
}
