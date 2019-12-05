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
use Exception;

/*
 * @author Pouyan Azari <pouyan.azari@uni-wuerzburg.de>
 * @license MIT
 * @copyright 2014-2019 University of Wuerzburg
 * @copyright 2014-2019 Pouyan Azari
 */

class Controller extends \Piwik\Plugins\Login\Controller
{
    /**
     * @return string
     * @throws Exception
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
     * @return string
     */
    public function index()
    {
        return $this->login();
    }

    /**
     * @throws Exception
     */
    public function logout()
    {
        Url::redirectToUrl(Config::getLogoutUrl());
    }
}
