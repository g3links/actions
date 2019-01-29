<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$modeltask = new \model\action(\model\env::session_src());
$action = $modeltask->getActionById(\model\env::session_idtaskselected());
if (!isset($action))
    \model\message::severe('Not found', \model\utils::format('id action: {0}, not found at project.', \model\env::session_idtaskselected()));

$lexi = \model\lexi::getall('actions');

//comments
//$comments = $modeltask->getCommentListBytask(\model\env::session_idtaskselected());
// attached files 
$readimagefileroute = \model\route::form('actions/action/p_readimagefile.php?idproject={0}&idtask={1}&filename=[attachedfile]', \model\env::session_idproject(), \model\env::session_idtaskselected());
$downloadfileroute = \model\route::form('actions/action/p_downloadfile.php?idproject={0}&idtask={1}&filename=[attachedfile]', \model\env::session_idproject(), \model\env::session_idtaskselected());

$index = 0;
$attachedfileslinks = [];
foreach ($action->attachedfiles as $attachedfile) {
    $attachedfileslink = new \stdClass();
    $attachedfileslink->name = $attachedfile;
    $attachedfileslink->url = str_replace('[attachedfile]', $attachedfile, $downloadfileroute);
    $attachedfileslink->index = $index;

    $fileinfo = \strtolower(pathinfo($attachedfile)['extension']);
    $attachedfileslink->imagepath = '';
    if ($fileinfo === 'png' | $fileinfo === 'jpg' | $fileinfo === 'bmp') {
        $attachedfileslink->imagepath = str_replace('[attachedfile]', $attachedfile, $readimagefileroute);
    }
    $index++;
    $attachedfileslinks[] = $attachedfileslink;
}

require_once \model\route::script('style.php');
$data = [
    'action' => $action,
    'attachedfileslinks' => $attachedfileslinks,
    'lbl_edit' => $lexi['sys022'],
    'lbl_in' => $lexi['sys023'],
    'lbl_id' => \model\utils::format($lexi['sys006'], \model\env::session_idproject() . ' - ' . \model\env::session_idtaskselected()),
    'lbl_user' => \model\utils::format($lexi['sys007'], (isset($action->username) ? $action->username : '')),
    'historycommentroute' => \model\route::form('actions/action/historycomment.php?idproject={0}&idtask={1}', \model\env::session_idproject(), \model\env::session_idtaskselected()),
    'lbl_statusname' => $lexi['sys071'],
    'lbl_titlehold' => $lexi['sys001'],
    'lbl_titlemaster' => $lexi['sys061'],
    'lbl_titleasignedusers' => $lexi['sys047'],
    'lbl_attachfile' => $lexi['sys054'],
    'lbl_categoryname' => $lexi['sys013'],
    'lbl_progress' => $lexi['sys068'],
    'lbl_priority' => $lexi['sys026'],
    'lbl_overdue' => $lexi['sys018'],
    'th_col1' => $lexi['sys003'],
    'th_col2' => $lexi['sys004'],
    'lbl_titlelinkedtask' => $lexi['sys052'],
    'lbl_comments' => $lexi['sys011'],
    'lbl_commentshistory' => $lexi['sys034'],
];
if ($action->isroleupdate) {
    if ($action->isrolegate) {
        $data += [
            'taskstatusroute' => \model\route::form('actions/action/change-status.php?idproject={0}&idtask={1}&newidgate=[idgate]', \model\env::session_idproject(), \model\env::session_idtaskselected()),
            'lbl_titlegates' => $lexi['sys025'],
            'lbl_titleinfo' => $lexi['sys021'],
            'lbl_submitinfo' => $lexi['sys071'],
        ];
    }
    if ($action->allowedit) {
        $data += [
            'lbl_titletag' => $lexi['sys008'],
            'lbl_titleupdatehold' => $lexi['sys002'],
            'viewholdroute' => \model\route::form('actions/action/m_edithold.php?idproject={0}&idtask={1}', \model\env::session_idproject(), \model\env::session_idtaskselected()),
            'edittagroute' => \model\route::form('actions/action/m_edittag.php?idproject={0}&idtask={1}', \model\env::session_idproject(), \model\env::session_idtaskselected()),
        ];
    }
    if ($action->allowedit & !$action->onhold) {
        $data += [
            'lbl_titleedit' => $lexi['sys067'],
            'lbl_titlesubtask' => $lexi['sys005'],
            'edittaskroute' => \model\route::form('actions/action/m_edittask.php?idproject={0}&idtask={1}', \model\env::session_idproject(), \model\env::session_idtaskselected()),
            'newactionroute' => \model\route::window('newaction', ['actions/actionnew/index.php?idproject={0}&idtask={1}', \model\env::session_idproject(), \model\env::session_idtaskselected()], \model\env::session_idproject(), \model\lexi::get('g3/project', 'sys009')),
        ];
    }
    if ($action->allowedit & $action->isroletrack) {
        $data += [
            'lbl_titletrack' => $lexi['sys078'],
            'viewtrackroute' => \model\route::form('actions/track/m_actiontrack.php?idproject={0}&idtask={1}&iscategory=yes', \model\env::session_idproject(), \model\env::session_idtaskselected()),
        ];
    }
    if ($action->allowedit & !$action->onhold & $action->isroleusers) {
        $data += [
            'lbl_titleuser' => $lexi['sys047'],
            'taskuserroute' => \model\route::form('actions/action/m_taskuser.php?idproject={0}&idtask={1}', \model\env::session_idproject(), \model\env::session_idtaskselected()),
        ];
    }
    if ($action->allowedit & !$action->onhold & $action->isrolefile) {
        $data += [
            'lbl_titlefiles' => $lexi['sys054'],
            'lbl_download' => $lexi['sys059'],
            'lbl_confirmdeletefile' => $lexi['sys058'],
            'lbl_cancelfile' => $lexi['sys021'],
            'afileattachroute' => \model\route::form('actions/action/m_fileattach.php?idproject={0}&idtask={1}', \model\env::session_idproject(), \model\env::session_idtaskselected()),
            'taskdeletefileroute' => \model\route::form('actions/action/deleteattachedfile.php?idproject={0}&idtask={1}&filename=[attachedfile]', \model\env::session_idproject(), \model\env::session_idtaskselected()),
        ];
    }
    if ($action->allowedit & !$action->onhold & $action->isroletasks) {
        $data += [
            'lbl_titleattachtask' => $lexi['sys052'],
            'actionattachroute' => \model\route::form('actions/action/m_taskattach.php?idproject={0}&idtask={1}', \model\env::session_idproject(), \model\env::session_idtaskselected()),
        ];
    }
    if ($action->allowedit & !$action->onhold & $action->istracking) {
        $data += [
            'lbl_titletracking' => $lexi['sys077'],
            'aviewtrackroute' => \model\route::form('actions/track/m_actiontrack.php?idproject={0}&idtask={1}', \model\env::session_idproject(), \model\env::session_idtaskselected()),
        ];
    }
    if ($action->allowedit & $action->isrolecomment) {
        $data += [
            'lbl_titlecomment' => $lexi['sys011'],
            'lbl_discard' => $lexi['sys017'],
            'editcommentroute' => \model\route::form('actions/action/m_editcomment.php?idproject={0}&idtask={1}', \model\env::session_idproject(), \model\env::session_idtaskselected()),
            'commentdeleteroute' => \model\route::form('actions/action/deletecomment.php?idproject={0}&idtask={2}&idcomment=[idcomment]', \model\env::session_idproject(), \model\env::session_idtaskselected()),
        ];
    }
}
\model\route::render('actions/action/index.twig', $data);
