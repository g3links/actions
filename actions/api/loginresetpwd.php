<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3session.php';

//Enter the following on the body: {"emailreset":"info@g3links.com"}

header("Access-Control-Allow-Origin: http://localhost:8080/test/api");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 600"); // 10 Minutes (600/60 = 10 mins)
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// get posted data
$data = json_decode(file_get_contents("php://input"));

$coderesponse = 200;
$message = "";

if (empty($data->emailreset ?? '')) {
    $coderesponse = 401;
    $message = ($data->emailreset ?? '') . ': ' . \model\lexi::get('', 'sys043');
}

if (empty($data->pwdreset ?? '') || empty($data->pwdreset1 ?? '') || $data->pwdreset !== $data->pwdreset1) {
    $coderesponse = 401;
    $message = \model\lexi::get('', 'sys030');
}

if ($coderesponse === 200) {
    if ((new \model\login)->authresetpassword('g3/*/resetpassword.html', $data->emailreset, $data->pwdreset) === false) {
        $coderesponse = 401;
        $message = $data->emailreset . ': ' . \model\lexi::get('', 'sys031');
    }
}

http_response_code($coderesponse);
echo json_encode(
        [
            "message" => $message,
        ]
);
