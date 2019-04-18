<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3session.php';

// info http responses: https://developer.mozilla.org/en-US/docs/Web/HTTP/Status
// test using RestClient (firefox) post http://localhost:8080/test/api/login.php
//Enter the following on the body: {"email":"info@g3links.com","password":"555"}
//***************************
// required headers
//header("Access-Control-Allow-Origin: http://localhost:8080/test/api");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 600"); // 10 Minutes (600/60 = 10 mins)
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// get posted data
$data = json_decode(file_get_contents("php://input"));

$coderesponse = 200;
$token = '';
$message = "";

$result = (new \model\login)->getApiSessionToken($data->email ?? '', $data->password ?? '');
if (isset($result)) {
    if (!empty($result->message ?? '')) {
        $coderesponse = 401;
        $token = 'failed';
        $message = $result->message;
    } else {
        $token = $result->token;
    }
}

// login failed
if (empty($token)) {
    $coderesponse = 401;
    $message = \model\lexi::get('', 'sys031');
}

http_response_code($coderesponse);
echo json_encode(
        [
            "message" => $message,
            "token" => $token
        ]
);
