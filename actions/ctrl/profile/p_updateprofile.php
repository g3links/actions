<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

\model\env::validateUserToken(filter_input(INPUT_POST, '_token'));

$user = new \stdClass();
$user->email = filter_input(INPUT_POST, 'email');
$user->name = filter_input(INPUT_POST, 'username');
$user->keyname = filter_input(INPUT_POST, 'keyname');
$user->theme = filter_input(INPUT_POST, 'idtheme');
$user->iduser = \model\env::getIdUser();

$user->logintoken = '';
if (filter_input(INPUT_POST, 'passw') !== null)
    $user->logintoken = filter_input(INPUT_POST, 'passw');

if (!filter_var($user->email, FILTER_VALIDATE_EMAIL)) 
    \model\message::severe('sys030', \model\lexi::get('', 'sys030',$user->email));

$modeluser->setuserprofile($user, 'g3/*/resetemail.html');

//restore new user data
\model\env::setUser($user->iduser);

require_once \model\route::script('style.php');
\model\route::close(\model\env::session_idproject(), 'user');
\model\route::refreshMaster();
