<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$modulename = '';
if (filter_input(INPUT_GET, 'modulename') !== null) 
    $modulename = filter_input(INPUT_GET, 'modulename');

$projlist = (new \model\action(\model\env::session_src()))->getLinkedModuleData($modulename);
$isrole = \model\env::isUserAllow(\model\env::session_idproject(), \model\project::ROLE_SHAREDATA);

$lexi = \model\lexi::getall();
$data = [
    'projlist' => $projlist,
    'lbl_title' => $lexi['prj002'],
    'th_col1' => $lexi['prj001'],
    'th_col2' => $lexi['prj109'],
    'lbl_notfound' => $lexi['prj044'],
    'lbl_submit' => $lexi['prj108'],
];
if($isrole) {
    $data += [
        'linktomoduleroute' => \model\route::form('project/links/linktomodule.php?idproject={0}&modulename={1}', \model\env::session_idproject(), $modulename),
    ];
}
\model\route::render('project/links/m_links.twig', $data);
