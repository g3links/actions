<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$gates = (new \model\action(\model\env::session_src()))->getGates(true);

$lexi = \model\lexi::getall('actions');
$data = [
    'gates' => $gates,
    'th_col1' => $lexi['sys030'],
    'th_col2' => $lexi['sys031'],
    'lbl_notfound' => $lexi['sys044'],
];
\model\route::render('actions/gate/loaddata.twig', $data);
