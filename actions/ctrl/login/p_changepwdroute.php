<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3session.php';

$emaillogon = '';
if (filter_input(INPUT_POST, 'emailchg') !== null) 
    $emaillogon = filter_input(INPUT_POST, 'emailchg');

$uk = '';
if (filter_input(INPUT_POST, 'pwdlogon') !== null) 
    $uk = filter_input(INPUT_POST, 'pwdlogon');

$pwdchg = '';
if (filter_input(INPUT_POST, 'pwdchg') !== null) 
    $pwdchg = filter_input(INPUT_POST, 'pwdchg');

$pwdchg1 = '';
if (filter_input(INPUT_POST, 'pwdchg1') !== null) 
    $pwdchg1 = filter_input(INPUT_POST, 'pwdchg1');

if((new \model\user)->changeUserPassword($emaillogon, $uk, $pwdchg, $pwdchg1) === false) {
    require \model\route::script('login/index.php');
    die();
}

(new \model\login())->registerUser($emaillogon, LOGINSRV, $pwdchg, $callback ?? '');
die();