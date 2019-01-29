<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$notetext = '';
if (filter_input(INPUT_POST, 'notetext') !== null)
    $notetext = filter_input(INPUT_POST, 'notetext');

$actiontype = '';
if (filter_input(INPUT_POST, 'action') !== null)
    $actiontype = (string) filter_input(INPUT_POST, 'action');

$idnote = 0;
if (filter_input(INPUT_POST, 'idnote') !== null)
    $idnote = (int) filter_input(INPUT_POST, 'idnote');

$args = filter_input_array(INPUT_POST);

(new \model\action(\model\env::session_src()))->setNote($actiontype, $args, $notetext, $idnote);

//refresh
if (!empty($notetext) && $actiontype === \model\action::NOTE_REPLY)
    require \model\route::script('actions/note/index.php');
   