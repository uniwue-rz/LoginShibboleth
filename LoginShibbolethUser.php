<?php

/**
 * Part of the LoginShibboleth Plugin.
 */

namespace Piwik\Plugins\LoginShibboleth;

use Exception;
use Piwik\Common;
use Piwik\Date;
use Piwik\Db;
use Piwik\Plugins\UsersManager\Model as UserModel;
use RuntimeException;

/**
 * LoginShibbolethUser is the user Model class for Shibboleth.
 *
 * This class inherit most of the functions from the Model class of Piwik
 * UsersManager. Any changes to default functions of Model should be added
 * here. The overridden functions of Model are getUser and __construct.
 *
 *
 * @author Pouyan Azari <pouyan.azari@uni-wuerzburg.de>
 * @license MIT
 * @copyright 2014-2019 University of Wuerzburg
 * @copyright 2014-2019 Pouyan Azari
 */
class LoginShibbolethUser extends UserModel
{
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
     * @var bool
     */
    private $ldapActive;

    /**
     * @var bool
     */
    private $isAdded;

    /**
     * User Model constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->userInfo = array('username' => '', 'email' => '', 'alias' => '');
        $this->userProperty = array('view' => array(), 'admin' => array(), 'superuser' => false, 'manual' => false);
        $this->ldapActive = Config::isLdapActive();
        $this->isAdded = false;
    }

    /**
     * @param $username
     * @return array|mixed
     * @throws Exception
     */
    public function getUser($username)
    {
        $exceptions = array();
        $this->isAdded = false;
        $this->mergeData($username);
        $login = $this->userInfo['username'];
        $viewSiteIds = $this->convertDomainPathToId($this->userProperty['view']);
        $adminSiteIds = $this->convertDomainPathToId($this->userProperty['admin']);
        $this->addOrUpdateUserGeneric($login, $viewSiteIds, $adminSiteIds, $this->userProperty['superuser']);
        $this->isAdded = true;
        if (!$this->isAdded && count($exceptions) > 0) {
            throw $exceptions[0];
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
     * @param string $login Username of the give user
     * @param array $viewSiteIds List of the website the user has view access
     * @param array $adminSiteIds List of the website the user has admin access
     * @param bool $isSuperUser If the user is SuperUser.
     */
    private function addOrUpdateUserGeneric($login, $viewSiteIds, $adminSiteIds, $isSuperUser)
    {
        $this->userInfo['token'] = $this->getToken();
        if (!$this->userExists($login)) {
            if ($this->userProperty['manual'] || count($viewSiteIds) > 0 || count($adminSiteIds) > 0) {
                $this->addUser(
                    $login,
                    md5($this->getPassword()),
                    $this->userInfo['email'],
                    $this->userInfo['alias'],
                    $this->userInfo['token'],
                    Date::now()->getDatetime()
                );
            }
        }
        if ($isSuperUser === true) {
            $this->setSuperUserAccess($login, true);
        }
        $viewSiteIdsLocal = array();
        $adminSiteIdsLocal = array();
        if (count($this->getSitesAccessFromUser($login)) > 0) {
            foreach ($this->getSitesAccessFromUser($login) as $siteAccess) {
                if ($siteAccess['access'] === 'view') {
                    $viewSiteIdsLocal[] = $siteAccess['site'];
                } else {
                    $adminSiteIdsLocal[] = $siteAccess['site'];
                }
            }
        }
        if (count($viewSiteIds) > 0) {
            $viewDiff = array_diff($viewSiteIds, $viewSiteIdsLocal);
            if (count($viewDiff) > 0) {
                $this->deleteUserAccess($login);
                if ($this->userProperty['manual']) {
                    $toAdd = array_unique(array_merge($viewSiteIds, $viewSiteIdsLocal), SORT_REGULAR);
                } else {
                    $toAdd = $viewDiff;
                }
                $this->addUserAccess($login, 'view', $toAdd);
            }
        }
        if (count($adminSiteIds) > 0) {
            $adminDiff = array_diff($adminSiteIds, $adminSiteIdsLocal);
            if (count($adminDiff) > 0) {
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

        if ($this->userProperty['manual'] === false && Config::isDeleteOldUserActive() && (count($this->getSitesAccessFromUser($login)) === 0 ||
                (count($viewSiteIds) === 0 &&
                    count($adminSiteIds) === 0))) {
            $this->deleteUserOnly($login);
        }
    }

    /**
     * @param string $username
     * @throws Exception
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
                if ($val !== '') {
                    $this->userInfo[$key] = $val;
                } else if ($ldapUserInfo[$key] !== '') {
                    $this->userInfo[$key] = $ldapUserInfo[$key];
                } else {
                    throw new RuntimeException('User does not have mail or alias attribute.');
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
            $password .= $chars[mt_rand(0, strlen($chars) - 1)];
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
        if (array_key_exists('password', $this->userInfo)) {
            return $this->userInfo['password'];
        }
        return $this->getRandomString(8);
    }

    /**
     * Get the token.
     *
     * @return string
     */
    public function getToken()
    {
        if (array_key_exists('token', $this->userInfo)) {
            return $this->userInfo['token'];
        }
        return $this->getRandomString(31);
    }

    /**
     * @param $domain
     * @return string
     * @throws Exception
     */
    private function getSiteId($domain)
    {
        $parsedDomain = parse_url($domain);
        $domain = 'http://' . $parsedDomain['path'];
	$siteId = Db::fetchOne('SELECT idsite FROM ' . Common::prefixTable('site') . ' WHERE main_url=?',
            array($domain));
	if (!$siteId) {
		$siteId = Db::fetchOne('SELECT idsite FROM ' . Common::prefixTable('site_url') . '  WHERE url=?',
                array($domain));
        }
        return $siteId;
    }

    /**
     * @param $sections
     * @return array
     * @throws Exception
     */
    private function convertDomainPathToId($sections)
    {
        $result = array();
        foreach ($sections as $s) {
            $siteId = (int)$this->getSiteId($s['domain'] . $s['path']);
            if ($siteId !== 0) {
                $result[] = $siteId;
            }
        }
        return $result;
    }
}
