<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3session.php';

$langcode = \model\lexi::getLang();

//style and header
$lexi = \model\lexi::getall('g3');

require_once \model\route::script('style.php');
$data = [
    'lang' => $langcode,
    'lbl_lang' => (new \model\project)->getLang($langcode)->name ?? '',
    'lbl_logout' => $lexi['sys008'],
    'findprojectsroute' => \model\route::window('tool', 'project/tools/projsearch.php', $lexi['sys009']),
    'findcontactsroute' => \model\route::window('tool', 'project/tools/peoplesearch.php', $lexi['sys010']),
    'tellroute' => \model\route::window('tool', 'project/tools/tellafriend.php', $lexi['sys013']),
    'aboutroute' => \model\route::window('about', 'login/about.php', $lexi['sys015']),
    'editlangroute' => \model\route::window('tool', 'g3/editlanguage.php', $lexi['sys026']),
];
if (!\model\env::isauthorized()) {
    $data += [
        'loginroute' => \model\route::window('login', 'login/index.php', $lexi['sys014']),
    ];
}
if (\model\env::isauthorized()) {
    $data += [
        'username' => \model\env::getUserName(),
        'useremail' => \model\env::getUserEmail(),
        'lbl_warning' => \model\utils::format($lexi['sys022'], 3),
        'lbl_search' => $lexi['sys023'],
        'updateuserroute' => \model\route::window('user', 'profile/index.php', $lexi['sys011']),
        'logoutformroute' => \model\route::form('logout.php'),
        'addprojectroute' => \model\route::window('newproj', 'project/admon/addproject.php', $lexi['sys007'], $lexi['sys016']),
        'startroute' => \model\route::window('start', 'start/index.php', \model\env::getUserIdProject(), $lexi['sys005'], \model\env::getUserName()),
        'menumssgroute' => \model\route::form('actions/actions/menumessage.php'),
        'menuactionsroute' => \model\route::form('actions/actions/menuactions.php'),
        'menuinvitationroute' => \model\route::form('g3/menuinvitations.php'),
        'menuprojectsroute' => \model\route::form('g3/menulistprojects.php'),
        'searchactionsroute' => \model\route::window('search', ['actions/search/index.php?search={0}&type=txt', '[searchtext]'], 'projects', $lexi['sys016'], $lexi['sys016']),
    ];
}
\model\route::render('g3/index.twig', $data);

\model\route::render('g3/powerby.twig');
