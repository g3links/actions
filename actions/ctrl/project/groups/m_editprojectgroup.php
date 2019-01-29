<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$idgroup = 0;
if (filter_input(INPUT_GET, 'id') !== null) 
    $idgroup = (int) filter_input(INPUT_GET, 'id');

$projectgroup = (new \model\action(\model\env::session_src()))->getprojectgroupusersactive($idgroup);

$lexi = \model\lexi::getall('g3/project');
$data = [
    'projectgroup' => $projectgroup,
    'lbl_title' => $projectgroup->idgroup === 0 ? $lexi['sys107'] : $lexi['sys106'],
    'lbl_name' => $lexi['sys043'],
    'lbl_submit' => $lexi['sys106'],
    'lbl_inactive' => $lexi['sys110'],
    'lbl_active' => $lexi['sys109'],
    'lbl_delete' => $lexi['sys111'],
];
if($projectgroup->isrole) {
    $data += [
        'updateprojgrouproute' => \model\route::form('project/groups/p_updateprojgroup.php?idproject={0}&id={1}', \model\env::session_idproject(), $idgroup),
    ];
}
\model\route::render('project/groups/m_editprojectgroup.twig', $data);
