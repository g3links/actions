<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

require_once \model\route::script('style.php');
$data = [
    'viewlistnotesroute' => \model\route::form('actions/note/listnotes.php'),    
    'noteformroute' => \model\route::form('actions/note/m_usernote.php?idproject=[idproject]&idnote=[idnote]'),
];
\model\route::render('actions/note/index.twig', $data);
