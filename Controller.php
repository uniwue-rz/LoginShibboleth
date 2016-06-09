<?php

/**
 * Part of the Piwik Login Shibboleth Plug-in.
 */

namespace Piwik\Plugins\LoginShibboleth;

use Piwik\Piwik;
use Piwik\View;
use Piwik\Url;
use Piwik\Notification;
use Piwik\Plugin\ControllerAdmin;
use Piwik\Plugin\Manager as PluginManager;

/*
 * @author Pouyan Azari <pouyan.azari@uni-wuerzburg.de>
 * @license MIT
 * @copyright 2014-2016 University of Wuerzburg
 * @copyright 2014-2016 Pouyan Azari
 */
class Controller extends \Piwik\Plugins\Login\Controller
{
    /**
     * The Admin page for the Login Shibboleth Plugin.
     *
     * @return mix
     */
    public function admin()
    {
        Piwik::checkUserHasSuperUserAccess();
        $view = new View('@LoginShibboleth/index');
        ControllerAdmin::setBasicVariablesAdminView($view);
        if (!function_exists('ldap_connect')) {
            $notification = new Notification(Piwik::translate('ShibbolethLogin_LdapFunctionsMissing'));
            $notification->context = Notification::CONTEXT_ERROR;
            $notification->type = Notification::TYPE_TRANSIENT;
            $notification->flags = 0;
            Notification\Manager::notify('ShibbolethLogin_LdapFunctionsMissing', $notification);
        }
        $view->servers = array();
        $this->setBasicVariablesView($view);
        $view->shibbolethConfig = Config::getPluginOptionValuesWithDefaults();
        $view->isLoginControllerActivated = PluginManager::getInstance()->isPluginActivated('Login');

        return $view->render();
    }

    /**
     * @param int $length Length of the password to be created.
     *
     * @return string
     */
    private function generatePassword($length)
    {
        $chars = '1234567890abcdefghijkmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $i = 0;
        $password = '';
        while ($i <= $length) {
            $password .= $chars{mt_rand(0, strlen($chars) - 1)};
            ++$i;
        }

        return $password;
    }

    /**
     * Default function.
     *
     *@return index
     */
    public function index()
    {
        return $this->login();
    }

    /**
     * Logout function for the application. As Shibboleth does not have a Logout.
     * This will be linked to a logout description page.
     */
    public function logout()
    {
        Url::redirectToUrl(Config::getLogoutUrl());
    }
}
