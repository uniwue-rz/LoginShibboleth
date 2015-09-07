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

use Exception;
use Piwik\FrontController;
use Piwik\Piwik;
use Piwik\Plugin\Manager;
use Piwik\Plugins\Login\Login;

/**
 *
 */
class LoginShibboleth extends \Piwik\Plugin
{
    /**
     * @see Piwik_Plugin::getListHooksRegistered
     */
    public function getListHooksRegistered()
    {
        $hooks = array(
            'Request.initAuthenticationObject' => 'initAuthenticationObject',
            'User.isNotAuthorized' => 'noAccess',
            'API.Request.authenticate' => 'ApiRequestAuthenticate',
            'AssetManager.getJavaScriptFiles' => 'getJsFiles',
        );

        return $hooks;
    }

    public function getJsFiles(&$jsFiles)
    {
        $jsFiles[] = 'plugins/Login/javascripts/login.js';
    }

    /**
     * Deactivate default Login module, as both cannot be activated together.
     */
    public function activate()
    {
        if (Manager::getInstance()->isPluginActivated('Login') == true) {
            Manager::getInstance()->deactivatePlugin('Login');
        }
    }

    /**
     * Activate default Login module, as one of them is needed to access Piwik.
     */
    public function deactivate()
    {
        if (Manager::getInstance()->isPluginActivated('Login') == false) {
            Manager::getInstance()->activatePlugin('Login');
        }
    }

    /**
     * Redirects to Login form with error message.
     * Listens to User.isNotAuthorized hook.
     */
    public function noAccess(Exception $exception)
    {
        $exceptionMessage = $exception->getMessage();
        echo FrontController::getInstance()->dispatch('LoginShibboleth', 'login', array($exceptionMessage));
    }

    /**
     * Set login name and autehntication token for authentication request.
     * Listens to API.Request.authenticate hook.
     */
    public function ApiRequestAuthenticate($tokenAuth)
    {
        \Piwik\Registry::get('auth')->setLogin($login = null);
        \Piwik\Registry::get('auth')->setTokenAuth($tokenAuth);
    }

    /**
     * Initializes the authentication object.
     * Listens to Request.initAuthenticationObject hook.
     */
    public function initAuthenticationObject($activateCookieAuth = false)
    {
        $auth = new LoginShibbolethAuth();
        \Piwik\Registry::set('auth', $auth);
        Login::initAuthenticationFromCookie($auth, $activateCookieAuth);
    }
}
