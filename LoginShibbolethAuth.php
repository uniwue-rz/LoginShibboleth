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
        if (is_null($this->login)) {
         if(isset($_SERVER["REMOTE_USER"])){
             $this->login = $_SERVER["REMOTE_USER"];
              $this->password = '';
              $model = new UserModel();
              $user = $model->getUser($this->login);
              $code = $user['superuser_access'] ? AuthResult::SUCCESS_SUPERUSER_AUTH_CODE : AuthResult::SUCCESS;
              return new AuthResult($code, $this->login, $this->token_auth);
         }}
         else if (!empty($this->login)) {
         $login = $this->login;

                $model = new UserModel();
                $user = $model->getUser($login);

                $userToken = null;
                if (!empty($user['token_auth'])) {
                    $userToken = $user['token_auth'];
        }
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
