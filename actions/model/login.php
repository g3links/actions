<?php

namespace model;

final class login {

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

        \model\env::sendMail($name, $email, \model\lexi::get('g3', 'sys024'), $emailstring);
    }

}
