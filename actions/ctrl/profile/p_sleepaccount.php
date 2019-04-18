<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

(new \model\login)->sleepaccount('g3/*/accounttatus.html');
require \model\route::script('restart.php');
