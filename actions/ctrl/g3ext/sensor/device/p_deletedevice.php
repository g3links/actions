<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$iddevice = 0;
if (filter_input(INPUT_GET, 'iddevice') !== null) 
    $iddevice = (int)filter_input(INPUT_GET, 'iddevice');

if ($iddevice > 0) {
    (new \model\ext\sensor\sensor(\model\env::session_src()))->deleteDevice($iddevice);
}

require_once \model\route::script('style.php');

\model\route::close(\model\env::session_idproject(),'deviceadmon');
\model\route::refresh('projsetup',['g3ext/sensor/device/index.php?idproject={0}',  \model\env::session_idproject()], \model\env::session_idproject());
