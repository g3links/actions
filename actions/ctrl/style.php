<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3session.php';

$datastyle = [
    'lbl_style' => \model\env::getUserTheme(),
    'host' => ROOT_APP,
    ];

\model\route::render('style.twig', $datastyle);
