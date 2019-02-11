<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$modulename = '';
if (filter_input(INPUT_GET, 'modulename') !== null) 
    $modulename = filter_input(INPUT_GET, 'modulename');

$datasharedprojects = (new \model\action(\model\env::session_src()))->getSharedProjectsModule($modulename);

// synchornize needed
$syncallprojects = \model\utils::firstOrDefault($datasharedprojects, '$v->requirerefresh === true');


$lexi = \model\lexi::getall();
$data = [
    'synchprojdataroute' => \model\route::form('project/links/synchprojdata.php?idproject={0}&modulename={1}', \model\env::session_idproject(), $modulename),
    'projlist' => $datasharedprojects,
    'syncallprojects' => isset($syncallprojects),
    'modulename' => $modulename,
    'lbl_requirerefresh' => $lexi['prj126'],
    'lbl_shared' => $lexi['prj005'],
];
\model\route::render('project/links/listprojectshared.twig', $data);
