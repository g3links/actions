<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$filterviews = (new \model\action(\model\env::session_src()))->getFilterviews();

$data = [
    'filterviews' => $filterviews,
    'filteredviewroute' => \model\route::form('actions/actions/p_filteredview.php?idproject={0}', \model\env::session_idproject()),
    'lbl_submit' => \model\lexi::get('g3/project','sys108'),
    'lbl_filter' => \model\lexi::get('g3/project','sys014'),
];
\model\route::render('actions/actions/m_filterview.twig', $data);
