<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$allowedit = \model\env::isUserAllow(\model\env::session_idproject(), \g3\ext\sensor\sensor::ROLE_SENSORUPDATE);

$lexi = \model\lexi::getall('g3ext/sensor');
require_once \model\route::script('style.php');
$data = [
    'allowedit' => $allowedit,
    'adddeviceroute' => \model\route::window('deviceadmon', ['g3ext/sensor/device/new_device.php?idproject={0}', \model\env::session_idproject()], \model\env::session_idproject(),$lexi['sys125']),
    'updatedeviceroute' => \model\route::window('deviceadmon',['g3ext/sensor/device/device.php?idproject={0}&iddevice={1}',  \model\env::session_idproject(), '[iddevice]'], \model\env::session_idproject(),''),
    'listdevicesroute' => \model\route::form('g3ext/sensor/device/listdevices.php?idproject={0}', \model\env::session_idproject()),
];
\model\route::render('g3ext/sensor/device/index.twig', $data);
