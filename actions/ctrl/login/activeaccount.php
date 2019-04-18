<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3session.php';

(new \model\login)->setActiveAccount('g3/*/accounttatus.html');
require \model\route::script('restart.php');
