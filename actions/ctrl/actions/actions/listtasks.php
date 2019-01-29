<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$navpage = 0;
if (filter_input(INPUT_GET, 'navpage') !== null)
    $navpage = (int) filter_input(INPUT_GET, 'navpage');

$idtrack = 0;
if (filter_input(INPUT_GET, 'idtrack') !== null)
    $idtrack = (int) filter_input(INPUT_GET, 'idtrack');

//sort ************************
$sortname = '';
if (filter_input(INPUT_GET, 'sort') !== null)
    $sortname = (string) filter_input(INPUT_GET, 'sort');

$prev_sortdirection = '';
if (filter_input(INPUT_GET, 'sortdirection') !== null)
    $prev_sortdirection = (string) filter_input(INPUT_GET, 'sortdirection');

$result = (new \model\action(\model\env::session_src()))->getListActions(\model\env::session_lastviewgate(), $idtrack, $sortname, $prev_sortdirection, $navpage);

$lexi = \model\lexi::getall('actions');

if (isset($result->actiontags) | isset($result->actionusers)) {
    $data = [
        'actiontags' => $result->actiontags ?? [],
        'actionusers' => $result->actionusers ?? [],
        'lbl_title' => $lexi['sys042'],
    ];
    \model\route::render('actions/actions/ctrl_tags.twig', $data);
}

if (isset($result->todoshold)) {
    $data = [
        'todoshold' => $result->todoshold,
        'lbl_title' => $lexi['sys001'],
    ];
    \model\route::render('actions/actions/listtasks_hold.twig', $data);
}

if (isset($result->tracks)) {
    $data = [
        'tracks' => $result->tracks,
        'idtrack' => $idtrack,
        'lbl_title' => $lexi['sys046'],
    ];
    \model\route::render('actions/actions/filter_track.twig', $data);
}

if ($navpage === 0) {
    $data = [
        'totalpages' => ceil((int) $result->total_records / (int) $result->max_records),
        'sortdirection' => $sortname . '_' . $result->_sort,
        's_priority' => $result->s_priority,
        's_description' => $result->s_description,
        's_date' => $result->s_date,
        's_projname' => $result->s_projname,
        'lbl_sorttitle' => $lexi['sys033'],
        'lbl_sortdate' => $lexi['sys018'],
        'lbl_sortproject' => $lexi['sys157'],
        'idproject' => \model\env::session_idproject(),
        'idtaskselected' => \model\env::session_idtaskselected(),
        'viewtaskroute' => \model\route::window('action', ['actions/action/index.php?idproject=[idproject]&idtask=[idtask]'], '', \model\lexi::get('actions', 'sys067'), ''),
        'lbl_notfound' => count($result->actions) === 0 ? $lexi['sys044'] : '',
        'hasprojname' => $result->hasprojname,
    ];
    \model\route::render('actions/actions/listtasks_header.twig', $data);
}

$data = [
    'actions' => $result->actions,
    'lbl_trackname' => $lexi['sys013'],
    'hasprojname' => $result->hasprojname,
];
\model\route::render('actions/actions/listtasks.twig', $data);

