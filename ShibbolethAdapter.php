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
     * @param $username string User login Id.
     *
     * @return boolean.
     */
    public function getUserSuperUserStatus($username = '')
    {
        $userGroupsArray = explode($this->separator, $this->getServerVar($this->groupKey));
        foreach ($userGroupsArray as $g) {
            if ($g == $this->superUserGroup) {
                return true;
            }
        }

        return false;
    }
    /**
     * Returns the Urls in which the User has view access and sets the has
     * view access.
     *
     * @param $username string login id of the user.
     *
     * @return array with user urls.
     */
    public function getUserViewUrls($username = '')
    {
        $this->hasView = false;
        $userGroupsArray = explode($this->separator, $this->getServerVar($this->groupKey));
        $patternToRegex = preg_match("/\(\.\*\)/", $this->getServerVar($this->groupKey), $result);
        if (sizeof($result) > 0) {
            $patternView = '/'.$this->viewGroup.'/';
            foreach ($userGroupsArray as $g) {
                preg_match($patternView, $g, $matchResults);
                if (sizeof($matchResults) > 1) {
                    return $matchResults[1];
                } else {
                    return array();
                }
            }
        } else {
            if ($this->hasViewActive) {
                foreach ($userGroupsArray as $g) {
                    if ($g == $this->viewGroup && !$this->hasView) {
                        $this->hasView = true;
                    }
                }
            }

            return array();
        }
    }
    /**
     * Returns the Urls in which the User has Admin access or sets the hasAdmin.
     *
     * @param $username string the login id of the user.
     *
     * @return array
     */
    public function getUserAdminUrls($username = '')
    {
        $this->hasAdmin = false;
        $userGroupArray = explode($this->separator, $this->getServerVar($this->groupKey));
        $patternToRegex = preg_match("/\(\.\*\)/", $this->getServerVar($this->groupKey), $result);
        if (sizeof($result) > 0) {
            $patternView = '/'.$this->adminGroup.'/';
            foreach ($userGroupsArray as $g) {
                preg_match($pattern, $g, $matchResults);
                if (sizeof($matchResults) > 1) {
                    return $matchResults[1];
                } else {
                    return array();
                }
            }
        } else {
            if ($this->hasAdminActive) {
                foreach ($userGroupsArray as $g) {
                    if ($g == $this->adminGroup && !$this->hasAdmin) {
                        $this->hasAdmin = true;
                    }
                }
            }

            return array();
        }
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
        $username = $this->getServerVar($this->loginKey);
        $alias = $this->getServerVar($this->aliasKey);
        $email = $this->getServerVar($this->emailKey);
        $result['username'] = $username;
        $result['alias'] = $alias;
        $result['email'] = $email;
        if ($this->hasViewActive) {
            $result['hasView'] = $this->hasView;
        }
        if ($this->hasAdminActive) {
            $result['hasAdmin'] = $this->hasAdmin;
        }

        return $result;
    }

    /**
     * Returns the $_SERVER set variable.
     *
     * @param $key string The variable key.
     *
     * @return mix (false if nothing was found.)
     */
    public function getServerVar($key)
    {
        if (array_key_exists($key, $_SERVER)) {
            return $_SERVER[$key];
        } else {
            return false;
        }
    }

    /**
     * Search for the users properties in the LDAP according to the settings.
     *
     * @param string $username (LDAP username of the user)
     *
     * @return array("view"=>array(),"admin"=>array(),"superuser"=>false);
     */
    public function getUserProperty($username = '')
    {
        $result = array('view' => array(),'admin' => array(),'superuser' => false);
        $result['view'] = $this->getUserViewUrls($username);
        $result['admin'] = $this->getUserAdminUrls($username);
        $result['superuser'] = $this->getUserSuperUserStatus($username);

        return $result;
    }
}
