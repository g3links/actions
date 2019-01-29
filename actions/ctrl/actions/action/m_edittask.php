<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$isrole = \model\env::isUserAllow(\model\env::session_idproject(), \model\action::ROLE_ACTIONUPDATE);

$modeltask = new \model\action(\model\env::session_src());

$action = $modeltask->getTaskById(\model\env::session_idtaskselected());

foreach ($action->taskdues as $taskdue) {
    $taskdue->lbl_starton = \model\utils::getDueDateFormatted($taskdue->starton, \model\env::getTimezone());
    $taskdue->lbl_dueon = \model\utils::getDueDateFormatted($taskdue->dueon, \model\env::getTimezone());
}

$lexi = \model\lexi::getall('actions');

$data = [
    'action' => $action,
    'lbl_submit' => $lexi['sys029'],
    'lbl_titleform' => $lexi['sys057'],
    //categories
    'inc_categories' => 'actions/action/ctrl_categories.twig',
    'lbl_categories' => $lexi['sys013'],
    'categories' => $modeltask->getCategories(),
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
    'allPriorities' => $modeltask->getPriorities(),
    //duedate
    'inc_duedate' => 'actions/action/ctrl_duedate.twig',
    'lbl_duedate' => $lexi['sys012'],
    'lbl_duedate_dueon' => $lexi['sys018'],
    'lbl_duedate_th_col1' => $lexi['sys003'],
    'lbl_duedate_th_col2' => $lexi['sys004'],
    'hours' => \model\utils::getHours(),
    'mins' => \model\utils::getMinutes(),

    //comment
    'inc_commenttext' => 'actions/action/ctrl_comment.twig',
    'lbl_commenttext' => $lexi['sys011'],
];
if ($isrole) {
    $data += [
        'updatetaskroute' => \model\route::form('actions/action/p_edittask.php?idproject={0}&idtask={1}', \model\env::session_idproject(), \model\env::session_idtaskselected()),
        'aaddduedateroute' => \model\route::form('actions/action/addduedate.php?idproject={0}&idrow=[idrow]', \model\env::session_idproject()),
    ];
}
\model\route::render('actions/action/m_edittask.twig', $data);
