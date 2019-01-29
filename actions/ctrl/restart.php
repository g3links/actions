<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3session.php';

$url = 'window.top.location.href = "'.WEB_APP.'/index.php"';

$script = '<script src="'.WEB_APP.'/js/g3.js"></script><script>'.$url.';</script>';
die($script);
