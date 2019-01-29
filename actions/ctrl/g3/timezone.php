<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3session.php';

if (filter_input(INPUT_GET, 'time') !== null) {
    \model\env::setTimezone(filter_input(INPUT_GET, 'time'));
}
if (filter_input(INPUT_GET, 'lang') !== null) {
    \model\env::setLang(filter_input(INPUT_GET, 'lang'));
}
