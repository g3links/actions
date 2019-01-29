<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$iddevice = 0;
if (filter_input(INPUT_GET, 'iddevice') !== null) 
    $iddevice = (int)filter_input(INPUT_GET, 'iddevice');

$updateddevice = new \stdClass();
$updateddevice->iddevice = $iddevice;
$updateddevice->inactive = false;
$updateddevice->name = filter_input(INPUT_POST, 'name');

if (filter_input(INPUT_POST, 'inactive') !== null) 
    $updateddevice->inactive = true;

$updateddevice->secretcode = filter_input(INPUT_POST, 'secretcode');

$updateddevice->token = '';
if (filter_input(INPUT_POST, 'token') !== null) {
    $token = filter_input(INPUT_POST, 'token');
    if (!empty($token)) {
        $updateddevice->token = filter_input(INPUT_POST, 'token');
    }
}
$updateddevice->partnro = filter_input(INPUT_POST, 'partnro');
$updateddevice->ip = filter_input(INPUT_POST, 'ip');
$updateddevice->url = filter_input(INPUT_POST, 'url');
$updateddevice->gps = filter_input(INPUT_POST, 'gps');
$updateddevice->country = filter_input(INPUT_POST, 'country');
$updateddevice->city = filter_input(INPUT_POST, 'city');
$updateddevice->state = filter_input(INPUT_POST, 'state');
$updateddevice->address1 = filter_input(INPUT_POST, 'address1');
$updateddevice->address2 = filter_input(INPUT_POST, 'address2');
$updateddevice->address3 = filter_input(INPUT_POST, 'address3');
$updateddevice->phone = filter_input(INPUT_POST, 'phone');
$updateddevice->zipcode = filter_input(INPUT_POST, 'zipcode');
$updateddevice->email = filter_input(INPUT_POST, 'email');
$updateddevice->weburl = filter_input(INPUT_POST, 'weburl');
$updateddevice->contact = filter_input(INPUT_POST, 'contact');

$modelsensor = new \model\ext\sensor\sensor(\model\env::session_src());

$device = new \stdClass();
if ($updateddevice->iddevice > 0) {
    $device = $modelsensor->getDevice($updateddevice->iddevice);
}

$device->iddevice = $updateddevice->iddevice;
$device->name = $updateddevice->name;
$device->inactive = $updateddevice->inactive;
$device->secretcode = $updateddevice->secretcode;
$device->token = $updateddevice->token;
$device->partnro = $updateddevice->partnro;
$device->ip = $updateddevice->ip;
$device->url = $updateddevice->url;
$device->gps = $updateddevice->gps;
$device->country = $updateddevice->country;
$device->city = $updateddevice->city;
$device->state = $updateddevice->state;
$device->address1 = $updateddevice->address1;
$device->address2 = $updateddevice->address2;
$device->address3 = $updateddevice->address3;
$device->phone = $updateddevice->phone;
$device->zipcode = $updateddevice->zipcode;
$device->email = $updateddevice->email;
$device->weburl = $updateddevice->weburl;
$device->contact = $updateddevice->contact;

if ($updateddevice->iddevice === 0) {
    $modelsensor->insertDevice($device);
} else {
    $modelsensor->updateDevice($device);
}

require_once \model\route::script('style.php');
\model\route::refresh('projsetup',['g3ext/sensor/device/index.php?idproject={0}',   \model\env::session_idproject()], \model\env::session_idproject());
