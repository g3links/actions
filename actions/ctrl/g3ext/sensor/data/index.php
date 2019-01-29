<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$iddevice = 0;
if (filter_input(INPUT_GET, 'iddevice') !== null) 
    $iddevice = filter_input(INPUT_GET, 'iddevice');

$modelsensor = new \model\ext\sensor\sensor(\model\env::session_src());
$totaldevicerecords = $modelsensor->getTotalDeviceRawData($iddevice);

$lexi = \model\lexi::getall('g3ext/sensor');
$data = [
    'device' => $modelsensor->getDevice($iddevice),
    'rawdataroute' => \model\route::form('g3ext/sensor/data/viewrawdata.php?idproject={0}&iddevice={1}&navpage={2}', \model\env::session_idproject(), $iddevice, '[navpage]'),
    'lbl_name' => $lexi['sys030'],
    'lbl_partnro' => $lexi['sys126'],
    'lbl_total' => \model\utils::format($lexi['sys148'], number_format($totaldevicerecords, 0)),
    'th_col1' => $lexi['sys149'],
    'th_col2' => $lexi['sys023'],
];
\model\route::render('g3ext/sensor/data/index.twig', $data);
