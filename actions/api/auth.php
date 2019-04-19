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

if ($coderesponse === 200) {
    $success = (new \model\login())->authUserToken($data->tokenauth);
    if ($success !== true) {
        $coderesponse = 501;
        $message = $success;
    }
}

http_response_code($coderesponse);
echo json_encode(
        [
            "message" => $message,
        ]
);
