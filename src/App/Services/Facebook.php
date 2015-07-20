<?php

namespace App\Services;

use Facebook\FacebookRedirectLoginHelper;
use Facebook\FacebookRequest;
use Facebook\FacebookSession;

class Facebook {

    private $appId;
    private $appSecret;

    /**
     * @param $appId Facebook Application ID
     * @param $appSecret Facebook Application secret
     * @param $redirectUrl null|string
     */
    public function __construct($appId, $appSecret, $redirectUrl = null) {
        $this->appId = $appId;
        $this->appSecret = $appSecret;
        $this->redirectUrl = $redirectUrl;
    }


    /**
     * @return FacebookRedirectLoginHelper
     */
    public function getHelper() {
        $helper = new FacebookRedirectLoginHelper($this->redirectUrl);
        return $helper;
    }

    /**
     * @return FacebookSession|null
     */
    public function getSession() {
        FacebookSession::setDefaultApplication($this->appId, $this->appSecret);
        $helper = $this->getHelper();
        if (isset($_SESSION) && isset($_SESSION['fb_token'])) {
            $session = new FacebookSession($_SESSION['fb_token']);
        } else {
            $session = $helper->getSessionFromRedirect();
        }

        return $session;
    }

    /**
     * @return string|\Facebook\GraphUser Login URL or GraphUser
     */
    public function connect() {
        $session = $this->getSession();
        $helper = $this->getHelper();
        if ($session) {
            try {
                $_SESSION['fb_token'] = $session->getToken();
                $profile = $this->getGraphObject('/me', 'GET', null, 'Facebook\GraphUser');

                if ($profile->getEmail() === null) {
                    throw new \Exception('L\'email n\'est pas disponible');
                }

                /*if (isset($_SESSION['upload'])) {
                    var_dump('coucou');
                    $helper->getLoginUrl(['email','publish_actions','user_photos']);
                }*/

                return $profile;
            } catch (\Exception $e) {
                unset($_SESSION['fb_token']);
                return $helper->getReRequestUrl(['email']);
            }
        }
        return $helper->getLoginUrl(['email']);
    }

    public function logout() {
        $session = $this->getSession();
        $session->getLogoutUrl("http://silex-facebook.dev.io");
    }

    /**
     * @param $path
     * @param string $method
     * @param string $type
     * @return mixed
     * @throws \Facebook\FacebookRequestException
     */
    public function getGraphObject($path, $method = 'GET', $data = null, $type = 'Facebook\GraphObject') {
        if ($data == null) {
            $request = new FacebookRequest($this->getSession(), $method, $path);
        } else {
            $request = new FacebookRequest($this->getSession(), $method, $path, $data);
        }
        $graphObject = $request->execute()->getGraphObject($type);
        return $graphObject;

    }

    public function getToken() {
        $session = $this->getSession();
        if ($session instanceof FacebookSession) {
            return $session->getToken();
        }
        return null;
    }

}
