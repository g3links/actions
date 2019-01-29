<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

if (!isset($callback) || isset($messageerror)) {

    require_once \model\route::script('style.php');
    // is user active
    $lexi = \model\lexi::getall('g3');
    $data = [
        'messageerror' => $messageerror ?? '',
        'useremail' => \model\env::getUserEmail(),
        'username' => \model\env::getUserName(),
        'iduser' => \model\env::getIdUser(),
        'callback' => filter_input(INPUT_SERVER, 'REQUEST_URI'),
        'confirmidentityroute' => \model\route::form('login/p_confirmidentity.php'),
        'lbl_password' => $lexi['sys034'],
        'lbl_submitlogin' => $lexi['sys014'],
        'lbl_confirmidentity' => $lexi['sys069'],
    ];
    \model\route::render('login/confirmidentity.twig', $data);
    die();
}
$callback = null;
$messageerror = null;