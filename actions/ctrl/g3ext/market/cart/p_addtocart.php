<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$idcart = 0;
if (filter_input(INPUT_GET, 'idcart') !== null)
    $idcart = (int) filter_input(INPUT_GET, 'idcart');

$idproduct = 0;
if (filter_input(INPUT_GET, 'id') !== null)
    $idproduct = (int) filter_input(INPUT_GET, 'id');

$qty = 0;
if (filter_input(INPUT_GET, 'qty') !== null)
    $qty = (int) filter_input(INPUT_GET, 'qty');

//create cart when idcart = 0, return new idcart to client for futher product register  
if ($idcart === 0) {
    $cart = new stdClass();
    $cart->description = \model\env::getUserName();
    
    $idcart = (new \model\ext\market\market(\model\env::session_src()))->setInsertCart($cart);
}

if ($idcart !== false) {
    //return error
    $cartdetail = new stdClass();
    $cartdetail->idcart = $idcart;
    $cartdetail->idcartdetail = 0;
    $cartdetail->idproduct = $idproduct;
    $cartdetail->orderquantity = $qty;

    $result = (new \model\ext\market\market(\model\env::session_src()))->setInsertCartDetail($cartdetail);
    $result->lbl_title = \model\lexi::get('g3ext/market', 'sys024');
    $result->lbl_qty = \model\lexi::get('g3ext/market', 'sys007');
} else {
    $result = new stdClass();
    $result->msgerror = \model\lexi::get('g3ext/market', 'sys025');
}

echo json_encode($result);
//echo \model\utils::format('<div>{0} {1} - {2}, {3}: {4}</div>', \model\lexi::get('g3ext/market', 'sys024'), $result->idproduct, $result->name, \model\lexi::get('g3ext/market', 'sys007'), $result->qty);
