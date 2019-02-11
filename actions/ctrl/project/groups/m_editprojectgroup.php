<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$idgroup = 0;
if (filter_input(INPUT_GET, 'id') !== null) 
    $idgroup = (int) filter_input(INPUT_GET, 'id');

$projectgroup = (new \model\action(\model\env::session_src()))->getprojectgroupusersactive($idgroup);

$lexi = \model\lexi::getall();
$data = [
    'projectgroup' => $projectgroup,
    'lbl_title' => $projectgroup->idgroup === 0 ? $lexi['prj107'] : $lexi['prj106'],
    'lbl_name' => $lexi['prj043'],
    'lbl_submit' => $lexi['prj106'],
    'lbl_inactive' => $lexi['prj110'],
    'lbl_active' => $lexi['prj109'],
    'lbl_delete' => $lexi['prj111'],
];
if($projectgroup->isrole) {
    $data += [
        'updateprojgrouproute' => \model\route::form('project/groups/p_updateprojgroup.php?idproject={0}&id={1}', \model\env::session_idproject(), $idgroup),
    ];
}
\model\route::render('project/groups/m_editprojectgroup.twig', $data);
