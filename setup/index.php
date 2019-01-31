<?php

if(!isset($customdefinitions) || is_file($customdefinitions))
    die();

$data = [
//    'updatesetuproute' => $langcode,
//    'languages' => $languages,
    'updatesetuproute' => '../setup/p_updatesetup.php',
//    'updatelangroute' => \model\route::form('g3/p_updatelanguage.php'),
//    'submit' => $lexi['sys026'],
//    'lbl_reset' => $lexi['sys012'],
];
$loader = new \Twig_Loader_Filesystem(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/setup');

$options = [
    'autoescape' => '',
];

$twig = new \Twig_Environment($loader, $options);

echo $twig->render('index.twig', $data);
