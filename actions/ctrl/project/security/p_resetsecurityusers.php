<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$seccode = '';
if(filter_input(INPUT_GET, 'seccode') !== null)
    $seccode = filter_input(INPUT_GET, 'seccode');

(new \model\action(\model\env::session_src()))->resetprojectsecurity($seccode);

require \model\route::script('project/security/index.php?idproject={0}', \model\env::session_idproject());
