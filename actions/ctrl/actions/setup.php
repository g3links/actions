<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$modelshare = new \model\action(\model\env::session_src());

$lexi = \model\lexi::getall('actions');

$modules = [];

$module = new \stdClass();
$module->approute = \model\route::window('projsetup',['actions/gate/index.php?idproject={0}', \model\env::session_idproject()], \model\env::session_idproject(), $lexi['sys080']);
$module->imagesymbol = 'imgGate';
$module->moduleid = 'a01';
$modules[] = $module;

$module = new \stdClass();
$module->approute = \model\route::window('projsetup',['actions/priority/index.php?idproject={0}', \model\env::session_idproject()], \model\env::session_idproject(), $lexi['sys026']);
$module->imagesymbol = 'imgPriority';
$module->moduleid = 'a02';
$modules[] = $module;

$module = new \stdClass();
$module->approute = \model\route::window('projsetup',['actions/category/index.php?idproject={0}', \model\env::session_idproject()], \model\env::session_idproject(), $lexi['sys013']);
$module->imagesymbol = 'imgCategory';
$module->moduleid = 'a03';
$modules[] = $module;

$module = new \stdClass();
$module->approute = \model\route::window('projsetup',['actions/track/index.php?idproject={0}', \model\env::session_idproject()], \model\env::session_idproject(), $lexi['sys046']);
$module->imagesymbol = 'imgTrack';
$module->moduleid = 'a04';
$modules[] = $module;

$data = [
    'lbl_title' => $lexi['sys153'],
    'lbl_shared' => $t_shared,
    'idproject' => \model\env::session_idproject(),
    'modules' => $modules,
];
\model\route::render('project/setup/setup.twig', $data);
