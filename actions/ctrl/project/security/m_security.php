<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$seccode = '';
if (filter_input(INPUT_GET, 'seccode') !== null)
    $seccode = filter_input(INPUT_GET, 'seccode');

$lexi = \model\lexi::getall();

// get master role
$roles = (new \model\project())->getRoles();
$userroles = [];
$newrole = new \stdClass();
$newrole->idrole = 0;
$newrole->name = $lexi['prj128'];
$userroles[] = $newrole;
foreach ($roles as $role) {
    $userroles[] = $role;
}

$projectsecurity = (new \model\action(\model\env::session_src()))->getSecurityProjectRoles($seccode);
if (isset($projectsecurity)) {
    $data = [
        'seccode' => $seccode,
        'roles' => $roles,
        'userroles' => $userroles,
        'projectsecurity' => $projectsecurity,
        'lbl_title' => $lexi['prj012'],
        'lbl_generic' => $lexi['prj127'],
        'lbl_security' => $lexi['prj122'],
        'th_col1' => $lexi['prj043'],
        'th_col2' => $lexi['prj012'],
        'lbl_submit' => $lexi['prj108'],
        'lbl_reset' => $lexi['prj125'],
    ];
    if ($projectsecurity->isrole) {
        $data += [
            'editprojsecurityusersroute' => \model\route::form('project/security/p_updatesecurityusers.php?idproject={0}&seccode={1}', \model\env::session_idproject(), $seccode),
            'resetprojsecurityusersroute' => \model\route::form('project/security/p_resetsecurityusers.php?idproject={0}&seccode={1}', \model\env::session_idproject(), $seccode),
        ];
    }
    \model\route::render('project/security/m_security.twig', $data);
}