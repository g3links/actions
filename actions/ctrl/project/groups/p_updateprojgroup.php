<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$idgroup = 0;
if (filter_input(INPUT_GET, 'id') !== null) 
    $idgroup = (int) filter_input(INPUT_GET, 'id');

$groupname = '';
if (filter_input(INPUT_POST, 'groupname') !== null) 
   $groupname = filter_input(INPUT_POST, 'groupname');

$updatetype = '';
if (filter_input(INPUT_POST, 'update') !== null) 
    $updatetype = 'update';

if (filter_input(INPUT_POST, 'active') !== null) 
    $updatetype = 'active';

if (filter_input(INPUT_POST, 'delete') !== null) 
    $updatetype = 'delete';

$args = filter_input_array(INPUT_POST);

(new \model\action(\model\env::session_src()))->updateprojgroup($updatetype, $idgroup, $groupname, $args);

require \model\route::script('project/groups/index.php?idproject={0}', \model\env::session_idproject());
