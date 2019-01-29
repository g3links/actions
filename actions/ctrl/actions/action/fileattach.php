<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$lexi = \model\lexi::getall('actions');

$data = [
    'lbl_title' => $lexi['sys056'],
];
\model\route::render('actions/actionnew/fileattach.twig', $data);
