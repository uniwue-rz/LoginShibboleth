<?php

/**
 * Part of the Piwik Shibboleth Login Plug-in.
 */

namespace Piwik\Plugins\LoginShibboleth;

use RuntimeException;
use Exception;

/**
 * ShibbolethAdapter is the Shibboleth data retrieval adapter.
 *
 * LoginShibboleth has two ways of retrieval of user data and access. The first is the ShibbolethAdapter which directly
 * interpret the data from Shibboleth and return the user information. The other one is LdapAdapter.
 * Any changes to the way shibboleth acts can be reflected here.
 *
 * @author Pouyan Azari <pouyan.azari@uni-wuerzburg.de>
 * @license MIT
 * @copyright 2014-2019 University of Wuerzburg
 * @copyright 2014-2019 Pouyan Azari
 */
class ShibbolethAdapter extends Adapter
{
    /**
     * Placeholder for the login key variable in $_SERVER.
     *
     * @var
     */
    private $loginKey;
    /**
     * Placeholder for the alias key variable in $_SERVER.
     *
     * @var
     */
    private $aliasKey;
    /**
     * Placeholder for the email key variable in $_SERVER.
     *
     * @var
     */
    private $emailKey;
    /**
     * Placeholder for the group key variable in $_SERVER.
     *
     * @var
     */
    private $groupKey;

    /**
     * Placeholder for the superUser group from Shibboleth.
     *
     * @var
     */
    private $superUserGroup;

    /**
     * Placeholder for the Group,.. Separator for the.
     *
     * @var
     */
    private $separator;

    /**
     * ShibbolethAdapter Initializer.
     */
    public function __construct()
    {
        $this->loginKey = Config::getShibbolethUserLogin();
        $this->aliasKey = Config::getShibbolethUserAlias();
        $this->emailKey = Config::getShibbolethUserEmail();
        $this->groupKey = Config::getShibbolethGroup();
        $this->superUserGroup = Config::getShibbolethSuperUserGroups();
        $this->separator = Config::getShibbolethSeparator();
    }

    /**
     * Returns the SuperUser status of the User.
     *
     * @param string $username Username of the given user from Shibboleth
     *
     * @return boolean.
     */
    public function getUserSuperUserStatus($username = '')
    {
        $result = false;
        $userGroupsArray = explode($this->separator, $this->getServerVar($this->groupKey));
        $superGroupsArray = explode($this->separator, $this->superUserGroup);
        foreach ($userGroupsArray as $g) {
            if (!$result && in_array($g, $superGroupsArray, true)) {
                $result = true;
            }
        }

        return $result;
    }

    /**
     * Checks if the given user has manual access
     *
     * @return bool
     * @throws RuntimeException
     */
    public function getHasManualAccess()
    {
        $result = false;
        if (Config::getShibbolethManualGroupsActive()) {
            if (Config::getShibbolethManualGroups() !== '') {
                $groups = explode(Config::getShibbolethSeparator(), Config::getShibbolethManualGroups());
                $userGroupsArray = explode(Config::getShibbolethSeparator(), $this->getServerVar($this->groupKey));
                foreach ($groups as $g) {
                    if (!$result && in_array($g, $userGroupsArray, true)) {
                        $result = true;
                    }
                }
            } else {
                throw new RuntimeException('Activating manual groups, you should also add the groups to the config.');
            }
        }
        return $result;
    }

    /**
     * Returns the Generic Urls
     *
     * @param string $username
     * @param string $accessType
     * @return array
     * @throws Exception
     */
    public function getUrlsGeneric($username = '', $accessType = 'View')
    {
        $attr = '';
        $dn = '';
        $option = '';
        $ldapActive = '';
        $serverGroups = '';
        if ($username === '') {
            $username = $this->getServerVar($this->loginKey);
        }
        if ($accessType === 'View') {
            $serverGroups = Config::getShibbolethViewGroups();
            $ldapActive = 'shibboleth_view_groups_ldap_active';
            $option = Config::getShibbolethViewGroupOption();
            $dn = Config::getShibbolethViewGroupLdapDN();
            $attr = explode($this->separator, Config::getShibbolethViewGroupLdapAttr());
        }
        if ($accessType === 'Admin') {
            $serverGroups = Config::getShibbolethAdminGroups();
            $ldapActive = 'shibboleth_admin_groups_ldap_active';
            $option = Config::getShibbolethAdminGroupOption();
            $dn = Config::getShibbolethAdminGroupLdapDN();
            $attr = explode($this->separator, Config::getShibbolethAdminGroupLdapAttr());
        }
        if (!in_array($accessType, array('Admin', 'View'))) {
            throw new RuntimeException("At this moment only 'Admin' and 'View' access types are available");
        }
        $urls = array();
        $userGroupsArray = explode($this->separator, $this->getServerVar($this->groupKey));
        $serverGroupsArray = explode($this->separator, $serverGroups);
        foreach ($serverGroupsArray as $g) {
            foreach ($userGroupsArray as $ug) {
                preg_match("/$g/", $ug, $result);
                if (count($result) > 0) {
                    if ($option === $ldapActive) {
                        if ($dn !== '' && $attr !== '') {
                            $la = new LdapAdapter();
                            $cnArray = explode(',', $ug);
                            $filter = '(' . $cnArray[0] . ')';
                            $ldapResult = $la->searchLdap($filter, $attr, $dn);
                            if ($ldapResult['count'] > 0) {
                                unset($ldapResult['count']);
                                foreach ($ldapResult as $entry) {
                                    $tmpUrl = array('domain' => '', 'path' => '');
                                    $tmpUrl['domain'] = $la->getLdapEntryAttributeSingleValue($entry, $attr[0]);
                                    $urls[] = $tmpUrl;
                                }
                            }
                        } else {
                            throw new RuntimeException('The Attribute or DN for LDAP group search should be set.');
                        }
                    } else {
                        $tmpUrl = array('domain' => '', 'path' => '');
                        $tmpUrl['domain'] = $result[1];
                        $urls[] = $tmpUrl;
                    }
                }
            }
        }
        return $urls;
    }

    /**
     * @param string $username
     * @return array
     * @throws Exception
     */
    public function getUserViewUrls($username = '')
    {
        return $this->getUrlsGeneric($username, 'View');
    }

    /**
     * @param string $username
     * @return array
     * @throws Exception
     */
    public function getUserAdminUrls($username = '')
    {
        return $this->getUrlsGeneric($username, 'Admin');
    }

    /**
     * Returns the User information, normally not needed from ldap.
     *
     * @param $username string login Id of the User.
     * In Shibboleth The Username will be set automatically.
     * Shibboleth can also add the new layer of "hasView" or "hasAdmin"
     * This will only used when the View or Admin groups of Shibboleth are set
     * to restrict access. See Plugin Settings in Piwik Backend.
     *
     * @return array `array("username"=>"", "email"=>, "alias"=>"", "hasView"=>boolean, "hasAdmin"=>boolean)`
     */
    public function getUserInfo($username = '')
    {
        $result = array('username' => '', 'email' => '', 'alias' => '');
        $result['username'] = $this->getServerVar($this->loginKey);
        $result['alias'] = $this->getServerVar($this->aliasKey);
        $result['email'] = $this->getServerVar($this->emailKey);
        return $result;
    }

    /**
     * Returns the $_SERVER set variable.
     *
     * @param string $key The $_SERVER variable key, set by Shibboleth
     * @return string
     */
    public function getServerVar($key)
    {
        if (array_key_exists($key, $_SERVER)) {
            return $_SERVER[$key];
        }
        return '';
    }

    /**
     * @param string $username
     * @return array
     * @throws Exception
     */
    public function getUserProperty($username = '')
    {
        $result = array('view' => array(), 'admin' => array(), 'superuser' => false);
        $result['view'] = $this->getUserViewUrls($username);
        $result['admin'] = $this->getUserAdminUrls($username);
        $result['superuser'] = $this->getUserSuperUserStatus($username);
        $result['manual'] = $this->getHasManualAccess();
        return $result;
    }
}