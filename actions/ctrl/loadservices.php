<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

// reset modules
$project->modules = [];

//here load services for the start-up 

if (\file_exists(DIR_APP . '/ctrl/ext')) {
    $pathextservices = DIR_APP . '/ctrl/ext';
    $dirfiles = \scandir($pathextservices);

    foreach ($dirfiles as $file) {
        if ($file[0] !== '.') {
            $filename = \model\utils::format('{0}/{1}/loadservices.php', $pathextservices, $file);
            if (\is_file($filename)) {
                $appfilename = \model\utils::format('ext/{0}/loadservices.php', $file);
                require \model\route::script($appfilename);
            }
        }
    }
}
