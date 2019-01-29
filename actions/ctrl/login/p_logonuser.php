<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3session.php';

$emaillogon = '';
$username = '';
$uk = '';

if (filter_input(INPUT_POST, 'logon') === null) 
    $messageerror = \model\lexi::get('g3', 'sys043');

// intent to register a new account
if (filter_input(INPUT_POST, 'emaillogon') !== null) 
    $emaillogon = filter_input(INPUT_POST, 'emaillogon');

// stop no valid email
if (empty($emaillogon)) 
    $messageerror = \model\lexi::get('g3', 'sys043');


if (filter_input(INPUT_POST, 'pwdlogon') !== null) 
    $uk = filter_input(INPUT_POST, 'pwdlogon');

$user = (new \model\user)->exist($emaillogon);
if (!isset($user)) 
    $messageerror = \model\lexi::get('g3', 'sys031');

$callback = '';
if (filter_input(INPUT_POST, 'callback') !== null) 
    $callback = (string)filter_input(INPUT_POST, 'callback');

if(isset($messageerror) && !empty($messageerror)) {
    require \model\route::script('login/index.php');
    die();
}

(new \model\login())->registerUser($emaillogon, LOGINSRV, $uk, $callback ?? '');

