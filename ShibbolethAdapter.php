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

class ShibbolethAdapter extends Adapter
{
    /**
     * Placeholder for plugin settings.
     *
     *@var
     */
    private $settings;
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
     * Placeholder for the view group or groups from Shibboleth.
     *
     * @var
     */
    private $viewGroup;
    /**
     * Placeholder for the admin group or groups from Shibboleth.
     *
     * @var
     */
    private $adminGroup;

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
     * Placeholder for the hasView.
     *
     * @var
     */
    private $hasView;

    /**
     * Placeholder for the hasAdmin.
     *
     * @var
     */
    private $hasAdmin;
    public function __construct()
    {
        $this->loginKey = Config::getShibbolethUserLogin();
        $this->aliasKey = Config::getShibbolethUserAlias();
        $this->emailKey = Config::getShibbolethUserEmail();
        $this->groupKey = Config::getShibbolethGroup();
        $this->viewGroup = Config::getShibbolethViewGroups();
        $this->adminGroup = Config::getShibbolethAdminGroups();
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
            if (!$result && in_array($g, $superGroupsArray)) {
                $result = true;
            }
        }

        return $result;
    }

    /**
     * Checks if the user has manual access.
     *
     * @param string $username Username of the given user from Shibboleth
     *
     * @return bool
     */
    public function getHasManualAccess($usernamme = '')
    {
        $result = false;
        if (Config::getShibbolethManualGroupsActive()) {
            if (Config::getShibbolethManualGroups() != '') {
                $groups = explode(Config::getShibbolethSeparator(), Config::getShibbolethManualGroups());
                $userGroupsArray = explode(Config::getShibbolethSeparator(), $this->getServerVar($this->groupKey));
                foreach ($groups as $g) {
                    if (!$result && in_array($g, $userGroupsArray)) {
                        $result = true;
                    }
                }
            } else {
                throw new \Exception('Activating manual groups, you should also add the groups to the config.');
            }
        }

        return $result;
    }

    /**
     * Generic get Urls.
     *
     * @param string $username   Username of the given user from Shibboleth
     * @param string $accessType Type of the access user want (View or Admin)
     *
     * @return array
     */
    public function getUrlsGeneric($username = '', $accessType = 'View')
    {
        if ($username == '') {
            $username == $this->getServerVar($this->loginKey);
        }
        if ($accessType == 'View') {
            $serverGroups = Config::getShibbolethViewGroups();
            $ldapActive = 'shibboleth_view_groups_ldap_active';
            $option = Config::getShibbolethViewGroupOption();
            $dn = Config::getShibbolethViewGroupLdapDN();
            $attr = explode($this->separator, Config::getShibbolethViewGroupLdapAttr());
        }
        if ($accessType == 'Admin') {
            $serverGroups = Config::getShibbolethAdminGroups();
            $ldapActive = 'shibboleth_admin_groups_ldap_active';
            $option = Config::getShibbolethAdminGroupOption();
            $dn = Config::getShibbolethAdminGroupLdapDN();
            $attr = explode($this->separator, Config::getShibbolethAdminGroupLdapAttr());
        }
        if (!in_array($accessType, array('Admin', 'View'))) {
            throw new \Exception("At this moment only 'Admin' and 'View' access types are available");
        }
        $urls = array();
        $userGroupsArray = explode($this->separator, $this->getServerVar($this->groupKey));
        $serverGroupsArray = explode($this->separator, $serverGroups);
        foreach ($serverGroupsArray as $g) {
            foreach ($userGroupsArray as $ug) {
                $a = preg_match("/$g/", $ug, $result);
                if (sizeof($result) > 0) {
                    if ($option == $ldapActive) {
                        if ($dn != '' && $attr != '') {
                            $la = new LdapAdapter();
                            $cnArray = explode(',', $ug);
                            $filter = '('.$cnArray[0].')';
                            $ldapResult = $la->searchLdap($filter, $attr, $dn);
                            if ($ldapResult['count'] > 0) {
                                unset($ldapResult['count']);
                                foreach ($ldapResult as $r) {
                                    if (array_key_exists($attr[0], $r)) {
                                        $tmpUrl = array('domain' => '', 'path' => '');
                                        $tmpUrl['domain'] = $r[$attr[0]][0];
                                        array_push($urls, $tmpUrl);
                                    }
                                }
                            }
                        } else {
                            throw new \Exception('The Attribute or DN for LDAP group search should be set.');
                        }
                    } else {
                        $tmpUrl = array('domain' => '', 'path' => '');
                        $tmpUrl['domain'] = $result[1];
                        array_push($urls, $tmpUrl);
                    }
                }
            }
        }

        return $urls;
    }

    /**
     * Returns the Urls in which the User has view access and sets the has
     * view access.
     *
     * @param string $username Username of the given user from Shibboleth
     *
     * @return array with user urls.
     */
    public function getUserViewUrls($username = '')
    {
        return $this->getUrlsGeneric($username, 'View');
    }
    /**
     * Returns the Urls in which the User has Admin access or sets the hasAdmin.
     *
     * @param string $username The login id of the user.
     *
     * @return array
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
     * @return array("username"=>"", "email"=>, "alias"=>"", "hasView"=>boolean, "hasAdmin"=>boolean)
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
     *
     * @return string
     */
    public function getServerVar($key)
    {
        if (array_key_exists($key, $_SERVER)) {
            return $_SERVER[$key];
        } else {
            return '';
        }
    }

    /**
     * Search for the users properties in the LDAP according to the settings.
     *
     * @param string $username Username of the given user from Shibboleth.
     *
     * @return array("view"=>array(),"admin"=>array(),"superuser"=>false, "manual"=>false);
     */
    public function getUserProperty($username = '')
    {
        $result = array('view' => array(),'admin' => array(),'superuser' => false);
        $result['view'] = $this->getUserViewUrls($username);
        $result['admin'] = $this->getUserAdminUrls($username);
        $result['superuser'] = $this->getUserSuperUserStatus($username);
        $result['manual'] = $this->getHasManualAccess();

        return $result;
    }
}
