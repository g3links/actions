<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$filename = '';
if (filter_input(INPUT_GET,'filename') !== null) {
    $filename = filter_input(INPUT_GET,'filename');
}

$filenamepath = \model\utils::format('{0}/attach/{1}/{2}/{3}', DATA_PATH , \model\env::session_idproject(), \model\env::session_idtaskselected() ,$filename);
\model\utils::readAttachedFile($filenamepath);
