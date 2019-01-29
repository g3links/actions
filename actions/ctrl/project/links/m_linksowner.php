<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$modulename = '';
if (filter_input(INPUT_GET, 'modulename') !== null) 
    $modulename = filter_input(INPUT_GET, 'modulename');

$modelshare = new \model\action(\model\env::session_src());

$projlist = $modelshare->getLinkedOwner($modulename);
$issharedmodule = $modelshare->isDataShared($modulename);
$isrole = \model\env::isUserAllow(\model\env::session_idproject(), \model\project::ROLE_SHAREDATA);

$lexi = \model\lexi::getall('g3/project');
$data = [
    'projlist' => $projlist,
    'issharedmodule' => $issharedmodule,
    'lbl_modulenamemssg' => $lexi['sys005'],
    'th_col1' => $lexi['sys001'],
    'th_col2' => $lexi['sys109'],
    'th_col3' => $lexi['sys023'],
    'lbl_notfound' => $lexi['sys044'],
    'lbl_submit' => $lexi['sys108'],
];
if($isrole) {
    $data += [
        'linkmoduleownerroute' => \model\route::form('project/links/linkmoduleowner.php?idproject={0}&modulename={1}', \model\env::session_idproject(), $modulename),
    ];
}
\model\route::render('project/links/m_linksowner.twig', $data);
