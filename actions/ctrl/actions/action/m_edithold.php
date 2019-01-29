<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$taskholds = (new \model\action(\model\env::session_src()))->getActionOnHold(\model\env::session_idtaskselected());

$lexi = \model\lexi::getall('actions');
$data = [
    'taskholds' => $taskholds,
    'editholdroute' => \model\route::form('actions/action/p_edithold.php?idproject={0}&idtask={1}', \model\env::session_idproject(),\model\env::session_idtaskselected()),
    'lbl_hold' => $lexi['sys020'],
    'lbl_update' => $lexi['sys009'],
    'th_col1' => $lexi['sys016'],
    'lbl_submit' => $lexi['sys029'],
]; 
\model\route::render('actions/action/m_edithold.twig', $data);
