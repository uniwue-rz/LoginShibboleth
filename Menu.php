<?php

/**
 * Part of the Piwik Login Shibboleth Plug-in.
 */

namespace Piwik\Plugins\LoginShibboleth;

use Piwik\Menu\MenuAdmin;
use Piwik\Piwik;
use Piwik\Version;

/**
 * The Menu configuration of the Plug-in.
 *
 * Here the menu settings of the plug-in can be set. At this moment the plug-in settings will reside in Settings.
 * If there are more menu related functionalities are needed, they can be added here.
 *
 * @author Pouyan Azari <pouyan.azari@uni-wuerzburg.de>
 * @license MIT
 * @copyright 2014-2016 University of Wuerzburg
 * @copyright 2014-2016 Pouyan Azari
 */
class Menu extends \Piwik\Plugin\Menu
{
    /**
     * Configures the menu inherited from Menu.
     *
     * @param Menu $menu The global Menu object.
     */
    public function configureAdminMenu(MenuAdmin $menu)
    {
        if (Piwik::hasUserSuperUserAccess()) {
            if (Version::VERSION >= 3) {
                $menu->addSystemItem('Login Shibboleth', $this->urlForAction('admin'), $order = 30);
            } else {
                $menu->addSettingsItem('Login Shibboleth', $this->urlForAction('admin'), $order = 30);
            }
        }
    }
}
