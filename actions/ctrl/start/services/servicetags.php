<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$actions = (new \model\project)->getProjectsTags();

// get tags associated to this list
$actiontags = [];
$actionusers = [];
foreach ($actions as $action) {
    if (isset($action->tags)) {
        foreach ($action->tags as $tag) 
            $actiontags[] = $tag->tagname;
    }
    
    if (isset($action->username)) 
        $actionusers[] = $action->username;
}

$actiontags = array_unique($actiontags);
sort($actiontags);
$actionusers = array_unique($actionusers);
sort($actionusers);

if (count($actiontags) > 0 | count($actionusers) > 0) {
    $lexi = \model\lexi::getall('actions');
    $data = [
        'actiontags' => $actiontags,
        'actionusers' => $actionusers,
        'lbl_title' => $lexi['sys042'],
    ];
    \model\route::render('actions/actions/ctrl_tags.twig', $data);
}
