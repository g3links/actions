<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$idproject = 0;
if (filter_input(INPUT_POST, 'idprojectrestore') !== null) 
    $idproject = (int) filter_input(INPUT_POST, 'idprojectrestore');

$sucess = (new \model\project)->restoreproject($idproject,'g3/*/statusproject.html');

require_once \model\route::script('style.php');

\model\route::refreshMaster();
\model\route::close(\model\env::session_idproject(),'newproj');
