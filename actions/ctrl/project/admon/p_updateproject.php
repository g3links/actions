<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$action = 'update';
if (filter_input(INPUT_POST, 'addproj') !== null) 
    $action = 'new';

$newproj = new \stdClass();
$newproj->title = (string) filter_input(INPUT_POST, 'title');
$newproj->description = (string) filter_input(INPUT_POST, 'description');
$newproj->remoteurl = (string) filter_input(INPUT_POST, 'remoteurl');
$newproj->startuppath = (string) filter_input(INPUT_POST, 'startuppath');
$newproj->startupwidth = (int) filter_input(INPUT_POST, 'startupwidth');
$newproj->remoteurl = (string) filter_input(INPUT_POST, 'remoteurl');
$newproj->idcurrency = (string) filter_input(INPUT_POST, 'idcurrency');
$newproj->ispublic = false;
if (filter_input(INPUT_POST, 'ispublic') !== null) 
    $newproj->ispublic = true;

$newproj->marketname = (string) filter_input(INPUT_POST, 'marketname');
$newproj->prefix = (string) filter_input(INPUT_POST, 'prefix');
$newproj->ticketseq = (int) filter_input(INPUT_POST, 'ticketseq');


$allOk = true;

//if empty. do nothing
if (!isset($newproj->title) || empty($newproj->title)) {
    \model\message::render(\model\lexi::get('g3/project','sys017'));
    $allOk = false;
}

if ($allOk) {
    $idproject = \model\env::session_idproject();
    if ($action === 'new') {
        $idproject = (new \model\project)->insertproject($newproj);
    } else {
        (new \model\project)->updateproject($idproject, $newproj);
    }

    require_once \model\route::script('style.php');

    if ($action === 'new') {
        \model\route::refreshMaster();
        \model\route::close(\model\env::session_idproject(), 'newproj');
    } else {
        \model\route::refreshMaster($idproject);
        \model\route::close($idproject, 'projsetup');
    }
}
