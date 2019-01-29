<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

(new \model\user)->sleepaccount();
require \model\route::script('logout.php');
