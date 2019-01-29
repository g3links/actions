<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$idproject = 0;

$project = new \stdClass();

$project->idproject = $idproject;
$project->title = '';
$project->description = '';
$project->remoteurl = '';
$project->startuppath = '';
$project->startupwidth = 500;
$project->ispublic = false;
$project->marketname = '';
$project->prefix = '';
$project->ticketseq = 0;

$projectusers = [];

$lexi = \model\lexi::getall('g3/project');

require_once \model\route::script('style.php');
$data = [
    'idproject' => $idproject,
    'editprojectroute' => \model\route::form('project/admon/p_updateproject.php?idproject={0}', $idproject),
    'project' => $project,
    'lbl_title' => \model\utils::format($lexi['sys031'], $idproject),
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
    'lbl_submitupdate' => $lexi['sys061'],
    'lbl_submitnew' => $lexi['sys020'],
    'searchprojarchivedroute' => \model\route::form('project/admon/projrestoresearch.php?search=[search]'),
    'restoreprojectroute' => \model\route::form('project/admon/p_restoreproject.php'),
    'lbl_restore' => $lexi['sys114'],
    'lbl_titlerestore' => $lexi['sys116'],
    'lbl_search' => $lexi['sys049'],
];
\model\route::render('project/admon/index.twig', $data);
