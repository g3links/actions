<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3session.php';

//Enter the following on the body: {"tokenauth":""} 
// or: {"lang":"en", "tokenauth":""} lang = message language

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 600"); // 10 Minutes (600/60 = 10 mins)
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// get posted data
$data = json_decode(file_get_contents("php://input"));

$coderesponse = 200;
$message = "";

if (isset($data->lang))
    \model\env::setLang($data->lang);

// login failed
if (!isset($data->tokenauth)) {
    $coderesponse = 401;
    $message = \model\lexi::get('', 'msg004');
}

if (isset($data->tokenauth)) {
    try {
        (new \model\login)->deleteaccount($data->tokenauth);
    } catch (\Firebase\JWT\ExpiredException $vexc) {
        $coderesponse = 501;
        $message = \model\lexi::get('', 'msg017', $vexc->getMessage());
    } catch (Exception $exc) {
        $coderesponse = 501;
        $message = \model\lexi::get('', 'msg017', $exc->getMessage());
    }
}

http_response_code($coderesponse);
echo json_encode(
        [
            "message" => $message,
        ]
);
