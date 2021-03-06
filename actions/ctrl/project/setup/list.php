<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$user_idaccess = \model\env::getUserAccessId(\model\env::session_idproject());

$lexiproject = \model\lexi::getall();
$t_shared = $lexiproject['prj003'];

//load custom apps setup:
if (\file_exists(DIR_APP . '/ctrl/ext')) {
    $pathextservices = DIR_APP . '/ctrl/ext';
    $dirfiles = \scandir($pathextservices);

    foreach ($dirfiles as $file) {
        if ($file[0] !== '.') {
            $filename = \model\utils::format('{0}/{1}/setup.php', $pathextservices, $file);
            if (\is_file($filename)) {
                $appfilename = \model\utils::format('ext/{0}/setup.php', $file);
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
    $module->approute = \model\route::window('projsetup', ['project/admon/index.php?idproject={0}', \model\env::session_idproject()], \model\env::session_idproject(), $lexiproject['prj058']);
    $module->imagesymbol = 'imgSetup';
    $module->moduleid = 'p01';
    $modules[] = $module;

    $module = new \stdClass();
    $module->approute = \model\route::window('projsetup', ['project/users/index.php?idproject={0}', \model\env::session_idproject()], \model\env::session_idproject(), $lexiproject['prj101']);
    $module->imagesymbol = 'imgUsersSetup';
    $module->moduleid = 'p02';
    $modules[] = $module;

    $module = new \stdClass();
    $module->approute = \model\route::window('projsetup', ['project/groups/index.php?idproject={0}', \model\env::session_idproject()], \model\env::session_idproject(), $lexiproject['prj103']);
    $module->imagesymbol = 'imgGroup';
    $module->moduleid = 'p04';
    $modules[] = $module;

    $module = new \stdClass();
    $module->approute = \model\route::window('projsetup', ['project/security/index.php?idproject={0}', \model\env::session_idproject()], \model\env::session_idproject(), $lexiproject['prj012']);
    $module->imagesymbol = 'imgSecurity';
    $module->moduleid = 'p03';
    $modules[] = $module;

    $data = [
        'lbl_title' => $lexiproject['prj058'],
        'lbl_shared' => $t_shared,
        'idproject' => \model\env::session_idproject(),
        'modules' => $modules,
    ];
    \model\route::render('project/setup/setup.twig', $data);
}
