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

class LdapAdapter
{
    /**
     * Placeholder for Ldap username.
     *
     * @var
     */
    private $username;
    /**
     * Placeholder for Ldap password.
     *
     * @var
     */
    private $password;
    /**
     * Placeholder for Ldap Distinguished Name.
     *
     * @var
     */
    private $dn;
    /**
     * Placeholder for Ldap Host.
     *
     * @var
     */
    private $host;
    /**
     * Placeholder for ldap Port.
     *
     * @var
     */
    private $port;

    /**
     * Placeholder for Ldap Active.
     *
     * @var
     */
    private $active;

    /**
     * Placeholder for Ldap Active Data sources.
     *
     * @var
     */
    private $activeData;

    public function __construct()
    {
        $this->username = Config::getLdapUserName();
        $this->password = Config::getLdapPassword();
        $this->dn = Config::getLdapDN();
        $this->host = Config::getLdapHost();
        $this->port = Config::getLdapPort();
        $this->active = Config::isLdapActive();
        $this->activeData = Config::getLdapActiveData();
        $this->ldapViewFilter = Config::getLdapViewFilter();
        $this->ldapViewAttrs = Config::getLdapViewAttrs();
        $this->ldapAdminFilter = Config::getLdapAdminFilter();
        $this->ldapAdminAttrs = Config::getLdapAdminAttrs();
        $this->ldapSuperuserFilter = Config::getLdapSuperUserFilter();
        $this->ldapSuperuserAttrs = Config::getLdapSuperUserAttrs();
        $this->ldapSuperuserValues = Config::getLdapSuperUserValue();
    }

    /**
     * Checks the connection at the Settings.
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function checkConnection()
    {
        $ldapconn = ldap_connect($this->host, $this->port);
        if ($ldapconn) {
            $this->conn = $ldapconn;

            return true;
        }

        throw new \Exception('Can not connect to the LDAP server.');
    }

    /**
     * Checks if the ldap can bind.
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function checkBind()
    {
        $this->checkConnection();
        if ($this->conn) {
            $ldapbind = ldap_bind($this->conn, $this->username, $this->password);
            if ($ldapbind) {
                $this->bind = $ldapbind;

                return true;
            }
        }

        throw new \Exception('Can not bind with the Ldap Server');
    }

    /**
     * Search LDAP Abstract.
     *
     * @param string $filter
     * @param array attributes
     *
     * @return ldap result object
     */
    public function searchLdap($filter, $attrs)
    {
        $result = array('count' => 0);
        $searchComm = ldap_search($this->conn, $this->dn, $filter, $attrs);
        if ($searchComm) {
            $result = ldap_get_entries($this->conn, $searchComm);
        }

        return $result;
    }

    /**
     * Returns the url for a given user on hand the userType.
     *
     * @param string $username
     * @param string $userType (view, admin, superUser)
     *
     * @return array ("domain"=>"www....", "path"=>"/...")
     */
    public function getManagedUrls($username, $userType)
    {
        $urls = array();
        $filterString = $userType.'Filter';
        $attrString = $userType.'Attrs';
        $filter = sprintf(str_replace('?', '%1$s', $this->$filterString), $username);
        $attrs = array_map('trim', explode(',', $this->$attrString));
        $ldapResult = $this->searchLdap($filter, $attrs);
        $attrs_low = array_map('strtolower', $attrs);
        if ($ldapResult['count'] > 0) {
            unset($ldapResult['count']);
            foreach ($ldapResult as $l) {
                if (sizeof($attrs_low) > 1) {
                    $domain = $l[$attrs_low[0]][0];
                    $path = $l[$attrs_low[1]][0];
                    $tmp_url = array('domain' => $domain, 'path' => $path);
                } else {
                    $domain = $l[$attrs_low[0]][0];
                    $tmp_url = array('domain' => $domain, 'path' => '');
                }
                array_push($urls, $tmp_url);
            }
        }

        return $urls;
    }

    /**
     * Returns the username's view URLs according to the Setting.
     *
     * @param string username
     *
     * @return array ("domain"=>"www....", "path"=>"/...")
     */
    public function getUserViewUrls($username)
    {
        $viewId = 'ldapView';
        if (in_array($viewId, $this->activeData)) {
            return $this->getManagedUrls($username, $viewId);
        }

        return array();
    }

    /**
     * Returns the usernames admin URLs according to the setting.
     *
     * @param string $username
     *
     * @return array ("domain"=>"www....", "path"=>"/...")
     */
    public function getUserAdminUrls($username)
    {
        $adminId = 'ldapAdmin';
        if (in_array($adminId, $this->activeData)) {
            return $this->getManagedUrls($username, $adminId);
        }

        return array();
    }

    /**
     * Converts filter to LDAP usable values.
     *
     * @param string $filter
     * @param string $username
     * @param string $placeholder
     *
     * @return string
     **/
    public function convertFilter($filter, $username, $delimeter)
    {
        return sprintf(str_replace($delimeter, '%1$s', $filter), $username);
    }

    /**
     * Converts Attrs to LDAP usable values.
     *
     * @param string $delimeter
     * @param string $attrs
     *
     * @return array
     */
    public function convertAttrs($delimeter, $attrs)
    {
        return array_map('trim', explode($delimeter, $attrs));
    }

    /**
     * Returns the superUser status of the user. If it is set that the user
     * SuperUser status should be checked also by LDAP.
     *
     * @param string $username
     *
     * @return bool
     */
    public function getUserSuperUserStatus($username)
    {
        $superUserId = 'ldapSuperUser';
        $result = false;
        if (in_array($superUserId, $this->activeData)) {
            $filterString = $superUserId.'Filter';
            $attrString = $superUserId.'Attrs';
            $valueString = $superUserId.'Values';
            $filter = $this->convertFilter($this->$filterString, $username, '?');
            $attrs = $this->convertAttrs(',', $this->$attrString);
            $values = $this->convertAttrs(';', $this->$valueString);
            $ldapResult = $this->searchLdap($filter, $attrs);
            $attrs_low = array_map('strtolower', $attrs);
            if ($ldapResult['count'] > 0) {
                unset($ldapResult['count']);
                foreach ($ldapResult as $l) {
                    foreach ($l[$attrs_low[0]] as $r) {
                        if ($r == $values[0] && $result == false) {
                            $result = true;
                        }
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Search for the users properties in the LDAP according to the settings.
     *
     * @param string $username (LDAP username of the user)
     *
     * @return array("view"=>array(),"admin"=>array(),"superuser"=>false);
     */
    public function getUserProperty($username)
    {
        $result = array('view' => array(),'admin' => array(),'superuser' => false);
        if ($this->checkBind()) {
            $result['view'] = $this->getUserViewUrls($username);
            $result['admin'] = $this->getUserAdminUrls($username);
            $result['superuser'] = $this->getUserSuperUserStatus($username);
        } else {
            throw \Exception('Can not bind to the LDAP Server');
        }

        return $result;
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
        $result = array('username' => $username, 'email' => '', 'alias' => '');

        return $result;
    }
}
