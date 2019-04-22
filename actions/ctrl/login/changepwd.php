<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3session.php';

$useremail = '';
if (!empty(\model\env::getUserEmail()))
    $useremail = \model\env::getUserEmail();

$lexi = \model\lexi::getall();

$data = [
    'useremail' => $useremail,
    'changepwdroute' => \model\route::form('api/loginpassw.php'),
    'apirestartroute' => \model\route::form('restart.php'),
    'lbl_password' => $lexi['sys034'],
    'lbl_password1' => $lexi['sys069'],
    'lbl_newpassword' => $lexi['sys035'],
    'lbl_submitchangepwd' => $lexi['sys068'],
];
\model\route::render('login/changepwd.twig', $data);
