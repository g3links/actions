<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$tags = (new \model\action(\model\env::session_src()))->getTagsForAction(\model\env::session_idtaskselected());

$lexi = \model\lexi::getall('actions');
$data = [
    'tags' => $tags,
    'updatetagroute' => \model\route::form('actions/action/p_updatetag.php?idproject={0}&idtask={1}', \model\env::session_idproject(),\model\env::session_idtaskselected()),
    'lbl_tag' => $lexi['sys008'],
    'lbl_tagedit' => $lexi['sys009'],
    'th_col1' => $lexi['sys016'],
    'lbl_submit' => $lexi['sys029'],
];
\model\route::render('actions/action/m_edittag.twig', $data);
