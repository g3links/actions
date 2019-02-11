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

$lexi = \model\lexi::getall();

require_once \model\route::script('style.php');
$data = [
    'idproject' => $idproject,
    'editprojectroute' => \model\route::form('project/admon/p_updateproject.php?idproject={0}', $idproject),
    'project' => $project,
    'lbl_title' => \model\utils::format($lexi['prj031'], $idproject),
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
    'lbl_submitupdate' => $lexi['prj061'],
    'lbl_submitnew' => $lexi['prj020'],
    'searchprojarchivedroute' => \model\route::form('project/admon/projrestoresearch.php?search=[search]'),
    'restoreprojectroute' => \model\route::form('project/admon/p_restoreproject.php'),
    'lbl_restore' => $lexi['prj114'],
    'lbl_titlerestore' => $lexi['prj116'],
    'lbl_search' => $lexi['prj049'],
];
\model\route::render('project/admon/index.twig', $data);
