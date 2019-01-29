<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

//$servicetype
// fixed: by default load system data
// dynamic: service need to be activate by demand (default system or custom data)
$activeservice = 'inactive'; //disabled
if (!isset($servicetype)) {
    $servicetype = 'dynamic';
}
if ($servicetype === 'fixed') {
    $activeservice = 'active';
}
$template = '';

$service = (new \model\project)->getService(\model\env::session_idproject(), $modulename);
if (isset($service)) {
    $activeservice = 'active';
    // if modules are not identical, allow custom
    if (\strtolower(\trim($service->template)) !== \strtolower(\trim($modulename . '.json'))) {
        $activeservice = 'custom';
        $template = $service->template;
    }
}

$data = [
    'activeservice' => $activeservice,
    'template' => $template,
    'servicetype' => $servicetype,
    'updateprojectservicesroute' => \model\route::form('project/links/p_projectservices.php?idproject={0}&module={1}&modulename={2}&type={3}', \model\env::session_idproject(), $module, $modulename, $servicetype),
    'lbl_disabled' => \model\lexi::get('g3', 'sys071'),
    'lbl_enabled' => \model\lexi::get('g3', 'sys072'),
    'lbl_custom' => \model\lexi::get('g3', 'sys074'),
    'lbl_submit' => \model\lexi::get('g3', 'sys073'),
];
\model\route::render('project/links/projectservices.twig', $data);
