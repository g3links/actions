<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

(new \model\action(\model\env::session_src()))->uploadfile(\model\env::session_idtaskselected());

require_once \model\route::script('style.php');
\model\route::refresh('actions',['actions/index.php?idproject={0}', \model\env::session_idproject()], \model\env::session_idproject());
\model\route::refresh('action',['actions/action/index.php?idproject={0}&idtask={1}', \model\env::session_idproject(), \model\env::session_idtaskselected()], \model\env::session_idproject());
