<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

\model\utils::unsetCookie('selectedusers' . \model\env::session_idproject());
\model\utils::unsetCookie('selectedgroups' . \model\env::session_idproject());
