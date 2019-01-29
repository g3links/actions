<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

// do not load services when seraching for all actions
if (\model\env::session_idproject() > 0) {
    $project = (new \model\project)->getproject(\model\env::session_idproject());
    require \model\route::script('loadservices.php');


    $groups = [];
    foreach ($project->modules as $module) {
        if (!isset($groups[$module->groupname])) {
            $groups[$module->groupname] = $module->groupname;
        }
    }
    if (count($project->modules) > 0) {
        $data = [
            'project' => $project,
            'groups' => $groups,
        ];
        \model\route::render('actions/actions/modules.twig', $data);
    }
}