<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$isrolecomment = \model\env::isUserAllow(\model\env::session_idproject(), \model\action::ROLE_ACTIONCOMMENT);
$historycomments = (new \model\action(\model\env::session_src()))->geCommenttListHistoryBytask(\model\env::session_idtaskselected());

$lexi = \model\lexi::getall('actions');
$data = [
    'lbl_viewhistory' => $lexi['sys019'],
    'historycomments' => $historycomments,
    'lbl_restore' => $lexi['sys028'],
];
if($isrolecomment) {
    $data += [
        'commentrestoreroute' => \model\route::form('actions/action/restorecomment.php?idproject={0}&idtask={1}&idcomment=[idcomment]', \model\env::session_idproject(), \model\env::session_idtaskselected()),
    ];
}
\model\route::render('actions/action/historycomment.twig', $data);

