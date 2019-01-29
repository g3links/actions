<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3session.php';

$langcode = \model\lexi::getLang();
$languages = (new \model\project)->getLangs();

$lexi = \model\lexi::getall('g3');
require_once \model\route::script('style.php');
$data = [
    'langcode' => $langcode,
    'languages' => $languages,
    'resetlangroute' => \model\route::form('g3/resettimezone.php'),
    'updatelangroute' => \model\route::form('g3/p_updatelanguage.php'),
    'submit' => $lexi['sys026'],
    'lbl_reset' => $lexi['sys012'],
];
\model\route::render('g3/editlanguage.twig', $data);
