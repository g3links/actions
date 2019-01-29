<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

// reset modules
$project->modules = [];

//here load services for the start-up 

$filename = \model\utils::format(DATA_PATH . 'config/loadservices.json');
if (\is_file($filename)) {
    $jsetups = file_get_contents($filename);
    $services = json_decode($jsetups);
    foreach ($services as $service) {
        $servicemoduletoload = \model\route::script($service->ctrl);
        if (isset($servicemoduletoload) && !empty($servicemoduletoload)) {
            require $servicemoduletoload;
        } else {
            echo '<p style="color: red;">' . $setup->ctrl . ', not found at: config/loadservices.json route script</p>';
        }
    }
}
