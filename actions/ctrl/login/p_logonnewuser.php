<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3session.php';

$emaillogon = '';
$username = '';

if (filter_input(INPUT_POST, 'usernamenew') !== null)
    $username = filter_input(INPUT_POST, 'usernamenew');

$pwdnew = '';
if (filter_input(INPUT_POST, 'pwdnew') !== null)
    $pwdnew = filter_input(INPUT_POST, 'pwdnew');

$pwdnew1 = '';
if (filter_input(INPUT_POST, 'pwdnew1') !== null)
    $pwdnew1 = filter_input(INPUT_POST, 'pwdnew1');

// intent to register a new account
if (filter_input(INPUT_POST, 'emailnew') !== null)
    $emaillogon = filter_input(INPUT_POST, 'emailnew');

if ((new \model\user())->insertUser($emaillogon, $username, $pwdnew, $pwdnew1) === false) {
    require \model\route::script('login/index.php');
    die();
}

(new \model\user)->registerUser($emaillogon, LOGINSRV, $pwdnew, $callback ?? '');
die();
