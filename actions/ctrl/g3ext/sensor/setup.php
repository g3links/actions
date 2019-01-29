<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$lexi = \model\lexi::getall('g3ext/sensor');
$hassensors = (new \model\ext\sensor\sensor(\model\env::session_src()))->hasDeviceService();

$modules = [];

$module = new \stdClass();
$module->approute = \model\route::window('projsetup', ['g3ext/sensor/device/index.php?idproject={0}', \model\env::session_idproject()], \model\env::session_idproject(), $lexi['sys151']);
$module->imagesymbol = 'imgSensor';
$module->moduleid = 's01';
$modules[] = $module;

if ($hassensors) {
    $module = new \stdClass();
    $module->approute = \model\route::window('projsetup', ['g3ext/sensor/data/selectdevice.php?idproject={0}', \model\env::session_idproject()], \model\env::session_idproject(), $lexi['sys152']);
    $module->imagesymbol = 'imgData';
    $module->moduleid = 's02';
    $modules[] = $module;
}

$data = [
    'lbl_title' => $lexi['sys151'],
    'lbl_shared' => $t_shared,
    'idproject' => \model\env::session_idproject(),
    'modules' => $modules,
];
\model\route::render('project/setup/setup.twig', $data);
