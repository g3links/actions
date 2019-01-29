<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$idorder = 0;
if (filter_input(INPUT_GET, 'idorder') !== null) 
    $idorder = (int) filter_input(INPUT_GET, 'idorder');

$result = (new \model\action(\model\env::session_src()))->getNewAction();

$lexi = \model\lexi::getall('actions');

require_once \model\route::script('style.php');
$data = [
    'action' => $result->action,
    'lbl_titleuser' => $lexi['sys047'],
    'lbl_titlefiles' => $lexi['sys054'],
    'lbl_submit' => $lexi['sys029'],
    //categories
    'inc_categories' => 'actions/action/ctrl_categories.twig',
    'lbl_categories' => $lexi['sys013'],
    'categories' => $result->categories,
    //description
    'inc_description' => 'actions/action/ctrl_description.twig',
    'lbl_description' => $lexi['sys016'],
    //progress
    'inc_progress' => 'actions/action/ctrl_progress.twig',
    'lbl_progress' => $lexi['sys068'],
    'lbl_title' => $lexi['sys033'],
    'inc_title' => 'actions/action/ctrl_title.twig',
    //priority
    'inc_priority' => 'actions/action/ctrl_priority.twig',
    'lbl_priority' => $lexi['sys026'],
    'allPriorities' => $result->allPriorities,
    //duedate
    'inc_duedate' => 'actions/action/ctrl_duedate.twig',
    'lbl_duedate' => $lexi['sys012'],
    'lbl_duedate_dueon' => $lexi['sys018'],
    'lbl_duedate_th_col1' => $lexi['sys003'],
    'lbl_duedate_th_col2' => $lexi['sys004'],
    'hours' => $result->hours,
    'mins' => $result->mins,
    'inc_commenttext' => 'actions/action/ctrl_comment.twig',
    'lbl_commenttext' => $lexi['sys011'],
];
if ($result->isrole) {
    $data += [
        'updatenewactionroute' => \model\route::form('g3ext/market/order/p_newaction.php?idproject={0}&idorder={1}', \model\env::session_idproject(), $idorder),
        'aaddduedateroute' => \model\route::form('actions/action/addduedate.php?idproject={0}&idrow=[idrow]', \model\env::session_idproject()),
        'fileattachroute' => \model\route::form('actions/action/fileattach.php?idproject={0}', \model\env::session_idproject()),
    ];
}
if($result->isroleusers) {
    $data += [
        'aassignedusersroute' => \model\route::form('actions/actionnew/m_taskusers.php?idproject={0}&idtask=0', \model\env::session_idproject()),
    ];
}
\model\route::render('g3ext/market/order/newaction.twig', $data);
