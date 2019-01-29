<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$sessionorder = (new \model\ext\market\market(\model\env::session_src()))->getSessionOrder();

require_once \model\route::script('style.php');
$data = [
    'defaultgate' => $sessionorder->defaultgate,
//    'listroute' => \model\route::form('g3ext/market/order/list.php?idproject={0}&idgate=[idgate]&navpage=[navpage]&sort=[sorttype]&sortdirection=[sortdirection]', \model\env::session_idproject()),
    'listroute' => \model\route::form('g3ext/market/order/list.php?idproject={0}&idgate=[idgate]&navpage=[navpage]', \model\env::session_idproject()),
    'productsroute' => \model\route::window('projsetup', ['g3ext/market/product/index.php?idproject={0}', \model\env::session_idproject()], \model\env::session_idproject(), \model\lexi::get('g3ext/market','sys005')),
];
\model\route::render('g3ext/market/order/index.twig', $data);
