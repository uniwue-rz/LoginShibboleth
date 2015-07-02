<?php

/**
 * Piwik - Open source web analytics.
 *
 * @link http://piwik.org
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\LoginShibboleth;


use Piwik\Db;
use Piwik\Piwik;
use Piwik\Date;
use Piwik\Config as PiwikConfig;


/**
 * The UsersManager API lets you Manage Users and their permissions to access specific websites.
 *
 * You can create users via "addUser", update existing users via "updateUser" and delete users via "deleteUser".
 * There are many ways to list users based on their login "getUser" and "getUsers", their email "getUserByEmail",
 * or which users have permission (view or admin) to access the specified websites "getUsersWithSiteAccess".
 *
 * Existing Permissions are listed given a login via "getSitesAccessFromUser", or a website ID via "getUsersAccessFromSite",
 * or you can list all users and websites for a given permission via "getUsersSitesFromAccess". Permissions are set and updated
 * via the method "setUserAccess".
 * See also the documentation about <a href='http://piwik.org/docs/manage-users/' target='_blank'>Managing Users</a> in Piwik.
 */
class Model extends \Piwik\Plugins\UsersManager\Model
{
    /**
     * Look if the User has access through Shibboleth.
     * The view and admin group are exceptions that created for the user
     * which normaly do not have the right to access to the website statistics.
     *
     * @return array $memberships
     */
     protected $user_key = PiwikConfig::getInstance()->shibboleth['userkey'];
    public function getUserAccessShib()
    {
        $is_view = false;
        $is_super_user = false;
        $memberships = array();
        $groups = false;
        if (array_key_exists('groupMembership', $_SERVER)) {
            $groups = $_SERVER['groupMembership'];
        }
        if ($groups) {
            $groups_seprated = explode(';', $groups);
            foreach ($groups_seprated as $group) {
                $group_as_array = explode(',', $group);
                if (in_array('cn=RZ-Piwik-Admin', $group_as_array)
                    && !$is_super_user) {
                    $is_super_user = true;
                }
                if (in_array('cn=RZ-Piwik-View', $group_as_array)
                    && !$is_super_user && !$is_view) {
                    $is_view = true;
                }
            }
            if ($is_view) {
                array_push($memberships, 'view');
            }
            if ($is_super_user) {
                array_push($memberships, 'superUser');
            }
        }

        return $memberships;
    }

    /**
     * Get the User from Shibboleth or Ldap.
     */
    public function getUser($login)
    {
        $login = $_SERVER[$this->user_key];
        $memberships = $this->getUserAccessShib();
        if (sizeof($memberships) == 0) {
            $siteIds = array();
            $ldap = new Ldap();
            $res = $ldap->search($login);
            if ($res['count'] > 0) {
                foreach ($res as $r) {
                    if (is_array($r)) {
                        array_push($siteIds, $this->getSiteId($r['wueaccountwebhostdomain'][0]));
                    }
                }
            }
            if (sizeof($siteIds) > 0) {
                if (!$this->userExists($login)) {
                    $this->addUser($login,
                                                md5($this->generatePassword(8)),
                                                $this->getEmail(),
                                                $_SERVER['fullName'],
                                                $_SERVER['Shib-Session-ID'],
                                                Date::now()->getDatetime());
                    $this->addUserAccess($login, 'view', $siteIds);
                } else {
                    if ($this->getSitesAccessFromUser($login) != 0) {
                        $localSiteIds = array();
                        foreach ($this->getSitesAccessFromUser($login) as $siteAccess) {
                            if (!in_array($siteAccess['site'], $siteIds)) {
                                $this->deleteUserAccess($login, $siteAccess['site']);
                            } else {
                                array_push($localSiteIds, $siteAccess['site']);
                            }
                        }
                        foreach ($siteIds as $si) {
                            if (!in_array($si, $localSiteIds)) {
                                $this->addUserAccess($login, 'view', array($si));
                            }
                        }
                    }
                }
            } else {
                return array(
                    'login' => $login,
                                    'alias' => $_SERVER['fullName'],
                                    'email' => $this->getEmail(),
                                    'token_auth' => $_SERVER['Shib-Session-ID'],
                                    'superuser_access' => 0,
                           );
            }
        } else {
            if (!$this->userExists($login)) {
                $this->addUser($login,
                        md5($this->generatePassword(8)),
                        $this->getEmail(),
                        $_SERVER['fullName'],
                        $_SERVER['Shib-Session-ID'],
                        Date::now()->getDatetime());
                if (in_array('view', $memberships) && !in_array('superUser', $memberships)) {
                    $access = 'view';
                }

                if (sizeof($this->getSitesAccessFromUser($login)) == 0 &&
                    !in_array('superUser', $memberships)) {
                    $this->addUserAccess($login, $access, array());
                }
            }

            return array(
                'login' => $login,
                'alias' => $_SERVER['fullName'],
                'email' => $this->getEmail(),
                'token_auth' => $_SERVER['Shib-Session-ID'],
                'superuser_access' => $this->hasSuperAccess($memberships),
            );
        }
    }

    /**
     * Get the UserEmail from Shibboleth.
     *
     * @return $mail (if not avaible in shibboleth, give dummy user User@uni-wuerzburg.de back)
     */
    public function getEmail()
    {
        $mail = (array_key_exists('mail', $_SERVER) ? $_SERVER['mail'] : 'user@uni-wuerzburg.de');

        return $mail;
    }

        /**
         * @param intger $length
         *
         * @return string $password
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
     * Check if user has SuperUserAccess.
     *
     * @return bool 0/1
     */
    public function hasSuperAccess($memberships)
    {
        $hasSuperAccess = 0;

        if (in_array('superUser', $memberships)) {
            $hasSuperAccess = 1;
        }

        return $hasSuperAccess;
    }

    /**
     * Get the SiteId from the given site url using mysql
     * driver.
     *
     * @param string $domain
     *
     * @return int $id
     */
    public function getSiteId($domain)
    {
        $parsedDomain = parse_url($domain);
        $domain = 'http://'.$parsedDomain['path'];
        $siteId = Db::fetchOne('SELECT idsite FROM piwik_site WHERE main_url=?',
                    array($domain));
        if (!$siteId) {
            $siteId = Db::fetchOne('SELECT idsite FROM piwik_site_url WHERE url=?',
                    array($domain));
        }

        return $siteId;
    }
}
