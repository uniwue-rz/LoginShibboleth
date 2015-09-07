<?php

/**
 * Piwik - Open source web analytics.
 *
 * @link http://piwik.org
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 *
 * @package LoginShibboleth
 **/

namespace Piwik\Plugins\LoginShibboleth;

use Piwik\AuthResult;
use Piwik\Plugins\LoginShibboleth\ShibbolethModel as UserModel;

/**
 *
 */
class LoginShibbolethAuth extends \Piwik\Plugins\Login\Auth
{
    protected $login = null;
    protected $password = null;
    protected $token_auth = null;
    public function __construct(){
      $config = parse_ini_file('config.ini.php');
      foreach ($config['shib'] as $key => $value) {
          $this->$key = $value;
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
     * @return string
     */
    public static function getLogPath()
    {
        return PIWIK_INCLUDE_PATH.self::SHIBB_LOG_FILE;
    }

    /**
     * Authenticates user.
     *
     * @return AuthResult
     */
    public function authenticate()
    {
        if (isset($_SERVER[$this->uid])) {
            $this->login = $_SERVER[$this->uid];
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
                $login = $this->login;
                $model = new UserModel();
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
                    $code = !empty($user['superuser_access']) ? AuthResult::SUCCESS_SUPERUSER_AUTH_CODE : AuthResult::SUCCESS;

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
