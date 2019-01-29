<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$categories = (new \model\action(\model\env::session_src()))->getCategories(true);

$lexi = \model\lexi::getall('actions');
$data = [
    'categories' => $categories,
    'th_col1' => $lexi['sys030'],
    'th_col2' => $lexi['sys031'],
    'lbl_notfound' => $lexi['sys044'],
];
\model\route::render('actions/category/loaddata.twig', $data);
