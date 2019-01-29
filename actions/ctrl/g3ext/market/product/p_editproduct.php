<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$idproduct = 0;
if (filter_input(INPUT_GET, 'id') !== null) 
    $idproduct = (int) filter_input(INPUT_GET, 'id');

$product = new \stdClass();
if (filter_input(INPUT_POST, 'save') !== null) {

    $product->keycode = '';
    if (filter_input(INPUT_POST, 'keycode') !== null) 
        $product->keycode = filter_input(INPUT_POST, 'keycode');
    
    $product->name = '';
    if (filter_input(INPUT_POST, 'name') !== null) 
        $product->name = filter_input(INPUT_POST, 'name');
    
    $product->inactive = false;
    if (filter_input(INPUT_POST, 'inactive') !== null) 
        $product->inactive = true;
    
    $product->productdescription = '';
    if (filter_input(INPUT_POST, 'productdescription') !== null) 
        $product->productdescription = filter_input(INPUT_POST, 'productdescription');
    
    $product->productmanifest = '';
    if (filter_input(INPUT_POST, 'productmanifest') !== null) 
        $product->productmanifest = filter_input(INPUT_POST, 'productmanifest');
    
    $product->issales = false;
    if (filter_input(INPUT_POST, 'issales') !== null) 
        $product->issales = true;
    
    $product->ispurchase = false;
    if (filter_input(INPUT_POST, 'ispurchase') !== null) 
        $product->ispurchase = true;
    
    $product->idcurrency = '';
    if (filter_input(INPUT_POST, 'idcurrency') !== null) 
        $product->idcurrency = filter_input(INPUT_POST, 'idcurrency');
    
    $product->saleprice = 0;
    if (filter_input(INPUT_POST, 'saleprice') !== null) 
        $product->saleprice = (float)filter_input(INPUT_POST, 'saleprice');

    $product->tags = '';
    if (filter_input(INPUT_POST, 'metatags') !== null) 
        $product->tags = filter_input(INPUT_POST, 'metatags');
    
    (new \model\ext\market\market(\model\env::session_src()))->setProduct($idproduct, $product);
}

if (filter_input(INPUT_POST, 'delete') !== null) {
    (new \model\ext\market\market(\model\env::session_src()))->deleteProduct($idproduct);
}

require \model\route::script('g3ext/market/product/index.php?idproject={0}', \model\env::session_idproject());
