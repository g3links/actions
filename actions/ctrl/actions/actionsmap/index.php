<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$actions = (new \model\action(\model\env::session_src()))->getListActionsMap();

require_once \model\route::script('style.php');
$data = [
    'actions' => $actions,
    'MapActionroute' => \model\route::window('action',['actions/action/index.php?idproject=[idproject]&idtask=[idtask]'], \model\env::session_idproject(), \model\lexi::get('actions', 'sys067'),''),
];
\model\route::render('actions/actionsmap/index.twig', $data);
