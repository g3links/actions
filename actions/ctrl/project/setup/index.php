<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

require_once \model\route::script('style.php');
$data = [
    'loadsetuproute' => \model\route::form('project/setup/list.php?idproject={0}', \model\env::session_idproject()), 
    'lbl_loading' => \model\lexi::get('','msg001'),
];
\model\route::render('project/setup/index.twig', $data);
