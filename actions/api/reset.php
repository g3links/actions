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
    \model\message::severe('sys004', \model\lexi::get('', 'msg004'));

try {
    $jsondecoded = \Firebase\JWT\JWT::decode($token, \model\env::getKey(), ['HS256']);
    \model\env::setUser($jsondecoded->iduser);
    (new \model\user)->resetUserPassword($jsondecoded->iduser, $jsondecoded->provider);
} catch (\Firebase\JWT\ExpiredException $vexc) {
    \model\message::severe('sys017', \model\lexi::get('', 'msg017', $vexc->getMessage()), 'reset', true);
} catch (Exception $exc) {
    \model\message::severe('sys017',\model\lexi::get('','msg017',$exc->getMessage()), 'reset', true);
}

require \model\route::script('logout.php');
