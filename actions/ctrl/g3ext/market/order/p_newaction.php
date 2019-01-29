<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$action = new stdClass();

$idorder = 0;
if (filter_input(INPUT_GET, 'idorder') !== null) 
    $idorder = (int) filter_input(INPUT_GET, 'idorder');

$action->idcategory = 0;
if (filter_input(INPUT_POST, 'idcategory') !== null) 
    $action->idcategory = (int)filter_input(INPUT_POST, 'idcategory');

$action->title = '';
if(filter_input(INPUT_POST, 'title') !== null)
    $action->title = filter_input(INPUT_POST, 'title');

$action->description = '';
if(filter_input(INPUT_POST, 'description') !== null)
    $action->description = filter_input(INPUT_POST, 'description');

$action->progress = 0;
if(filter_input(INPUT_POST, 'progress') !== null)
    $action->progress = (int) filter_input(INPUT_POST, 'progress');

$action->idpriority = 0;
if(filter_input(INPUT_POST, 'idpriority') !== null)
    $action->idpriority = (int)filter_input(INPUT_POST, 'idpriority');

$action->idtaskparent = 0;
if(filter_input(INPUT_POST, 'idparent') !== null)
    $action->idtaskparent = (int)filter_input(INPUT_POST, 'idparent');

$action->commenttext = '';
if (filter_input(INPUT_POST, 'commenttext') !== null) 
    $action->commenttext = (string)filter_input(INPUT_POST, 'commenttext');

$args = filter_input_array(INPUT_POST);

$idtaskinserted = (new \model\action(\model\env::session_src()))->inserttask($action, $args,'actions/*/assignedaction.html');
(new \model\ext\market\market(\model\env::session_src()))->setActionToOrder($idorder, $idtaskinserted);

require_once \model\route::script('style.php');
\model\route::close(\model\env::session_idproject(),'newaction');
\model\route::refresh('orderadmon', ['g3ext/market/order/index.php?idproject={0}', \model\env::session_idproject()], \model\env::session_idproject());
\model\route::refresh('actions',['actions/index.php?idproject={0}', \model\env::session_idproject()], \model\env::session_idproject());
