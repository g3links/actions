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
//    "fbapp": {
//        "oauth_link": "https://www.facebook.com/v3.0/dialog/oauth?client_id=(app_id)&redirect_uri=(loginUrl)&state=(email)",
//        "code_link": "https://graph.facebook.com/v3.0/oauth/access_token?client_id={app-id}&redirect_uri={redirect-uri}&client_secret={app-secret}&code={code-parameter}",
//        "melink": "https://graph.facebook.com/me?fields=id,name&access_token=(access_token)&appsecret_proof=(appsecret_proof)",
//        "app_id": "1825130917804433",
//        "app_secret": "f5cf59ecd053d1abbf896b086e535455",
//        "default_graph_version": "v2.12",
//        "loginUrl": "https://g3links.com/actions/registerlogin/facebook-callback.php"
//    },
//    "goapp": {
//        "oauth_link": "https://accounts.google.com/o/oauth2/auth?response_type=id_token&client_id=(app_id)&redirect_uri=(loginUrl)&scope=openid&response_mode=form_post&state=(email)",
//        "app_id": "646753156767-udb89uen47qt324e1ofm477j8idopjhd.apps.googleusercontent.com",
//        "app_secret": "kd7doqG0yHP_uhqAYRHSMrW5",
//        "default_graph_version": "",
//        "loginUrl": "https://g3links.com/actions/registerlogin/google-callback.php"
//    },
//    "msapp": {
//        "oauth_link": "https://login.microsoftonline.com/common/oauth2/v2.0/authorize?client_id=(app_id)&response_type=id_token&redirect_uri=(loginUrl)&scope=openid&response_mode=form_post&nonce=678910&state=(email)",
//        "app_id": "07222bb5-f43f-45d6-bc54-658a416d5a07",
//        "app_secret": "aMRWW52exdcjgPNk73DGax8",
//        "default_graph_version": "",
//        "loginUrl": "https://g3links.com/actions/registerlogin/ms-callback.php",
//        "logoutUrl": "https://g3links.com/actions/registerlogin/ms-logout.php"
//    }

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
