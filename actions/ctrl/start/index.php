<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

require_once \model\route::script('style.php');
$data = [
    'liatactionsroute' => \model\route::form('start/services/listopenactions.php'),
    'viewtaskroute' => \model\route::window('action',['actions/action/index.php?idproject=[idproject]&idtask=[idtask]'],'', \model\lexi::get('actions', 'sys067'),''),
    'servicesroute' => \model\route::form('start/services/index.php'),
    'servicetagsroute' => \model\route::form('start/services/servicetags.php'),
    'searchtagsroute' => \model\route::window('search',['actions/search/index.php?type=[type]&search=[searchtext]&idgate=1'] , \model\env::getUserIdProject(), \model\lexi::get('g3/project', 'sys049'),''),
];
\model\route::render('start/index.twig', $data);
