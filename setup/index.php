<?php

if (!isset($customdefinitions) || is_file($customdefinitions))
    die();

$data = [
    'datapath' => $datapath ?? '',
    'pagetitle' => $pagetitle ?? 'G3 Links Actions',
    'welcomepage' => $welcomepage ?? 'https://g3links.com/wp',
    'mailer_host' => $mailer_host ?? '', //'',
    'mailer_sender' => $mailer_sender ?? '', //'', 
    'mailer_sendfrom' => $mailer_sendfrom ?? '', //'', 
    'mailer_username' => $mailer_username ?? '',
    'mailer_password' => $mailer_password ?? '',
    'mailer_smtpsecure' => $mailer_smtpsecure ?? 'ssl',
    'mailer_port' => $mailer_port ?? '465',
    'mailer_testemail' => $mailer_testemail ?? '', //'',
    'emailerror_email' => $emailerror_email ?? '', //'',
    'token_key' => $token_key ?? '', //'',
    'infomessage' => $infomessage ?? '',
    'updatesetuproute' => '../setup/p_updatesetup.php',
];
$loader = new \Twig_Loader_Filesystem(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/setup');

$options = [
    'autoescape' => '',
];

$twig = new \Twig_Environment($loader, $options);

echo $twig->render('index.twig', $data);
