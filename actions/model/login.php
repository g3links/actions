<?php

namespace model;

class login extends \model\dbconnect {

    public function __construct() {
        $this->src = \model\env::src(0);
        parent::__construct(\model\env::CONFIG_CORE);
    }

    public function getApiSessionToken($useremail, $pwd) {
        $message = '';
        $token = '';
        $loginid = $this->_getUserLoginToken($useremail, $pwd);

        $user = $this->_getUserByToken($loginid, $useremail);
        if (!isset($user) || $user->isvalidated || $user->deleted)
            $message = \model\lexi::get('', 'sys031');

        if (empty($message)) {
            if (!$this->_isAuthorizationConfirm($user->iduser))
                $message = \model\lexi::get('', 'sys024');
        }

        if (empty($message)) {
            // set token
            $remote_addr = \model\utils::get_remote_addr();

            $daytime = 86400; // 1 day = 86400
            $daysexpire = 3;
            $shost = filter_input(INPUT_SERVER, 'HTTP_HOST');
            $agent = filter_input(INPUT_SERVER, 'HTTP_USER_AGENT');

            // remove expire date, moved to client cookie expiration
            $data = ['exp' => time() + ( $daytime * $daysexpire ), "iduser" => $user->iduser, "useremail" => \trim($useremail), "dom" => $shost, "remoteip" => $remote_addr, "agent" => $agent];
            $token = \Firebase\JWT\JWT::encode($data, \model\env::getKey());
        }

        $result = new \stdClass();
        $result->token = $token;
        $result->message = $message;

        return $result;
    }

    public function getStatusRegistration($useremail, $logintoken) {
        $user = $this->_getUserLogonByEmail($useremail, $logintoken);
        if (!isset($user))
            return '';

        if ($user->deleted)
            return \model\lexi::get('', 'sys076', $useremail);

        if ($user->isvalidated)
            return \model\lexi::get('', 'sys033');

        if ($user->needauth) {
            if ($user->idproject === 0) {
                // allow to auth again
                $this->_deleteAuthUser($user->iduser);
            } else {
                // already been auth 
                return \model\lexi::get('', 'sys075', $useremail);
            }
        }
//            
//            //account different from credentials, stop here
        if ($user->accountdonotmatch)
            return \model\lexi::get('', 'sys031');

        return '';
    }

    public function insertUser($email, $username, $pwd, $keyname = '', $theme = '') {
        // validate
        $alreadyuser = $this->getuserByEmail($email);
        if (!isset($alreadyuser)) {
            // create
            $email = \strtolower($email);
            //clean keyname
            $keyname = \strtolower($keyname);
            $keyname = str_replace('@', '', $keyname);
            $keyname = str_replace(' ', '', $keyname);

            $lastInsertId = $this->executeSql('INSERT INTO user (email, name, keyname, theme, idproject) VALUES (?, ?, ?, ?, ?)', trim((string) $email), trim((string) $username), trim((string) $keyname), trim((string) $theme), 0);
        }

        // find user by email
        $user = $this->_getUserLogonByEmail($email, $pwd);
        if (!isset($user)) {
            \model\env::sendErroremail('login', $email . ': cannot be created');
            return false;
        }

        if ($user->needauth) {
            if (!$user->issend) {
                // send email
                $this->_authEmail($user->iduser, $user->name, $user->email, $user->loginid);
                $needauth = $this->_getAuth($user->iduser);
                if (!isset($needauth)) {
                    // create auth
                    $this->executeSql('INSERT INTO needauth (iduser, provider, isauth, issend) VALUES (?, ?, ?, ?)', (int) $user->iduser, (string) LOGINSRV, 0, 1);
                } else {
                    $this->executeSql('UPDATE needauth SET issend = ? WHERE iduser = ? AND provider = ?', 1, (int) $user->iduser, (string) LOGINSRV);
                }
                $this->_updatetoken($user->iduser, $user->loginid);
            }
        }

        $needauth = $this->_getAuth($user->iduser);
        if (!isset($needauth)) {
            // create auth
            $this->executeSql('INSERT INTO needauth (iduser, provider, isauth, issend) VALUES (?, ?, ?, ?)', (int) $user->iduser, (string) LOGINSRV, 0, 0);
        }

        return true;
    }

    public function getuserByEmail($email) {
        return $this->getRecord('SELECT iduser,name,deleted,isvalidated FROM user WHERE email = ?', (string) $email);
    }

    public function setActiveAccount($emailadvicefile) {
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
        $filename = \model\route::render($emailadvicefile);

        $lines = file($filename);
        foreach ($lines as $line) {
            $line = str_replace('[memberusername]', \model\env::getUserName(), $line);
            $line = str_replace('[userstatus]', $emailsubject, $line);
            $emailstring[] = $line;
        }

        \model\env::sendMail(\model\env::getUserName(), \model\env::getUserEmail(), $emailsubject, $emailstring);
    }

    private function _getUserByToken($idToken, $useremail) {
        if (empty($idToken ?? ''))
            return null;

        return $this->getRecord('SELECT user.iduser,user.lastaccesson,user.deleted,user.isvalidated,user.email,user.name,user.keyname FROM user LEFT JOIN userprovider USING ( iduser ) WHERE userprovider.token = ? AND userprovider.idprovider = ? AND user.email = ?', (string) $idToken, (string) LOGINSRV, (string) $useremail);
    }

    private function _getUserLogonByEmail($useremail, $pwd) {
        if (empty($useremail ?? ''))
            return null;

        $user = $this->getRecord('SELECT iduser,lastaccesson,deleted,isvalidated,email,name,keyname,theme,idproject FROM user WHERE email = ?', \trim(\strtolower($useremail)));
        if (!isset($user->iduser))
            return null;

        $user->accountdonotmatch = false;
        $user->needauth = true;
        $user->issend = false;

        //*****************************
        // check is active 
        //*****************************
        if ($user->isvalidated || $user->deleted)
            return $user;

        //confirm token is valid
        $var_token = $this->getRecord('SELECT token FROM userprovider WHERE iduser = ? AND idprovider = ?', (int) $user->iduser, (string) LOGINSRV);

        $user->loginid = $this->_getUserLoginToken($useremail, $pwd);

        // token exist and mismatch
        if (isset($var_token) && $user->loginid !== $var_token->token)
            $user->accountdonotmatch = true;

        $needauth = $this->_getAuth($user->iduser, LOGINSRV);
        if (isset($needauth->isauth)) {
            $user->issend = $needauth->issend;
            $user->needauth = $needauth->isauth;
        }

        return $user;
    }

    private function _isAuthorizationConfirm($iduser) {
        $needauth = $this->_getAuth($iduser, LOGINSRV);
        if (isset($needauth)) {
            if (!$needauth->isauth)
                return false;

            $this->_deleteAuthUser($iduser, LOGINSRV);
        }

        return true;
    }

    private function _getAuth($iduser) {
        return $this->getRecord('SELECT isauth, issend FROM needauth WHERE iduser = ? and provider = ?', (int) $iduser, \trim((string) LOGINSRV));
    }

    private function _deleteAuthUser($iduser) {
        $this->executeSql('DELETE FROM needauth WHERE iduser = ? AND provider = ?', (int) $iduser, trim((string) LOGINSRV));
    }

    private function _registeruser($useremail, $logintoken) {
        $data = ['pr' => LOGINSRV, 'email' => $useremail];
        $loginid = \Firebase\JWT\JWT::encode($data, $logintoken);

        $user = $this->_getUserByToken($loginid, $useremail);
        return isset($user);
    }

    private function _updatetoken($iduser, $token, $email = null) {
        $result = $this->getRecord('SELECT userprovider.iduser FROM userprovider WHERE userprovider.iduser = ? and userprovider.idprovider = ?', (int) $iduser, \trim((string) LOGINSRV));

        if (isset($result))
            $this->executeSql('UPDATE userprovider SET token = ? WHERE iduser = ? AND idprovider = ?', \trim((string) $token), (int) $iduser, (string) LOGINSRV);

        if (!isset($result))
            $this->executeSql('INSERT INTO userprovider (iduser, idprovider, token) VALUES (?, ?, ?)', trim((int) $iduser), (string) LOGINSRV, (string) $token);

        if (isset($email))
            $this->executeSql('UPDATE user set email = ? WHERE iduser = ?', \trim((string) $email), (int) $iduser);
    }

    private function _registerEmail($useremail, $logintoken) {
        try {
            $jsondecoded = \Firebase\JWT\JWT::decode(filter_input(INPUT_COOKIE, 'g3links'), \model\env::getKey(), ['HS256']);

            try {
                $data = ['pr' => LOGINSRV, 'email' => $useremail];
                $providertoken = \Firebase\JWT\JWT::encode($data, $logintoken);
                $this->_updatetoken($jsondecoded->iduser, $providertoken, $useremail);
                // force to logon again
                \model\env::resetlogon();

//                try {
////                    // keep new info for future sessions
//                   $token = \model\env::getUserSessionToken($jsondecoded->iduser, $useremail);
//                    \model\utils::setCookie('g3links', $token);
//                    \model\utils::unsetCookie('g3');
//                } catch (Exception $excS) {
//                    \model\env::sendErroremail('error encode session: ' . $jsondecoded->useremail, $excS->getMessage() . ', public key');
//                    \model\message::render($jsondecoded->useremail . ', session cannot be registered.');
//                    return;
//                }
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

    private function _authEmail($iduser, $name, $email, $providertoken) {
        // 24 hours before exprire
        $config = \model\env::getConfig('api');
        $data = ['exp' => time() + 86400, 'iduser' => $iduser, "email" => $email, "loginid" => $providertoken];
        $jwttoken = \Firebase\JWT\JWT::encode($data, \model\env::getKey());

        $token = \model\utils::format('{0}/{1}/{2}', ROOT_APP, $config->url, \model\route::url('authweb.php?tokenauth={0}', $jwttoken));

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

    public function resetUserPassword($authtoken) {
        $jsondecoded = \Firebase\JWT\JWT::decode($authtoken, \model\env::getKey(), ['HS256']);
        \model\env::setUser($jsondecoded->iduser);

        $data = ['pr' => LOGINSRV, 'email' => $jsondecoded->email];
        $providertoken = \Firebase\JWT\JWT::encode($data, $jsondecoded->pwd);
        $this->_updatetoken($jsondecoded->iduser, $providertoken);
    }

    public function changeUserPassword($emaillogon, $logintoken, $pwdchg, $pwdchg1) {
// stop no valid email
        if (empty($emaillogon))
            return false;

        // stop did not match
        if (empty($pwdchg) || $pwdchg !== $pwdchg1)
            return false;

        $loginid = $this->_getUserLoginToken($emaillogon, $logintoken);

        $user = $this->_getUserByToken($loginid, $emaillogon);
        if (!isset($user)) {
            return false;
        }

        $data = ['pr' => LOGINSRV, 'email' => $emaillogon];
        $providertoken = \Firebase\JWT\JWT::encode($data, $pwdchg);
        $this->_updatetoken($user->iduser, $providertoken, $emaillogon);

        \model\env::resetlogon();
        return true;
    }

    public function confirmDeleteAccountEmail($emailadvicefilename) {
        $config = \model\env::getConfig('api');

        $data = ['exp' => time() + 86400, 'iduser' => \model\env::getIdUser()];
        $jwttoken = \Firebase\JWT\JWT::encode($data, \model\env::getKey());
        $token = \model\utils::format('{0}/{1}/{2}', ROOT_APP, $config->url, \model\route::url('closeweb.php?tokenauth={0}', $jwttoken));

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

    public function deleteaccount($authtoken) {
        $jsondecoded = \Firebase\JWT\JWT::decode($authtoken, \model\env::getKey(), ['HS256']);

        \model\env::setUser($jsondecoded->iduser);
        // @TODO remove account and all data

        \model\env::resetlogon();
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
        $storedemail = (new \model\user)->getuser()->email;
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

    public function sleepaccount($emailadvicefilename) {
        $this->executeSql('UPDATE user SET deleted = ? WHERE iduser = ? AND email = ? AND deleted = ? AND isvalidated = ?', 1, (int) \model\env::getIdUser(), trim((string) \model\env::getUserEmail()), 0, 0);

        $projects = (new \model\project)->getprojects();

        //deactivate each user project
        foreach ($projects as $project)
            (new \model\action(\model\env::src($project->idproject)))->setInactiveUser();

        // send message to user
        $emailsubject = \model\lexi::get('', 'sys064');
        $emailstring = [];
        $filename = \model\route::render($emailadvicefilename);

        $lines = file($filename);
        foreach ($lines as $line) {
            $line = str_replace('[memberusername]', \model\env::getUserName(), $line);
            $line = str_replace('[userstatus]', $emailsubject, $line);
            $emailstring[] = $line;
        }

        \model\env::sendMail(\model\env::getUserName(), \model\env::getUserEmail(), $emailsubject, $emailstring);
        \model\env::resetlogon();
    }

    public function authresetpassword($emailadvicefilename, $email, $pwdreset) {
        // stop no valid email
        if (empty($email) || empty($pwdreset))
            return false;

        $user = $this->getuserByEmail($email);
        if (!isset($user))
            return false;

        $config = \model\env::getConfig('api');

        $data = ['exp' => time() + 86400, 'iduser' => $user->iduser, 'email' => $email, 'pwd' => $pwdreset];
        $jwttoken = \Firebase\JWT\JWT::encode($data, \model\env::getKey());
        $token = \model\utils::format('{0}/{1}/{2}', ROOT_APP, $config->url, \model\route::url('resetweb.php?tokenauth={0}', $jwttoken));

// get email string
        $filename = \model\route::render($emailadvicefilename);

        $emailstring = [];
        $lines = file($filename);
        foreach ($lines as $line) {
            $line = str_replace('[membername]', $user->name, $line);
            $line = str_replace('[provider]', LOGINSRVNAME, $line);
            $line = str_replace('[token]', $token, $line);
            $emailstring[] = $line;
        }

        \model\env::sendMail($user->name, $email, \model\lexi::get('', 'sys024'), $emailstring);
        return true;
    }

    public function authUserToken($tokenauth) {
        $jsondecoded = \Firebase\JWT\JWT::decode($tokenauth, \model\env::getKey(), ['HS256']);

        $user = $this->_getUserByToken($jsondecoded->loginid, $jsondecoded->email);
        if (!isset($user))
            return false;

//not validated
        if ($user->isvalidated || $user->deleted)
            return false;

        $this->executeSql('UPDATE needauth SET isauth = ? WHERE iduser = ? AND provider = ?', 1, (int) $jsondecoded->iduser, LOGINSRV);

        return true;
    }

    private function _getUserLoginToken($useremail, $logintoken) {
        $data = ['pr' => LOGINSRV, 'email' => $useremail];
        return \Firebase\JWT\JWT::encode($data, $logintoken);
    }

    public function confirmUserIdBeforeRender($useremail, $logintoken, $callback = '') {
        $loginid = $this->_getUserLoginToken($useremail, $logintoken);

//*******************************
//get user by provider only (must be registered)
//*******************************
        //find user email + token
        $user = $this->_getUserByToken($loginid, $useremail);
        if (!isset($user->iduser)) {
            // call was made to validate identity only
            if (!empty($callback)) {
                // STOP return to logon
                $_SERVER['REQUEST_URI'] = $callback;
                $messageerror = \model\lexi::get('', 'sys031');
                require \model\route::script('login/confirmidentity.php');
                die();
            }
        }

        if (!empty($callback)) {
            // indetity confirmed, just return to callback
            \model\utils::console('login callback: ' . $callback);
            require \model\route::script($callback);
            die();
        }
    }

}
