<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$user_idaccess = \model\env::getUserAccessId(\model\env::session_idproject());

$lexiproject = \model\lexi::getall('g3/project');
$t_shared = $lexiproject['sys003'];

//load custom apps setup:
if (\file_exists(DIR_APP . '/ctrl/g3ext')) {
    $pathextservices = DIR_APP . '/ctrl/g3ext';
    $dirfiles = \scandir($pathextservices);

    foreach ($dirfiles as $file) {
        if ($file[0] !== '.') {
            $filename = \model\utils::format('{0}/{1}/setup.php', $pathextservices, $file);
            if (\is_file($filename)) {
                $appfilename = \model\utils::format('g3ext/{0}/setup.php', $file);
                require \model\route::script($appfilename);
            }
        }
    }
}

// g3 system setup
require \model\route::script('actions/setup.php');
if ($user_idaccess < 3) { //not for public access
//main setup
    $modules = [];

    $module = new \stdClass();
    $module->approute = \model\route::window('projsetup', ['project/admon/index.php?idproject={0}', \model\env::session_idproject()], \model\env::session_idproject(), $lexiproject['sys058']);
    $module->imagesymbol = 'imgSetup';
    $module->moduleid = 'p01';
    $modules[] = $module;

    $module = new \stdClass();
    $module->approute = \model\route::window('projsetup', ['project/users/index.php?idproject={0}', \model\env::session_idproject()], \model\env::session_idproject(), $lexiproject['sys101']);
    $module->imagesymbol = 'imgUsersSetup';
    $module->moduleid = 'p02';
    $modules[] = $module;

    $module = new \stdClass();
    $module->approute = \model\route::window('projsetup', ['project/groups/index.php?idproject={0}', \model\env::session_idproject()], \model\env::session_idproject(), $lexiproject['sys103']);
    $module->imagesymbol = 'imgGroup';
    $module->moduleid = 'p04';
    $modules[] = $module;

    $module = new \stdClass();
    $module->approute = \model\route::window('projsetup', ['project/security/index.php?idproject={0}', \model\env::session_idproject()], \model\env::session_idproject(), $lexiproject['sys012']);
    $module->imagesymbol = 'imgSecurity';
    $module->moduleid = 'p03';
    $modules[] = $module;

    $data = [
        'lbl_title' => $lexiproject['sys058'],
        'lbl_shared' => $t_shared,
        'idproject' => \model\env::session_idproject(),
        'modules' => $modules,
    ];
    \model\route::render('project/setup/setup.twig', $data);
}
