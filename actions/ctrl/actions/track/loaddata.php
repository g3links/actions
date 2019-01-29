<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$tracks = (new \model\action(\model\env::session_src()))->getTracks(true);

$lexi = \model\lexi::getall('actions');
$data = [
    'tracks' => $tracks,
    'lbl_title' => \model\lexi::get('g3/project','sys005'),
    'th_col1' => $lexi['sys031'],
    'th_col2' => $lexi['sys030'],
    'lbl_notfound' => $lexi['sys044'],
];
\model\route::render('actions/track/loaddata.twig', $data);
