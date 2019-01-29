<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$project = (new \model\action(\model\env::session_src()))->getprojectowners();

$lexi = \model\lexi::getall('g3/project');
require_once \model\route::script('style.php');
$data = [
    'project' => $project,
    'idproject' => \model\env::session_idproject(),
    'lbl_title' => \model\utils::format($lexi['sys031'], \model\env::session_idproject()),
    'lbl_modified' => $lexi['sys039'],
    'lbl_created' => $lexi['sys023'],
    'lbl_packagename' => $lexi['sys043'],
    'lbl_description' => $lexi['sys082'],
    'lbl_prefix' => $lexi['sys118'],
    'lbl_startup' => $lexi['sys016'],
    'lbl_width' => $lexi['sys072'],
    'lbl_widthtip' => $lexi['sys018'],
    'lbl_ispublic' => $lexi['sys078'],
    'lbl_market' => $lexi['sys079'],
    'lbl_remoteurl' => $lexi['sys004'],
    'lbl_currency' => $lexi['sys006'],
    'lbl_submitupdate' => $lexi['sys061'],
    'lbl_submitnew' => $lexi['sys020'],
    'lbl_confirmdelete' => $lexi['sys024'],
    'lbl_save' => $lexi['sys025'],
    'lbl_cancel' => $lexi['sys021'],
    'lbl_titleowner' => $lexi['sys083'],
    'th_col1' => $lexi['sys043'],
    'th_col2' => $lexi['sys029'],
    'lbl_notfound' => $lexi['sys051'],
    'lbl_addowner' => $lexi['sys085'],
    'lbl_deleteowner' => $lexi['sys093'],
    'lbl_cancelowner' => $lexi['sys021'],
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


