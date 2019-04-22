<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3session.php';

//Enter the following on the body: {"username": "gus","email":"info@g3links.com","pwd":"555","pwd1":"555"}

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 600"); // 10 Minutes (600/60 = 10 mins)
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// get posted data
$data = json_decode(file_get_contents("php://input"));

$coderesponse = 200;
$message = "";
$securesource = 'NU';

// validate data
//*******************************
if (empty($data->email ?? '') || empty($data->username ?? '')) {
    $coderesponse = 401;
    $message = $data->email . ': ' . \model\lexi::get('', 'sys043');
}
if (!filter_var($data->email ?? '', FILTER_VALIDATE_EMAIL)) {
    $coderesponse = 401;
    $message = $data->email . ': ' . \model\lexi::get('', 'sys043');
}

if (empty($data->pwd ?? '') || empty($data->pwd1 ?? '') || $data->pwd !== $data->pwd1) {
    $coderesponse = 401;
    $message = \model\lexi::get('', 'sys030');
}

$usermodel = new \model\login();

// confirm previous registrations
if ($coderesponse === 200) {
    $statusreg = $usermodel->getStatusRegistration($data->email, $data->pwd);
    if (!empty($statusreg)) {
        $coderesponse = 401;
        $message = $statusreg;
    } else {
// insert user
        $sucess = $usermodel->insertUser('g3/*/regauthorization.html', $data->email, $data->username, $data->pwd);
        if (!$sucess) {
            $coderesponse = 401;
            $message = $data->email . ': cannot be created';
        }
    }
}

// send welcome email
if ($coderesponse === 200) {
    $filename = \model\route::render('g3/*/regwelcome.html');

    $emailstring = [];
    $lines = file($filename);
    foreach ($lines as $line) {
        $line = str_replace('[membername]', $data->username, $line);
        $line = str_replace('[email]', $data->email, $line);
        $emailstring[] = $line;
    }

    \model\env::sendMail($data->username, $data->email, \model\lexi::get('', 'sys045'), $emailstring);
}

http_response_code($coderesponse);
echo json_encode(
        [
            "message" => $message,
            "securesource" => $securesource,
        ]
);
