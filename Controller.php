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

use Piwik\Piwik;
use Piwik\View;
use Piwik\Url;
use Piwik\Notification;
use Piwik\Plugin\ControllerAdmin;
use Piwik\Plugin\Manager as PluginManager;

//require_once PIWIK_INCLUDE_PATH.'/core/Config.php';

/**
 * Login controller.
 */
class Controller extends \Piwik\Plugins\Login\Controller
{
    private $config;

    public function __construct()
    {
        $config = parse_ini_file('config.ini.php');
        $this->config = $config['controller'];
    }

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
     * @param $length
     *
     * @return string
     */
    private function generatePassword($length)
    {
        $chars = '234567890abcdefghijkmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
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
     */
    public function index()
    {
        return $this->login();
    }

    public function logout()
    {
        Url::redirectToUrl('https://www.rz.uni-wuerzburg.de/en/services/rzserver/zvd/wuelogin/');
    }
    /**
     * @param $password
     *
     * @throws \Exception
     */
    protected function checkPasswordHash($password)
    {
        // do not check password (Login uses hashed password, LoginLdap uses real passwords)
    }
}
