<?php

$customdefinitions = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3definitions.php';
if (is_file($customdefinitions))
    die();

// config file
$config = "<?php \n"
        . "define('WEB_APP', '/[WEB_APP]'); \n"
        . "define('DATA_PATH', '[DATA_PATH]'); \n"
        . "define('PAGETITLE', '[PAGETITLE]'); \n"
        . "define('WELCOMEPAGE', '[WELCOMEPAGE]');";

$config = \str_replace('[WEB_APP]', 'actions', $config); //filter_input(INPUT_POST, 'webapp')
$config = \str_replace('[DATA_PATH]', filter_input(INPUT_POST, 'datapath'), $config);
$config = \str_replace('[PAGETITLE]', filter_input(INPUT_POST, 'pagetitle'), $config);
$config = \str_replace('[WELCOMEPAGE]', filter_input(INPUT_POST, 'welcomepage'), $config);


// settings
$result = new stdClass();

$result->db = new stdClass();
$result->db->provider = 'sqlite'; //filter_input(INPUT_POST, 'db_provider');
$result->db->dsn = 'db/g3core.db'; //filter_input(INPUT_POST, 'db_dsn');
$result->db->username = ''; //filter_input(INPUT_POST, 'db_username');
$result->db->password = ''; //filter_input(INPUT_POST, 'db_password');

$result->mailer = new stdClass();
$result->mailer->host = filter_input(INPUT_POST, 'mailer_host');
$result->mailer->sender = filter_input(INPUT_POST, 'mailer_sender');
$result->mailer->sendfrom = filter_input(INPUT_POST, 'mailer_sendfrom');
$result->mailer->username = filter_input(INPUT_POST, 'mailer_username');
$result->mailer->password = filter_input(INPUT_POST, 'mailer_password');
$result->mailer->smtpsecure = filter_input(INPUT_POST, 'mailer_smtpsecure');
$result->mailer->port = filter_input(INPUT_POST, 'mailer_port');
$result->mailer->testemail = filter_input(INPUT_POST, 'mailer_testemail');

$result->emailerror = new stdClass();
$result->emailerror->email = filter_input(INPUT_POST, 'emailerror_email');

$result->api = new stdClass();
$result->api->url = 'api.1'; //filter_input(INPUT_POST, 'api_url');

$result->token = new stdClass();
$result->token->key = filter_input(INPUT_POST, 'token_key');

$result->maxrecords = new stdClass();
$result->maxrecords->actions = '50'; //filter_input(INPUT_POST, 'maxrecords_actions');
$result->maxrecords->search = '50'; //filter_input(INPUT_POST, 'maxrecords_search');
//    $result->maxrecords->products = '50'; //filter_input(INPUT_POST, 'maxrecords_products');
//    $result->maxrecords->orders = '50'; //filter_input(INPUT_POST, 'maxrecords_orders');
//    $result->maxrecords->sensors = '50'; //filter_input(INPUT_POST, 'maxrecords_sensors');
//

$filename = filter_input(INPUT_POST, 'datapath') . '/config/g3.json';
$jasondata = json_encode($result);

try {
    \file_put_contents($filename, $jasondata);
    \file_put_contents($customdefinitions, $config);
} catch (Exception $ex) {
    echo $ex->getMessage();
    die();
}

die('<script>window.top.location.href = "/index.php";</script>');
