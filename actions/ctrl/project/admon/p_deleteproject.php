<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

(new \model\project)->deleteproject(\model\env::session_idproject(), 'g3/*/statusproject.html');

require_once \model\route::script('style.php');
\model\route::refreshMaster(\model\env::session_idproject());
\model\route::close(\model\env::session_idproject()); // close all project windows

