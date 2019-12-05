<?php

/**
 * Login Shibboleth Plugin.
 */

namespace Piwik\Plugins\LoginShibboleth;

use RuntimeException;
use Countable;

/**
 * LdapAdapter is the LDAP data retrieval adapter.
 *
 * It handles all the LDAP connections of the plug-in to LDAP server.
 * Settings are read from the Piwik config file. More LDAP functionalities can be added here if needed.
 * before going productive, test the functionalities with test suites available.
 *
 * @author Pouyan Azari <pouyan.azari@uni-wuerzburg.de>
 * @license MIT
 * @copyright 2014-2019 University of Wuerzburg
 * @copyright 2014-2019 Pouyan Azari
 */
class LdapAdapter
{
    /**
     * Placeholder for LDAP username.
     *
     * @var
     */
    private $username;
    /**
     * Placeholder for LDAP password.
     *
     * @var
     */
    private $password;
    /**
     * Placeholder for LDAP Host.
     *
     * @var
     */
    private $host;
    /**
     * Placeholder for LDAP Port.
     *
     * @var
     */
    private $port;

    /**
     * Placeholder for LDAP Active Data sources.
     *
     * @var
     */
    private $activeData;

    /**
     * @var mixed
     */
    private $connection;

    /**
     * @var mixed
     */
    private $bind;

    /**
     * Initializer.
     */
    public function __construct()
    {
        $this->username = Config::getLdapUserName();
        $this->password = Config::getLdapPassword();
        $this->host = Config::getLdapHost();
        $this->port = Config::getLdapPort();
        $this->activeData = Config::getLdapActiveData();
        $this->connection = false;
        $this->bind = false;
    }

    /**
     * Checks the connection at the Settings.
     **
     * @throws RuntimeException
     */
    public function connectLdap()
    {
        if ($this->connection === false) {
            $this->connection = ldap_connect($this->host, $this->port);
        }
    }

    /**
     * Checks if the LDAP can bind.
     **
     * @throws RuntimeException
     */
    public function bindLdap()
    {
        $this->connectLdap();
        if ($this->bind === false) {
            $this->bind = ldap_bind($this->connection, $this->username, $this->password);
        }
    }

    /**
     * Search LDAP Abstract.
     *
     * @param string $filter Filter for the LDAP query
     * @param array $attributes Needed attributes for the LDAP query
     * @param string $dn Distinguished Names to search in
     *
     * @return array
     * @throws RuntimeException
     */
    public function searchLdap($filter, $attributes, $dn)
    {
        $result = array('count' => 0);
        $this->bindLdap();
        if ($this->bind === false) {
            throw new RuntimeException('Problem connecting to LDAP');
        }
        if ($dn === '') {
            throw new RuntimeException('DN is not set');
        }
        $searchComm = ldap_search($this->connection, $dn, $filter, $attributes);
        if ($searchComm) {
            $result = ldap_get_entries($this->connection, $searchComm);
        }
        return $result;
    }

    /**
     * Returns the given attribute from the ldap result when it exists.
     *
     * @param array $ldapResult
     * @param string $attribute
     * @return array
     */
    public function getLdapAttribute($ldapResult, $attribute)
    {
        if ($ldapResult['count'] > 0) {
            $entry = $ldapResult[0];
            return $this->getEntryLdapAttribute($entry, $attribute);
        }
        return array();
    }

    /**
     * Returns the entry attribute
     *
     * @param array $entry
     * @param string $attribute
     * @return array
     */
    public function getEntryLdapAttribute($entry, $attribute)
    {
        if (isset($entry[$attribute]) === true && $entry[$attribute]['count'] > 0) {
            unset($entry[$attribute]['count']);
            return $entry[$attribute];
        }
        return array();
    }

    /**
     * Returns a single Ldap Entry attribute value
     *
     * @param array $entry
     * @param string $attribute
     * @return string
     */
    public function getLdapEntryAttributeSingleValue($entry, $attribute)
    {
        $attributeValues = $this->getEntryLdapAttribute($entry, $attribute);
        if (count($attributeValues) === 1) {
            return $attributeValues[0];
        }
        return '';
    }

    /**
     * Returns the single ldap attribute value from the given ldap results.
     *
     * @param array $ldapResult
     * @param string $attribute
     * @return string
     */
    public function getLdapAttributeSingleValue($ldapResult, $attribute)
    {
        $attributeValues = $this->getLdapAttribute($ldapResult, $attribute);
        if (count($attributeValues) === 1) {
            return $attributeValues[0];
        }
        return '';
    }

    /**
     * Generic function for the LDAP filter and attributes.
     *
     * @param string $username The username from Shibboleth
     * @param string $type The Type of the filterAttr (View or Admin)
     * @param string $filString The filter placeholder string
     * @param string $attString The attributes placeholder string
     * @param string $sep The Separator string
     * @param bool $lActive The Flag for LDAP:
     * @param array $lData The Array containing active LDAP component (View, SuperUser, Admin)
     *
     * @return array ex. `array("filter"=>string, "attrs"=>array(domainPlaceholder, pathPlaceholder))`
     */
    public function getGenericFilterAttr(
        $username,
        $type = '',
        $filString = null,
        $attString = null,
        $sep = null,
        $lActive = false,
        $lData = array()
    )
    {
        $filter = '';
        $attrs = array();
        $result = array('filter' => $filter, 'attrs' => $attrs);
        if ((bool)$lActive === true) {
            if (in_array('ldapView', $lData, true)) {
                if ($filString !== null) {
                    preg_match('/\\?/', $filString, $matched);
                    if (count($matched) > 0) {
                        $filter = sprintf(str_replace('?', '%1$s', $filString), $username);
                        $result['filter'] = $filter;
                    } else {
                        throw new RuntimeException(
                            sprintf('There is not ? used in LDAP %s filter to be replaced by username', $type)
                        );
                    }
                } else {
                    throw new RuntimeException(
                        sprintf('If you activate the LDAP %s data, you should set the filter', $type)
                    );
                }
                if ($attString !== null) {
                    if ($sep !== null) {
                        $attrs = array_map(
                            'trim',
                            explode($sep, $attString)
                        );
                        $attrs = array_map('strtolower', $attrs);
                        $result['attrs'] = $attrs;
                    } else {
                        throw new RuntimeException(
                            'You should set the separator. It is set in Shibboleth Setting'
                        );
                    }
                } else {
                    throw new RuntimeException(
                        sprintf('If you activate the LDAP % data, you should set the attributes', $type)
                    );
                }
            } else {
                throw new RuntimeException(sprintf('LDAP %s data is not active, set it in select list.', $type));
            }
        } else {
            throw new RuntimeException('LDAP is not active so it can not be used.');
        }
        return $result;
    }

    /**
     * Returns the View LDAP filter and attributes.
     *
     * @param string $username The username from Shibboleth
     * @param string $filString The filter placeholder string
     * @param string $attString The attributes placeholder string
     * @param string $sep The Separator string
     * @param bool $lActive The Flag for LDAP
     * @param array $lData The Array containing active LDAP component (View, SuperUser, Admin)
     *
     * @return array ex. `array("filter"=>string, "attrs"=>array(domainPlaceholder, pathPlaceholder))`
     */
    public function getViewFilterAttr(
        $username,
        $filString = null,
        $attString = null,
        $sep = null,
        $lActive = false,
        $lData = array()
    )
    {
        if ($filString === null) {
            $filString = Config::getLdapViewFilter();
        }
        if ($attString === null) {
            $attString = Config::getLdapViewAttrs();
        }
        if ($sep === null) {
            $sep = Config::getShibbolethSeparator();
        }
        if (!$lActive) {
            $lActive = (bool)Config::isLdapActive();
        }
        if (count($lData) === 0) {
            $lData = Config::getLdapActiveData();
        }
        return $this->getGenericFilterAttr($username, 'View', $filString, $attString, $sep, $lActive, $lData);
    }

    /**
     * Returns the Admin filters and attributes for LDAP.
     *
     * @param string $username The username from Shibboleth
     * @param string $filString The filter placeholder string
     * @param string $attString The attributes placeholder string
     * @param string $sep The Separator string
     * @param bool $lActive The Flag for LDAP
     * @param array $lData The Array containing active LDAP component (View, SuperUser, Admin)
     *
     * @return array ex. `array("filter"=>string, "attrs"=>array(domainPlaceholder, pathPlaceholder))`
     */
    public function getAdminFilterAttr(
        $username,
        $filString = null,
        $attString = null,
        $sep = null,
        $lActive = false,
        $lData = array()
    )
    {
        if ($filString === null) {
            $filString = Config::getLdapAdminFilter();
        }
        if ($attString === null) {
            $attString = Config::getLdapAdminAttrs();
        }
        if ($sep === null) {
            $sep = Config::getShibbolethSeparator();
        }
        if (!$lActive) {
            $lActive = Config::isLdapActive();
        }
        if (count($lData) === 0) {
            $lData = Config::getLdapActiveData();
        }

        return $this->getGenericFilterAttr($username, 'Admin', $filString, $attString, $sep, $lActive, $lData);
    }

    /**
     * @param $username
     * @param $accessType
     * @param array $availableAccess
     * @return array
     * @throws RuntimeException
     */
    public function getManagedUrls($username, $accessType, $availableAccess = array('View', 'Admin'))
    {
        $filterAttrs = array();
        $urls = array();
        if (in_array($accessType, $availableAccess, true)) {
            if ($accessType === 'View') {
                $filterAttrs = $this->getViewFilterAttr($username);
            }
            if ($accessType === 'Admin') {
                $filterAttrs = $this->getAdminFilterAttr($username);
            }
        } else {
            throw new RuntimeException(sprintf('AccessType can be one of Admin or View not %s', $accessType));
        }
        $ldapResult = $this->searchLdap(
            $filterAttrs['filter'],
            $filterAttrs['attrs'],
            Config::getLdapDNForAccessSearch()
        );
        if ($ldapResult['count'] > 0) {
            unset($ldapResult['count']);
            foreach ($ldapResult as $entry) {
                if (count($filterAttrs['attrs']) > 1) {
                    list($domainAttribute, $pathAttribute) = $filterAttrs['attrs'];
                    $domain = $this->getLdapEntryAttributeSingleValue($entry, $domainAttribute);
                    $path = $this->getLdapEntryAttributeSingleValue($entry, $pathAttribute);
                    if ($path === '/') {
                        $path = '';
                    }
                    $tmp_url = array('domain' => $domain, 'path' => $path);
                } else {
                    list($domainAttribute) = $filterAttrs['attrs'];
                    $domain = $this->getLdapEntryAttributeSingleValue($entry, $domainAttribute);
                    $tmp_url = array('domain' => $domain, 'path' => '');
                }
                $urls[] = $tmp_url;
            }
        }
        return $urls;
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
        if (in_array($superUserId, $this->activeData, true)) {
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
                        if ($result === false && in_array($lsup, $l[$filterAttrs['attrs'][0]], true)) {
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
     * @return array ex `array("view"=>array(),"admin"=>array(),"superuser"=>false)``
     * @throws RuntimeException
     *
     */
    public function getUserProperty($username)
    {
        $this->bindLdap();
        $result = array('view' => array(), 'admin' => array(), 'superuser' => false, 'manual' => false);
        if ($this->bind === true) {
            $isUserSuperUser = $this->getUserSuperUserStatus($username);
            if ($isUserSuperUser === false) {
                $result['view'] = $this->getManagedUrls($username, 'View');
                $result['admin'] = $this->getManagedUrls($username, 'Admin');
            }
        } else {
            throw new RuntimeException('Can not bind to the LDAP Server');
        }
        return $result;
    }

    /**
     * @param string $username
     * @return mixed|string
     * @throws RuntimeException
     */
    public function getMail($username = '')
    {
        if (Config::getLdapUserDataActive()) {
            if (Config::getLdapDNForUserSearch() === '') {
                throw new RuntimeException('DN for Ldap User search should be set.');
            }
            if (Config::getLdapUserEmail() !== '') {
                if (Config::getLdapUserUsername() !== '') {
                    $filter = sprintf('(%s=%s)', Config::getLdapUserUsername(), $username);
                    $attr = array(Config::getLdapUserEmail());
                    $ldapResult = $this->searchLdap($filter, $attr, Config::getLdapDNForUserSearch());
                    return $this->getLdapAttributeSingleValue($ldapResult, $attr[0]);
                }
                throw new RuntimeException('Please set LDAP User username key');
            }
            throw new RuntimeException('Please set LDAP User mail key');
        }
        return '';
    }

    /**
     * Returns the Alias for the given user
     *
     * @param string $username
     * @return string
     * @throws RuntimeException
     */
    public function getAlias($username = '')
    {
        if (Config::getLdapUserDataActive()) {
            if (Config::getLdapDNForUserSearch() === '') {
                throw new RuntimeException('DN for Ldap User search should be set.');
            }
            if (Config::getLdapUserAlias() !== '') {
                if (Config::getLdapUserUsername() !== '') {
                    $filter = sprintf('(%s=%s)', Config::getLdapUserUsername(), $username);
                    $attrs = explode(Config::getShibbolethSeparator(), Config::getLdapUserAlias());
                    $attrs = array_map('strtolower', $attrs);
                    $ldapResult = $this->searchLdap($filter, $attrs, Config::getLdapDNForUserSearch());
                    if (count($attrs) > 1) {
                        return sprintf('%s %s',
                            $this->getLdapAttributeSingleValue($ldapResult, $attrs[0]),
                            $this->getLdapAttributeSingleValue($ldapResult, $attrs[1])
                        );
                    }
                    if ($ldapResult['count'] > 0) {
                        return $this->getLdapAttributeSingleValue($ldapResult, $attrs[0]);
                    }
                    return '';
                }
                throw new RuntimeException('Please set LDAP User username key');
            }
            throw new RuntimeException('Please set LDAP User mail key');
        }
        return '';
    }

    /**
     * Returns the User information.
     *
     * @param string $username Username of the user from Shibboleth
     *
     * @return array ex. `array("username"=>"", "email"=>, "alias"=>"")`
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