<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$priorities = (new \model\action(\model\env::session_src()))->getPriorities(true);

$lexi = \model\lexi::getall('actions');
$data = [
    'priorities' => $priorities,
    'th_col1' => $lexi['sys031'],
    'th_col2' => $lexi['sys030'],
    'lbl_notfound' => $lexi['sys044'],
];
\model\route::render('actions/priority/loaddata.twig', $data);
