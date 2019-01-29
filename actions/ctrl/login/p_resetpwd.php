<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3session.php';

$email = '';
// intent to register a new account
if (filter_input(INPUT_POST, 'emailreset') !== null) 
    $email = filter_input(INPUT_POST, 'emailreset');

$modeluser = new \model\user();
$user = $modeluser->authresetpassword($email);
if($user === false) {
    require \model\route::script('login/index.php');
    die();
}

$lexi = \model\lexi::getall('g3');

$data = [
    'lbl_security' => $lexi['sys018'],
    'lbl_username' => $lexi['sys025'],
    'username' => $user->name,
    'lbl_email' => \model\utils::format($lexi['sys027'],$email),
    'host' => ROOT_APP,
];
\model\route::render('login/authrequired.twig', $data);
