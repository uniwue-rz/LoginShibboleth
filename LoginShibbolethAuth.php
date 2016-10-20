<?php

/**
 * Part of Piwik Login Shibboleth Plug-in.
 */

namespace Piwik\Plugins\LoginShibboleth;

use Piwik\AuthResult;
use Piwik\Plugins\LoginShibboleth\LoginShibbolethUser as UserModel;
use Piwik\Plugins\Login\SessionInitializer;
use Piwik\Plugins\UsersManager\Model as PiwikUserModel;
use Piwik\Container\StaticContainer;
use Piwik\Piwik;

/**
 * LoginShibbolethAuth does the authentication.
 *
 * This is the overridden Auth class of native Login Plug-in in Piwik. It handles all
 * the login request and API queries to the plug-in. The class only changes the normal login and everything else
 * is the same so the API functions still could work. Any authentication related settings should be done here.
 *
 * @author Pouyan Azari <pouyan.azari@uni-wuerzburg.de>
 * @license MIT
 * @copyright 2014-2016 University of Wuerzburg
 * @copyright 2014-2016 Pouyan Azari
 */
class LoginShibbolethAuth extends \Piwik\Plugins\Login\Auth
{
    /**
     * Placeholder for the logging interface.
     *
     * @var
     */
    protected $logger;
    /**
     * Placeholder for the login (UserName).
     *
     * @var
     */
    protected $login;
    /**
     * Placeholder for the password.
     *
     * @var
     */
    protected $password;
    /**
     * Placeholder for token auth.
     *
     * @var
     */
    protected $token_auth;

    /**
     * Placeholder for the Hash Password.
     *
     * @var
     */
    protected $hashedPassword;

    /**
     * Initiator.
     */
    public function __construct()
    {
        if (!isset($logger)) {
            $this->logger = StaticContainer::get('Psr\Log\LoggerInterface');
        }
    }
    /**
     * Authentication module's name, e.g., "Login".
     *
     * @return string
     */
    public function getName()
    {
        return 'LoginShibboleth';
    }

    /**
     * Authenticates user.
     *
     * @return AuthResult
     */
    public function authenticate()
    {
        if (isset($_SERVER[Config::getShibbolethUserLogin()])) {
            $this->login = $_SERVER[Config::getShibbolethUserLogin()];
            $this->password = '';
            $model = new UserModel();
            $user = $model->getUser($this->login);
            $code = $user['superuser_access'] ? AuthResult::SUCCESS_SUPERUSER_AUTH_CODE : AuthResult::SUCCESS;

            return new AuthResult($code, $this->login, $this->token_auth);
        } elseif (!empty($this->hashedPassword)) {
            return $this->authenticateWithPassword($this->login, $this->getTokenAuthSecret());
        } elseif (is_null($this->login)) {
            return $this->authenticateWithToken($this->token_auth);
        } elseif (!empty($this->login)) {
            return $this->authenticateWithTokenOrHashToken($this->token_auth, $this->login);
        }

        return new AuthResult(AuthResult::FAILURE, $this->login, $this->token_auth);
    }

    private function authenticateWithPassword($login, $passwordHash)
    {
        $piwikUserModel = new PiwikUserModel();
        $user = $piwikUserModel->getUser($login);
        if (!empty($user['login']) && $user['password'] === $passwordHash) {
            return $this->authenticationSuccess($user);
        }

        return new AuthResult(AuthResult::FAILURE, $login, null);
    }

    private function authenticateWithToken($token)
    {
        $piwikUserModel = new PiwikUserModel();
        $user = $piwikUserModel->getUser($login);

        if (!empty($user['login'])) {
            return $this->authenticationSuccess($user);
        }

        return new AuthResult(AuthResult::FAILURE, null, $token);
    }

    private function authenticateWithTokenOrHashToken($token, $login)
    {
        $piwikUserModel = new PiwikUserModel();
        $user = $piwikUserModel->getUser($login);

        if (!empty($user['token_auth'])
            // authenticate either with the token or the "hash token"
            && ((SessionInitializer::getHashTokenAuth($login, $user['token_auth']) === $token)
                || $user['token_auth'] === $token)
        ) {
            return $this->authenticationSuccess($user);
        }

        return new AuthResult(AuthResult::FAILURE, $login, $token);
    }

    private function authenticationSuccess(array $user)
    {
        $this->setTokenAuth($user['token_auth']);

        $isSuperUser = (int) $user['superuser_access'];
        $code = $isSuperUser ? AuthResult::SUCCESS_SUPERUSER_AUTH_CODE : AuthResult::SUCCESS;

        return new AuthResult($code, $user['login'], $user['token_auth']);
    }
}
