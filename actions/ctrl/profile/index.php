<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$result = (new \model\user)->getProfileSession();

$lexi = \model\lexi::getall('g3');

require_once \model\route::script('style.php');
$data = [
    'user' => $result->user,
    'theme' => $result->theme,
    'lbl_username' => $lexi['sys053'],
    'lbl_updateuser' => $lexi['sys055'],
    'lbl_email' => $lexi['sys028'],
    'lbl_keyname' => $lexi['sys054'],
    'lbl_theme' => $lexi['sys063'],
    'editprofileroute' => \model\route::form('profile/m_profile.php'),
];
\model\route::render('profile/index.twig', $data);
