<?php

/**
 * Piwik - Open source web analytics.
 *
 * @link http://piwik.org
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 **/

namespace Piwik\Plugins\LoginShibboleth;

use Piwik\AuthResult;
use Piwik\Plugins\LoginShibboleth\LoginShibbolethUser as UserModel;
use Piwik\Container\StaticContainer;

/**
 *LoginShibbolethAuth is the auth.
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
        }
        if (is_null($this->login)) {
            $model = new UserModel();
            $user = $model->getUserByTokenAuth($this->token_auth);
            if (!empty($user['login'])) {
                $code = $user['superuser_access'] ? AuthResult::SUCCESS_SUPERUSER_AUTH_CODE : AuthResult::SUCCESS;

                return new AuthResult($code, $user['login'], $this->token_auth);
            }
        } elseif (!empty($this->login)) {
            if ($this->login != 'anonymous') {
                $model = new UserModel();
                $login = $this->login;
                $user = $model->getUser($login);
                $userToken = null;
                if (!empty($user['token_auth'])) {
                    $userToken = $user['token_auth'];
                }
                if (!empty($userToken)
                    && (($this->getHashTokenAuth($login, $userToken) === $this->token_auth)
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
     * Accessor to set password.
     *
     * @param string $password password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }
}
