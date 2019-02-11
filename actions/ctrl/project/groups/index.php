<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$result = (new \model\action(\model\env::session_src()))->getprojectgroups();

$lexi = \model\lexi::getall();

require_once \model\route::script('style.php');
$data = [
    'groups' => $result->groups,
    'lbl_creategroup' => $lexi['prj107'],
    'lbl_submit' => $lexi['prj107'],
    'lbl_notfound' => $lexi['prj044'],
];
if($result->isrole) {
    $data += [
        'editprojectgrouproute' => \model\route::form('project/groups/m_editprojectgroup.php?idproject={0}&id=[idgroup]', \model\env::session_idproject()),
    ];
}
\model\route::render('project/groups/index.twig', $data);
