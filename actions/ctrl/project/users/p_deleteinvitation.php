<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$idprojectinv = 0;
if (filter_input(INPUT_GET, 'idprojectinv') !== null) 
    $idprojectinv = (int) filter_input(INPUT_GET, 'idprojectinv');

(new \model\project)->removeinvitation($idprojectinv);

require \model\route::script('project/users/index.php');

