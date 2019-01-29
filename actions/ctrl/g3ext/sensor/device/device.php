<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$iddevice = 0;
if (filter_input(INPUT_GET, 'iddevice') !== null) 
    $iddevice = (int)filter_input(INPUT_GET, 'iddevice');

$isrole = \model\env::isUserAllow(\model\env::session_idproject(), \g3\ext\sensor\sensor::ROLE_SENSORUPDATE);

$device = (new \model\ext\sensor\sensor(\model\env::session_src()))->getDevice($iddevice);

//generate tokencode
$data = ['idproject' => \model\env::session_idproject(), 'id' => $iddevice, 'partnro' => $device->partnro];
$device->tokencode = \Firebase\JWT\JWT::encode($data, \model\env::getKey());

$lexi = \model\lexi::getall('g3ext/sensor');
require_once \model\route::script('style.php');
$data = [
    'isrole' => $isrole,
    'device' => $device,
    'deletedeviceroute' => \model\route::form('g3ext/sensor/device/p_deletedevice.php?idproject={0}&iddevice={1}',  \model\env::session_idproject(), $device->iddevice),
    'lbl_title' => $lexi['sys147'],
    'editdeviceroute' => \model\route::form('g3ext/sensor/device/p_editdevice.php?idproject={0}&iddevice={1}', \model\env::session_idproject(), $device->iddevice),
    'lbltip_lastupdated' => $lexi['sys022'],
    'lbltip_created' => $lexi['sys023'],
    'lbl_group1' => $lexi['sys128'],
    'lbl_name' => $lexi['sys030'],
    'lbl_partnro' => $lexi['sys126'],
    'lbl_tokencode' => $lexi['sys129'],
    'lbl_group2' => $lexi['sys130'],
    'lbl_inactive' => $lexi['sys060'],
    'lbl_secretcode' => $lexi['sys131'],
    'lbl_password' => $lexi['sys132'],
    'lbl_group3' => $lexi['sys133'],
    'lbl_ip' => $lexi['sys134'],
    'lbl_gps' => $lexi['sys135'],
    'lbl_url' => $lexi['sys136'],
    'lbl_group4' => $lexi['sys137'],
    'lbl_contact' => $lexi['sys030'],
    'lbl_email' => $lexi['sys138'],
    'lbl_weburl' => $lexi['sys139'],
    'lbl_group5' => $lexi['sys140'],
    'lbl_country' => $lexi['sys141'],
    'lbl_state' => $lexi['sys142'],
    'lbl_city' => $lexi['sys143'],
    'lbl_address' => $lexi['sys140'],
    'lbl_zipcode' => $lexi['sys144'],
    'lbl_phone' => $lexi['sys145'],
    'lbl_submitupdate' => $lexi['sys029'],
    'lbl_submitnew' => $lexi['sys146'],
    'lbl_confirm' => $lexi['sys032'],
    'lbl_cancel' => $lexi['sys021'],
];
\model\route::render('g3ext/sensor/device/device.twig', $data);
