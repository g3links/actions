<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3session.php';

$iduser = '';
if (filter_input(INPUT_POST, 'iduser') !== null) 
    $iduser = (string)filter_input(INPUT_POST, 'iduser');

$uk = '';
if (filter_input(INPUT_POST, 'pwdlogon') !== null) 
    $uk = (string)filter_input(INPUT_POST, 'pwdlogon');

$callback = '';
if (filter_input(INPUT_POST, 'callback') !== null) 
    $callback = (string)filter_input(INPUT_POST, 'callback');

$user = (new \model\user)->getuser($iduser);
if (!isset($user)) {
    echo \model\utils::format('<h3 style="color: red;">{0}</h3>',\model\lexi::get('', 'sys031'));
    die();
}

(new \model\login)->confirmUserIdBeforeRender($user->email, LOGINSRV, $uk, $callback ?? '');

