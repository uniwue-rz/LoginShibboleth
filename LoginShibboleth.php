<?php

/**
 * Part of the Piwik LoginShibboleth PLugin.
 */

namespace Piwik\Plugins\LoginShibboleth;

use Exception;
use Piwik\FrontController;
use Piwik\Piwik;
use Piwik\Plugin\Manager;
use Piwik\Plugins\Login\Login;

/**
 * Main Login Shibboleth Settings.
 *
 * Here the different JavaScript, function and activation hooks are added.
 * If there is another JavaScript file is to be added or functions needed
 *to be run before or after this plug-in activation
 * use this class to reflect them.
 *
 * @author Pouyan Azari <pouyan.azari@uni-wuerzburg.de>
 * @license MIT
 * @copyright 2014-2016 University of Wuerzburg
 * @copyright 2014-2016 Pouyan Azari
 */
class LoginShibboleth extends \Piwik\Plugin
{
    /**
     * Register the hooks to this Plugin.
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

    /**
     * Adds the JavaScript files that the plug-in needs to the global list.
     *
     * @param array $jsFiles The array containing the JavaScript file paths
     */
    public function getJsFiles(&$jsFiles)
    {
        $jsFiles[] = 'plugins/Login/javascripts/login.js';
        $jsFiles[] = 'plugins/LoginShibboleth/angularjs/admin/admin.controller.js';
    }

    /**
     * Adds the style sheets files that the plug-in needs to the global list.
     *
     * @param array $stylesheetFiles The array containing the style sheet file paths

     */
    public function getStylesheetFiles(&$stylesheetFiles)
    {
        $stylesheetFiles[] = 'plugins/Login/stylesheets/login.less';
        $stylesheetFiles[] = 'plugins/Login/stylesheets/variables.less';
        $stylesheetFiles[] = 'plugins/LoginShibboleth/angularjs/admin/admin.controller.less';
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
     *
     * @param \Exception $exception The exception to be return when no access.
     */
    public function noAccess(Exception $exception)
    {
        $exceptionMessage = $exception->getMessage();
        echo FrontController::getInstance()->dispatch('LoginShibboleth', 'login', array($exceptionMessage));
    }

    /**
     * Set login name and autehntication token for authentication request.
     * Listens to API.Request.authenticate hook.
     *
     * @param string $tokenAuth The token that can be used for auth to API
     */
    public function ApiRequestAuthenticate($tokenAuth)
    {
        \Piwik\Registry::get('auth')->setLogin($login = null);
        \Piwik\Registry::get('auth')->setTokenAuth($tokenAuth);
    }

    /**
     * Initializes the authentication object.
     * Listens to Request.initAuthenticationObject hook.
     *
     * @param bool $activateCookieAuth If the authentication is cookie based.
     */
    public function initAuthenticationObject($activateCookieAuth = false)
    {
        $auth = new LoginShibbolethAuth();
        \Piwik\Registry::set('auth', $auth);
        Login::initAuthenticationFromCookie($auth, $activateCookieAuth);
    }
}
