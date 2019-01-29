<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

if (\model\env::session_idproject() === 0) {
   require \model\route::script('restart.php');
}

(new \model\project)->joinproject(\model\env::session_idproject());

require_once \model\route::script('style.php');

\model\route::close(\model\env::session_idproject(), 'tool');
\model\route::refreshMaster(\model\env::session_idproject());
