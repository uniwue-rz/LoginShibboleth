<?php

/**
 * Login Shibboleth Plugin.
 */

namespace Piwik\Plugins\LoginShibboleth;

/**
 * LDAP adapter function. This handles all the LDAP connection of the plugin to LDAP server.
 * Settings are read from the Piwik config file.
 *
 * @author Pouyan Azari <pouyan.azari@uni-wuerzburg.de>
 * @license MIT
 */
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
        $this->host = Config::getLdapHost();
        $this->port = Config::getLdapPort();
        $this->active = Config::isLdapActive();
        $this->activeData = Config::getLdapActiveData();
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
     * Checks if the LDAP can bind.
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

        throw new \Exception('Can not bind with the LDAP Server');
    }

    /**
     * Search LDAP Abstract.
     *
     * @param string $filter     Filter for the LDAP query
     * @param array  $attributes Needed attributes for the LDAP query
     *
     * @return multi-dimensional array
     */
    public function searchLdap($filter, $attrs, $dn)
    {
        $result = array('count' => 0);
        if ($this->checkBind()) {
        } else {
            throw new \Exception('Problem connecting to LDAP');
        }
        if ($dn == '') {
            throw new \Exception('DN is not set');
        }
        $searchComm = ldap_search($this->conn, $dn, $filter, $attrs);
        if ($searchComm) {
            $result = ldap_get_entries($this->conn, $searchComm);
        }

        return $result;
    }

    /**
     * Generic function for the LDAP filter and attributes.
     *
     * @param string $username  The username from Shibboleth
     * @param string $filString The filter placeholder string
     * @param string $attString The attributes placeholder string
     * @param string $sep       The Separator string
     * @param bool   $lActive   The Flag for LDAP:
     * @param array  $lData     The Array containing active LDAP component (View, SuperUser, Admin)
     * @param string $types     The Type of the filterAttr (View or Admin)
     *
     * @return array("filter"=>string, "attrs"=>array(domainPlaceholder, pathPlaceholder))
     */
    public function getGenericFilterAttr(
        $username,
        $type = '',
        $filString = false,
        $attString = false,
        $sep = false,
        $lActive = false,
        $lData = array()
    ) {
        $filter = '';
        $attrs = array();
        $result = array('filter' => $filter, 'attrs' => $attrs);
        if ($lActive) {
            if (in_array('ldapView', $lData)) {
                if ($filString != '') {
                    preg_match('/\\?/', $filString, $matched);
                    if (sizeof($matched) > 0) {
                        $filter = sprintf(str_replace('?', '%1$s', $filString), $username);
                        $result['filter'] = $filter;
                    } else {
                        throw new \Exception("There is not ? used in LDAP $type filter to be replaced by usernamme");
                    }
                } else {
                    throw new \Exception("If you activate the LDAP $type data".
                        ', you should set the filter');
                }
                if ($attString != '') {
                    if ($sep != '') {
                        $attrs = array_map(
                            'trim',
                            explode($sep, $attString)
                        );
                        $attrs = array_map('strtolower', $attrs);
                        $result['attrs'] = $attrs;
                    } else {
                        throw new \Exception('You should set the separator. It is set in Shibboleth Setting');
                    }
                } else {
                    throw new \Exception('If you activate the LDAP '.$type.' data'.
                      ', you should set the attributes');
                }
            } else {
                throw new \Exception('LDAP '.$type.' data is not active, set it in select list.');
            }
        } else {
            throw new \Exception('LDAP is not active so it can not be used.');
        }

        return $result;
    }

    /**
     * Returns the View LDAP filter and attributes.
     *
     * @param string $username  The username from Shibboleth
     * @param string $filString The filter placeholder string
     * @param string $attString The attributes placeholder string
     * @param string $sep       The Separator string
     * @param bool   $lActive   The Flag for LDAP
     * @param array  $lData     The Array containing active LDAP component (View, SuperUser, Admin)
     *
     * @return array("filter"=>string, "attrs"=>array(domainPlaceholder, pathPlaceholder))
     */
    public function getViewFilterAttr(
        $username,
        $filString = false,
        $attString = false,
        $sep = false,
        $lActive = false,
        $lData = array()
    ) {
        if (!$filString) {
            $filString = Config::getLdapViewFilter();
        }
        if (!$attString) {
            $attString = Config::getLdapViewAttrs();
        }
        if (!$sep) {
            $sep = Config::getShibbolethSeparator();
        }
        if (!$lActive) {
            $lActive = (bool) Config::isLdapActive();
        }
        if (sizeof($lData) == 0) {
            $lData = Config::getLdapActiveData();
        }

        return $this->getGenericFilterAttr($username, 'View', $filString, $attString, $sep, $lActive, $lData);
    }

    /**
     * Returns the Admin filters and attributes for LDAP.
     *
     * @param string $username Username used, coming from Shibboleth
     */
    public function getAdminFilterAttr(
        $username,
        $filString = false,
        $attString = false,
        $sep = false,
        $lActive = false,
        $lData = array()
    ) {
        if (!$filString) {
            $filString = Config::getLdapAdminFilter();
        }
        if (!$attString) {
            $attString = Config::getLdapAdminAttrs();
        }
        if (!$sep) {
            $sep = Config::getShibbolethSeparator();
        }
        if (!$lActive) {
            $lActive = Config::isLdapActive();
        }
        if (sizeof($lData) == 0) {
            $lData = Config::getLdapActiveData();
        }

        return $this->getGenericFilterAttr($username, 'Admin', $filString, $attString, $sep, $lActive, $lData);
    }

    /**
     * Returns the URL for a given user on hand the userType.
     *
     * @param string $username        Username which the user typed in the login form.
     * @param string $accessType      Type of the user Access (View, Admin)
     * @param array  $availableAccess Array including the available access types.
     *
     * @return array ("domain"=>"www....", "path"=>"/...")
     */
    public function getManagedUrls($username, $accessType, $availableAccess = array('View', 'Admin'))
    {
        $urls = array();
        if (in_array($accessType, $availableAccess)) {
            if ($accessType == 'View') {
                $filterAttrs = $this->getViewFilterAttr($username);
            }
            if ($accessType == 'Admin') {
                $filterAttrs = $this->getAdminFilterAttr($username);
            }
        } else {
            throw new \Exception('"View" or "Admin" LDAP access types are at this moment available, '.
                                 "your access type is $accessType");
        }
        $ldapResult = $this->searchLdap(
            $filterAttrs['filter'],
            $filterAttrs['attrs'],
            Config::getLdapDNForAccessSearch()
        );
        if ($ldapResult['count'] > 0) {
            unset($ldapResult['count']);
            foreach ($ldapResult as $l) {
                if (sizeof($filterAttrs['attrs']) > 1) {
                    $domain = $l[$filterAttrs['attrs'][0]][0];
                    $path = $l[$filterAttrs['attrs'][1]][0];
                    if ($path == '/') {
                        $path = '';
                    }
                    $tmp_url = array('domain' => $domain, 'path' => $path);
                } else {
                    $domain = $l[$filterAttrs['attrs'][0]][0];
                    $tmp_url = array('domain' => $domain, 'path' => '');
                }
                array_push($urls, $tmp_url);
            }
        }

        return $urls;
    }

    /**
     * Returns the username's view URL according to the Setting.
     *
     * @param string $username The username from Shibboleth
     *
     * @return array ("domain"=>"www....", "path"=>"/...")
     */
    public function getUserViewUrls($username)
    {
        if (in_array('ldapView', $this->activeData)) {
            return $this->getManagedUrls($username, 'View');
        }

        return array();
    }

    /**
     * Returns the username's Admin URL according to the setting.
     *
     * @param string $username The username from the Shibboleth.
     *
     * @return array ("domain"=>"www....", "path"=>"/...")
     */
    public function getUserAdminUrls($username)
    {
        if (in_array('ldapAdmin', $this->activeData)) {
            return $this->getManagedUrls($username, 'Admin');
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
            $filterAttrs = $this->getGenericFilterAttr(
                $username,
                'SuperUser',
                Config::getLdapSuperUserFilter(),
                Config::getLdapSuperUserAttrs(),
                Config::getShibbolethSeparator(),
                Config::isLdapActive(),
                Config::getLdapActiveData()
            );
            $ldapResult = $this->searchLdap(
                $filterAttrs['filter'],
                $filterAttrs['attrs'],
                Config::getLdapDNForUserSearch()
            );
            $ldapSuperUserGroups = explode(Config::getShibbolethSeparator(), Config::getLdapSuperUserValue());
            if ($ldapResult['count'] > 0) {
                unset($ldapResult['count']);
                foreach ($ldapResult as $l) {
                    foreach ($ldapSuperUserGroups as $lsup) {
                        if (in_array($lsup, $l[$filterAttrs['attrs'][0]]) && !$result) {
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
        $result = array('view' => array(),'admin' => array(),'superuser' => false, 'manual'=>false);
        if ($this->checkBind()) {
            $result['view'] = $this->getManagedUrls($username, "View");
            $result['admin'] = $this->getManagedUrls($username, "Admin");
            $result['superuser'] = $this->getUserSuperUserStatus($username);
        } else {
            throw \Exception('Can not bind to the LDAP Server');
        }

        return $result;
    }

    /**
     * Returns user mail from LDAP.
     *
     * @param string $username Username of the given user from Shibboleth.
     *
     * @return string
     */
    public function getMail($username = '')
    {
        if (Config::getLdapUserDataActive()) {
            if (Config::getLdapDNForUserSearch() == '') {
                throw new \Exception('DN for Ldap User search should be set.');
            }
            if (Config::getLdapUserEmail() != '') {
                if (Config::getLdapUserUsername() != '') {
                    $filter = '('.Config::getLdapUserUsername().'='.$username.')';
                    $attr = array(Config::getLdapUserEmail());
                    $ldapResult = $this->searchLdap($filter, $attr, Config::getLdapDNForUserSearch());
                    if ($ldapResult['count'] > 0) {
                        return $ldapResult[0][$attr[0]][0];
                    }
                } else {
                    throw new \Exception('Please set LDAP User username key');
                }
            } else {
                throw new \Exception('Please set LDAP User mail key');
            }
        } else {
            return '';
        }
    }

    /**
     * Returns the url of the given group on hand the setting.
     *
     * @param string $group The group name.
     *
     * @return string
     */
    public function getGroupUrl($group = false)
    {
        if ($group) {
        }
    }

    /**
     * Returns user alias from LDAP.
     *
     * @param string $username Username of the given user from Shibboleth.
     *
     * @return string
     */
    public function getAlias($username = '')
    {
        if (Config::getLdapUserDataActive()) {
            if (Config::getLdapDNForUserSearch() == '') {
                throw new \Exception('DN for Ldap User search should be set.');
            }
            if (Config::getLdapUserAlias() != '') {
                if (Config::getLdapUserUsername() != '') {
                    $filter = '('.Config::getLdapUserUsername().'='.$username.')';
                    $attrs = explode(Config::getShibbolethSeparator(), Config::getLdapUserAlias());
                    $attrs = array_map('strtolower', $attrs);
                    $ldapResult = $this->searchLdap($filter, $attrs, Config::getLdapDNForUserSearch());
                    if (sizeof($attrs) > 1) {
                        if ($ldapResult['count'] > 0) {
                            return $ldapResult[0][$attrs[0]][0].' '.$ldapResult[0][$attrs[1]][0];
                        } else {
                            return '';
                        }
                    } else {
                        if ($ldapResult['count'] > 0) {
                            return $ldapResult[0][$attr[0]][0];
                        } else {
                            return '';
                        }
                    }
                } else {
                    throw new \Exception('Please set LDAP User username key');
                }
            } else {
                throw new \Exception('Please set LDAP User mail key');
            }
        } else {
            return '';
        }
    }

    /**
     * Returns the User information.
     *
     * @param string $username Username of the user from Shibboleth
     *
     * @return array("username"=>"", "email"=>, "alias"=>"")
     */
    public function getUserInfo($username = '')
    {
        $result = array('username' => $username, 'email' => '', 'alias' => '');
        if (Config::getLdapUserDataActive()) {
            $result['email'] = $this->getMail($username);
            $result['alias'] = $this->getAlias($username);
        }

        return $result;
    }
}
