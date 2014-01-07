<?php

namespace WebFW\Dev\Classes;

use WebFW\Core\Config;
use WebFW\Core\Exceptions\UnauthorizedException;

/**
 * Class AuthenticationHelper
 *
 * A helper class for authenticating users for access to restricted areas.
 * Implements the HTTP Digest authentication algorithm (RFC 2617).
 *
 * @package WebFW\Dev
 */
class AuthenticationHelper
{
    protected $realm;
    protected $username;
    protected $password;

    /**
     * Authentication call.
     * If authentication fails, an UnauthorizedException will be thrown.
     *
     * @param string $realm The authentication realm
     * @param string $username The username to authenticate
     * @param string $password Password associated with the username to check
     * @throws UnauthorizedException If authorization is not valid
     */
    public static function authenticate($realm, $username, $password)
    {
        new static($realm, $username, $password);
    }

    protected function __construct($realm, $username, $password)
    {
        $this->realm = $realm;
        $this->username = $username;
        $this->password = $password;

        if (!$this->checkAuthenticationCredentials()) {
            header('WWW-Authenticate: Digest realm="'. $this->realm . '",qop="auth",nonce="'
                . uniqid(Config::get('General', 'projectName'), true) . '",opaque="' . md5($this->realm) . '"');
            throw new UnauthorizedException($this->realm);
        }
    }

    protected function checkAuthenticationCredentials()
    {
        if (!array_key_exists('PHP_AUTH_DIGEST', $_SERVER)) {
            return false;
        }

        $data = $this->parseHTTPDigest();
        if ($data === null) {
            return false;
        }

        if ($data['username'] !== $this->username) {
            return false;
        }

        $digest1 = md5($data['username'] . ':' . $this->realm . ':' . $this->password);
        $digest2 = md5($_SERVER['REQUEST_METHOD'] . ':' . $data['uri']);
        $valid_response = md5($digest1 . ':' . $data['nonce'] . ':' . $data['nc'] . ':'
            . $data['cnonce'] . ':' . $data['qop'] . ':' . $digest2);

        if ($data['response'] != $valid_response) {
            return false;
        }

        return true;
    }

    protected function parseHTTPDigest()
    {
        $digestText = $_SERVER['PHP_AUTH_DIGEST'];

        $neededParts = array(
            'nonce' => 1,
            'nc' => 1,
            'cnonce' => 1,
            'qop' => 1,
            'username' => 1,
            'uri' => 1,
            'response' => 1,
        );
        $data = array();
        $keys = implode('|', array_keys($neededParts));

        preg_match_all('@(' . $keys . ')=(?:([\'"])([^\2]+?)\2|([^\s,]+))@', $digestText, $matches, PREG_SET_ORDER);

        foreach ($matches as &$match) {
            $data[$match[1]] = $match[3] ? $match[3] : $match[4];
            unset($neededParts[$match[1]]);
        }

        return $neededParts ? null : $data;
    }
}
