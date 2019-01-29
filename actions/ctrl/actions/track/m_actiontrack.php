<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$iscategory = false;
if (filter_input(INPUT_GET, 'iscategory') !== null) 
    $iscategory = true;

$modeltask = (new \model\action(\model\env::session_src()));

$action = $modeltask->getTaskById(\model\env::session_idtaskselected()); // get first record
$categories = $modeltask->getCategories();

$lexi = \model\lexi::getall('actions');
$data = [
    'idproject' => \model\env::session_idproject(),
    'iscategory' => $iscategory,
    'lbl_iscategory' => \model\utils::formatBooleanToString($iscategory),
    'hascategories' => count($categories) > 0 ? true : false,
    'hastracks' => $modeltask->hasTrack(),
    'categories' => $categories,
    'action' => $action,
    'tracks' => $modeltask->getTracks(),
    'username' => \model\env::getUserName(),
    'edittrackroute' => \model\route::form('actions/track/p_updateactiontrack.php?idproject={0}', \model\env::session_idproject()),
    'lbl_assignedto' => $lexi['sys047'],
    'lbl_category' => $lexi['sys013'],
    'lbl_changestatus' => $lexi['sys014'],
    'lbl_comments' => $lexi['sys011'],
    'lbl_save' => $lexi['sys029'],
];
\model\route::render('actions/track/m_actiontrack.twig', $data);
