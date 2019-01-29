<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

require_once \model\route::script('style.php');
// include menu actions
require \model\route::script('project/admon/menuproject.php');

$result = (new \model\action(\model\env::session_src()))->getActionSession();

$data = [
    'needtodrawmap' => $result->needtodrawmap,
    'Gates' => $result->Gates,
    'lastviewgate' => \model\env::session_lastviewgate(),
    'actionstatusroute' => \model\route::form('actions/index.php?idproject={0}&idgate=[idgate]', \model\env::session_idproject()),
//    'actionstatusroute' => \model\route::form('actions/index.php',['idproject'=>\model\env::session_idproject(),'idgate'=>'[idgate]']),
    'listroute' => \model\route::form('actions/actions/listtasks.php?idproject={0}&idgate={1}&navpage=[navpage]&sort=[sorttype]&sortdirection=[sortdirection]&idtrack=[idtrack]', \model\env::session_idproject(), \model\env::session_lastviewgate()),
//    'listroute' => \model\route::form('actions/actions/listtasks.php',['idproject'=>\model\env::session_idproject(),'idgate'=>\model\env::session_lastviewgate(),'navpage'=>'[navpage]','sort'=>'[sorttype]','sortdirection'=>'[sortdirection]','idtrack'=>'[idtrack]']),
    'searchtagactionsroute' => \model\route::window('search',['actions/search/index.php?idproject={0}&idgate={1}&type=[type]&search=[searchtext]', 0, \model\env::session_lastviewgate()], \model\env::session_idproject(), \model\lexi::get('g3/project', 'sys049'),''),
//    'searchtagactionsroute' => \model\route::window('search',['actions/search/index.php',['idproject'=>0,'idgate'=>\model\env::session_lastviewgate(),'type'=>'[type]','search'=>'[searchtext]']], \model\env::session_idproject(), \model\lexi::get('g3/project', 'sys049'),''),
    'servicesroute' => \model\route::form('actions/actions/modules.php?idproject={0}', \model\env::session_idproject()),
//    'servicesroute' => \model\route::form('actions/actions/modules.php', ['idproject' => \model\env::session_idproject()]),
    'removefilterroute' => \model\route::form('actions/actions/removefilter.php?idproject={0}', \model\env::session_idproject()),
];
\model\route::render('actions/index.twig', $data);

