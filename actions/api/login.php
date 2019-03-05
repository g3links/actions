<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3session.php';

// http responses: https://developer.mozilla.org/en-US/docs/Web/HTTP/Status
// 
//***************************
// use RestClient (firefox) post http://localhost:8080/test/api/login.php
//Enter the following on the body.
//{"email":"info@g3links.com","password":"555"}
//***************************
//
// required headers
//header("Access-Control-Allow-Origin: http://localhost:8080/test/api");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 600"); // 10 Minutes (600/60 = 10 mins)
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// get posted data
$data = json_decode(file_get_contents("php://input"));

$token = (new \model\user)->getUserSessionToken($data->email, LOGINSRV, $data->password);
if ($token !== false) {
    http_response_code(200);

    echo json_encode(
            [
                "message" => "success",
                "token" => $token
            ]
    );
} else {
// login failed
    http_response_code(401);
    echo json_encode(
            [
                "message" => "Login failed."
            ]
    );
}
