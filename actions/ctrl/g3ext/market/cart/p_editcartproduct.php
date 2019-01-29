<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$idcartdetail = 0;
if (filter_input(INPUT_GET, 'id') !== null)
    $idcartdetail = (int) filter_input(INPUT_GET, 'id');

$cartdetail = (object) [
            'idcartdetail' => $idcartdetail,
            'orderquantity' => 0
];

if (filter_input(INPUT_POST, 'save') !== null) {
    If (filter_input(INPUT_POST, 'orderquantity') !== null)
        $cartdetail->orderquantity = (int) filter_input(INPUT_POST, 'orderquantity');

    (new \model\ext\market\market(\model\env::session_src()))->setUpdateCartDetail($cartdetail);
}

if (filter_input(INPUT_POST, 'delete') !== null) {
    (new \model\ext\market\market(\model\env::session_src()))->deleteCartDetail($cartdetail);
}

require \model\route::script('g3ext/market/cart/index.php?idproject={0}', \model\env::session_idproject());
