<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$result = (new \model\action(\model\env::session_src()))->getprojectgroups();

$lexi = \model\lexi::getall('g3/project');

require_once \model\route::script('style.php');
$data = [
    'groups' => $result->groups,
    'lbl_creategroup' => $lexi['sys107'],
    'lbl_submit' => $lexi['sys107'],
    'lbl_notfound' => $lexi['sys044'],
];
if($result->isrole) {
    $data += [
        'editprojectgrouproute' => \model\route::form('project/groups/m_editprojectgroup.php?idproject={0}&id=[idgroup]', \model\env::session_idproject()),
    ];
}
\model\route::render('project/groups/index.twig', $data);
