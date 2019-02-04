<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3session.php';

//replace lenguage by user request
if (filter_input(INPUT_GET, 'lang') !== null) {
    \model\env::setLang(filter_input(INPUT_GET, 'lang'));
}
$token = null;
if (filter_input(INPUT_GET, 'tokenauth') !== null) {
    $token = filter_input(INPUT_GET, 'tokenauth');
}

if (!isset($token))
    \model\message::severe('sys004', \model\lexi::get('', 'sys004'));

try {
    $jsondecoded = \Firebase\JWT\JWT::decode($token, \model\env::getKey(), ['HS256']);
    \model\env::setUser($jsondecoded->iduser);
    (new \model\user)->deleteaccount($jsondecoded->iduser);
} catch (\Firebase\JWT\ExpiredException $vexc) {
    \model\message::severe('sys017', \model\lexi::get('', 'sys017', $vexc->getMessage()), 'close', true);
} catch (Exception $exc) {
    \model\message::severe('sys017',\model\lexi::get('','sys017',$exc->getMessage()), 'close', true);
}

require \model\route::script('logout.php');
