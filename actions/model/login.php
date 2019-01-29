<?php

namespace model;

final class login {

    public function registerTokenUser($useremail, $logonservice, $loginid) {
        $user = (new \model\user)->getuserByToken($loginid, $logonservice, $useremail);
        if (!isset($user))
            return;

        if ($user->isvalidated || $user->deleted) {
            //not validated
            unset($user);
            return;
        }

        $this->_saveSession($user->iduser, $user->email);
    }

    public function registerEmail($useremail, $logonservice, $logintoken) {
        try {
            $jsondecoded = \Firebase\JWT\JWT::decode(filter_input(INPUT_COOKIE, 'g3links'), \model\env::getKey(), array('HS256'));

            try {
                $data = ['pr' => $logonservice, 'email' => $useremail];
                $providertoken = \Firebase\JWT\JWT::encode($data, $logintoken);
                (new \model\user)->changeUserToken($jsondecoded->iduser, $logonservice, $providertoken, $useremail);

                try {
//                    // keep new info for future sessions
                    $this->_saveSession($jsondecoded->iduser, $useremail);
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
            \model\message::render(\model\lexi::get('', 'sys017', $vexc->getMessage()), 'login', true);
        } catch (Exception $excSd) {
            \model\env::sendErroremail('update email, error decode session: ' . \model\env::getUserEmail(), $excSd->getMessage() . ', public key');
            \model\message::render(\model\env::getUserEmail() . ', cannot decode session credentials.');
            return;
        }
    }

    public function registerUserExist($useremail, $logonservice, $logintoken) {
        $data = ['pr' => $logonservice, 'email' => $useremail];
        $loginid = \Firebase\JWT\JWT::encode($data, $logintoken);

        $user = (new \model\user)->getuserByToken($loginid, $logonservice, $useremail);
        return isset($user);
    }
    
    public function registerUser($useremail, $logonservice, $logintoken, $callback = '') {
        $data = ['pr' => $logonservice, 'email' => $useremail];
        $loginid = \Firebase\JWT\JWT::encode($data, $logintoken);

        $modeluser = new \model\user();
//*******************************
//get user by provider only (must be registered)
//*******************************
        //find user email + token
        $user = $modeluser->getuserByToken($loginid, $logonservice, $useremail);
        if (!isset($user->iduser)) {
            // call was made to validate iidentity only
            if (!empty($callback)) {
                // STOP return to logon
                $_SERVER['REQUEST_URI'] = $callback;
                $messageerror = \model\lexi::get('g3', 'sys031');
                require \model\route::script('login/confirmidentity.php');
                die();
            }

            //user not found, find user by email
            $user = $modeluser->getuserwithEmail($loginid, $logonservice, $useremail);
            if (!isset($user->iduser)) {
                $messageerror = \model\lexi::get('g3', 'sys031');
                require \model\route::script('login/index.php');
                die();
            }

            if (isset($user->iduser)) {
                if ($user->accountdonotmatch) {
                    //account different from credentials, stop here
                    unset($user);
                    $messageerror = \model\lexi::get('g3', 'sys031');
                    require \model\route::script('login/index.php');
                    die();
                }

                if ($user->needauth) {
                    if (!$user->issend) {
                        $this->_authEmail($user->iduser, $logonservice, $user->name, $user->email, $loginid);
                        $modeluser->setAuthEmailSent($user->iduser, $logonservice, $loginid);
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

            $messageerror = \model\lexi::get('g3', 'sys033');
            require \model\route::script('login/index.php');
            die();
        }
        // user needs to activate the account to continue
        if ($user->deleted) {
            require \model\route::script('login/activateaccount.php');
            die();
        }

        if (!$modeluser->confirmAuthorization($user->iduser, $logonservice)) {
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
            $this->_saveSession($user->iduser, $user->email);

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

    private function _authEmail($iduser, $provider, $name, $email, $providertoken) {
        // 24 hours before exprire
        $config = \model\env::getConfig('api');
        $data = ['exp' => time() + 86400, 'iduser' => $iduser, "provider" => $provider, "email" => $email, "loginid" => $providertoken];
        $jwttoken = \Firebase\JWT\JWT::encode($data, \model\env::getKey());

        $token = ROOT_APP . $config->url . '/' . \model\route::url('auth.php?tokenauth={0}', $jwttoken);

        $filename = \model\route::render('g3/*/regauthorization.html');

        $emailstring = array();
        $lines = file($filename);
        foreach ($lines as $line) {
            $line = str_replace('[membername]', $name, $line);
            $line = str_replace('[provider]', LOGINSRVNAME, $line);
            $line = str_replace('[token]', $token, $line);
            $emailstring[] = $line;
        }

        \model\env::sendMail($name, $email, \model\lexi::get('g3', 'sys024'), $emailstring);
    }

    private function _saveSession($iduser, $useremail) {
        \model\env::setUser($iduser);

        // 5 days expire session
        $data = ['exp' => time() + 432000, "iduser" => $iduser, "useremail" => \trim($useremail)];
        \model\utils::setCookie('g3links', \Firebase\JWT\JWT::encode($data, \model\env::getKey()));
    }

    public function closeSession() {
        $tks = explode('.', filter_input(INPUT_COOKIE, 'g3links'));
        list($headb64, $bodyb64, $cryptob64) = $tks;
        $payload = \Firebase\JWT\JWT::jsonDecode(\Firebase\JWT\JWT::urlsafeB64Decode($bodyb64));

        \model\env::setUserEmail($payload->useremail);

        // shotdown access
        $data = ['exp' => time() - 1, "iduser" => $payload->iduser, "useremail" => $payload->useremail];
        \model\utils::setCookie('g3links', \Firebase\JWT\JWT::encode($data, \model\env::getKey()));
    }

    public function getUserCredentials() {
        // LOGIN token ******************
        $token = null;
        if (filter_input(INPUT_GET, 'tokenid') !== null) 
            $token = filter_input(INPUT_GET, 'tokenid');

        if (isset($token)) {
            try {
                $jsondecoded = \Firebase\JWT\JWT::decode($token, \model\env::getKey(), array('HS256'));
                $this->registerTokenUser($jsondecoded->email, $jsondecoded->logonservice, $jsondecoded->loginid);
            } catch (\Firebase\JWT\ExpiredException $vexc) {
                \model\message::severe('sys004', $vexc->getMessage());
            } catch (Exception $exc) {
                \model\message::severe('sys004', $exc->getMessage());
            }
        }

        // retrieve email name ******************
        if (filter_input(INPUT_COOKIE,'g3links') !== null) {
            $tks = explode('.', filter_input(INPUT_COOKIE, 'g3links'));
            list($headb64, $bodyb64, $cryptob64) = $tks;
            $payload = \Firebase\JWT\JWT::jsonDecode(\Firebase\JWT\JWT::urlsafeB64Decode($bodyb64));
            \model\env::setUserEmail($payload->useremail);
        }

        //validate user access
        if (!\model\env::isauthorized() && filter_input(INPUT_COOKIE,'g3links') !== null) {
            try {
                $jsondecoded = \Firebase\JWT\JWT::decode(filter_input(INPUT_COOKIE, 'g3links'), \model\env::getKey(), array('HS256'));

                \model\env::setUser($jsondecoded->iduser);
                \model\env::setUserEmail($jsondecoded->useremail);
            } catch (\Firebase\JWT\ExpiredException $vexc) {
                //ignore
            } catch (Exception $exc) {
                //ignore
            }
        }
    }

}
