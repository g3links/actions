<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3session.php';

$lexi = \model\lexi::getall('g3');

$data = [
    'lbl_security' => $lexi['sys018'],
    'lbl_username' => $lexi['sys025'],
    'username' => $username,
    'lbl_email' => \model\utils::format($lexi['sys027'] , $useremail),
    'host' => ROOT_APP,
];
\model\route::render('login/authrequired.twig', $data);
