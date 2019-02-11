<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$modulename = '';
if (filter_input(INPUT_GET, 'modulename') !== null) 
    $modulename = filter_input(INPUT_GET, 'modulename');

$projlist = (new \model\action(\model\env::session_src()))->getLinkedModule($modulename);

$data = [
    'projlist' => $projlist,
    'lbl_linked' => \model\lexi::get('','prj129'),
];
\model\route::render('project/links/listprojectlinked.twig', $data);
