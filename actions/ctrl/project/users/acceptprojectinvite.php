<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$idprojectinv = 0;
if (filter_input(INPUT_POST, 'idprojectinv') !== null)
    $idprojectinv = (int) filter_input(INPUT_POST, 'idprojectinv');

(new \model\project)->insertprojuserinvitation($idprojectinv);

\model\route::refreshMaster();
