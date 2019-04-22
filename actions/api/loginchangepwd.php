<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3session.php';

//Enter the following on the body: {"emaillogon":"info@g3links.com","pwdlogon":"555","pwdchg":"555","pwdchg1":"555"}

header("Access-Control-Allow-Origin: http://localhost:8080/test/api");
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
if (empty($data->emaillogon ?? '')) {
    $coderesponse = 401;
    $message = $data->emaillogon . ': ' . \model\lexi::get('', 'sys043');
}
if (!filter_var($data->emaillogon, FILTER_VALIDATE_EMAIL)) {
    $coderesponse = 401;
    $message = $data->emaillogon . ': ' . \model\lexi::get('', 'sys043');
}

if (empty($data->pwdlogon ?? '') || empty($data->pwdchg ?? '') || empty($data->pwdchg1 ?? '') || $data->pwdchg !== $data->pwdchg1) {
    $coderesponse = 401;
    $message = \model\lexi::get('', 'sys030');
}

$usermodel = new \model\login();

if ($coderesponse === 200) {
    if ($usermodel->changeUserPassword($data->emaillogon, $data->pwdlogon, $data->pwdchg, $data->pwdchg1) === false) {
        $coderesponse = 401;
        $message = \model\lexi::get('', 'sys031');
    }
}

http_response_code($coderesponse);
echo json_encode(
        [
            "message" => $message,
        ]
);
