<?php

/**
 * Part of Piwik Login Shibboleth Plug-in.
 */

namespace Piwik\Plugins\LoginShibboleth;

use Piwik\AuthResult;
use Piwik\Plugins\Login\Auth;
use Piwik\Plugins\LoginShibboleth\LoginShibbolethUser as UserModel;
use \Piwik\Plugins\Login\SessionInitializer;
use RuntimeException;

/**
 * LoginShibbolethAuth does the authentication.
 *
 * This is the overridden Auth class of native Login Plug-in in Piwik. It handles all
 * the login request and API queries to the plug-in. The class only changes the normal login and everything else
 * is the same so the API functions still could work. Any authentication related settings should be done here.
 *
 * @author Pouyan Azari <pouyan.azari@uni-wuerzburg.de>
 * @license MIT
 * @copyright 2014-2019 University of Wuerzburg
 * @copyright 2014-2019 Pouyan Azari
 */
class LoginShibbolethAuth extends Auth
{
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
     * Authentication module's name, e.g., "Login".
     *
     * @return string
     */
    public function getName()
    {
        return 'LoginShibboleth';
    }

    /**
     * @return AuthResult
     * @throws \Exception
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
        }
        if ($this->login === null) {
            $model = new UserModel();
            $user = $model->getUserByTokenAuth($this->token_auth);
            if (!empty($user['login'])) {
                $code = $user['superuser_access'] ? AuthResult::SUCCESS_SUPERUSER_AUTH_CODE : AuthResult::SUCCESS;

                return new AuthResult($code, $user['login'], $this->token_auth);
            }
        } elseif (!empty($this->login)) {
            if ($this->login !== 'anonymous') {
                $model = new UserModel();
                $login = $this->login;
                $user = $model->getUser($login);
                $userToken = null;
                if (!empty($user['token_auth'])) {
                    $userToken = $user['token_auth'];
                }
                if (!empty($userToken)
                    && ((SessionInitializer::getHashTokenAuth($login, $userToken) === $this->token_auth)
                        || $userToken === $this->token_auth)
                ) {
                    $this->setTokenAuth($userToken);
                    $code = !empty($user['superuser_access']) ?
                        AuthResult::SUCCESS_SUPERUSER_AUTH_CODE : AuthResult::SUCCESS;
                    return new AuthResult($code, $login, $userToken);
                }
            }
        }

        return new AuthResult(AuthResult::FAILURE, $this->login, $this->token_auth);
    }

    /**
     * Returns the secret used to calculate a user's token auth.
     *
     * @return string
     *
     * @throws RuntimeException if the token auth cannot be calculated at the current time.
     */
    public function getTokenAuthSecret()
    {
        $user = $this->login;
        if (empty($user)) {
            throw new RuntimeException("Cannot find user '{$this->login}'");
        }
        return $user['password'];
    }

    /**
     * Accessor to set password.
     *
     * @param string $password password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }
}