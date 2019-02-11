<?php

namespace model;

final class env {

    const COMMENT_SYS = "SYS";
    const COMMENT_USER = "USER";
    const CONFIG_CORE = 'g3';
    const CONFIG_ACTIONS = 'g3actions';
    // Shared modules id
    const MODULE_GATE = 'gate';
    const MODULE_PRIORITY = 'priority';
    const MODULE_CATEGORY = "category";
    const MODULE_CURRENCY = "currency";
    const MODULE_CURRENCYCONV = "currencyconv";
    const MODULE_TRACK = "track";
    const MODULE_PRODUCTS = 'products';
    const MODULE_PERIOD = 'period';
    const MODULE_PAYTYPE = 'paytype';
    const MODULE_BUDGETGROUP = 'budgetgroup';
    const MODULE_BUDGETBOOK = 'budgetbook';
    const MODULE_ROLE = 'role';
    const MODULE_LANGUAGE = 'language';
    const MODULE_THEME = 'theme';
    const MODULE_FILTERVIEW = 'filterview';

//    const MODULE_GROUPS = "groups";
//    const MODULE_USERS = "users";
//    const MODULE_INVITATIONS = "invitations";
//    const MODULE_FINANCIAL = 'financial';
//    const MODULE_SALES = 'sales';

    private static $idproject = 0;
    private static $user_accessid = 0; // level access: 1=internal, 2=external, 3=public
    private static $app_iduser = 0;
    private static $app_useremail;
    private static $app_username;
    private static $app_keyname; // nickname used with prefix @
    private static $app_theme; // empty = default theme
    private static $app_useridproject = 0;
    // config data
    //**********************************************
    private static $cacheconfigpath;
    private static $DATA;

    public static function getConfig($section, $packagename = null, $idproject = 0) {
        if (!isset($packagename))
            $packagename = self::CONFIG_CORE;

        $filepath = \model\utils::format('{0}/config/{1}.json', DATA_PATH, $packagename);            
    // check for project custom confg
        if($idproject > 0) {
            $filepathback = \model\utils::format('{0}/attach/{1}/config/{2}.json', DATA_PATH, $idproject, $packagename);
            if(is_file($filepathback))
                $filepath = $filepathback;
        }
        
        // set cache 
        if (self::$cacheconfigpath !== $filepath) {
            $jconfig = file_get_contents($filepath);
            self::$DATA = json_decode($jconfig);
            self::$cacheconfigpath = $filepath;
        }

        // confirm data exist
        if (!isset(self::$DATA)) {
            $mssg = \model\utils::format('Unknown config package: {0}', $packagename);
            error_log('*** g3 config: ' . $mssg);
            \model\message::render($mssg);
        }
        // confirm section exist
        if (isset($section) && !isset(self::$DATA->$section)) {
            // always check for data path location        
            $mssg = \model\utils::format('Unknown config section: {0}, at package: {1}', $section, $packagename);
            error_log('*** g3 config: ' . $mssg);
            \model\message::render($mssg);
        }

        return self::$DATA->$section ?? new \stdClass();
    }

    // user
    //**********************************************
    public function getUserSession() {
        $token = null;
        if (filter_input(INPUT_GET, 'tokenid') !== null) {
        // LOGIN token ******************
            // security passed from URL
            $token = filter_input(INPUT_GET, 'tokenid');

            if (isset($token)) {
                try {
                    $jsondecoded = \Firebase\JWT\JWT::decode($token, self::getKey(), ['HS256']);
                    (new \model\user)->registerTokenUser($jsondecoded->email, $jsondecoded->logonservice, $jsondecoded->loginid);
                } catch (\Firebase\JWT\ExpiredException $vexc) {
                    \model\message::severe('sys004', $vexc->getMessage());
                } catch (Exception $exc) {
                    \model\message::severe('sys004', $exc->getMessage());
                }
            }
        }

        // retrieve email name ******************
        if (filter_input(INPUT_COOKIE, 'g3links') !== null) {
            $tks = explode('.', filter_input(INPUT_COOKIE, 'g3links'));
            list($headb64, $bodyb64, $cryptob64) = $tks;
            $payload = \Firebase\JWT\JWT::jsonDecode(\Firebase\JWT\JWT::urlsafeB64Decode($bodyb64));
            self::setUserEmail($payload->useremail);
        }

        //validate user access
        if (!self::isauthorized() && filter_input(INPUT_COOKIE, 'g3links') !== null) {
            try {
                $jsondecoded = \Firebase\JWT\JWT::decode(filter_input(INPUT_COOKIE, 'g3links'), self::getKey(), ['HS256']);

                if (\model\utils::get_remote_addr() === $jsondecoded->remoteip) {
                    self::setUser($jsondecoded->iduser);
                    self::setUserEmail($jsondecoded->useremail);
                } else {
                    self::closelogon();
                }
            } catch (\Firebase\JWT\ExpiredException $vexc) {
                //ignore
            } catch (Exception $exc) {
                //ignore
            }
        }
    }

    public static function isauthorized() {
        if (!(self::$app_iduser > 0))
            return false;

        if (empty(self::getUserEmail()))
            return false;

        return true;
    }

    public static function resetlogon() {
        self::$app_iduser = 0;
        \model\utils::unsetCookie('g3links');
    }

    public static function closelogon() {
        self::$app_iduser = 0;

        $tks = explode('.', filter_input(INPUT_COOKIE, 'g3links'));
        list($headb64, $bodyb64, $cryptob64) = $tks;
        $payload = \Firebase\JWT\JWT::jsonDecode(\Firebase\JWT\JWT::urlsafeB64Decode($bodyb64));

        self::setUserEmail($payload->useremail);

        // shotdown access
        $data = ['exp' => time() - 1, "iduser" => $payload->iduser, "useremail" => $payload->useremail];
        \model\utils::setCookie('g3links', \Firebase\JWT\JWT::encode($data, self::getKey()));
    }

    public static function setUser($iduser) {
        $user = (new \model\user)->getuser($iduser);

        if (!isset($user))
            return;

        self::$app_iduser = $iduser;
        self::$app_useremail = trim($user->email);
        self::$app_username = trim($user->name);
        self::$app_keyname = trim($user->keyname);
        self::$app_theme = $user->theme;

        self::$app_useridproject = $user->idproject;
        if ($user->idproject > 0)
            return;

        //create project for user
        $newproj = new \stdClass();
        $newproj->title = self::$app_username;
        $newproj->description = '';
        $newproj->prefix = '';
        $newproj->ticketseq = 0;
        $newproj->remoteurl = '';
        $newproj->startuppath = '';
        $newproj->startupwidth = 0;
        $newproj->ispublic = false;
        $newproj->marketname = '';

        self::$app_useridproject = (new \model\project)->insertproject($newproj);
        (new \model\project)->setuseridproject(self::$app_useridproject);
    }

    public static function getUserIdProject() {
        return self::$app_useridproject;
    }

    public static function src($idproject = 0) {
        $srcp = new \stdClass();
        $srcp->idproject = $idproject;

        return $srcp;
    }

    public static function getUserName() {
        return self::$app_username ?? '';
    }

    public static function setUserEmail($email) {
        self::$app_useremail = $email;
    }

    public static function getUserEmail() {
        return self::$app_useremail ?? '';
    }

    public static function getIdUser() {
        if (isset(self::$app_iduser))
            return self::$app_iduser;

        return 0;
    }

    public static function getUserAccessId($idproject) {
        self::$idproject = $idproject;
        self::$user_accessid = 0;

        if ($idproject > 0 && isset(self::$app_iduser))
            self::$user_accessid = (new \model\project)->getUserAccessId($idproject, self::$app_iduser);

        return self::$user_accessid;
    }

    public static function getUserTheme() {
        if (!isset(self::$app_theme) || self::$app_theme === '')
            return '';

        return '_' . self::$app_theme;
    }

    // security token
    //**********************************************
    public static function getUserToken() {
        if (!self::isauthorized())
            return null;

        // 20 min before expire
        $data = ['exp' => time() + 1200, "iduser" => self::$app_iduser, "useremail" => self::$app_useremail];
        return \Firebase\JWT\JWT::encode($data, self::getKey());
    }

    public static function getHtmlUserToken() {
        $token = self::getUserToken();
        if (!isset($token))
            return '';

        return \model\utils::format('<input type="hidden" name="_token" value="{0}" >', $token);
    }

    public static function validateUserToken($token) {
        $sucess = false;
        $errormssg = \model\lexi::get('', 'msg004');

        if (isset($token) && self::isauthorized()) {
            try {
                $jsondecoded = \Firebase\JWT\JWT::decode($token, self::getKey(), ['HS256']);
                if ($jsondecoded->iduser === self::$app_iduser && $jsondecoded->useremail === self::$app_useremail)
                    $sucess = true;
            } catch (\Firebase\JWT\ExpiredException $vexc) {
                $errormssg .= ', ' . $vexc->getMessage();
            } catch (Exception $exc) {
                $errormssg .= ', ' . $exc->getMessage();
            }
        }

        if (!$sucess)
            \model\message::severe('sys004', $errormssg);
    }

    public static function saveSession($iduser, $useremail) {
        self::setUser($iduser);

        $remote_addr = \model\utils::get_remote_addr();
            
        // 5 days expire session
        $data = ['exp' => time() + 432000, "iduser" => $iduser, "useremail" => \trim($useremail), "remoteip" => $remote_addr];
        \model\utils::setCookie('g3links', \Firebase\JWT\JWT::encode($data, self::getKey()));
    }

    // user level access security
    //**********************************************
    private static $userrole;
    private static $security_loaded = false;
    private static $securiylevels;
    private static $lastprojectuploaded = 0;

    public static function isUserAllow($idproject, $seccode, $source = null) {
        if (!isset($idproject) || $idproject === 0)
            return false;

        if (!(new \model\project)->isUserActiveProject($idproject))
            return false;

        if (self::$idproject !== $idproject)
            self::getUserAccessId($idproject);

        $result = self::_isuserallow($idproject, self::$app_iduser, $seccode, self::$user_accessid);

        if (isset($source) && !$result)
            \model\message::render(\model\lexi::get('', 'msg053', $seccode), $source);

        return $result;
    }

    private static function _isuserallow($idproject, $iduser, $seccode, $idaccess = 0) {
        if (!self::$security_loaded) {
//get generic security            
            self::$securiylevels = (new \model\project)->getSecurityLevels();
//get user security            
            self::$userrole = (new \model\project)->getuseridrole($idproject);

            self::$security_loaded = true;
        }

//get custom project security
        if ($idproject > 0 && $idproject !== self::$lastprojectuploaded) {
            self::$lastprojectuploaded = $idproject;

            //update user security
            $projectsecuriylevels = (new \model\action(\model\env::src($idproject)))->getSecurityLevels();
            foreach ($projectsecuriylevels as $projectsecuriylevel) {
                $result = \model\utils::firstOrDefault(self::$securiylevels, \model\utils::format('$v->seccode === "{0}"', $projectsecuriylevel->seccode));
                if (isset($result)) {
                    $result->idrole = $projectsecuriylevel->idrole;
                }
            }
        }

        $result = \model\utils::firstOrDefault(self::$securiylevels, \model\utils::format('$v->seccode === "{0}"', $seccode));
        if (!isset($result))
            return false;

        if ($result->idaccess < $idaccess || $idaccess === 0)
            return false;

        return self::$userrole <= $result->idrole;
    }

    // Time zone
    //**********************************************
    public static function setTimezone($timezone) {
        \model\utils::setCookie('timezone', $timezone);
    }

    public static function getTimezone() {
        if (filter_input(INPUT_COOKIE,'timezone') !== null)
            return filter_input(INPUT_COOKIE,'timezone');

        return '0';
    }

    // language and country
    //**********************************************
    private static $app_country = 'US';
    private static $app_language = 'en';

    public static function setCacheLang() {
        if (filter_input(INPUT_COOKIE,'lang') !== null) {
            self::$app_language = \model\lexi::getLang();
            self::$app_country = \model\lexi::getLangCountry();
        }
    }

    public static function setLang($lang = null) {
        if (isset($lang)) {
            $parsedata = \explode('-', $lang);
            if (count($parsedata) > 0)
                self::$app_language = (new \model\project)->getLang($parsedata[0])->idlang ?? self::$app_language;

            if (count($parsedata) > 1)
                self::$app_country = $parsedata[1] ?? self::$app_country;
        }

        \model\utils::setCookie('lang', self::$app_language . '-' . self::$app_country);
    }

//    public static function getLang() {
//        if (filter_input(INPUT_COOKIE,'lang') !== null)
//            return filter_input(INPUT_COOKIE,'lang');
//
//        return self::$app_language . '-' . self::$app_country;
//    }
//    public static function getLangCode() {
//        $idlanguage = self::$app_language;
//        $parsedata = \explode('-', self::getLang());
//        if (isset($parsedata[0]))
//            $idlanguage = $parsedata[0];
//
//        return $idlanguage;
//    }
//    public static function getLangCountryCode() {
//        $idcountry = self::$app_country;
//        $parsedata = \explode('-', self::getLang());
//        if (isset($parsedata[1]))
//            $idcountry = $parsedata[1];
//
//        return $idcountry;
//    }

    public static function getMaxRecords($section) {
        $config = self::getConfig('maxrecords');
        return (int) ($config->$section ?? 50);
    }

    public static function getKey() {
        return self::getConfig('token')->key;
    }

    // session ******************************
    private static $session_idproject = 0;

    public static function session_idproject() {
        return self::$session_idproject;
    }

    private static $session_idtaskselected = 0;

    public static function session_idtaskselected() {
        return self::$session_idtaskselected;
    }

    private static $session_lastviewgate = 0;

    public static function session_lastviewgate() {
        return self::$session_lastviewgate;
    }

    public static function session_src() {
        $srcp = new \stdClass();
        $srcp->iduser = self::$app_iduser;
        $srcp->idproject = self::$session_idproject;
        return $srcp;
    }

    public static function startAuth() {
        // is user active
        if (!self::isauthorized()) {
            $callback = filter_input(INPUT_SERVER, 'REQUEST_URI');
            require \model\route::script('login/index.php');
            die();
        }

        self::$session_idproject = 0;
        if (filter_input(INPUT_GET, 'idproject') !== null) {
            //issue when passed by code.... its return null,
            self::$session_idproject = filter_input(INPUT_GET, 'idproject');
            if (!isset(self::$session_idproject))
                self::$session_idproject = $_GET['idproject'];

            self::$session_idproject = (int) self::$session_idproject;
        }

        if (filter_input(INPUT_POST, 'idproject') !== null)
            self::$session_idproject = (int) filter_input(INPUT_POST, 'idproject');

        self::$session_idtaskselected = 0;
        if (filter_input(INPUT_GET, 'idtask') !== null)
            self::$session_idtaskselected = (int) filter_input(INPUT_GET, 'idtask');

        if (filter_input(INPUT_POST, 'idtask') !== null)
            self::$session_idtaskselected = (int) filter_input(INPUT_POST, 'idtask');

        self::$session_lastviewgate = 0;
        if (filter_input(INPUT_GET, 'idgate') !== null)
            self::$session_lastviewgate = (int) filter_input(INPUT_GET, 'idgate');

        if (filter_input(INPUT_POST, 'idgate') !== null)
            self::$session_lastviewgate = (int) filter_input(INPUT_POST, 'idgate');

        if (self::$session_lastviewgate === 0)
            self::$session_lastviewgate = (new \model\action(self::session_src()))->getDefaultGate();
    }

    // email 
    //*****************************************
    public static function sendErroremail($source, $messageInfo) {
        $mailsettigs = self::getConfig('emailerror');

        $messageArray[] = $messageInfo;
        self::sendMail("G3 administrator", $mailsettigs->email, $source, $messageArray);
    }

    public static function sendMail($membername, $memberemail, $subject, $mailbodyArray) {
        $mssgError = (new \model\mailer)->sendMail(self::getConfig('mailer'), $membername, $memberemail, $subject, $mailbodyArray);
        if (isset($mssgError))
            \model\message::render(\model\lexi::get('', 'msg074', $mssgError));
    }

}
