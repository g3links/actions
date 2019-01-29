<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$naction = new stdClass();

$naction->idcategory = 0;
if (filter_input(INPUT_POST, 'idcategory') !== null) {
    $naction->idcategory = (int)filter_input(INPUT_POST, 'idcategory');
}
$naction->title = '';
if(filter_input(INPUT_POST, 'title') !== null)
    $naction->title = filter_input(INPUT_POST, 'title');

$naction->description = '';
if(filter_input(INPUT_POST, 'description') !== null)
    $naction->description = filter_input(INPUT_POST, 'description');

$naction->progress = 0;
if(filter_input(INPUT_POST, 'progress') !== null)
    $naction->progress = (int) filter_input(INPUT_POST, 'progress');

$naction->idpriority = 0;
if(filter_input(INPUT_POST, 'idpriority') !== null)
    $naction->idpriority = (int)filter_input(INPUT_POST, 'idpriority');

$naction->commenttext = '';
if(filter_input(INPUT_POST, 'commenttext') !== null)
    $naction->commenttext = filter_input(INPUT_POST, 'commenttext');

$args = filter_input_array(INPUT_POST);

(new \model\action(\model\env::session_src()))->updatetask(\model\env::session_idtaskselected(), $naction, $args);

require \model\route::script('actions/action/index.php');