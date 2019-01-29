<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$devices = (new \model\ext\sensor\sensor(\model\env::session_src()))->getListDevices();
foreach ($devices as $device) {
    $device->lbl_name =$device->name . ' (' . $device->partnro . ')';
}

$lexi = \model\lexi::getall('g3ext/sensor');
require_once \model\route::script('style.php');
$data = [
    'devices' => $devices,
    'datadeviceroute' => \model\route::form('g3ext/sensor/data/index.php?idproject={0}&iddevice={1}', \model\env::session_idproject(), '[iddevice]'),
    'lbl_data' => $lexi['sys030'],
    'lbl_notfound' => $lexi['sys044'],
];
\model\route::render('g3ext/sensor/data/selectdevice.twig', $data);
