<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/g3authsession.php';

$result = (new \model\ext\market\market(\model\env::session_src()))->getSessionProduct();

$ignoreshared = '';
if (filter_input(INPUT_GET, 'ignoreshared') !== null)
    $ignoreshared = '&ignoreshared=yes';

require_once \model\route::script('style.php');
$data = [
    'tags' => $result->tags,
    'listroute' => \model\route::form('g3ext/market/product/list.php?idproject={0}&navpage=[navpage]&search=[searchtext]&tag=[tag]' . $ignoreshared, \model\env::session_idproject()),
    'editproductroute' => \model\route::form('g3ext/market/product/editproduct.php?idproject={0}&idproduct=[idproduct]', \model\env::session_idproject()),
    'linkmenuroute' => \model\route::form('project/admon/linkmenu.php?idproject={0}&action={1}', \model\env::session_idproject(), $result->actionname),
    'shareddataroute' => \model\route::form('project/links/listprojectshared.php?idproject={0}&modulename={1}', \model\env::session_idproject(), $result->actionname),
    'linkeddataroute' => \model\route::form('project/links/listprojectlinked.php?idproject={0}&modulename={1}', \model\env::session_idproject(), $result->actionname),
    'lbl_titlenew' => \model\lexi::get('g3ext/market', 'sys047'),
    'lbl_searchwarning' => \model\utils::format(\model\lexi::get('g3ext/market', 'sys021'), 3),
];
//if ($result->allowedit) {
//    $data += [
//    ];
//}
\model\route::render('g3ext/market/product/index.twig', $data);
