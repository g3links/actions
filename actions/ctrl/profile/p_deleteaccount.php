<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

(new \model\user)->confirmDeleteAccountEmail('g3/*/closeaccount.html');

$lexi = \model\lexi::getall('g3');
$data = [
    'lbl_security' => $lexi['sys018'],
    'lbl_username' => $lexi['sys025'],
    'username' => \model\env::getUserName(),
    'lbl_email' => \model\utils::format($lexi['sys027'],\model\env::getUserEmail()),
    'host' => ROOT_APP,
];
\model\route::render('login/authrequired.twig', $data);
