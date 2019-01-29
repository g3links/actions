<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3session.php';

require_once \model\route::script('style.php');
require \model\route::render('g3/*/about.html');
require \model\route::render('g3/*/terms.html');
