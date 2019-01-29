<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3session.php';

if ((int) $total_records === 0)
    return;

if ((int) $max_records === 0)
    return;

$totalpages = ceil((int) $total_records / (int) $max_records);

if ($totalpages > 1) {
    $data = [
        'totalpages' => $totalpages,
        'navpage' => (int) $navpage,
    ];
    \model\route::render('g3/footpage.twig', $data);
}
