<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3session.php';

\model\utils::unsetCookie('lang');

require \model\route::script('restart.php');
