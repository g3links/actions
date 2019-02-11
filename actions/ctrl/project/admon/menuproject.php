<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$result = (new \model\project)->getMenuProject(\model\env::session_idproject());

if (isset($result->assignedusernames)) {
    $data = [
        'assignedusernames' => $result->assignedusernames,
    ];
    \model\route::render('actions/actions/ctrl_filteredusers.twig', $data);
}

$lexi = \model\lexi::getall();
$data = [
    'allownewaction' => $result->allownewaction,
    'allowsetup' => $result->allowsetup,
    'allowmessg' => $result->allowmessg,
    'allowfilter' => $result->allowfilter,
    'initmenuroute' => \model\route::form('project/admon/m_projectnote.php?idproject={0}', $result->useridproject),
    'filterusersroute' => \model\route::form('project/users/m_filteruser.php?idproject={0}', \model\env::session_idproject()),
    'filterviewsroute' => \model\route::form('actions/actions/m_filterview.php?idproject={0}', \model\env::session_idproject()),
    'initsetuproute' => \model\route::window('projadmon',['project/setup/index.php?idproject={0}', $result->useridproject], $result->useridproject, ''),
    'newactionroute' => \model\route::window('newaction',['actions/actionnew/index.php?idproject={0}&idtask={1}', $result->useridproject, 0], $result->useridproject, $lexi['prj009']),
    'actionsmaproute' => \model\route::window('actionsmap',['actions/actionsmap/index.php?idproject={0}', $result->useridproject, 0], $result->useridproject, $lexi['prj136']),
    'lbl_noteprojecttitle' => $lexi['prj100'],
    'link_noteproject' => $lexi['prj092'],
    'lbl_user' => $lexi['prj035'],
    'lbl_filtertitle' => $lexi['prj066'],
    'link_filter' => $lexi['prj077'],
    'lbl_setup' => $lexi['prj076'],
    'lbl_showmap' => $lexi['prj136'],
    'lbl_filterview' => $lexi['prj014'],
];
\model\route::render('project/admon/menuproject.twig', $data);
