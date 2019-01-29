<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$lexi = \model\lexi::getall('actions');
$data = [
    'taskattachroute' => \model\route::form('actions/action/taskstoattach.php?idproject={0}&idtask={1}', \model\env::session_idproject(), \model\env::session_idtaskselected()),
    'updatedtaskattachroute' => \model\route::form('actions/action/p_updatedtaskattach.php?idproject={0}&idtask={1}', \model\env::session_idproject(), \model\env::session_idtaskselected()),
    'lbl_name' => $lexi['sys053'],
    'lbl_submit' => $lexi['sys029'],
];
\model\route::render('actions/action/m_taskattach.twig', $data);
