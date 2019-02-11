<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

//display invitations (if any)
$projectinvitations = (new \model\user)->getinvitationsbyEmail();
if (count($projectinvitations) > 0) {
    $lexi = \model\lexi::getall();

    $data = [
        'lbl_title' => $lexi['sys002'],
        'projectinvitations' => $projectinvitations,
        'projinvacceptroute' => \model\route::form('project/users/acceptprojectinvite.php'),
    ];
    \model\route::render('g3/menuinvitations.twig', $data);
}
