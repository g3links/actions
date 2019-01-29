<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

// highlight when security is restricted
$idrole = (new \model\project)->getuseridrole(\model\env::session_idproject());

$roles = (new \model\project)->getProjectRoles(\model\env::getIdUser(), ( new \model\action(\model\env::session_src()))->getprojectsecurity());
foreach ($roles as $role) {
    if (!isset($role->secs))
        continue;

    foreach ($role->secs as $security) {
        $security->hasaccess = true;
        if ($security->idrole < $idrole)
            $security->hasaccess = false;
    }
}

//$userrole = (new \model\project)->getRole((new \model\project)->getuserrole(\model\env::session_idproject())->idrole);
// get role name from roles list
$userrole = \model\utils::firstOrDefault($roles, '$v->idrole === '. (new \model\project)->getuserrole(\model\env::session_idproject())->idrole);

$lexi = \model\lexi::getall('g3/project');
require_once \model\route::script('style.php');
$data = [
    'roles' => $roles,
    'roledescription' => $userrole->name ?? '',
    'updatesecurityroute' => \model\route::form('project/security/m_security.php?idproject={0}&seccode=[seccode]', \model\env::session_idproject()),
    'lbl_mysecurity' => $lexi['sys048'],
    'th_col1' => $lexi['sys019'],
    'th_col2' => $lexi['sys043'],
    'th_col3' => $lexi['sys048'],
    'lbl_notfound' => $lexi['sys044'],
];
\model\route::render('project/security/index.twig', $data);
