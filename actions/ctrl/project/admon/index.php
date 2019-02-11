<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$project = (new \model\action(\model\env::session_src()))->getprojectowners();

$lexi = \model\lexi::getall();
require_once \model\route::script('style.php');
$data = [
    'project' => $project,
    'idproject' => \model\env::session_idproject(),
    'lbl_title' => \model\utils::format($lexi['prj031'], \model\env::session_idproject()),
    'lbl_modified' => $lexi['prj039'],
    'lbl_created' => $lexi['prj023'],
    'lbl_packagename' => $lexi['prj043'],
    'lbl_description' => $lexi['prj082'],
    'lbl_prefix' => $lexi['prj118'],
    'lbl_startup' => $lexi['prj016'],
    'lbl_width' => $lexi['prj072'],
    'lbl_widthtip' => $lexi['prj018'],
    'lbl_ispublic' => $lexi['prj078'],
    'lbl_market' => $lexi['prj079'],
    'lbl_remoteurl' => $lexi['prj004'],
    'lbl_currency' => $lexi['prj006'],
    'lbl_submitupdate' => $lexi['prj061'],
    'lbl_submitnew' => $lexi['prj020'],
    'lbl_confirmdelete' => $lexi['prj024'],
    'lbl_save' => $lexi['prj025'],
    'lbl_cancel' => $lexi['prj021'],
    'lbl_titleowner' => $lexi['prj083'],
    'th_col1' => $lexi['prj043'],
    'th_col2' => $lexi['prj029'],
    'lbl_notfound' => $lexi['prj051'],
    'lbl_addowner' => $lexi['prj085'],
    'lbl_deleteowner' => $lexi['prj093'],
    'lbl_cancelowner' => $lexi['prj021'],
];
if ($project->isrole & $project->isowner) {
    $data += [
        'editprojectroute' => \model\route::form('project/admon/p_updateproject.php?idproject={0}', \model\env::session_idproject()),
        'deleteprojectroute' => \model\route::form('project/admon/p_deleteproject.php?idproject={0}', \model\env::session_idproject()),
        'addprojectownerroute' => \model\route::form('project/security/m_projectowner.php?idproject={0}', \model\env::session_idproject()),
    ];
}
if($project->isrole & $project->isowner & count($project->users) > 0) {
    $data += [
        'deleteprojownerroute' => \model\route::form('project/security/p_projectownerdelete.php?idproject={0}&iduser=[iduser]', \model\env::session_idproject()),
    ];
}
\model\route::render('project/admon/index.twig', $data);


