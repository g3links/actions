<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$result = (new \model\project)->getMenuProject(\model\env::session_idproject());

if (isset($result->assignedusernames)) {
    $data = [
        'assignedusernames' => $result->assignedusernames,
    ];
    \model\route::render('actions/actions/ctrl_filteredusers.twig', $data);
}

$lexi = \model\lexi::getall('g3/project');
$data = [
    'allownewaction' => $result->allownewaction,
    'allowsetup' => $result->allowsetup,
    'allowmessg' => $result->allowmessg,
    'allowfilter' => $result->allowfilter,
    'initmenuroute' => \model\route::form('project/admon/m_projectnote.php?idproject={0}', $result->useridproject),
    'filterusersroute' => \model\route::form('project/users/m_filteruser.php?idproject={0}', \model\env::session_idproject()),
    'filterviewsroute' => \model\route::form('actions/actions/m_filterview.php?idproject={0}', \model\env::session_idproject()),
    'initsetuproute' => \model\route::window('projadmon',['project/setup/index.php?idproject={0}', $result->useridproject], $result->useridproject, ''),
    'newactionroute' => \model\route::window('newaction',['actions/actionnew/index.php?idproject={0}&idtask={1}', $result->useridproject, 0], $result->useridproject, $lexi['sys009']),
    'actionsmaproute' => \model\route::window('actionsmap',['actions/actionsmap/index.php?idproject={0}', $result->useridproject, 0], $result->useridproject, $lexi['sys136']),
    'lbl_noteprojecttitle' => $lexi['sys100'],
    'link_noteproject' => $lexi['sys092'],
    'lbl_user' => $lexi['sys035'],
    'lbl_filtertitle' => $lexi['sys066'],
    'link_filter' => $lexi['sys077'],
    'lbl_setup' => $lexi['sys076'],
    'lbl_showmap' => $lexi['sys136'],
    'lbl_filterview' => $lexi['sys014'],
];
\model\route::render('project/admon/menuproject.twig', $data);
