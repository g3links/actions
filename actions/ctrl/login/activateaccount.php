<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3session.php';

$lexi = \model\lexi::getall();

$data = [
    'lbl_security' => $lexi['sys018'],
    'username' => \model\env::getUserName(),
    'link_confirm' => $lexi['sys019'],
    'link_confirmhere' => $lexi['sys020'],
    'link_activate' => \model\route::form('login/activeaccount.php'),
    'host' => ROOT_APP,
];
\model\route::render('login/activateaccount.twig', $data);
