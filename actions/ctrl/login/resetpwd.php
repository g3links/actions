<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3session.php';

$lexi = \model\lexi::getall();

$data = [
    'emailresetpwdroute' => \model\route::form('api/loginresetpwd.php'),
    'authrequiredroute' => \model\route::form('login/authrequired.php'),
    'lbl_email' => $lexi['sys028'],
    'lbl_password1' => $lexi['sys069'],
    'lbl_sendreset' => $lexi['sys039'],
    'lbl_newpassword' => $lexi['sys035'],
    'lbl_resetpwd' => $lexi['sys029'],
];
\model\route::render('login/resetpwd.twig', $data);
