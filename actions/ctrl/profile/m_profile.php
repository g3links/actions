<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

require \model\route::script('login/confirmidentity.php');
 
$result = (new \model\user)->getUserProfile();

$lexi = \model\lexi::getall('g3');
require_once \model\route::script('style.php');

$data = [
    'user' => $result->user,
    'themes' => $result->themes,
    'passw' => $logintoken ?? '',
    'token' => \model\env::getHtmlUserToken(),
    'lbl_username' => $lexi['sys053'],
    'lbl_email' => $lexi['sys028'],
    'lbl_keyname' => $lexi['sys054'],
    'submit' => $lexi['sys055'],
    'lbl_enableaccount' => $lexi['sys056'],
    'lbl_disableaccount' => $lexi['sys057'],
    'lbl_confirmdisableaccount' => $lexi['sys058'],
    'lbl_cancel' => $lexi['sys059'],
    'lbl_deleteaccount' => $lexi['sys060'],
    'lbl_deleteaccountwarning' => $lexi['sys061'],
    'lbl_deleteaccountconfirm' => $lexi['sys062'],
    'lbl_theme' => $lexi['sys063'],
    'updateprofileroute' => \model\route::form('profile/p_updateprofile.php'),
    'inactiveaccountroute' => \model\route::form('profile/p_sleepaccount.php'),
    'closeaccountroute' => \model\route::form('profile/p_deleteaccount.php'),
];
\model\route::render('profile/m_profile.twig', $data);
