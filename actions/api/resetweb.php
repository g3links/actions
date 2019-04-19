<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3session.php';

//replace lenguage by user request
if (filter_input(INPUT_GET, 'lang') !== null)
    \model\env::setLang(filter_input(INPUT_GET, 'lang'));

if (filter_input(INPUT_GET, 'tokenauth') !== null)
    $token = filter_input(INPUT_GET, 'tokenauth');

if (!isset($token))
    \model\message::severe('sys004', \model\lexi::get('', 'msg004'));

$success = (new \model\login)->resetUserPassword($token);
if ($success !== true) {
    \model\message::severe('sys017', $success, 'reset', true);
}

require \model\route::script('restart.php');
