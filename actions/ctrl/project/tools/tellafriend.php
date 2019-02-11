<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3session.php';

$lexi = \model\lexi::getall();
require_once \model\route::script('style.php');
$data = [
    'sendinvitationroute' => \model\route::form('project/tools/p_sendinvitation.php'),
    'lbl_name' => $lexi['prj043'],
    'lbl_email' => $lexi['prj029'],
    'submit' => $lexi['prj091'],
    'lbl_tip' => $lexi['prj040'],
];
\model\route::render('project/tools/tellafriend.twig', $data);
