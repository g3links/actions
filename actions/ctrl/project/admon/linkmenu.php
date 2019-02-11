<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

if (\model\env::isUserAllow(\model\env::session_idproject(), \model\project::ROLE_SHAREDATA)) {
    $actionname = '';
    if (filter_input(INPUT_GET, 'action') !== null) 
        $actionname = filter_input(INPUT_GET, 'action');
    
    if (!empty($actionname)) {

        $lexi = \model\lexi::getall();
        $data = [
            'linksroute' => \model\route::form('project/links/m_links.php?idproject={0}&modulename={1}', \model\env::session_idproject(), $actionname),
            'linksownerroute' => \model\route::form('project/links/m_linksowner.php?idproject={0}&modulename={1}', \model\env::session_idproject(), $actionname),
            'lbl_linkowner' => $lexi['prj005'],
            'lbl_link' => $lexi['prj010'],
        ];
        \model\route::render('project/admon/linkmenu.twig', $data);
    }
}
