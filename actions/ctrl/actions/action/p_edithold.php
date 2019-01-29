<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$args = filter_input_array(INPUT_POST);

(new \model\action(\model\env::session_src()))->updatehold(\model\env::session_idtaskselected(), $args);

require \model\route::script('actions/action/index.php');