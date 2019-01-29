<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$name = '';
$email = '';
if (filter_input(INPUT_GET, 'name') !== null) 
    $name = filter_input(INPUT_GET, 'name');

if (filter_input(INPUT_POST, 'name') !== null) 
    $name = filter_input(INPUT_POST, 'name');

if (filter_input(INPUT_GET, 'email') !== null) 
    $email = filter_input(INPUT_GET, 'email');

if (filter_input(INPUT_POST, 'email') !== null) 
    $email = filter_input(INPUT_POST, 'email');

$lexi = \model\lexi::getall('g3/project');
require_once \model\route::script('style.php');


if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    \model\message::render(\model\utils::format($lexi['sys070'], $email));
} else {
    // get email string
    $filename = \model\route::render('g3/*/requestinvitation.html');

    $emailstring = array();

    $lines = file($filename);
    foreach ($lines as $line) {
        $line = str_replace('[membername]', $name, $line);
        $line = str_replace('[username]', \model\env::getUserName(), $line);
        $emailstring[] = $line;
    }

    \model\env::sendMail($name, $email, \model\utils::format($lexi['sys089'], \model\env::getUserName()), $emailstring);
    \model\message::render(\model\utils::format($lexi['sys068'], $name, $email));
}

\model\route::close(\model\env::session_idproject(),'tool');
