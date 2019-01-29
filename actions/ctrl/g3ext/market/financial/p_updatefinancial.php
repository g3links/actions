<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$taxincluded = 0;
$taxpercentage = 0;

if (filter_input(INPUT_POST, 'save') !== null) {
    if(filter_input(INPUT_POST, 'taxincluded') !== null)
        $taxincluded = (int) filter_input(INPUT_POST, 'taxincluded');
    
    if(filter_input(INPUT_POST, 'taxpercentage') !== null)
        $taxpercentage = (float) filter_input(INPUT_POST, 'taxpercentage');
}

(new \model\ext\market\bp(\model\env::session_src()))->setFinancial($taxincluded, $taxpercentage);

require \model\route::script('g3ext/market/financial/index.php?idproject={0}', \model\env::session_idproject());
