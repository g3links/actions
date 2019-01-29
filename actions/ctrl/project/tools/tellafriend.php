<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3session.php';

$lexi = \model\lexi::getall('g3/project');
require_once \model\route::script('style.php');
$data = [
    'sendinvitationroute' => \model\route::form('project/tools/p_sendinvitation.php'),
    'lbl_name' => $lexi['sys043'],
    'lbl_email' => $lexi['sys029'],
    'submit' => $lexi['sys091'],
    'lbl_tip' => $lexi['sys040'],
];
\model\route::render('project/tools/tellafriend.twig', $data);
