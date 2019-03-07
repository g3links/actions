<?php

$customdefinitions = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3definitions.php';
if (is_file($customdefinitions))
    die();

//validation
$infomessage = '';

$datapath = filter_input(INPUT_POST, 'datapath');
$pagetitle = filter_input(INPUT_POST, 'pagetitle');
$welcomepage = filter_input(INPUT_POST, 'welcomepage');
$mailer_host = filter_input(INPUT_POST, 'mailer_host');
$mailer_sender = filter_input(INPUT_POST, 'mailer_sender');
$mailer_sendfrom = filter_input(INPUT_POST, 'mailer_sendfrom');
$mailer_username = filter_input(INPUT_POST, 'mailer_username');
$mailer_password = filter_input(INPUT_POST, 'mailer_password');
$mailer_smtpsecure = filter_input(INPUT_POST, 'mailer_smtpsecure');
$mailer_port = filter_input(INPUT_POST, 'mailer_port');
$mailer_testemail = filter_input(INPUT_POST, 'mailer_testemail');
$emailerror_email = filter_input(INPUT_POST, 'emailerror_email');
$token_key = filter_input(INPUT_POST, 'token_key');
//$maxrecords_actions = filter_input(INPUT_POST, 'maxrecords_actions');
//$maxrecords_search = filter_input(INPUT_POST, 'maxrecords_search');
//$maxrecords_products = filter_input(INPUT_POST, 'maxrecords_products');
//$maxrecords_orders = filter_input(INPUT_POST, 'maxrecords_orders');
//$maxrecords_sensors = filter_input(INPUT_POST, 'maxrecords_sensors');

if (!is_dir($datapath)) {
    $infomessage = 'real data path not found';
}

if (empty($welcomepage) |
        empty($mailer_host) |
        empty($mailer_sender) |
        empty($mailer_sendfrom) |
        empty($mailer_username) |
        empty($mailer_password) |
        empty($mailer_smtpsecure) |
        empty($mailer_port) |
        empty($mailer_testemail) |
        empty($emailerror_email) |
        empty($token_key)
) {
    $infomessage = 'missing data';
}

if (!empty($infomessage)) {
    require filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3session.php';
    die();
}

// config file
$configdefinitions = "<?php \n"
        . "define('WEB_APP', '/[WEB_APP]'); \n"
        . "define('DATA_PATH', '[DATA_PATH]'); \n"
        . "define('PAGETITLE', '[PAGETITLE]'); \n"
        . "define('WELCOMEPAGE', '[WELCOMEPAGE]');";

$configdefinitions = \str_replace('[WEB_APP]', 'actions', $configdefinitions);
$configdefinitions = \str_replace('[DATA_PATH]', filter_input(INPUT_POST, 'datapath'), $configdefinitions);
$configdefinitions = \str_replace('[PAGETITLE]', filter_input(INPUT_POST, 'pagetitle'), $configdefinitions);
$configdefinitions = \str_replace('[WELCOMEPAGE]', filter_input(INPUT_POST, 'welcomepage'), $configdefinitions);


// settings
$g3result = new stdClass();

$g3result->db = new stdClass();
$g3result->db->provider = 'sqlite';
$g3result->db->dsn = 'db/g3core.db';
$g3result->db->username = '';
$g3result->db->password = '';

$g3result->mailer = new stdClass();
$g3result->mailer->host = filter_input(INPUT_POST, 'mailer_host');
$g3result->mailer->sender = filter_input(INPUT_POST, 'mailer_sender');
$g3result->mailer->sendfrom = filter_input(INPUT_POST, 'mailer_sendfrom');
$g3result->mailer->username = filter_input(INPUT_POST, 'mailer_username');
$g3result->mailer->password = filter_input(INPUT_POST, 'mailer_password');
$g3result->mailer->smtpsecure = filter_input(INPUT_POST, 'mailer_smtpsecure');
$g3result->mailer->port = filter_input(INPUT_POST, 'mailer_port');
$g3result->mailer->testemail = filter_input(INPUT_POST, 'mailer_testemail');

$g3result->emailerror = new stdClass();
$g3result->emailerror->email = filter_input(INPUT_POST, 'emailerror_email');

$g3result->api = new stdClass();
$g3result->api->url = 'api';

$g3result->token = new stdClass();
$g3result->token->key = filter_input(INPUT_POST, 'token_key');

$g3result->maxrecords = new stdClass();
$g3result->maxrecords->actions = '50';
$g3result->maxrecords->search = '50';
//    $g3result->maxrecords->products = '50';
//    $g3result->maxrecords->orders = '50';
//    $g3result->maxrecords->sensors = '50';
//

$g3filename = filter_input(INPUT_POST, 'datapath') . '/config/g3.json';
$g3jasondata = json_encode($g3result);

// setup g3actions settings
$g3actionsfilename = filter_input(INPUT_POST, 'datapath') . '/config/g3actions.json';
if (!is_file($g3actionsfilename)) {
    $g3actionsresult = new stdClass();

    $g3actionsresult->db = new stdClass();
    $g3actionsresult->db->provider = 'sqlite';
    $g3actionsresult->db->dsn = 'db/g3task{0}.db';
    $g3actionsresult->db->username = '';
    $g3actionsresult->db->password = '';

    $g3actionsresult->filelimits = new stdClass();
    $g3actionsresult->filelimits->attachedfile = '5';

    $g3actionsjasondata = json_encode($g3actionsresult);
}

try {
    if (isset($g3actionsjasondata))
        \file_put_contents($g3actionsfilename, $g3actionsjasondata);

    \file_put_contents($g3filename, $g3jasondata);
    \file_put_contents($customdefinitions, $configdefinitions);
} catch (Exception $ex) {
    echo $ex->getMessage();
    die();
}

die('<script>window.top.location.href = "/index.php";</script>');
