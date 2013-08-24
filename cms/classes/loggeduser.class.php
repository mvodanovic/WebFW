<?php

namespace WebFW\CMS\Classes;

use WebFW\Core\Exception;
use WebFW\Core\SessionHandler;
use WebFW\CMS\DBLayer\User;

class LoggedUser
{
    protected static $sessionKey = 'cms-logged-user';
    protected static $cookieName = 'webfw-cms-autologin';
    protected $user = null;
    protected static $passwordGeneratorSeed = 'iweiu829e23e';

    protected static $instance = null;

    const EXC_INVALID_CREDENTIALS = 1;
    const EXC_INACTIVE_USER = 2;
    const EXC_INTERNAL_ERROR = 3;

    protected $exception = array(
        self::EXC_INVALID_CREDENTIALS => 'Invalid credentials',
        self::EXC_INACTIVE_USER => 'User not active',
        self::EXC_INTERNAL_ERROR => 'Internal server error',
    );

    protected function __construct()
    {
        $this->user = SessionHandler::get(static::$sessionKey);
    }

    public static function getInstance()
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    public static function isLoggedIn()
    {
        return static::getInstance()->getLoggedUser() !== null;
    }

    public function __get($key)
    {
        return $this->user->$key;
    }

    public function __set($key, $value) {
        $this->user->$key = $value;
    }

    public function getLoggedUser()
    {
        return $this->user;
    }

    public static function generatePasswordHash($password, $seed)
    {
        return hash('sha256', $seed . static::$passwordGeneratorSeed . $password);
    }

    protected static function checkPasswordHash($password, $seed, $passwordHash)
    {
        return static::generatePasswordHash($password, $seed) === $passwordHash;
    }

    public function doLogin($login, $password, $remember = false)
    {
        $user = new User();
        $seed = null;
        $passwordField = null;
        try {
            $user->loadBy(array('email' => $login));
            $seed = 'email';
            $passwordField = 'password_email';
        } catch (Exception $e) {
            try {
                $user->loadBy(array('username' => $login));
                $seed = 'username';
                $passwordField = 'password_username';
            } catch (Exception $e) {
                throw new Exception($this->exception[static::EXC_INVALID_CREDENTIALS], static::EXC_INVALID_CREDENTIALS);
            }
        }

        $ok = static::checkPasswordHash($password, $seed, $user->$passwordField);

        if (!$ok) {
            throw new Exception($this->exception[static::EXC_INVALID_CREDENTIALS], static::EXC_INVALID_CREDENTIALS);
        }

        $this->doLoginByUser($user, $remember);

    }

    public function doLoginByUser(&$user, $remember = false)
    {
        if (!($user instanceof User)) {
            throw new Exception($this->exception[static::EXC_INTERNAL_ERROR], static::EXC_INTERNAL_ERROR);
        }

        if ($user->active !== true) {
            throw new Exception($this->exception[static::EXC_INACTIVE_USER], static::EXC_INACTIVE_USER);
        }

        SessionHandler::set(static::$sessionKey, $user);
        $this->user = $user;
        if ($remember === true) {
            $this->setAutologinCookie();
        }
    }

    public function doLoginByAutoloadCookie()
    {
        if (static::isLoggedIn()) {
            return;
        }

        if (!array_key_exists(static::$cookieName, $_COOKIE)) {
            return;
        }

        $cookie = explode('#', $_COOKIE[static::$cookieName], 2);
        if (count($cookie) !== 2) {
            return;
        }

        $user = new User();
        try {
            $user->loadBy(array('username' => $cookie[0], 'password_username' => $cookie[1]));
            $this->doLoginByUser($user);
        } catch (Exception $e) {
            $this->deleteAutologinCookie();
        }
    }

    public function doLogout()
    {
        SessionHandler::kill(static::$sessionKey);
        if ($this->user !== null) {
            $this->deleteAutologinCookie();
            $this->user = null;
        }
    }

    protected function setAutologinCookie()
    {
        /// 31536000 = 365 * 24 * 60 * 60 = 1 year
        setcookie(
            static::$cookieName,
            $this->user->username . '#' . $this->user->password_username,
            $_SERVER['REQUEST_TIME'] + 31536000,
            '/'
        );
    }

    protected function deleteAutologinCookie()
    {
        setcookie(static::$cookieName, '', 1, '/' );
    }
}
