<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$modulename = '';
if (filter_input(INPUT_GET, 'modulename') !== null) 
    $modulename = filter_input(INPUT_GET, 'modulename');

$isshared = false;
if (filter_input(INPUT_POST, 'isshared') !== null) 
    $isshared = true;

$args = filter_input_array(INPUT_POST);

(new \model\action(\model\env::session_src()))->linkmoduleowner($modulename, $isshared, $args);
