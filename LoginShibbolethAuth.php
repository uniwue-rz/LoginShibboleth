<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package LoginShibboleth
 */
namespace Piwik\Plugins\LoginShibboleth;

use Exception;
use Piwik\AuthResult;
use Piwik\Common;
use Piwik\Config;
use Piwik\Db;
use Piwik\Plugins\LoginShibboleth\Model as UserModel;
use Piwik\Session;

/**
 *
 * @package Login
 */
class LoginShibbolethAuth extends \Piwik\Plugins\Login\Auth
{
    protected $login = null;
    protected $password = null;
    protected $token_auth = null;

    const SHIBB_LOG_FILE = "/tmp/logs/shibboleth.log";

    /**
     * Authentication module's name, e.g., "Login"
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
        return PIWIK_INCLUDE_PATH . self::SHIBB_LOG_FILE;
    }

    /**
     * Authenticates user
     *
     * @return AuthResult
     */
    public function authenticate()
    {
         if(isset($_SERVER["REMOTE_USER"])){
              $this->login = $_SERVER["REMOTE_USER"];
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
                $this->LdapLog("INFO: ldapauth authenticate() - token login success.", 0);
                $code = $user['superuser_access'] ? AuthResult::SUCCESS_SUPERUSER_AUTH_CODE : AuthResult::SUCCESS;

                return new AuthResult($code, $user['login'], $this->token_auth);
            } else {
                $this->LdapLog("WARN: ldapauth authenticate() - token login tried, but user info missing!", 1);
            }
        } else if (!empty($this->login)) {

            $ldapException = null;
            if ($this->login != "anonymous") {
                try {
                    if ($this->authenticateLDAP($this->login, $this->password, $kerberosEnabled)) {
                        $this->LdapLog("INFO: ldapauth authenticate() - not anonymous login ok by authenticateLDAP().", 0);
                        $model = new UserModel();
                        $user = $model->getUserByTokenAuth($this->token_auth);
                        $code = $user['superuser_access'] ? AuthResult::SUCCESS_SUPERUSER_AUTH_CODE : AuthResult::SUCCESS;
                        return new AuthResult($code, $this->login, $this->token_auth);
                    } else {
                        $this->LdapLog("WARN: ldapauth authenticate() - not anonymous login failed by authenticateLDAP()!", 1);
                    }
                } catch (Exception $ex) {
                    $this->LdapLog("WARN: ldapauth authenticate() - not anonymous login exception: " . $ex->getMessage(), 1);
                    $ldapException = $ex;
                }

                $this->LdapLog("INFO: ldapauth authenticate() - login: " . $this->login, 0);
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
                    $this->LdapLog("INFO: ldapauth authenticate() - success, setTokenAuth: " . $userToken, 0);

                    $code = !empty($user['superuser_access']) ? AuthResult::SUCCESS_SUPERUSER_AUTH_CODE : AuthResult::SUCCESS;

                    return new AuthResult($code, $login, $userToken);
                } else {
                    $this->LdapLog("WARN: ldapauth authenticate() - userToken empty or does not match!", 1);
                }

                if (!is_null($ldapException)) {
                    $this->LdapLog("WARN: ldapauth authenticate() - ldapException: " . $ldapException->getMessage(), 0);
                    throw $ldapException;
                }
            } else {
                $this->LdapLog("WARN: ldapauth authenticate() - login variable is set to anonymous and this is not expected!", 1);
            }
        } else {
            $this->LdapLog("WARN: ldapauth authenticate() - problem with login variable, this should not happen!", 1);
        }
        return new AuthResult(AuthResult::FAILURE, $this->login, $this->token_auth);
    }

    /**
     * Accessor to set password
     *
     * @param string $password password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }
}
