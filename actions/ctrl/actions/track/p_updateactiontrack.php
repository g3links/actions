<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$tracking = new \stdClass();
$tracking->iscategory = false;
if(filter_input(INPUT_POST, 'iscategory') !== null)
    $tracking->iscategory = true;

$tracking->idcategory = 0;
if(filter_input(INPUT_POST, 'idcategory') !== null)
    $tracking->idcategory = (int) filter_input(INPUT_POST, 'idcategory');

$tracking->idtrack = 0;
if(filter_input(INPUT_POST, 'idtrack') !== null)
    $tracking->idtrack = (int) filter_input(INPUT_POST, 'idtrack');

$tracking->commenttext = '';
if(filter_input(INPUT_POST, 'commenttext') !== null)
    $tracking->commenttext = (string)filter_input(INPUT_POST, 'commenttext');

(new \model\action(\model\env::session_src()))->updateActionTrack(\model\env::session_idtaskselected(), $tracking, 'actions/*/assignedaction.html');

require \model\route::script('actions/action/index.php');