<?php

/**
 * Part of the Piwik LoginShibboleth PLugin.
 */

namespace Piwik\Plugins\LoginShibboleth;

use DI\NotFoundException;
use Exception;
use Piwik\Auth;
use Piwik\Container\StaticContainer;
use Piwik\Exception\PluginDeactivatedException;
use Piwik\FrontController;
use Piwik\Plugin;
use Piwik\Plugin\Manager;

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
 * @copyright 2014-2019 University of Wuerzburg
 * @copyright 2014-2019 Pouyan Azari
 */
class LoginShibboleth extends Plugin
{
    public function __construct($pluginName = false)
    {
        parent::__construct($pluginName);
    }

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
        if (Manager::getInstance()->isPluginActivated('Login') === true) {
            Manager::getInstance()->deactivatePlugin('Login');
        }
    }

    /**
     * @throws Exception
     */
    public function deactivate()
    {
        if (Manager::getInstance()->isPluginActivated('Login') === false) {
            Manager::getInstance()->activatePlugin('Login');
        }
    }

    /**
     * @param Exception $exception
     * @throws PluginDeactivatedException
     */
    public function noAccess(Exception $exception)
    {
        $exceptionMessage = $exception->getMessage();
        echo FrontController::getInstance()->dispatch('LoginShibboleth', 'login', array($exceptionMessage));
    }

    /**
     * Returns the Api Request Authentication
     *
     * @param $tokenAuth
     * @throws NotFoundException
     */
    public function ApiRequestAuthenticate($tokenAuth)
    {
        StaticContainer::get(Auth::class)->setLogin($login = null);
        StaticContainer::get(Auth::class)->setTokenAuth($tokenAuth);
    }

    /**
     * Initializes the authentication object.
     * Listens to Request.initAuthenticationObject hook.
     *
     * @param bool
     */
    public function initAuthenticationObject($activateCookieAuth = false)
    {
        $auth = new LoginShibbolethAuth();
        StaticContainer::getContainer()->set(Auth::class, $auth);
    }
}