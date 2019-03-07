<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3session.php';

$rootweb = 'Location: '. WEB_APP;
header($rootweb);
//header($rootweb . '/index.php');
////header($rootweb . '/index.php?lang=en');
////header($rootweb . '/index.php?lang=en-AU');
////header($rootweb . '/index.php?lang=es');
