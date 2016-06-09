<?php

namespace Piwik\Plugins\LoginShibboleth;

use Piwik\Plugins\UsersManager\Model as Model;
use Piwik\Date;
use Piwik\Db;

class LoginShibbolethUser extends Model
{
    /**
     * Placeholder for the settings.
     *
     * @var
     */
    private $settings;

    /**
     * Placeholder for User Login Id.
     *
     * @var
     */
    private $username;

    /**
     * Placeholder for User's Email.
     *
     * @var
     */
    private $email;

    /**
     * Placeholder for the User's alias.
     *
     * @var
     */
    private $alias;

    /**
     * Placeholder for User's token.
     *
     * @var
     */
    private $token;

    /**
     * Placeholder for User's password.
     *
     * @var
     */
    private $password;

    /**
     * Placeholder for UserInfo array.
     *
     * @var
     */
    private $userInfo;

    /**
     * Placeholder for UserProperty array.
     *
     * @var
     */
    private $userProperty;

    /**
     * User Model constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->userInfo = array('username' => '','email' => '','alias' => '');
        $this->userProperty = array('view' => array(),'admin' => array(),'superuser' => false, 'manual' => false);
        $this->primaryAdapter = Config::getPrimaryAdapter();
        $this->ldapActive = Config::isLdapActive();
    }

    /**
     * Retrieves Shibboleth/LdapUserinforamtion.
     *
     * @param string $username The username of the user to get LDAP information for.
     *
     * @return string[] Associative array containing LDAP field data, eg, `array('dn' => '...')`
     */
    public function getUser($username)
    {
        $adminSiteIds = array();
        $viewSiteIds = array();
        $exceptions = array();
        $this->isAdded = false;
        $this->mergeData($username = '');

        $login = $this->userInfo['username'];

        $viewSiteIds = $this->convertDomainPathToId($this->userProperty['view']);
        $adminSiteIds = $this->convertDomainPathToId($this->userProperty['admin']);

        $this->addOrUpdateUserGeneric($login, $viewSiteIds, $adminSiteIds, $this->userProperty['superuser']);
        $this->isAdded = true;

        if (!$this->isAdded) {
            foreach ($exceptions as $e) {
                throw $e;
            }
        }

        return array(
          'login' => $login,
          'alias' => $this->userInfo['alias'],
          'email' => $this->userInfo['email'],
          'token_auth' => $this->userInfo['token'],
          'superuser_access' => $this->userProperty['superuser'],
          'password' => md5($this->getPassword()),
        );
    }

    /**
     * Adds the user with the given rights.
     * It also updates the user if it exists.
     *
     * @param $login string username of the give user
     * @param $viewSiteIds array list of the website the user has view access
     * @param $adminSiteIds array list of the website the user has admin access
     * @param $isSuperUser boolean if the user is SuperUser.
     */
    public function addOrUpdateUserGeneric($login, $viewSiteIds, $adminSiteIds, $isSuperUser)
    {
        $this->userInfo['token'] = $this->getToken();
        if (!$this->userExists($login)) {
            if (sizeof($viewSiteIds) > 0 || sizeof($adminSiteIds) > 0 || $this->userProperty['manual']) {
                $this->addUser(
                    $login,
                    md5($this->getPassword()),
                    $this->userInfo['email'],
                    $this->userInfo['alias'],
                    $this->userInfo['token'],
                    Date::now()->getDatetime(),
                    $isSuperUser
                );
            }
        }
        $viewSiteIdsLocal = array();
        $adminSiteIdsLocal = array();
        if (sizeof($this->getSitesAccessFromUser($login)) > 0) {
            foreach ($this->getSitesAccessFromUser($login) as $siteAccess) {
                if ($siteAccess['access'] == 'view') {
                    array_push($viewSiteIdsLocal, $siteAccess['site']);
                } else {
                    array_push($adminSiteIdsLocal, $siteAccess['site']);
                }
            }
        }
        if (sizeof($viewSiteIds) > 0) {
            $viewDiff = array_diff($viewSiteIds, $viewSiteIdsLocal);
            if (sizeof($viewDiff) > 0) {
                $this->deleteUserAccess($login);
                if ($this->userProperty['manual']) {
                    $toAdd = array_unique(array_merge($viewSiteIds, $viewSiteIdsLocal), SORT_REGULAR);
                } else {
                    $toAdd = $viewDiff;
                }
                $this->addUserAccess($login, 'view', $toAdd);
            }
        }
        if (sizeof($adminSiteIds) > 0) {
            $adminDiff = array_diff($adminSiteIds, $adminSiteIdsLocal);
            if (sizeof($adminDiff) > 0) {
                $this->deleteUserAccess($login);
                if ($this->userProperty['manual']) {
                    $toAdd = array_unique(array_merge($adminSiteIds, $adminSiteIdsLocal), SORT_REGULAR);
                } else {
                    $toAdd = $adminSiteIds;
                }
                $this->addUserAccess($login, 'admin', $toAdd);
            }
        }
        if ($isSuperUser) {
            $this->setSuperUserAccess($login, $isSuperUser);
        }

        if (Config::isDeleteOldUserActive()) {
            if ((sizeof($this->getSitesAccessFromUser($login)) == 0 ||
                (sizeof($viewSiteIds) == 0 &&
                sizeof($adminSiteIds) == 0)) && !$this->userProperty['manual']) {
                $this->deleteUserOnly($login);
            }
        }
    }

    /**
     * Merges the URL from different sources.
     *
     * @param string $username Username of given user from Shibboleth
     */
    private function mergeData($username = '')
    {
        $sh = new ShibbolethAdapter();
        $shibbolethUserInfo = $sh->getUserInfo($username);
        $shibbolethUserProperty = $sh->getUserProperty($username);
        if ($this->ldapActive) {
            $la = new LdapAdapter();
            $ldapUserInfo = $la->getUserInfo($shibbolethUserInfo['username']);
            $ldapUserProperty = $la->getUserProperty($shibbolethUserInfo['username']);
            $this->userProperty['view'] = array_merge($shibbolethUserProperty['view'], $ldapUserProperty['view']);
            $this->userProperty['admin'] = array_merge($shibbolethUserProperty['admin'], $ldapUserProperty['admin']);
            if ($shibbolethUserProperty['superuser'] || $ldapUserProperty['superuser']) {
                $this->userProperty['superuser'] = true;
            }
            if ($shibbolethUserProperty['manual']) {
                $this->userProperty['manual'] = true;
            }
            foreach ($shibbolethUserInfo as $key => $val) {
                if ($val != '') {
                    $this->userInfo[$key] = $val;
                } else {
                    if ($ldapUserInfo[$key] != '') {
                        $this->userInfo[$key] = $ldapUserInfo[$key];
                    } else {
                        throw new \Exception('User does not have mail or alias attribute.');
                    }
                }
            }
        } else {
            $this->userProperty = $shibbolethUserProperty;
            $this->userInfo = $shibbolethUserInfo;
        }
    }

    /**
     * generates the random string for passwords and tokens, behind the shibboleth
     * both are useless.
     *
     * @param int $length Length of the string needed.
     *
     * @return string
     */
    public function getRandomString($length)
    {
        $chars = '1234567890abcdefghijkmnopqrstuvwxyz';
        $i = 0;
        $password = '';
        while ($i <= $length) {
            $password .= $chars{mt_rand(0, strlen($chars) - 1)};
            ++$i;
        }

        return $password;
    }

    /**
     * Get the password.
     *
     * @return string 8char
     */
    public function getPassword()
    {
        return $this->getRandomString(8);
    }

    /**
     * Get the token.
     *
     * @return 32char string
     */
    public function getToken()
    {
        return $this->getRandomString(31);
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
        $siteId = Db::fetchOne('SELECT idsite FROM piwik_site WHERE main_url=?', array($domain));
        if (!$siteId) {
            $siteId = Db::fetchOne('SELECT idsite FROM piwik_site_url WHERE url=?', array($domain));
        }

        return $siteId;
    }

    /**
     * Get the siteIds of a given domains.
     *
     * @param array $section of domains and pathes
     *
     * @return array()
     */
    public function convertDomainPathToId($sections)
    {
        $result = array();
        foreach ($sections as $s) {
            $siteId = intval($this->getSiteId($s['domain'].$s['path']));
            if ($siteId != 0) {
                array_push($result, $siteId);
            }
        }

        return $result;
    }
}
