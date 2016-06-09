<?php

/**
 * Part of the Piwik Login Shibboleth Plug-in.
 */

namespace Piwik\Plugins\LoginShibboleth;

/**
 * Adapter is the abstract class for data retrievals.
 *
 * Any data reterival class should extend this class, which makes them have at least the function listed here.
 * If there is plan to extend all adapters changes should be added here.
 *
 * @author Pouyan Azari <pouyan.azari@uni-wuerzburg.de>
 * @license MIT
 * @copyright 2014-2016 University of Wuerzburg
 * @copyright 2014-2016 Pouyan Azari
 */
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
