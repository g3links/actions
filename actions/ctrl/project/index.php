<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$lexi = \model\lexi::getall();
require_once \model\route::script('style.php');

$project = (new \model\project)->getproject(\model\env::session_idproject());
if (!isset($project)) 
    \model\message::severe('sys013', $lexi['prj013']);

if (!empty(\trim($project->startuppath))) {
    //custom URL provided
    require \model\route::script('project/admon/menuproject.php');

    $data = [
        'url' => \trim($project->startuppath),
    ];
    \model\route::render('project/admon/urllink.twig', $data);
} else {
    if (!empty(\trim($project->remoteurl))) {
        // remote actions url
        \model\route::open('actions', $project->remoteurl, \model\env::session_idproject(), \model\lexi::get('', 'sys006'), \model\env::getUserName());
    } else {
        // regular project actions
        require \model\route::script('actions/index.php?idproject={0}', \model\env::session_idproject());
    }
}
