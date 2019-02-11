<?php

namespace model;

class user extends \model\dbconnect {

    public function __construct() {
        $this->src = \model\env::src(0);
        parent::__construct(\model\env::CONFIG_CORE);
    }

    public function getProfileSession($iduser = null) {
        $result = new \stdClass();
        $result->user = $this->getuser($iduser);

        if ($result->user->theme === '') {
            $result->user->theme = '1';
        }
        $result->theme = (new \model\project)->getTheme((int) $result->user->theme);

        return $result;
    }

    public function getUserProfile($iduser = null) {
        $result = new \stdClass();
        $result->user = $this->getuser($iduser);

        if ($result->user->theme === '') {
            $result->user->theme = '1';
        }
        $result->themes = (new \model\project)->getThemes();

        return $result;
    }

    public function getuser($iduser = null) {
        if (!isset($iduser))
            $iduser = \model\env::getIdUser();

        return $this->getRecord('SELECT iduser,lastaccesson,deleted,isvalidated,email,name,keyname,theme,idproject FROM user WHERE iduser = ?', (int) $iduser);
    }

    public function insertUser($email, $username, $pwdnew, $pwdnew1, $keyname = '', $theme = '') {
        // validate
        if (!isset($email) || empty($email) || !isset($username) || empty($username)) {
            \model\message::render($this->setMessage($email . ': ' . \model\lexi::get('', 'sys043')));
            return false;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            \model\message::render($email . ': ' . \model\lexi::get('', 'sys043'));
            return false;
        }

        $uk = '';
        if ($pwdnew !== $pwdnew1) {
            \model\message::render(\model\lexi::get('', 'sys030'));
            return false;
        }

        $uk = $pwdnew;

        $user = $this->getuserByEmail($email);
        if (isset($user)) {
            \model\message::render(\model\lexi::get('', 'sys031', $email));
            return false;
        }

        // create
        $email = \strtolower($email);
        //clean keyname
        $keyname = \strtolower($keyname);
        $keyname = str_replace('@', '', $keyname);
        $keyname = str_replace(' ', '', $keyname);

        $lastInsertId = $this->executeSql('INSERT INTO user (email, name, keyname, theme, idproject) VALUES (?, ?, ?, ?, ?)', trim((string) $email), trim((string) $username), trim((string) $keyname), trim((string) $theme), 0);
        if (!isset($lastInsertId))
            return false;

        $user = $this->getuserByEmail($email);
        if (!isset($user))
            \model\env::sendErroremail('login', $email . ': not found');
        return false;

        // send welcome email
        $filename = \model\route::render('g3/*/regwelcome.html');

        $emailstring = [];
        $lines = file($filename);
        foreach ($lines as $line) {
            $line = str_replace('[membername]', $username, $line);
            $line = str_replace('[email]', $email, $line);
            $emailstring[] = $line;
        }
        \model\env::sendMail($username, $email, \model\lexi::get('', 'sys045'), $emailstring);

        return $lastInsertId;
    }

    public function getuserByEmail($email) {
        return $this->getRecord('SELECT iduser,name FROM user WHERE email = ?', (string) $email);
    }

    public function isActive($iduser = null) {
        if (!isset($iduser))
            $iduser = \model\env::getIdUser();

        $result = $this->getRecords('SELECT count(*) AS result FROM user WHERE iduser = ? AND isvalidated = ? AND deleted = ?', (int) $iduser, 0, 0);
        return ($result->result ?? 0) ? true : false;
    }

    public function setActiveAccount() {
        $this->executeSql('UPDATE user SET deleted = ? WHERE iduser = ? AND email = ? AND isvalidated = ?', 0, (int) \model\env::getIdUser(), (string) \model\env::getUserEmail(), 0);

        if (!$this->isActive()) {
            \model\message::render(\model\lexi::get('', 'sys070', \model\env::getUserEmail()));
            return false;
        }

        //active user each project
        $projects = (new \model\project)->getprojects();
        foreach ($projects as $project)
            (new \model\action(\model\env::src($project->idproject)))->setActiveUser();

        // send message to user
        $emailsubject = \model\lexi::get('', 'sys021');

        $emailstring = [];
        $filename = \model\route::render('g3/*/accounttatus.html');

        $lines = file($filename);
        foreach ($lines as $line) {
            $line = str_replace('[memberusername]', \model\env::getUserName(), $line);
            $line = str_replace('[userstatus]', $emailsubject, $line);
            $emailstring[] = $line;
        }

        \model\env::sendMail(\model\env::getUserName(), \model\env::getUserEmail(), $emailsubject, $emailstring);
    }

    public function getinvitationsbyEmail() {
        return $this->getRecords('SELECT projectinvitation.idprojectinv,projectinvitation.idrole,projectinvitation.idproject,project.title FROM projectinvitation JOIN project USING ( idproject ) WHERE projectinvitation.useremailinvited = ?', \trim((string) \model\env::getUserEmail()));
    }

    public function search($search, $take) {
        $search = \model\utils::getSearchText($search);        
        return $this->getRecords('SELECT name,email,iduser FROM user WHERE ( name LIKE ? OR email LIKE ? ) AND deleted = ? AND isvalidated = ? LIMIT ?', $search, $search, 0, 0, (int) $take);
    }

    public function getpeople($search, $take) {
        $search = '%' . \trim((string) $search) . '%';
        return $this->getRecords('SELECT name,email FROM user WHERE name LIKE ? OR email LIKE ? LIMIT ?', $search, $search, (int) $take);
    }

    public function getuserByToken($idToken, $logonservice, $useremail) {
        if (!isset($idToken) || empty($idToken))
            return null;

        return $this->getRecord('SELECT user.iduser,user.lastaccesson,user.deleted,user.isvalidated,user.email,user.name,user.keyname FROM user LEFT JOIN userprovider USING ( iduser ) WHERE userprovider.token = ? AND userprovider.idprovider = ? AND user.email = ?', \trim((string) $idToken), \trim((string) $logonservice), (string) $useremail);
    }

    public function getUserLogonByEmail($token, $logonservice, $useremail = null) {
        if (!isset($useremail) || empty($useremail))
            return null;

        //*****************************
        //try to find by email before adding user
        //*****************************
        $user = $this->getRecord('SELECT iduser,lastaccesson,deleted,isvalidated,email,name,keyname,theme FROM user WHERE email = ?', \trim(\strtolower($useremail)));
        if (!isset($user->iduser))
            return null;

        $user->accountdonotmatch = false;
        $user->needauth = false;
        $user->issend = false;

        //*****************************
        //is token available
        //*****************************
        if (!($user->isvalidated || $user->deleted)) {

            $var_token = $this->getRecord('SELECT token FROM userprovider WHERE iduser = ? AND idprovider = ?', (int) $user->iduser, (string) $logonservice);
            // token exist and mismatch
            if (isset($var_token) && $token !== $var_token->token)
                $user->accountdonotmatch = true;

            // token availabble
            if (!isset($var_token)) {
                $user->needauth = true;
                //check if already waiting for auth
                $needauth = $this->_getAuth($user->iduser, $logonservice);
                if (!isset($needauth->isauth)) {
                    //neeed create auth
                    $this->executeSql('INSERT INTO needauth (iduser, provider, isauth, issend) VALUES (?, ?, ?, ?)', (int) $user->iduser, trim((string) $logonservice), 0, 0);
                } else {
                    $user->issend = $needauth->issend;

                    if ($needauth->isauth)
                        $user->needauth = false;
                }
            }
        }

        return $user;
    }

    public function isAuthorizationConfirm($iduser, $provider) {
        $needauth = $this->_getAuth($iduser, $provider);
        if (isset($needauth)) {
            if ($needauth->isauth) {
                $this->_deleteAuthUser($iduser, $provider);
            } else {
                return false;
            }
        }

        return true;
    }

    private function _getAuth($iduser, $provider) {
        return $this->getRecord('SELECT isauth, issend FROM needauth WHERE iduser = ? and provider = ?', (int) $iduser, \trim((string) $provider));
    }

    private function _deleteAuthUser($iduser, $provider) {
        $this->executeSql('DELETE FROM needauth WHERE iduser = ? AND provider = ?', (int) $iduser, trim((string) $provider));
    }

    private function _registeruser($useremail, $logonservice, $logintoken) {
        $data = ['pr' => $logonservice, 'email' => $useremail];
        $loginid = \Firebase\JWT\JWT::encode($data, $logintoken);

        $user = $this->getuserByToken($loginid, $logonservice, $useremail);
        return isset($user);
    }
    
    private function _updatetoken($iduser, $token, $logonservice, $email = null) {
        if (empty($token)) {
            $this->executeSql('DELETE FROM userprovider WHERE iduser = ? AND idprovider = ?', (int) $iduser, (string) $logonservice);
            return;
        }

        $result = $this->getRecord('SELECT userprovider.iduser FROM userprovider WHERE userprovider.iduser = ? and userprovider.idprovider = ?', (int) $iduser, \trim((string) $logonservice));

        if (isset($result))
            $this->executeSql('UPDATE userprovider SET token = ? WHERE iduser = ? AND idprovider = ?', \trim((string) $token), (int) $iduser, (string) $logonservice);

        if (!isset($result))
            $this->executeSql('INSERT INTO userprovider (iduser, idprovider, token, createdon) VALUES (?, ?, ?, ?)', trim((int) $iduser), trim((string) $logonservice), trim((string) $token), \model\utils::forDatabaseDateTime(new \DateTime()));

        if (isset($email))
            $this->executeSql('UPDATE user set email = ? WHERE iduser = ?', \trim((string) $email), (int) $iduser);
    }

    private function _registerEmail($useremail, $logonservice, $logintoken) {
        try {
            $jsondecoded = \Firebase\JWT\JWT::decode(filter_input(INPUT_COOKIE, 'g3links'), \model\env::getKey(), ['HS256']);

            try {
                $data = ['pr' => $logonservice, 'email' => $useremail];
                $providertoken = \Firebase\JWT\JWT::encode($data, $logintoken);
                $this->changeUserToken($jsondecoded->iduser, $logonservice, $providertoken, $useremail);

                try {
//                    // keep new info for future sessions
                    (new \model\env)->saveSession($jsondecoded->iduser, $useremail);
                } catch (Exception $excS) {
                    \model\env::sendErroremail('error encode session: ' . $jsondecoded->useremail, $excS->getMessage() . ', public key');
                    \model\message::render($jsondecoded->useremail . ', session cannot be registered.');
                    return;
                }
            } catch (Exception $excC) {
                \model\env::sendErroremail('update profile, error encode credentials: ' . $jsondecoded->useremail, $excC->getMessage() . ', public key');
                \model\message::render($jsondecoded->useremail . ', credentials cannot be registered.');
                return;
            }
        } catch (\Firebase\JWT\ExpiredException $vexc) {
            \model\message::render(\model\lexi::get('', 'msg017', $vexc->getMessage()), 'login', true);
        } catch (Exception $excSd) {
            \model\env::sendErroremail('update email, error decode session: ' . \model\env::getUserEmail(), $excSd->getMessage() . ', public key');
            \model\message::render(\model\env::getUserEmail() . ', cannot decode session credentials.');
            return;
        }
    }
    
    private function _authEmail($iduser, $provider, $name, $email, $providertoken) {
        // 24 hours before exprire
        $config = \model\env::getConfig('api');
        $data = ['exp' => time() + 86400, 'iduser' => $iduser, "provider" => $provider, "email" => $email, "loginid" => $providertoken];
        $jwttoken = \Firebase\JWT\JWT::encode($data, \model\env::getKey());

        $token = \model\utils::format('{0}/{1}/{2}', ROOT_APP, $config->url, \model\route::url('auth.php?tokenauth={0}', $jwttoken));

        $filename = \model\route::render('g3/*/regauthorization.html');

        $emailstring = array();
        $lines = file($filename);
        foreach ($lines as $line) {
            $line = str_replace('[membername]', $name, $line);
            $line = str_replace('[provider]', LOGINSRVNAME, $line);
            $line = str_replace('[token]', $token, $line);
            $emailstring[] = $line;
        }

        \model\env::sendMail($name, $email, \model\lexi::get('', 'sys024'), $emailstring);
    }
    
    public function resetUserPassword($iduser, $logonservice) {
        $this->_updatetoken($iduser, '', $logonservice);
        $this->_deleteAuthUser($iduser, $logonservice);
    }

    public function changeUserPassword($emaillogon, $uk, $pwdchg, $pwdchg1) {
// stop no valid email
        if (empty($emaillogon)) {
            \model\message::render(\model\lexi::get('', 'sys043'));
            return false;
        }

        // stop did not match
        if ($pwdchg !== $pwdchg1) {
            \model\message::render(\model\lexi::get('', 'sys030'));
            return false;
        }

        $user = $this->getuserByEmail($emaillogon);
        if (!isset($user)) {
            \model\message::render(\model\lexi::get('', 'sys031'));
            return false;
        }

        if ($this->_registeruser($emaillogon, LOGINSRV, $uk) === false) {
            \model\message::render(\model\lexi::get('', 'sys031'));
            return false;
        }

        $data = ['pr' => LOGINSRV, 'email' => $emaillogon];
        $providertoken = \Firebase\JWT\JWT::encode($data, $pwdchg);
        $this->_updatetoken($user->iduser, $providertoken, LOGINSRV, $emaillogon);
        return true;
    }

    public function changeUserToken($iduser, $logonservice, $token, $email = null) {
        $this->_updatetoken($iduser, $token, $logonservice, $email);
    }

    public function setAuthEmailSent($iduser, $provider, $idToken) {
        $this->executeSql('UPDATE needauth SET issend = ? WHERE iduser = ? AND provider = ?', 1, (int) $iduser, trim((string) $provider));

        $this->_updatetoken($iduser, $idToken, $provider);
    }

    public function setAuthUserEmail($iduser, $provider) {
        $this->executeSql('UPDATE needauth SET isauth = ? WHERE iduser = ? AND provider = ?', 1, (int) $iduser, trim((string) $provider));
    }

    public function confirmDeleteAccountEmail($emailadvicefilename) {
        $config = \model\env::getConfig('api');

        $data = ['exp' => time() + 86400, 'iduser' => \model\env::getIdUser(), "provider" => LOGINSRV];
        $jwttoken = \Firebase\JWT\JWT::encode($data, \model\env::getKey());
        $token = \model\utils::format('{0}/{1}/{2}', ROOT_APP, $config->url, \model\route::url('close.php?tokenauth={0}', $jwttoken));

// get email string
        $filename = \model\route::render($emailadvicefilename);

        $emailstring = array();
        $lines = file($filename);
        foreach ($lines as $line) {
            $line = str_replace('[membername]', \model\env::getUserName(), $line);
            $line = str_replace('[useremail]', \model\env::getUserEmail(), $line);
            $line = str_replace('[provider]', LOGINSRVNAME, $line);
            $line = str_replace('[token]', $token, $line);
            $emailstring[] = $line;
        }

        \model\env::sendMail(\model\env::getUserName(), \model\env::getUserEmail(), \model\lexi::get('', 'sys024'), $emailstring);
    }

    public function deleteaccount($iduser) {
        // @TODO remove account and all data
//        return true;
    }

    public function setuserprofile($user, $emailadvicefilename) {
        //reset theme default
        $user->theme = $user->theme === '1' ? '' : $user->theme;

        if (!isset($user->name) || empty($user->name)) {
            \model\message::render(\model\lexi::get('', 'msg058'));
            return false;
        }

// email has changed, send a warning
        $sendwarning = false;
        $storedemail = $this->getuser()->email;
        $user->storedemail = $storedemail;
        if (\strtolower(\trim($storedemail)) !== \strtolower(\trim($user->email))) {
            if (isset($user->logintoken)) {
                $this->_registerEmail($user->email, LOGINSRV, $user->logintoken);
                $sendwarning = true;
            }
        }

        //clean keyname
        $user->keyname = \strtolower($user->keyname);
        $user->keyname = str_replace('@', '', $user->keyname);
        $user->keyname = str_replace(' ', '', $user->keyname);

        $this->executeSql('UPDATE user SET name = ?, keyname = ?, theme = ? WHERE iduser = ? AND deleted = ? AND isvalidated = ?', trim((string) $user->name), trim((string) $user->keyname), trim((string) $user->theme), \model\env::getIdUser(), 0, 0);

        // send email warning
        if (!$sendwarning)
            RETURN;

        $filename = \model\route::render($emailadvicefilename);

        $emailstring = array();
        $lines = file($filename);
        foreach ($lines as $line) {
            $line = str_replace('[memberusername]', $user->name, $line);
            $line = str_replace('[previousemail]', $user->storedemail, $line);
            $line = str_replace('[currentemail]', $user->email, $line);
            $emailstring[] = $line;
        }

        \model\env::sendMail($user->name, $user->storedemail, \model\lexi::get('', 'sys065'), $emailstring);
    }

    public function sleepaccount() {
        $this->executeSql('UPDATE user SET deleted = ? WHERE iduser = ? AND email = ? AND deleted = ? AND isvalidated = ?', 1, (int) \model\env::getIdUser(), trim((string) \model\env::getUserEmail()), 0, 0);

        $projects = (new \model\project)->getprojects();

        //deactivate each user project
        foreach ($projects as $project)
            (new \model\action(\model\env::src($project->idproject)))->setInactiveUser();

        // send message to user
        $emailsubject = \model\lexi::get('', 'sys064');
        $emailstring = [];
        $filename = \model\route::render('g3/*/accounttatus.html');

        $lines = file($filename);
        foreach ($lines as $line) {
            $line = str_replace('[memberusername]', \model\env::getUserName(), $line);
            $line = str_replace('[userstatus]', $emailsubject, $line);
            $emailstring[] = $line;
        }

        \model\env::sendMail(\model\env::getUserName(), \model\env::getUserEmail(), $emailsubject, $emailstring);
    }

    public function authresetpassword($email) {
        // stop no valid email
        $lexi = \model\lexi::getall();
        if (empty($email)) {
            \model\message::render($lexi('sys043'));
            return false;
        }

        $user = $this->getuserByEmail($email);
        if (!isset($user)) {
            \model\message::render($lexi('sys031'));
            return false;
        }

        $config = \model\env::getConfig('api');

        $data = ['exp' => time() + 86400, 'iduser' => $user->iduser, "provider" => LOGINSRV];
        $jwttoken = \Firebase\JWT\JWT::encode($data, \model\env::getKey());
        $token = \model\utils::format('{0}/{1}/{2}',ROOT_APP, $config->url, \model\route::url('reset.php?tokenauth={0}', $jwttoken));

// get email string
        $filename = \model\route::render('g3/*/resetpassword.html');

        $emailstring = [];
        $lines = file($filename);
        foreach ($lines as $line) {
            $line = str_replace('[membername]', $user->name, $line);
            $line = str_replace('[provider]', LOGINSRVNAME, $line);
            $line = str_replace('[token]', $token, $line);
            $emailstring[] = $line;
        }

        \model\env::sendMail($user->name, $email, $lexi['sys024'], $emailstring);
        return $user;
    }

    public function registerTokenUser($useremail, $logonservice, $loginid) {
        $user = $this->getuserByToken($loginid, $logonservice, $useremail);
        if (!isset($user))
            return;

//not validated
        if ($user->isvalidated || $user->deleted)
            return;

        (new \model\env)->saveSession($user->iduser, $user->email);
    }

    public function registerUser($useremail, $logonservice, $logintoken, $callback = '') {
        $data = ['pr' => $logonservice, 'email' => $useremail];
        $loginid = \Firebase\JWT\JWT::encode($data, $logintoken);

//        $modeluser = new \model\user();
//*******************************
//get user by provider only (must be registered)
//*******************************
        //find user email + token
        $user = $this->getuserByToken($loginid, $logonservice, $useremail);
        if (!isset($user->iduser)) {
            // call was made to validate iidentity only
            if (!empty($callback)) {
                // STOP return to logon
                $_SERVER['REQUEST_URI'] = $callback;
                $messageerror = \model\lexi::get('', 'sys031');
                require \model\route::script('login/confirmidentity.php');
                die();
            }

            //user not found, find user by email
            $user = $this->getUserLogonByEmail($loginid, $logonservice, $useremail);
            if (!isset($user->iduser)) {
                $messageerror = \model\lexi::get('', 'sys031');
                require \model\route::script('login/index.php');
                die();
            }

            if (isset($user->iduser)) {
                if ($user->accountdonotmatch) {
                    //account different from credentials, stop here
                    unset($user);
                    $messageerror = \model\lexi::get('', 'sys031');
                    require \model\route::script('login/index.php');
                    die();
                }

                if ($user->needauth) {
                    if (!$user->issend) {
                        $this->_authEmail($user->iduser, $logonservice, $user->name, $user->email, $loginid);
                        $this->setAuthEmailSent($user->iduser, $logonservice, $loginid);
                    }

                    $username = $user->name;
                    $useremail = $user->email;
                    unset($user);

                    // authorization required
                    require \model\route::script('login/authrequired.php');
                    die();
                }
            }
        }

        if ($user->isvalidated) {
            unset($user);

            $messageerror = \model\lexi::get('', 'sys033');
            require \model\route::script('login/index.php');
            die();
        }
        // user needs to activate the account to continue
        if ($user->deleted) {
            require \model\route::script('login/activateaccount.php');
            die();
        }

        if (!$this->isAuthorizationConfirm($user->iduser, $logonservice)) {
            // authorization required
            $this->_authEmail($user->iduser, $logonservice, $user->name, $user->email, $loginid);
            $username = $user->name;
            $useremail = $user->email;
            require \model\route::script('login/authrequired.php');
            die();
        }

//*******************************
// let them in
//*******************************
        //name and email can be empty, the UI must force entry data before continuing
        if (!\model\env::isauthorized())
            (new \model\env)->saveSession($user->iduser, $user->email);

        // confirm identity before callback execution
        if (!empty($callback)) {
            // indetity confirmed, just return to callback
            \model\utils::console('login callback: ' . $callback);
            require \model\route::script($callback);
            die();
        }

        // otherwise, start over
        require \model\route::script('restart.php');
    }

}
