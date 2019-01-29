<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$projects = (new \model\project)->getactiveprojects();

// open selected project
$cacheselecteproject = 0;
if (filter_input(INPUT_GET, 'idproject') !== null) 
    $cacheselecteproject = (int) filter_input(INPUT_GET, 'idproject');

$useridproject = \model\env::session_idproject() === 0 ? \model\env::getUserIdProject() : \model\env::session_idproject();

$lexi = \model\lexi::getall('g3');
$data = [
    'projects' => $projects,
    'cacheselecteproject' => $cacheselecteproject,
    'actionroute' => \model\route::window('actions', ['project/index.php?idproject=[idproject]'], '', $lexi['sys001'], ''),
    'initsetuproute' => \model\route::window('projsetup', ['project/admon/index.php?idproject=[idproject]'], \model\env::session_idproject(), \model\lexi::get('g3/project', 'sys058')),
    'lbl_title' => $lexi['sys003'],
    'lbl_share' => $lexi['sys004'],
    'lbl_remote' => $lexi['sys070'],
];
\model\route::render('g3/menulistprojects.twig', $data);

