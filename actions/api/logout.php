<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3session.php';

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 600"); // 10 Minutes (600/60 = 10 mins)
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// get posted data
//$data = json_decode(file_get_contents("php://input"));
\model\utils::unsetCookie('g3links');

$coderesponse = 200;
$message = "";

http_response_code($coderesponse);
echo json_encode(
        [
            "message" => $message,
        ]
);
