<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3session.php';

//replace lenguage by user request
if (filter_input(INPUT_GET, 'lang') !== null)
    \model\env::setLang(filter_input(INPUT_GET, 'lang'));

if (filter_input(INPUT_GET, 'tokenauth') !== null)
    $token = filter_input(INPUT_GET, 'tokenauth');

if (!isset($token))
    \model\message::severe('sys004', \model\lexi::get('', 'msg004'));

try {
    $activated = (new \model\login)->authUserToken($token);
    if (!$activated) {
        \model\message::severe('sys017', \model\lexi::get('', 'sys031'), 'auth', true);
    }
} catch (\Firebase\JWT\ExpiredException $vexc) {
    \model\message::severe('sys017', \model\lexi::get('', 'msg017', $vexc->getMessage()), 'auth', true);
} catch (Exception $exc) {
    \model\message::severe('sys017', \model\lexi::get('', 'msg017', $exc->getMessage()), 'auth', true);
}

require \model\route::script('restart.php');
