<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$result = (new \model\action(\model\env::session_src()))->getTotalActiveActions(\model\env::getUserIdProject());

$lexi = \model\lexi::getall('g3');
$data = [
    'total' => $result->mytodos,
    'totalHold' => $result->mytodoshold,
    'totalList' => $result->todosall,
    'totalallHold' => $result->todoshold,
    'listmyactionsroute' => \model\route::window('actions',['actions/index.php?idproject={0}', \model\env::getUserIdProject()], \model\env::getUserIdProject(), $lexi['sys006'], \model\env::getUserName()),
    'listallactionsroute' => \model\route::window('allactions','actions/index.php', $lexi['sys001'], $lexi['sys001']),
];
\model\route::render('actions/actions/menuactions.twig', $data);
