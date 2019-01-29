<?php

namespace model\ext\market;

class market extends \model\dbconnect {

    public function __construct($src) {
        $this->src = $src;
        parent::__construct(\model\env::CONFIG_ACTIONS);
    }

    const ROLE_CART = '070';
    const ROLE_ORDER = '075';
    const ROLE_PRODUCT = '090';
    const ROLE_CURRENCY = '120';

    //******** CURRENCY ******************
    public function getSessionCurrency() {
        $result = new \stdClass();

        $result->allowedit = $this->isuserallow(self::ROLE_CURRENCY, self::class);
        $result->module = \model\env::CONFIG_ACTIONS;
        $result->modulename = \model\env::MODULE_CURRENCY;

        return $result;
    }

    private $currencycache;
    private $defaultCurrrency;

    public function getCurrency($idcurrency) {
        if (!empty($idcurrency)) {
            if (!isset($this->currencycache))
                $this->getCurrencies();

            return \model\utils::firstOrDefault($this->currencycache, \model\utils::format('$v->idcurrency === "{0}"', $idcurrency));
        }
        return null;
    }

//    // routine for a single currency file
//    public function getCurrencies() {
//        $modelcurrency = new \model\project($this->src);
//        $this->currencycache = $modelcurrency->getServiceRecords('actions/*/', \model\environment::MODULE_CURRENCY);
//        $this->currencycache = \model\utils::filter($this->currencycache, 'deleted === false');
//        // add name to currency to
//        foreach ($this->currencycache as $currency) {
//            if (isset($currency->convs)) {
//
//                foreach ($currency->convs as $currencyconv) {
//                    $currencyconv->name = '';
//                    $to = \model\utils::array_filter_first($this->currencycache, 'idcurrency', $currencyconv->idcurrency);
//                    if (isset($to)) {
//                        $currencyconv->name = $to->name;
//                    }
//                }
//            }
//        }
//
//        return $this->currencycache;
//    }
// get currency with Convertions in a single file located in default language
// it's better to have a single file conversion table formaintenance
    public function getCurrencies($all = false) {
        $modelcurrency = new \model\project();
        $this->currencycache = $modelcurrency->getServiceRecords($this->src->idproject, \model\env::CONFIG_ACTIONS, \model\env::MODULE_CURRENCY);
        if (!$all)
            $this->currencycache = \model\utils::filter($this->currencycache, '$v->deleted === 0');

        $currencyconv = $modelcurrency->getServiceRecords($this->src->idproject, \model\env::CONFIG_ACTIONS, \model\env::MODULE_CURRENCYCONV);
        foreach ($this->currencycache as $currency) {
            $converters = [];
            $records = \model\utils::filter($currencyconv, \model\utils::format('$v->idcurrency === "{0}"', $currency->idcurrency));
            foreach ($records as $record) {
//find currency name
                $to = \model\utils::firstOrDefault($this->currencycache, \model\utils::format('$v->idcurrency === "{0}"', $record->idcurrencyto));

                $converter = new \stdClass();
                $converter->idcurrency = $record->idcurrencyto;
                $converter->name = $to->name ?? '';
                $converter->value = $record->value;
                $converters[] = $converter;
            }
            $currency->convs = $converters;
        }

        return $this->currencycache;
    }

    public function getDefaultCurrency() {
        if (!isset($this->defaultCurrrency)) {
            $this->defaultCurrrency = (new \model\project)->getDefaultCurrency($this->src->idproject)->idcurrency ?? '';
            return $this->getCurrency($this->defaultCurrrency)->idcurrency;
        }

        return $this->defaultCurrrency;
    }

    public function hasCurrency() {
        if (!isset($this->currencycache))
            $this->currencycache = $this->getCurrencies();

        return count($this->currencycache) > 0;
    }

    public function convertProductPrice($product) {
        $localcurrency = $this->getDefaultCurrency();

        if (!isset($product->idcurrency))
            return null;

// default 1=1 convertion rate
        $product->rateconv = 1;

        if ($product->idcurrency === $localcurrency)
            return $product;

// get idcurrency and convertion to localcurrency
        $from_currency = $this->getCurrency($product->idcurrency);
        if (!isset($from_currency))
            $product->message = \model\lexi::get('g3ext/market', 'sys018', $product->idcurrency);

        $to_currency = \model\utils::firstOrDefault($from_currency->convs, \model\utils::format('$v->idcurrency === "{0}"', $localcurrency));
        if (!isset($to_currency)) {
            $product->message = \model\lexi::get('g3ext/market', 'sys019', $product->idcurrency, $localcurrency);
        } else {
            $product->rateconv = $to_currency->value; //change unit price
            $product->idcurrency = $localcurrency;
            $product->currencyname = $to_currency->name;
        }

        return $product;
    }

    public function hasProductService() {
        $result = $this->getRecord('SELECT count(*) AS result FROM product WHERE deleted = ?', 0);
        return ($result->result ?? 0) > 0;
    }

    public function getTotalActiveCarts() {
        $result = $this->getRecord('SELECT count(*) AS result FROM cart WHERE idgate = ?', (new \model\action($this->src))->getDefaultGate());
        return ($result->result ?? 0);
    }

    public function getSessionCart() {
        $result = new \stdClass();

        $result->allowedit = $this->isuserallow(self::ROLE_CART, self::class);
        $result->defaultgate = (new \model\action($this->src))->getDefaultGate();
        return $result;
    }

    public function getCarts($lastviewgate, $navpage = 0, $downloadfileroute = null) {
        $defaultgate = (new \model\action($this->src))->getDefaultGate();
        $isrole = $this->isUserAllow(self::ROLE_CART, self::class);

        if ($lastviewgate === 0)
            $lastviewgate = $defaultgate;

        $allowedit = $lastviewgate === $defaultgate;

        $carts = $this->getRecords('SELECT idcart,description,createdon,lastmodifiedon,idgate,idorder FROM cart WHERE idgate = ? ORDER BY createdon', (int) $lastviewgate);
        foreach ($carts as $cart) {
            $cart->recordid = $this->src->idproject . '-' . $cart->idcart;

            $cart->items = $this->getRecords('SELECT cartdetail.idcartdetail,cartdetail.idproduct,cartdetail.productcode,cartdetail.productname,cartdetail.orderquantity,cartdetail.idcurrencyfrom AS idcurrency,cartdetail.idcurrencyto,cartdetail.rateconv,cartdetail.saleprice,cartdetail.createdon,cartdetail.lastmodifiedon,product.srcidproduct,product.idproject,productdetail.productdescription,productdetail.productmanifest,product.srcidproduct,product.idproject FROM cartdetail LEFT JOIN product USING ( idproduct ) LEFT JOIN productdetail USING ( idproduct ) WHERE cartdetail.idcart = ?', (int) $cart->idcart);

            if (!$allowedit & !isset($downloadfileroute))
                continue;

            foreach ($cart->items as $product) {
                if ($allowedit)
                    $product = $this->convertProductPrice($product); // only convert active cart

                if (isset($downloadfileroute)) {
                    $processdw2 = str_replace('[attachedfile]', $product->srcidproduct > 0 ? $product->srcidproduct : $product->idproduct, $downloadfileroute);
                    $product->img = str_replace('[idproject]', $product->idproject > 0 ? $product->idproject : $this->src->idproject, $processdw2);
                }
            }
        }

        $result = new \stdClass();
        $result->lastviewgate = $lastviewgate;
        $result->isrole = $isrole;
        $result->allowedit = $allowedit;
        $result->total_records = count($carts);
        $result->max_records = \model\env::getMaxRecords('orders');
        $result->carts = \model\utils::takeList($carts, $navpage, $result->max_records);

        return $result;
    }

    private function _getCart($idcart) {
        $cart = $this->getRecord('SELECT idcart,description,idgate,idorder,createdon,lastmodifiedon FROM cart WHERE idcart = ?', (int) $idcart);
        if (isset($cart)) {
            $cart->gatename = (new \model\action($this->src))->getGate($cart->idgate)->name ?? '';
            $cart->items = $this->getRecords('SELECT cartdetail.idcartdetail,cartdetail.idproduct,cartdetail.productcode,cartdetail.productname,cartdetail.orderquantity,cartdetail.idcurrencyfrom,cartdetail.idcurrencyto,cartdetail.rateconv,cartdetail.saleprice,cartdetail.rateconv * cartdetail.saleprice AS convsaleprice,cartdetail.createdon,cartdetail.lastmodifiedon,product.srcidproduct,product.idproject,productdetail.productdescription,productdetail.productmanifest FROM cartdetail LEFT JOIN product USING ( idproduct ) LEFT JOIN productdetail USING ( idproduct ) WHERE cartdetail.idcart = ?', (int) $idcart);
        }

        return $cart;
    }

    public function getCartProduct($idcartdetail) {
        $result = new \stdClass();
        $result->isrole = $this->isUserAllow(self::ROLE_CART, self::class);
        $result->product = null;

        $cartdetail = $this->getRecord('SELECT cartdetail.orderquantity,cart.idgate,cartdetail.idproduct FROM cartdetail JOIN cart USING ( idcart ) WHERE cartdetail.idcartdetail = ?', (int) $idcartdetail);
        if (isset($cartdetail)) {
//confirm gate is open
            $product = $this->getRecord('SELECT product.keycode,product.name,product.inactive,product.idproject,product.srcidproduct,product.issales,product.ispurchase,productdetail.productdescription,productdetail.productmanifest,productdetail.saleprice,productdetail.idcurrency FROM product LEFT JOIN productdetail USING ( idproduct ) WHERE product.idproduct = ? AND product.deleted = 0', (int) $cartdetail->idproduct);
            if (isset($product)) {
                $product->orderquantity = $cartdetail->orderquantity;
                $product->idcartdetail = $idcartdetail;
                $product->productdescription = $product->productdescription ?? '';
                $product->productmanifest = $product->productmanifest ?? '';

                if ($cartdetail->idgate === (new \model\action($this->src))->getDefaultGate())
                    $product = $this->convertProductPrice($product); // only convert active cart

                $result->product = $product;
            }
        }

        if (!isset($product))
            $result->isrole = false;

        return $result;
    }

    public function getMarketProduct($idproduct) {
        $product = $this->getRecord('SELECT product.idproduct,product.keycode,product.name,product.idproject,product.srcidproduct,product.issales,product.ispurchase,productdetail.productdescription,productdetail.productmanifest,productdetail.saleprice,productdetail.idcurrency,product.createon,product.lastmodifiedon FROM product LEFT JOIN productdetail USING ( idproduct ) WHERE product.idproduct = ? AND product.inactive = 0 AND product.deleted = 0', (int) $idproduct);
        return $this->convertProductPrice($product);
    }

    public function setInsertCart($newcart) {
        if (!$this->isuserallow(self::ROLE_CART, self::class))
            return false;

        return $this->executeSql('INSERT INTO cart (idgate,description,iduser,username) VALUES (?, ?, ?, ?)', (new \model\action($this->src))->getDefaultGate(), $newcart->description, \model\env::getIdUser(), \model\env::getUserName());
    }

    public function setUpdateCartDetail($cartdetail) {
        if (!$this->isuserallow(self::ROLE_CART, self::class))
            return false;

        $cartexists = $this->getRecord('SELECT cart.idgate FROM cartdetail JOIN cart USING (idcart) WHERE cartdetail.idcartdetail = ?', (int) $cartdetail->idcartdetail);
        if (!isset($cartexists))
            return false;

        if ($cartexists->idgate !== (new \model\action($this->src))->getDefaultGate())
            return false;

        $this->executeSql('UPDATE cartdetail SET orderquantity = ?, iduser = ?, username = ? WHERE idcartdetail = ?', (int) $cartdetail->orderquantity, \model\env::getIdUser(), \model\env::getUserName(), (int) $cartdetail->idcartdetail);
    }

    public function setInsertCartDetail($cartdetail) {
        if (!$this->isuserallow(self::ROLE_CART, self::class))
            return false;

        $cartexists = $this->getRecord('SELECT idgate,idorder FROM cart WHERE idcart = ?', (int) $cartdetail->idcart);
        if (!isset($cartexists))
            return false;

        if ($cartexists->idgate !== (new \model\action($this->src))->getDefaultGate())
            return false;

        if ($cartexists->idorder > 0) {
            \model\message::render(\model\lexi::get('g3ext/market', 'sys029'));
            return false;
        }

        $product = $this->getRecord('SELECT product.keycode,product.name,productdetail.saleprice,productdetail.idcurrency FROM product LEFT JOIN productdetail USING ( idproduct ) WHERE product.idproduct = ? AND product.inactive = 0 AND product.deleted = 0', (int) $cartdetail->idproduct);
        if (isset($product)) {
            $idcurrencyfrom = $product->idcurrency;
            $product = $this->convertProductPrice($product); // only convert active cart

            $cartdetail->idcartdetail = $this->executeSql('INSERT INTO cartdetail (idcart,idproduct,orderquantity,productcode,productname,idcurrencyfrom,idcurrencyto,rateconv,saleprice,iduser,username) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', (int) $cartdetail->idcart, (int) $cartdetail->idproduct, (int) $cartdetail->orderquantity, $product->keycode, $product->name, $idcurrencyfrom, $product->idcurrency, $product->rateconv, $product->saleprice, \model\env::getIdUser(), \model\env::getUserName());
        }

        $result = new \stdClass();
        $result->idcart = $cartdetail->idcart;
        $result->idcartdetail = $cartdetail->idcartdetail ?? 0;
        $result->code = $product->keycode ?? '';
        $result->name = $product->name ?? '';
        $result->qty = $cartdetail->orderquantity;
        $result->msgerror = '';

        return $result;
    }

    public function deleteCartDetail($cartdetail) {
        if (!$this->isuserallow(self::ROLE_CART, self::class))
            return false;

// only delete when Cart gate default (open)
        $cart = $this->getRecord('SELECT cart.idgate,cart.idorder FROM cartdetail JOIN cart USING ( idcart ) WHERE cartdetail.idcartdetail = ?', (int) $cartdetail->idcartdetail);
        if (!isset($cart))
            return false;

        if ($cart->idorder > 0) {
            \model\message::render(\model\lexi::get('g3ext/market', 'sys029'));
            return false;
        }

        if ($cart->idgate === (new \model\action($this->src))->getDefaultGate())
            return false;

        $this->executeSql('DELETE FROM cartdetail WHERE idcartdetail = ?', (int) $cartdetail->idcartdetail);
    }

    public function setUpdateCartGate($updatecart) {
        if (!$this->isuserallow(self::ROLE_CART, self::class))
            return false;

        $cart = $this->_getCart($updatecart->idcart);
        if (!isset($cart))
            return false;

        if ($cart->idgate === $updatecart->toidgate)
            return false;

        $modelaction = new \model\action($this->src);
        $defaultgate = $modelaction->getDefaultGate();
        $targetgate = $modelaction->getGate($updatecart->toidgate);

        if (!isset($targetgate) || !isset($targetgate->action)) {
            \model\message::render(\model\lexi::get('g3ext/market', 'sys044'));
            return false;
        }

// security: been posted and moving to gate default
//        if($cart->idorder > 0 & $cart->idgate !== $defaultgate & $updatecart->toidgate === $defaultgate)
        if ($cart->idorder > 0) {
            \model\message::render(\model\lexi::get('g3ext/market', 'sys029'));
            return false;
        }

        if ($cart->idgate === $defaultgate && strtolower(\trim($targetgate->action)) === "close") {
//  create order
            $idpriority = $modelaction->getDefaultPriority();
            $this->startTransaction();
            $lastIdInserted = $this->executeSql('INSERT INTO [order] (idpriority, idgate,description,iduser,username) VALUES (?, ?, ?, ?, ?)', $idpriority, (int) $cart->idgate, $cart->description, \model\env::getIdUser(), \model\env::getUserName());
            if ($lastIdInserted > 0) {
                $sqlline = 'INSERT INTO orderdetail (idorder,idproduct,srcidproduct,idproject,productcode,productname,orderquantity,productdescription,productmanifest,idcurrencyfrom,idcurrencyto,rateconv,saleprice) VALUES ';
                $sqlparms = [];
                foreach ($cart->items as $item) {
                    if (count($sqlparms) > 0)
                        $sqlline .= ',';

                    $sqlline .= '(?,?,?,?,?,?,?,?,?,?,?,?,?)';
                    array_push($sqlparms, $lastIdInserted, (int) $item->idproduct, (int) $item->srcidproduct, (int) $item->idproject, (string) $item->productcode, (string) $item->productname, (int) $item->orderquantity, (string) $item->productdescription, (string) $item->productmanifest, (string) $item->idcurrencyfrom, (string) $item->idcurrencyto, (float) $item->rateconv, (float) $item->saleprice);
                }

                $this->executeSql($sqlline, $sqlparms);
            }
        }

        $this->executeSql('UPDATE cart SET idgate = ?, idorder = ?, iduser = ?, username = ? WHERE idcart = ?', $updatecart->toidgate, $lastIdInserted, \model\env::getIdUser(), \model\env::getUserName(), (int) $cart->idcart);

        if ($cart->idgate === $defaultgate)
            $this->endTransaction();

        $texto = \model\utils::format('{0}: {1}, cart id: {2}-{3} {4}', \model\lexi::get('g3ext/market', 'sys010'), $modelaction->getGate($updatecart->toidgate)->name ?? '', $this->src->idproject, $cart->idcart, \model\env::getUserName());
        $modelaction->addSystemNote($texto);

// send email to users (under review)
//        $filename = \model\route::render('actions/*/statuscart.html');
//        $this->_emailcartstatus($result->title, $gatename, $filename);
    }

// under review
//    private function _emailcartstatus($taskname, $statusname, $filename) {
//        $modelproject = new \model\project();
//
//        $projname = $modelproject->getproject($this->src->idproject)->title;
//        $users = $modelproject->getactiveusersinproject($this->src->idproject);
//
//        foreach ($users as $user) {
//            $emailstring = array();
//            if (empty($user->email))
//                continue;
//
//            if (!filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
//                \model\message::render(\model\lexi::get('', 'sys030', $user->email));
//                continue;
//            }
//
//            $lines = file($filename);
//            foreach ($lines as $line) {
//                $line = str_replace('[projectname]', $projname, $line);
//                $line = str_replace('[membername]', $user->name, $line);
//                $line = str_replace('[taskname]', $taskname, $line);
//                $line = str_replace('[statusname]', $statusname, $line);
//                $emailstring[] = $line;
//            }
//
//            \model\env::sendMail($user->name, $user->email, \model\lexi::get('', 'sys005') . ': ' . $taskname, $emailstring);
//        }
//    }

    public function getSearchTags() {
        return $this->getRecords('SELECT idtag FROM producttag GROUP BY idtag');
    }

    public function searchCartProducts($search, $idtag, $downloadfileroute = null) {
        if (!empty($search)) {
            $searchtext = \model\utils::getSearchText($search);
            $products = $this->getRecords('SELECT product.createon,product.idproduct,product.keycode,product.lastmodifiedon,product.name,product.idproject,product.srcidproduct,product.issales,product.ispurchase,productdetail.productdescription,productdetail.productmanifest,productdetail.saleprice,productdetail.idcurrency FROM product LEFT JOIN productdetail USING ( idproduct ) WHERE product.inactive = 0 AND ( product.name LIKE ? OR product.keycode LIKE ? OR product.tags LIKE ? ) ORDER BY product.name', (string) $searchtext, (string) $searchtext, (string) $searchtext);
        }
        if (!empty($idtag)) {
            $searchidtag = \model\utils::getSearchText($idtag);
            $products = $this->getRecords('SELECT product.createon,product.idproduct,product.keycode,product.lastmodifiedon,product.name,product.idproject,product.srcidproduct,product.issales,product.ispurchase,productdetail.productdescription,productdetail.productmanifest,productdetail.saleprice,productdetail.idcurrency FROM product LEFT JOIN productdetail USING ( idproduct ) WHERE product.inactive = 0 AND product.tags LIKE ? ORDER BY product.name', (string) $searchidtag);
        }

        if (isset($products)) {
// price convertion and images
            foreach ($products as $product) {
                $product = $this->convertProductPrice($product);

                if (isset($downloadfileroute)) {
                    $processdw2 = str_replace('[attachedfile]', $product->srcidproduct > 0 ? $product->srcidproduct : $product->idproduct, $downloadfileroute);
                    $product->image = str_replace('[idproject]', $product->idproject > 0 ? $product->idproject : $this->src->idproject, $processdw2);
                }
            }
        }

        $result = new \stdClass();
        $result->products = $products ?? [];
        $result->isrole = $this->isUserAllow(self::ROLE_PRODUCT, self::class);

        return $result;
    }

    public function getCartHistory($idcart) {
        $cart = $this->getRecord('SELECT description,idgate,idorder,iduser,username,createdon,lastmodifiedon FROM cart WHERE idcart = ?', (int) $idcart);
        $carts_h = $this->getRecords('SELECT idcarth,description,idgate,idorder,iduser,username,lastmodifiedon,createdon FROM cart_h WHERE idcart = ?', (int) $idcart);
        $cartdetails = $this->getRecords('SELECT idcartdetail,idproduct,productcode,productname,orderquantity,idcurrencyfrom,idcurrencyto,rateconv,saleprice,iduser,username,createdon,lastmodifiedon FROM cartdetail WHERE idcart = ?', (int) $idcart);
        $cartdetails_h = $this->getRecords('SELECT idcartdetailh,idcartdetail,idproduct,productcode,productname,orderquantity,idcurrencyfrom,idcurrencyto,rateconv,saleprice,iduser,username,createdon,lastmodifiedon FROM cartdetail_h WHERE idcart = ?', (int) $idcart);

        $imgAdd = 'imgAdd';
        $imgEdit = 'imgUpdate';
        $imgDelete = 'imgDelete';

        $history = [];

////        // current cart
//        if (isset($cart)) {
//            $cart->img = $imgAdd;
//            $history[] = $cart;
//        }

        $modelaction = new \model\action($this->src);

        $sortfields = ['idcarth' => 'desc'];
        $carts_h = \model\utils::sorttakeList($carts_h, $sortfields, 0, 0);
        foreach ($carts_h as $cart_h) {
            $cart_h->gatename = $modelaction->getGate($cart_h->idgate)->name ?? $cart_h->idgate;
            if (!isset($cart)) {
                $cart = new \stdClass(); //stop further records

                $cart_h->img = $imgDelete;
                $history[] = $cart_h;
                continue;
            }

            $cart_h->img = $imgEdit;
            $history[] = $cart_h;
        }

// current cartdetail
        foreach ($cartdetails as $cartdetail) {
            $cartdetail->img = $imgAdd;
            $history[] = $cartdetail;
        }

        $idcartdetail_loop = 0;
        $sortfields = ['idcartdetail' => '', 'idcartdetailh' => 'desc'];
        $cartdetails_h = \model\utils::sorttakeList($cartdetails_h, $sortfields, 0, 0);
        foreach ($cartdetails_h as $cartdetail_h) {
            $cartdetail_h->img = $imgEdit;
// only review for deleted once
            if ($idcartdetail_loop !== $cartdetail_h->idcartdetail) {
                $idcartdetail_loop = $cartdetail_h->idcartdetail;

                $cartdetail = \model\utils::firstOrDefault($cartdetails, '$v->idcartdetail === ' . $cartdetail_h->idcartdetail);
                if (!isset($cartdetail)) {
//delete records
                    $delete = clone($cartdetail_h);
                    $delete->img = $imgDelete;
                    $history[] = $delete;

// add record
                    $cartdetail_h->img = $imgAdd;
                    $cartdetail_h->createdon = $cartdetail_h->lastmodifiedon;
                    $history[] = $cartdetail_h;
                    continue;
                }
            }

            $history[] = $cartdetail_h;
        }

//sort all records
        $sortfields = ['createdon' => 'desc'];
        return \model\utils::sorttakeList($history, $sortfields, 0, 0);
    }

    public function getTotalActiveOrders() {
        $result = $this->getRecord('SELECT count(*) AS result FROM [order] WHERE idgate = ?', (new \model\action($this->src))->getDefaultGate());
        return ($result->result ?? 0);
    }

    public function getSessionOrder() {
        $result = new \stdClass();

        $result->allowedit = $this->isuserallow(self::ROLE_ORDER, self::class);
        $result->defaultgate = (new \model\action($this->src))->getDefaultGate();
        return $result;
    }

    public function getOrders($idgate, $navpage = 0) {
        $defaultgate = (new \model\action($this->src))->getDefaultGate();
        if ($idgate === 0)
            $idgate = $defaultgate;

        $orders = $this->getRecords('SELECT idorder,idpriority,progress,idgate,idtrack,idcategory,description,fullrequired,createdon,lastmodifiedon FROM [order] WHERE idgate = ?', (int) $idgate);

        $result = new \stdClass();
        $result->total_records = count($orders);
        $result->max_records = \model\env::getMaxRecords('orders');
        $result->Gates = (new \model\action($this->src))->getGates();
        $result->idgate = $idgate;
        $result->isrole = $this->isUserAllow(self::ROLE_ORDER, self::class);
        $result->isroleaction = $this->isUserAllow(\model\action::ROLE_ACTIONINSERT, self::class);

        $orders = \model\utils::takeList($orders, $navpage, $result->max_records);
        foreach ($orders as $order) {
            $order->recordid = $this->src->idproject . '-' . $order->idorder;
            $order->items = $this->getRecords('SELECT idorderdetail,idproduct,orderquantity,srcidproduct,idproject,productcode,productname,idcurrencyto,saleprice,rateconv,(select SUM(qtyfulfill) FROM orderfulfill where idorderdetail = orderdetail.idorderdetail) AS qtyfulfilled FROM orderdetail WHERE idorder = ?', (int) $order->idorder);
            $order->isfulfilled = count(\model\utils::filter($order->items, '$v->orderquantity !== $v->qtyfulfilled')) === 0;
            $order->allowcommit = (!$order->isfulfilled & !$order->fullrequired) | ($order->isfulfilled & $order->fullrequired);
            $order->actions = $this->getRecords('SELECT task.idtask,task.title,task.idpriority,task.createdon FROM taskorder JOIN task USING (idtask) WHERE taskorder.idorder = ? AND task.idgate = ?', $order->idorder, $defaultgate);
            $order->hasactionhistory = ($this->getRecord('SELECT count(*) AS result FROM taskorder JOIN task USING (idtask) WHERE taskorder.idorder = ? AND task.idgate <> ?', $order->idorder, $defaultgate)->result ?? 0) > 0;
        }
        $result->orders = $orders;

        return $result;
    }

    public function getOrder($idorder, $downloadfileroute = null) {
        $order = $this->getRecord('SELECT idpriority,progress,idgate,idtrack,idcategory,description,fullrequired,createdon,lastmodifiedon FROM [order] WHERE idorder = ?', (int) $idorder);
        if (!isset($order))
            return false;

        $orderdetails = $this->getRecords('SELECT idorderdetail,idproduct,orderquantity,srcidproduct,idproject,productcode,productname,idcurrencyto,saleprice,rateconv,(select SUM(qtyfulfill) FROM orderfulfill where idorderdetail = orderdetail.idorderdetail) AS qtyfulfilled FROM orderdetail WHERE idorder = ?', (int) $idorder);
        $orderfulfills = $this->getRecords('SELECT orderfulfill.idorderfulfill,orderfulfill.idorderdetail,orderfulfill.qtyfulfill,orderfulfill.createdon,orderfulfill.lastmodifiedon,orderfulfill.iduser,orderfulfill.username FROM orderfulfill JOIN orderdetail USING ( idorderdetail ) WHERE orderdetail.idorder = ?', (int) $idorder);
        foreach ($orderdetails as $item) {
            if (isset($downloadfileroute)) {
                $processdw2 = str_replace('[attachedfile]', $item->srcidproduct > 0 ? $item->srcidproduct : $item->idproduct, $downloadfileroute);
                $item->img = str_replace('[idproject]', $item->idproject > 0 ? $item->idproject : $this->src->idproject, $processdw2);
            }

            $item->fulfills = \model\utils::filter($orderfulfills, '$v->idorderdetail == ' . $item->idorderdetail);
        }

        $order->recordid = $this->src->idproject . '-' . $idorder;

        $result = new \stdClass();
        $result->order = $order;
        $result->orderdetails = $orderdetails;
        $result->isrole = $this->isUserAllow(self::ROLE_ORDER, self::class);
        $result->order->isfulfilled = count(\model\utils::filter($orderdetails, '$v->orderquantity !== $v->qtyfulfilled')) === 0;
        $result->order->allowcommit = (!$result->order->isfulfilled & !$result->order->fullrequired) | ($result->order->isfulfilled & $result->order->fullrequired);

        return $result;
    }

    public function setOrderFulfill($updateorder, $args) {
        if (!$this->isUserAllow(self::ROLE_ORDER, self::class))
            return false;

        $order = $this->getRecord('SELECT idpriority,progress,idgate,idtrack,idcategory,description,fullrequired,createdon,lastmodifiedon FROM [order] WHERE idorder = ?', (int) $updateorder->idorder);
        if (!isset($order))
            return false;

        if ($order->idgate !== (new \model\action($this->src))->getDefaultGate())
            return false;

        $orderdetails = $this->getRecords('SELECT idorderdetail,orderquantity,(select SUM(qtyfulfill) FROM orderfulfill where idorderdetail = orderdetail.idorderdetail) AS qtyfulfilled FROM orderdetail WHERE idorder = ?', (int) $updateorder->idorder);
        foreach ($orderdetails as $orderdetail)
            $orderdetail->qtytofulfill = 0;

        $orderfulfills = $this->getRecords('SELECT orderfulfill.idorderfulfill FROM orderfulfill JOIN orderdetail USING ( idorderdetail ) WHERE orderdetail.idorder = ?', (int) $updateorder->idorder);
        foreach ($orderfulfills as $orderfulfill)
            $orderfulfill->toremove = false;

        foreach ($args as $param_name => $param_val) {
// get qty to fulfill
            if (\substr($param_name, 0, 3) === 'fi-' & (int) $param_val > 0) {
                $src_idorderdetail = (int) \str_replace('fi-', '', $param_name);
                $orderdetailf = \model\utils::firstOrDefault($orderdetails, '$v->idorderdetail === ' . $src_idorderdetail);

                if (isset($orderdetailf) && ($orderdetailf->orderquantity - ($orderdetailf->qtyfulfilled ?? 0)) >= (int) $param_val)
                    $orderdetailf->qtytofulfill = (int) $param_val;
            }
// get qty to remove
            if (\substr($param_name, 0, 3) === 'fd-' & (int) $param_val === 1) {
                $src_idorderfulfill = (int) \str_replace('fd-', '', $param_name);
                $orderfulfillf = \model\utils::firstOrDefault($orderfulfills, '$v->idorderfulfill === ' . $src_idorderfulfill);
                if (isset($orderfulfillf))
                    $orderfulfillf->toremove = true;
            }
        }

// update records with values
        $orderdetailsf = \model\utils::filter($orderdetails, '$v->qtytofulfill > 0');
        $orderfulfillsf = \model\utils::filter($orderfulfills, '$v->toremove');
        if (count($orderdetailsf) > 0 || count($orderfulfillsf) > 0) {
            $this->startTransaction();

            foreach ($orderdetailsf as $orderdetailf)
                $this->executeSql('INSERT INTO orderfulfill (idorderdetail,qtyfulfill,iduser,username) VALUES (?, ?, ?, ?)', (int) $orderdetailf->idorderdetail, $orderdetailf->qtytofulfill, \model\env::getIdUser(), \model\env::getUserName());

            foreach ($orderfulfillsf as $orderfulfillf)
                $this->executeSql('DELETE FROM orderfulfill WHERE idorderfulfill = ?', (int) $orderfulfillf->idorderfulfill);

            $this->endTransaction();
        }

        if ($updateorder->fullrequired !== $order->fullrequired)
            $this->executeSql('UPDATE [order] SET fullrequired = ?, iduser = ?, username = ? WHERE idorder = ?', $updateorder->fullrequired ? 1 : 0, \model\env::getIdUser(), \model\env::getUserName(), (int) $updateorder->idorder);
    }

    public function setUpdateOrderGate($updateorder) {
        if (!$this->isuserallow(self::ROLE_ORDER, self::class))
            return false;

        $result = $this->getOrder($updateorder->idorder);
        if (!isset($result->order))
            return false;

        if ($result->order->idgate === $updateorder->toidgate)
            return false;

        $modelaction = new \model\action($this->src);
        $defaultgate = $modelaction->getDefaultGate();

// @TODO close or delete order, need to deal with any fulfill
        if ($result->order->idgate === $defaultgate) {
            if (!isset($result->order->allowcommit) || !$result->order->allowcommit) {
                \model\message::render(\model\lexi::get('g3ext/market', 'sys039'));
                return false;
            }
            $hasactionhistory = ($this->getRecord('SELECT count(*) AS result FROM taskorder JOIN task USING (idtask) WHERE taskorder.idorder = ? AND task.idgate <> ?', $result->order->idorder, $defaultgate)->result ?? 0) > 0;
            if ($hasactionhistory) {
                \model\message::render(\model\lexi::get('g3ext/market', 'sys046'));
                return false;
            }
        }

        $this->executeSql('UPDATE [order] SET idgate = ?, iduser = ?, username = ? WHERE idorder = ?', $updateorder->toidgate, \model\env::getIdUser(), \model\env::getUserName(), (int) $updateorder->idorder);

        $texto = \model\utils::format('{0}: {1}, order id: {2}-{3} {4}', \model\lexi::get('g3ext/market', 'sys038'), $modelaction->getGate($updateorder->toidgate)->name ?? '', $this->src->idproject, $updateorder->idorder, \model\env::getUserName());
        $modelaction->addSystemNote($texto);

// send email to users (under review)
//        $filename = \model\route::render('actions/*/statusorder.html');
//        $this->_emailorderstatus($result->title, $gatename, $filename);
    }

    public function getOrderHistory($idorder) {
        $order = $this->getRecord('SELECT idpriority,progress,idgate,idtrack,idcategory,description,fullrequired,createdon,lastmodifiedon FROM [order] WHERE idorder = ?', (int) $idorder);
        $orders_h = $this->getRecords('SELECT idorderh,idpriority,progress,idgate,idtrack,idcategory,description,fullrequired,createdon,lastmodifiedon FROM order_h WHERE idorder = ?', (int) $idorder);
        $orderdetails = $this->getRecords('SELECT idorderdetail,idproduct,orderquantity,srcidproduct,idproject,productcode,productname,idcurrencyto,saleprice,rateconv,createdon,lastmodifiedon FROM orderdetail WHERE idorder = ?', (int) $idorder);
        $orderdetails_h = $this->getRecords('SELECT idorderdetailh,idorderdetail,idproduct,orderquantity,srcidproduct,idproject,productcode,productname,idcurrencyto,saleprice,rateconv,createdon,lastmodifiedon FROM orderdetail_h WHERE idorder = ?', (int) $idorder);
        $orderfulfills = $this->getRecords('SELECT orderfulfill.idorderfulfill,orderfulfill.idorderdetail,orderfulfill.qtyfulfill,orderfulfill.createdon,orderfulfill.lastmodifiedon,orderfulfill.iduser,orderfulfill.username,orderdetail.productcode,orderdetail.productname FROM orderfulfill JOIN orderdetail USING ( idorderdetail ) WHERE orderdetail.idorder = ?', (int) $idorder);
        $orderfulfills_h = $this->getRecords('SELECT orderfulfill_h.idorderfulfillh,orderfulfill_h.idorderdetail,orderfulfill_h.idorderfulfill,orderfulfill_h.qtyfulfill,orderfulfill_h.createdon,orderfulfill_h.lastmodifiedon,orderfulfill_h.iduser,orderfulfill_h.username,orderdetail.productcode,orderdetail.productname FROM orderfulfill_h JOIN orderdetail USING ( idorderdetail ) WHERE orderdetail.idorder = ?', (int) $idorder);

        $imgAdd = 'imgAdd';
        $imgEdit = 'imgUpdate';
        $imgDelete = 'imgDelete';

        $history = [];

        $modelaction = new \model\action($this->src);

        $sortfields = ['idorderh' => 'desc'];
        $orders_h = \model\utils::sorttakeList($orders_h, $sortfields, 0, 0);
        foreach ($orders_h as $order_h) {
            $order_h->gatename = $modelaction->getGate($order_h->idgate)->name ?? $order_h->idgate;
            if (!isset($order)) {
                $order = new \stdClass(); //stop further records

                $order_h->img = $imgDelete;
                $history[] = $order_h;
                continue;
            }

            $order_h->img = $imgEdit;
            $history[] = $order_h;
        }

//        foreach ($orderdetails as $orderdetail) {
//            $orderdetail->img = $imgAdd;
//            $history[] = $orderdetail;
//        }

        $idorderdetail_loop = 0;
        $sortfields = ['idorderdetail' => '', 'idorderdetailh' => 'desc'];
        $orderdetails_h = \model\utils::sorttakeList($orderdetails_h, $sortfields, 0, 0);
        foreach ($orderdetails_h as $orderdetail_h) {
            $orderdetail_h->img = $imgEdit;
// only review for deleted once
            if ($idorderdetail_loop !== $orderdetail_h->idorderdetail) {
                $idorderdetail_loop = $orderdetail_h->idorderdetail;

                $orderdetail = \model\utils::firstOrDefault($orderdetails, '$v->idorderdetail === ' . $orderdetail_h->idorderdetail);
                if (!isset($orderdetail)) {
//delete records
                    $delete = clone($orderdetail_h);
                    $delete->img = $imgDelete;
                    $history[] = $delete;

// add record
                    $orderdetail_h->img = $imgAdd;
                    $orderdetail_h->createdon = $orderdetail_h->lastmodifiedon;
                    $history[] = $orderdetail_h;
                    continue;
                }
            }

            $history[] = $orderdetail_h;
        }

        $idorderfulfill_loop = 0;
        $sortfields = ['idorderfulfill' => '', 'idorderfulfillh' => 'desc'];
        $orderfulfills_h = \model\utils::sorttakeList($orderfulfills_h, $sortfields, 0, 0);
        foreach ($orderfulfills_h as $orderfulfill_h) {
            $orderfulfill_h->img = $imgEdit;
// only review for deleted once
            if ($idorderfulfill_loop !== $orderfulfill_h->idorderfulfill) {
                $idorderfulfill_loop = $orderfulfill_h->idorderfulfill;

                $orderfulfill = \model\utils::firstOrDefault($orderfulfills, '$v->idorderfulfill === ' . $orderfulfill_h->idorderfulfill);
                if (!isset($orderfulfill)) {
//delete records
                    $delete = clone($orderfulfill_h);
                    $delete->img = $imgDelete;
                    $history[] = $delete;

// add record
                    $orderfulfill_h->img = $imgAdd;
                    $orderfulfill_h->createdon = $orderfulfill_h->lastmodifiedon;
                    $history[] = $orderfulfill_h;
                    continue;
                }
            }

            $history[] = $orderfulfill_h;
        }

        foreach ($orderfulfills as $orderfulfill) {
            $orderfulfill->img = $imgAdd;
            $history[] = $orderfulfill;
        }

//sort all records
        $sortfields = ['createdon' => 'desc'];
        return \model\utils::sorttakeList($history, $sortfields, 0, 0);
    }

    public function setActionToOrder($idorder, $idtaskinserted) {
        if (!$this->isuserallow(self::ROLE_ORDER, self::class))
            return false;

        $order = $this->getRecord('SELECT idpriority,progress,idgate,idtrack,idcategory,description,fullrequired,createdon,lastmodifiedon FROM [order] WHERE idorder = ?', (int) $idorder);
        if (!isset($order))
            return false;

        $action = $this->getRecord('SELECT idtask,idgate FROM task WHERE idtask = ?', (int) $idtaskinserted);
        if (!isset($action))
            return false;

        $this->executeSql('INSERT INTO taskorder (idorder,idtask) VALUES (?, ?)', (int) $idorder, (int) $idtaskinserted);
    }

    public function getOrderActionHistory($idorder) {
        $defaultgate = (new \model\action($this->src))->getDefaultGate();
        return $this->getRecords('SELECT task.idtask,task.title,task.idpriority,task.createdon FROM taskorder JOIN task USING (idtask) WHERE taskorder.idorder = ? AND task.idgate <> ?', $idorder, $defaultgate);
    }

    public function getSessionProduct() {
        $result = new \stdClass();

        $result->allowedit = $this->isuserallow(self::ROLE_PRODUCT, self::class);
        $result->actionname = \model\env::MODULE_PRODUCTS;
        $result->tags = $this->getSearchTags();

        return $result;
    }

    public function searchProducts($searchtext, $idtag, $navpage = 0, $ignoreshared = false, $downloadfileroute = null) {
        $result = new \stdClass();
        $result->allowedit = $this->isuserallow(self::ROLE_PRODUCT, self::class);
        $result->actionname = \model\env::MODULE_PRODUCTS;

// order seach, tag, all 
        $sql_elements = ' product.createon,product.idproduct,product.keycode,product.lastmodifiedon,product.name,product.inactive,product.idproject,product.srcidproduct,product.issales,product.ispurchase,productdetail.productdescription,productdetail.productmanifest,productdetail.saleprice,productdetail.idcurrency FROM product LEFT JOIN productdetail USING ( idproduct ) WHERE product.deleted = 0 ';
        if (!empty($searchtext)) {
            $searchtext = \model\utils::getSearchText($searchtext);
            $result->products = $this->getRecords('SELECT' . $sql_elements . 'AND ( product.keycode LIKE ? OR product.name LIKE ?) ORDER BY product.name', (string) $searchtext, (string) $searchtext);
        } else {
            if (!empty($idtag)) {
                $searchidtag = \model\utils::getSearchText($idtag);
                $result->products = $this->getRecords('SELECT' . $sql_elements . 'AND ( product.tags LIKE ?) ORDER BY product.name', $searchidtag);
            } else {
                $result->products = $this->getRecords('SELECT' . $sql_elements . 'ORDER BY product.name');
            }
        }

        if ($ignoreshared)
            $result->products = \model\utils::filter($result->products, '$v->srcidproduct === 0');

        $result->total_records = count($result->products);
        $result->max_records = \model\env::getMaxRecords('products');
        $result->products = \model\utils::takeList($result->products, $navpage, $result->max_records);
        foreach ($result->products as $product) {
//            if ($convcurrency) 
//                $product = $this->convertProductPrice($product);

            $processdw2 = str_replace('[attachedfile]', $product->srcidproduct > 0 ? $product->srcidproduct : $product->idproduct, $downloadfileroute);
            $product->image = str_replace('[idproject]', $product->idproject > 0 ? $product->idproject : $this->src->idproject, $processdw2);
        }

        return $result;
    }

    public function getAllProducts() {
        return $this->getRecords('SELECT createon,idproduct,keycode,lastmodifiedon,name,inactive,idproject,srcidproduct,issales,ispurchase FROM product ORDER BY name');
    }

    public function getProductsSales() {
        return $this->getRecords('SELECT createon,idproduct,keycode,lastmodifiedon,name,inactive,idproject,srcidproduct,issales,ispurchase FROM product WHERE deleted = ? AND issales = ? ORDER BY name', 0, 1);
    }

    public function getProducts() {
        return $this->getRecords('SELECT createon,idproduct,keycode,lastmodifiedon,name,inactive,idproject,srcidproduct,issales,ispurchase FROM product WHERE deleted = 0 ORDER BY name');
    }

    public function getProduct($idproduct) {
        return $this->getRecord('SELECT createon,idproduct,keycode,lastmodifiedon,name,inactive,idproject,srcidproduct,issales,ispurchase FROM product WHERE idproduct = ? AND deleted = ?', (int) $idproduct, 0);
    }

    public function getProductDetail($idproduct) {
        $result = new \stdClass();
        $result->isrole = $this->isuserallow(self::ROLE_PRODUCT, self::class);

        if ($idproduct !== 0) {
            $result->product = $this->getRecord('SELECT product.createon,product.idproduct,product.keycode,product.lastmodifiedon,product.name,product.inactive,product.idproject,product.srcidproduct,product.issales,product.ispurchase,product.tags,productdetail.productdescription,productdetail.productmanifest,productdetail.saleprice,productdetail.idcurrency FROM product LEFT JOIN productdetail USING ( idproduct ) WHERE product.deleted = 0 AND product.idproduct = ?', (int) $idproduct);
            if (!isset($result->product->productdescription)) {
                $result->product->productdescription = '';
            }
            if (!isset($result->product->productmanifest)) {
                $result->product->productmanifest = '';
            }
            if (!isset($result->product->saleprice)) {
                $result->product->saleprice = 0;
            }
            if (!isset($result->product->idcurrency)) {
                $result->product->idcurrency = '';
            }
        } else {
            $result->product = new \stdClass();
            $result->product->idproduct = $idproduct;
            $result->product->createon = \model\utils::offsetDateTime(new \DateTime('now'), \model\env::getTimezone());
            $result->product->lastmodifiedon = \model\utils::offsetDateTime(new \DateTime('now'), \model\env::getTimezone());
            $result->product->inactive = false;
            $result->product->keycode = '';
            $result->product->name = '';
            $result->product->issales = true;
            $result->product->ispurchase = true;
            $result->product->productdescription = '';
            $result->product->productmanifest = '';
            $result->product->saleprice = 0;
            $result->product->idproject = 0;
            $result->product->idcurrency = $this->getDefaultCurrency();
            $result->product->tags = '';
        }

        if (!isset($result->product->idcurrency)) {
            $result->product->idcurrency = $this->getDefaultCurrency();
        }

        $result->currencies = $this->getCurrencies();

        return $result;
    }

    public function setProduct($idproduct, $updateproduct) {
        if (!$this->isuserallow(self::ROLE_PRODUCT, self::class))
            return false;

        $product = $this->getRecord('SELECT idproduct,keycode,name,idproject,srcidproduct FROM product WHERE deleted = 0 AND idproduct = ?', (int) $idproduct);
        $settags = false;
        $tags = \explode(',', $updateproduct->tags);

        $this->startTransaction();
        //insert
        if ($idproduct === 0 & !isset($product)) {
            $idproduct = $this->executeSql('INSERT INTO product (keycode, name, issales, ispurchase, inactive, deleted, idproject,tags,iduser,username) VALUES (?,?,?,?,?,?,?,?,?,?)', (string) $updateproduct->keycode, (string) $updateproduct->name, \model\utils::formatBooleanToInt($updateproduct->issales), \model\utils::formatBooleanToInt($updateproduct->ispurchase), 0, 0, 0, $updateproduct->tags, \model\env::getIdUser(), \model\env::getUserName());
            if ($idproduct > 0)
                $this->executeSql('INSERT INTO productdetail (idproduct,productdescription,productmanifest,idcurrency,saleprice,iduser,username) VALUES (?,?,?,?,?,?,?)', $idproduct, (string) $updateproduct->productdescription, (string) $updateproduct->productmanifest, (string) $updateproduct->idcurrency, (float) $updateproduct->saleprice, \model\env::getIdUser(), \model\env::getUserName());

            $settags = true;
        }
        //update  
        if ($idproduct > 0 & isset($product)) {
            // do not update product for shared projects
            if ($product->idproject === 0)
                $this->executeSql('UPDATE product SET keycode = ?, name = ?, inactive = ?, issales = ?, ispurchase = ?,tags = ?,iduser = ?,username = ? WHERE idproduct = ?', (string) $updateproduct->keycode, (string) $updateproduct->name, \model\utils::formatBooleanToInt($updateproduct->inactive), \model\utils::formatBooleanToInt($updateproduct->issales), \model\utils::formatBooleanToInt($updateproduct->ispurchase), $updateproduct->tags, \model\env::getIdUser(), \model\env::getUserName(), (int) $idproduct);

            if ($product->idproject > 0)
                $this->executeSql('UPDATE product SET inactive = ?, issales = ?,tags = ?,iduser = ?,username = ? WHERE idproduct = ?', \model\utils::formatBooleanToInt($updateproduct->inactive), \model\utils::formatBooleanToInt($updateproduct->issales), $updateproduct->tags, \model\env::getIdUser(), \model\env::getUserName(), (int) $idproduct);

            $this->executeSql('UPDATE productdetail SET productdescription = ?,productmanifest = ?,idcurrency = ?,saleprice = ?,iduser = ?,username = ? WHERE idproduct = ?', (string) $updateproduct->productdescription, (string) $updateproduct->productmanifest, (string) $updateproduct->idcurrency, (float) $updateproduct->saleprice, \model\env::getIdUser(), \model\env::getUserName(), (int) $idproduct);
            $settags = true;
        }

        // tags
        $this->executeSql('DELETE FROM producttag WHERE idproduct = ?', (int) $idproduct);
        if ($settags & count($tags) > 0) {
            $sqlline = 'INSERT INTO producttag (idtag,idproduct) VALUES ';
            $sqlparms = [];
            foreach ($tags as $tag) {
                if (count($sqlparms) > 0)
                    $sqlline .= ',';

                $sqlline .= '(?,?)';
                array_push($sqlparms, $tag, (int) $idproduct);
            }
            $this->executeSql($sqlline, $sqlparms);
        }

        $this->endTransaction();

        if (isset($_FILES["upfile"]) && $_FILES["upfile"]["error"] === 0) {
            $filename = $idproduct . '.png';
//            $filetype = $_FILES["upfile"]["type"];
            $filesize = $_FILES["upfile"]["size"];
            $tempname = $_FILES["upfile"]["tmp_name"];

            $this->_uploadImgProduct($filename, $filesize, $tempname);
        }

        (new \model\action($this->src))->addSystemNote(\model\lexi::get('g3ext/market', 'sys053', $updateproduct->name, $updateproduct->keycode));

// flag for linked projects
        (new \model\action($this->src))->setSharedProjForRefresh(\model\env::MODULE_PRODUCTS, true);
    }

    private function _uploadImgProduct($filename, $filesize, $tempname) {
        $allOk = true;
// Verify file size - 5MB maximum
        $maxsize = 5 * 1024 * 1024;
        if ($filesize > $maxsize) {
            \model\message::render(\model\lexi::get('g3ext/market', 'sys054'));
            $allOk = false;
        }

        if (!$allOk)
            return;

        $folderpath = DATA_PATH . "/attach/" . $this->src->idproject . "/";
        if (!is_dir($folderpath)) {
            mkdir($folderpath, 0777, true);
        }
        $folderpath .= "products/";
        if (!is_dir($folderpath)) {
            mkdir($folderpath, 0777, true);
        }

        $targetname = $folderpath . $filename;
        $thumb_width = 70;
        $thumb_height = 70;

// resize image
        list($width, $height, $type) = getimagesize($tempname);
        switch (strtolower(image_type_to_mime_type($type))) {
            case 'image/gif':
                $NewImage = \imagecreatefromgif($tempname);
                break;
            case 'image/png':
                $NewImage = \imagecreatefrompng($tempname);
                break;
            case 'image/jpeg':
                $NewImage = \imagecreatefromjpeg($tempname);
                break;
            default:
                \model\message::render(\model\lexi::get('g3ext/market', 'sys055'));
                return false;
//                break;
        }

        $r = $width / $height;
        if ($thumb_width / $thumb_height > $r) {
            $newwidth = $thumb_height * $r;
            $newheight = $thumb_height;
        } else {
            $newheight = $thumb_width / $r;
            $newwidth = $thumb_width;
        }

        $thumb = \imagecreatetruecolor($thumb_width, $thumb_height);
        imagealphablending($thumb, false);
        imagesavealpha($thumb, true);
        if (imagecopyresampled($thumb, $NewImage, 0, 0, 0, 0, $thumb_width, $thumb_height, $width, $height))
            if (imagepng($thumb, $targetname, 5))
                return false;
    }

    public function deleteProduct($idproduct) {
        if (!$this->isuserallow(self::ROLE_PRODUCT, self::class))
            return false;

        $product = $this->getRecord('SELECT idproduct,keycode,name,idproject,srcidproduct FROM product WHERE deleted = 0 AND idproduct = ?', (int) $idproduct);
        if (isset($product)) {
            $this->startTransaction();
            $this->executeSql('UPDATE product SET deleted = 1 WHERE idproduct = ?', (int) $idproduct);
            $this->executeSql('DELETE FROM producttag WHERE idproduct = ?', (int) $idproduct);
            $this->endTransaction();

            (new \model\action($this->src))->addSystemNote(\model\lexi::get('g3ext/market', 'sys056', $product->name, $product->keycode));

// flag for linked projects
            (new \model\action($this->src))->setSharedProjForRefresh(\model\env::MODULE_PRODUCTS, true);
        }
    }

    public function updateLinkedProductData($src_idproject, $modulename) {
        if (!$this->isuserallow(\model\project::ROLE_SHAREDATA, self::class))
            return false;

        $modelmarket = new \model\ext\market\market(\model\env::src($src_idproject));

        $from_dataset = $modelmarket->getProductsSales();
//flag as non-assigned
        foreach ($from_dataset as $row)
            $row->idproject = "0";

// get local data to be updated
        $to_dataset = $modelmarket->getAllProducts();
        $filters = \model\utils::filter($to_dataset, '$v->idproject === ' . $src_idproject);

        $this->startTransaction();
        foreach ($filters as $row) {
//find record in source
            $results = \model\utils::filter($from_dataset, '$v->idproduct === ', $row->srcidproduct);
// not found, remove
            if (count($results) === 0) {
                $this->executeSql('UPDATE product SET deleted = 1 WHERE idproduct = ?', (int) $row->idproduct);
                $this->executeSql('DELETE FROM producttag WHERE idproduct = ?', (int) $row->idproduct);
            }

            foreach ($results as $result) {
// only update when changes (update, otherwise skip)save a trip to DB)
                if ($row->inactive)
                    $result->inactive = true;

                $this->executeSql('UPDATE product SET keycode = ?, name = ?, inactive = ?, deleted = ?, idproject = ?, srcidproduct = ? WHERE idproduct = ?', (string) $result->keycode, (string) $result->name, \model\utils::formatBooleanToInt($result->inactive), 0, (int) $src_idproject, (int) $row->srcidproduct, (int) $result->idproduct);
//flag record assigned 
                $result->idproject = $src_idproject;
            }
        }

//get all non-assigned yet; and add record
        $filters = \model\utils::filter($from_dataset, '$v->idproject === 0');
        foreach ($filters as $row) {
//insert only if not found
            $result = \model\utils::firstOrDefault($to_dataset, '$v->srcidproduct === ' . $row->idproduct);
            if (!isset($result))
                $this->executeSql('INSERT INTO product (keycode, name, inactive, deleted, idproject, issales, ispurchase, srcidproduct) VALUES (?, ?, ?, ?, ?, ?, ?, ?)', (string) $row->keycode, (string) $row->name, \model\utils::formatBooleanToInt($row->inactive), 0, (int) $src_idproject, \model\utils::formatBooleanToInt($row->issales), 1, (int) $row->idproduct);
        }
        $this->endTransaction();
        
        (new \model\action(\model\env::src($src_idproject)))->setSharedProjForRefresh($modulename, false);
    }

    public function inactiveLinkedProductData($src_idproject) {
        if (!$this->isuserallow(\model\project::ROLE_SHAREDATA, self::class))
            return false;

        $this->executeSql('UPDATE product SET deleted = 1 WHERE idproject = ?', (int) $src_idproject);
    }

}
