<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

if (\model\env::isUserAllow(\model\env::session_idproject(), \model\action::ROLE_ACTIONCATEGORY)) {
    $servicetype = 'fixed';
    $module = \model\env::CONFIG_ACTIONS;
    $modulename = \model\env::MODULE_GATE;
    require \model\route::script('project/links/projectservices.php');
}

require_once \model\route::script('style.php');
$data = [
    'loaddataroute' => \model\route::form('actions/gate/loaddata.php?idproject={0}', \model\env::session_idproject()),
];
\model\route::render('actions/gate/index.twig', $data);
