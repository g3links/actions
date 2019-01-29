<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$lexi = \model\lexi::getall('g3ext/sensor');
$data = [
    'projectdevices' => (new \model\ext\sensor\sensor(\model\env::session_src()))->getListDevices(),
    'th_col1' => $lexi['sys030'],
    'th_col2' => $lexi['sys126'],
    'th_col3' => $lexi['sys060'],
    'lbl_notfound' => $lexi['sys044'],
];
\model\route::render('g3ext/sensor/device/listdevices.twig', $data);
