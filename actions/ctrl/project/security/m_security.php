<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$seccode = '';
if (filter_input(INPUT_GET, 'seccode') !== null)
    $seccode = filter_input(INPUT_GET, 'seccode');

$lexi = \model\lexi::getall('g3/project');

// get master role
$roles = (new \model\project())->getRoles();
$userroles = [];
$newrole = new \stdClass();
$newrole->idrole = 0;
$newrole->name = $lexi['sys128'];
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
        'lbl_title' => $lexi['sys012'],
        'lbl_generic' => $lexi['sys127'],
        'lbl_security' => $lexi['sys122'],
        'th_col1' => $lexi['sys043'],
        'th_col2' => $lexi['sys012'],
        'lbl_submit' => $lexi['sys108'],
        'lbl_reset' => $lexi['sys125'],
    ];
    if ($projectsecurity->isrole) {
        $data += [
            'editprojsecurityusersroute' => \model\route::form('project/security/p_updatesecurityusers.php?idproject={0}&seccode={1}', \model\env::session_idproject(), $seccode),
            'resetprojsecurityusersroute' => \model\route::form('project/security/p_resetsecurityusers.php?idproject={0}&seccode={1}', \model\env::session_idproject(), $seccode),
        ];
    }
    \model\route::render('project/security/m_security.twig', $data);
}