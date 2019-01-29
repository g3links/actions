<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$modulename = '';
if (filter_input(INPUT_GET, 'modulename') !== null)
    $modulename = (string) filter_input(INPUT_GET, 'modulename');

$servicetype = '';
if (filter_input(INPUT_GET, 'type') !== null)
    $servicetype = (string) filter_input(INPUT_GET, 'type');

$module = '';
if (filter_input(INPUT_GET, 'module') !== null)
    $module = (string) filter_input(INPUT_GET, 'module');

$serviceto = 'inactive';
if (filter_input(INPUT_POST, 'service') !== null) {
    $serviceto = (string) filter_input(INPUT_POST, 'service');
}

$modelcore = new \model\project();
if ($serviceto === 'inactive') {
    $modelcore->deleteService(\model\env::session_idproject(), $modulename);
}

if ($serviceto === 'active') {
    // data must exist        
    $filelocation = \model\route::render(\model\utils::format('{0}config/{1}/*/{2}.json', DATA_PATH, $module, $modulename));
    // check for project custom confg
    if (\model\env::session_idproject() > 0) {
        $filelocationback = \model\route::render(\model\utils::format('{0}attach/{1}/config/{2}/', DATA_PATH, \model\env::session_idproject(), $module));
        if (file_exists($filelocationback))
            $filelocation = \model\route::render(\model\utils::format('{0}attach/{1}/config/{2}/*/{2}.json', DATA_PATH, \model\env::session_idproject(), $module), $modulename);
    }

    if (!\is_file($filelocation)) {
        echo '<div style="color: red;">' . \model\lexi::get('', 'sys002') . '</div>';
        die();
//            (new \model\message)->render($mssgerror);
    }

    if ($servicetype === 'fixed') {
        $modelcore->deleteService(\model\env::session_idproject(), $modulename);
    } else {
        $modelcore->updateService(\model\env::session_idproject(), $modulename, $modulename . '.json');
    }
}

if ($serviceto === 'custom') {
    $template = '';
    if (filter_input(INPUT_POST, 'template') !== null) {
        $template = (string) filter_input(INPUT_POST, 'template');

        // data must exist
        $filelocation = \model\route::render(\model\utils::format('{0}config/{1}/*/{2}', DATA_PATH, $module, $template));
        if (!\is_file($filelocation)) {
            echo '<div style="color: red;">' . \model\lexi::get('', 'sys002') . '</div>';
            die();
//            (new \model\message)->render($mssgerror);
        }

        $modelcore->updateService(\model\env::session_idproject(), $modulename, $template);
    }
}
