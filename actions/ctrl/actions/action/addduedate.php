<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$idrow = 0;
if (filter_input(INPUT_GET, 'idrow') !== null) {
    $idrow = (int) filter_input(INPUT_GET, 'idrow');
}

$hours = \model\utils::getHours();
$mins = \model\utils::getMinutes();

$data = [
    'idrow' => $idrow,
    'hours' => $hours,
    'mins' => $mins,
];
\model\route::render('actions/action/addduedate.twig', $data);
