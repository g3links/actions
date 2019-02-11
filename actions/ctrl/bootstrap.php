<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3session.php';

if (\model\env::isauthorized()) {
        //hide welcome page
    \model\route::hide('welcomepage');

//    require_once \model\route::script('style.php');
    \model\route::open('start', 'start/index.php', \model\env::getUserIdProject(), \model\lexi::get('', 'sys005'), \model\env::getUserName());
//\model\route::open('actions', ['actions/index.php?idproject={0}', \model\env::getUserIdProject()], \model\env::getUserIdProject(), \model\lexi::get('', 'sys006'), \model\env::getUserName());
    \model\route::open('actions', ['actions/index.php', ['idproject' => \model\env::getUserIdProject()]], \model\env::getUserIdProject(), \model\lexi::get('', 'sys006'), \model\env::getUserName());
}