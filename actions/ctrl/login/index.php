<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3session.php';

$useremail = '';
if (!empty(\model\env::getUserEmail()))
    $useremail = \model\env::getUserEmail();

$lexi = \model\lexi::getall();

require_once \model\route::script('style.php');
$data = [
    'messageerror' => $messageerror ?? '',
    'useremail' => $useremail,
    'lbl_host' => WEB_HOST,
    'allowcreateuser' => !(isset($callback) && !empty($callback)),
    'callback' => $callback ?? '', // run this procedure after logon
    'apilogonroute' => \model\route::form('api/login.php'),
    'apilogonnewroute' => \model\route::form('api/loginnew.php'),
    'apisecuritycoderoute' => \model\route::form('api/securitycode.php'),
//    'changepwdroute' => \model\route::form('api/loginpassw.php'),
    'changepwdformroute' => \model\route::form('login/changepwd.php'),
    'resetpwdformroute' => \model\route::form('login/resetpwd.php'),
    'emailresetpwdroute' => \model\route::form('api/loginresetpwd.php'),
    'apirestartroute' => \model\route::form('restart.php'),
    'aboutroute' => \model\route::form('login/about.php'),
//    'authrequiredroute' => \model\route::form('login/authrequired.php'),
    'lbl_email' => $lexi['sys028'],
    'lbl_password' => $lexi['sys034'],
    'lbl_password1' => $lexi['sys069'],
    'lbl_security' => $lexi['sys018'],
    'lbl_submitlogin' => $lexi['sys014'],
    'lbl_submitregister' => $lexi['sys038'],
    'lbl_username' => $lexi['sys041'],
    'lnk_message' => $lexi['sys042'],
    'lbl_clearuser' => $lexi['sys037'],
//    'lbl_sendreset' => $lexi['sys039'],
    'lbl_changepwd' => $lexi['sys068'],
    'lbl_newpassword' => $lexi['sys035'],
//    'lbl_submitchangepwd' => $lexi['sys068'],
    'lbl_resetpwd' => $lexi['sys029'],
    'lbl_securitycode' => $lexi['sys078'],
    'lbl_submitsecuritycode' => $lexi['sys079'],
    'lbl_securitymessage' => $lexi['sys027'],
];
\model\route::render('login/index.twig', $data);

require \model\route::render('g3/*/terms.html');
