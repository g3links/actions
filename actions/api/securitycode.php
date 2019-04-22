<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3session.php';

//Enter the following on the body: {"email":"info@g3links.com", "securesource": "??", "securekey": "########", "pwdnew" : ""}

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 600"); // 10 Minutes (600/60 = 10 mins)
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// get posted data
$data = json_decode(file_get_contents("php://input"));

$coderesponse = 200;
$message = "";

// validate data
//*******************************
if (empty($data->email ?? '')) {
    $coderesponse = 401;
    $message = $data->email . ': ' . \model\lexi::get('', 'sys043');
}
if (!filter_var($data->email ?? '', FILTER_VALIDATE_EMAIL)) {
    $coderesponse = 401;
    $message = $data->email . ': ' . \model\lexi::get('', 'sys043');
}

if (empty($data->securesource ?? '') || empty($data->securekey ?? '')) {
    $coderesponse = 401;
    $message = \model\lexi::get('', 'sys077');
}

//// confirm login changes
if ($coderesponse === 200) {
    // new user
    if ($data->securesource === "NU") {
        $success = (new \model\login())->validateNewUserSecurityToken($data->email, $data->securekey);
        if ($success !== true) {
            $coderesponse = 401;
            $message = $success;
        }
    }
    // change password
    if ($data->securesource === "RP") {
        $success = (new \model\login())->validateResetUserPassword($data->email, $data->securekey, $data->pwdnew);
        if ($success !== true) {
            $coderesponse = 401;
            $message = $success;
        }
    }
}

http_response_code($coderesponse);
echo json_encode(
        [
            "message" => $message,
        ]
);
