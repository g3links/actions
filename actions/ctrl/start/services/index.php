<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$projects = (new \model\project)->getprojects();

$lexi = \model\lexi::getall('g3');

foreach ($projects as $project) {
    $project->actionroute = \model\route::window('actions', ['project/index.php?idproject={0}', $project->idproject],$project->idproject, $lexi['sys001'],$project->title);
    $project->setuproute = \model\route::window('projadmon',['project/setup/index.php?idproject={0}', $project->idproject], $project->idproject, '');

    require \model\route::script('loadservices.php');
}

$data = [
    'projects' => $projects,
];
\model\route::render('start/services/index.twig', $data);
