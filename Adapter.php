<?php

/**
 * Piwik - free/libre analytics platform.
 *
 * @link http://piwik.org
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\LoginShibboleth;

abstract class Adapter
{
    /**
     * Search for the users properties in the LDAP according to the settings.
     *
     * @param string $username (LDAP username of the user)
     *
     * @return array("view"=>array(),"admin"=>array(),"superuser"=>false);
     */
    abstract public function getUserProperty($username);
    /**
     * Returns the SuperUser status of the User.
     *
     * @param $username string Login given by the User.
     */
    abstract public function getUserSuperUserStatus($username);
    /**
     * Returns the Urls in which the User has view access.
     *
     * @param $username string Login given by the User.

     */
    abstract public function getUserViewUrls($username);
    /**
     * Returns the Urls in which the User has Admin access.
     *
     * @param $username string Login given by the User.

     */
    abstract public function getUserAdminUrls($username);
    /**
     * Returns the User information, normally not needed from ldap.
     *
     * @param $username string Login given by the User.

     */
    abstract public function getUserInfo($username);
}
