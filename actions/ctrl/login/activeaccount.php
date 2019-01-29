<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3session.php';

(new \model\user)->setActiveAccount();
require \model\route::script('logout.php');
