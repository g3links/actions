<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$iddevice = 0;
if (filter_input(INPUT_GET, 'iddevice') !== null) 
    $iddevice = (int)filter_input(INPUT_GET, 'iddevice');

$navpage = 0;
if (filter_input(INPUT_GET, 'navpage') !== null) 
    $navpage = (int)filter_input(INPUT_GET, 'navpage');

$max_records = \model\env::getMaxRecords('sensors');

$modelsensor = new \model\ext\sensor\sensor(\model\env::session_src());
$devicerecords = $modelsensor->getDeviceRawData($iddevice, $navpage, $max_records);

$total_records = $modelsensor->getTotalDeviceRawData($iddevice);
require \model\route::script('g3/footpage.php');

$lexi = \model\lexi::getall('g3ext/sensor');
$data = [
    'devicerecords' => $devicerecords,
    'lbl_notfound' => $lexi['sys044'],
];
\model\route::render('g3ext/sensor/data/viewrawdata.twig', $data);
