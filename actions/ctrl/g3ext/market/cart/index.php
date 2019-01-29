<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$sessioncart = (new \model\ext\market\market(\model\env::session_src()))->getSessionCart();

require_once \model\route::script('style.php');
$data = [
    'allownew' => $sessioncart->allowedit,
    'defaultgate' => $sessioncart->defaultgate,
    'listroute' => \model\route::form('g3ext/market/cart/list.php?idproject={0}&idgate=[idgate]&navpage=[navpage]', \model\env::session_idproject()),
    'productsroute' => \model\route::window('projsetup', ['g3ext/market/product/index.php?idproject={0}', \model\env::session_idproject()], \model\env::session_idproject(), \model\lexi::get('g3ext/market','sys005')),
];
if($sessioncart->allowedit) {
    $data += [
        'lbl_newtitle' => \model\lexi::get('g3ext/market','sys001'),
    ];
}
\model\route::render('g3ext/market/cart/index.twig', $data);
