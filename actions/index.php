<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3session.php';

// remove cache data
\model\utils::unsetCookie('selectedusers', true);
\model\utils::unsetCookie('selectedgroups', true);

// time zone
\model\env::setCacheLang();

require_once \model\route::script('style.php');

// get timezone
if (filter_input(INPUT_COOKIE,'timezone') === null || filter_input(INPUT_COOKIE,'lang') === null) {
    $data = [
        'timezoneroute' => \model\route::form('g3/timezone.php?time={0}&lang={1}', '[time]', '[lang]'),
        'callbackroute' => \model\route::form('index.php'), // call the script from relative runtime path
    ];
    \model\route::render('gettimezone.twig', $data);
    die();
}

// language by user request
if (filter_input(INPUT_GET, 'lang')  !== null) {
    \model\env::setLang(filter_input(INPUT_GET, 'lang'));
}

// load main layout
$data = [
    // for developers stop loading welcome page
    'showstartpage' => filter_input(INPUT_SERVER, 'SERVER_NAME') === 'localhost' ? false : !\model\env::isauthorized(),
    'pagetitle' => PAGETITLE,
    'welcomepage' => WELCOMEPAGE,
    'host' => ROOT_APP,
];
\model\route::render('index.twig', $data);

// open selected project, 0 = default user project
$cacheselecteproject = 0;
if (filter_input(INPUT_GET, 'idproject') !== null) {
    $cacheselecteproject = (int) filter_input(INPUT_GET, 'idproject');
}

//load main menu
\model\route::refreshMaster($cacheselecteproject);

//custom load other apps
require \model\route::script('bootstrap.php');
