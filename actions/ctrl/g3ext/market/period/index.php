<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

if (\model\env::isUserAllow(\model\env::session_idproject(), \model\action::ROLE_PERIODS)) {
    $module = \model\env::CONFIG_ACTIONS;
    $modulename = \model\env::MODULE_PERIOD;
    require \model\route::script('project/links/projectservices.php');
}

require_once \model\route::script('style.php');
$data = [
    'loaddataroute' => \model\route::form('g3ext/market/period/loaddata.php?idproject={0}', \model\env::session_idproject()),
];
\model\route::render('g3ext/market/period/index.twig', $data);
