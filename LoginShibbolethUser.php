<?php


/**
 * Piwik - free/libre analytics platform.
 *
 * @link http://piwik.org
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\LoginShibboleth;

use Piwik\Plugins\UsersManager\Model as Model;
use Piwik\Date;
use Piwik\Db;
use Exception;

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
     * PlaceHolder for UserInfo array.
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
        $this->userInfo = array('username' => '','email' => '','alias' => '');
        $this->userProperty = array('view' => array(),'admin' => array(),'superuser' => false);
        $this->primaryAdapter = Config::getPrimaryAdapter();
        $this->ldapActive = Config::isLdapActive();
        $this->ldapActiveData = Config::getLdapActiveData();
        $this->isViewRestricted = Config::isShibbolethViewRestricted();
        $this->isAdminRestricted = Config::isShibbolethAdminRestricted();
        $this->isAdded = false;
        $this->handleAuth($username);
        $login = $this->userInfo['username'];
        $viewSiteIds = $this->convertDomainPathToId($this->userProperty['view']);

        if (array_key_exists('hasView', $this->userInfo)) {
            if ($this->userInfo['hasView']) {
                $this->addOrUpdateUserGeneric($login, $viewSiteIds, $adminSiteIds, $this->userProperty['superuser']);
                $this->isAdded = true;
            } else {
                array_push($exceptions, new Exception('Adding User View access for '.$login.' is restricted.'));
            }
        } else {
            $this->addOrUpdateUserGeneric($login, $viewSiteIds, $adminSiteIds, $this->userProperty['superuser']);
            $this->isAdded = true;
        }

        // Handles the restricted Admin Access
        $adminSiteIds = $this->convertDomainPathToId($this->userProperty['admin']);
        if (array_key_exists('hasAdmin', $this->userInfo)) {
            if ($this->userInfo['hasAdmin']) {
                $this->addOrUpdateUserGeneric($login, $viewSiteIds, $adminSiteIds, $this->userProperty['superuser']);
                $this->isAdded = true;
            } else {
                array_push($exceptions, new Exception('Adding User Admin access for '.$login.' is restricted.'));
            }
        } else {
            $this->addOrUpdateUserGeneric($login, $viewSiteIds, $adminSiteIds, $this->userProperty['superuser']);
            $this->isAdded = true;
        }
        // If no access was given because of the restrictions.
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
        if (sizeof($this->getSitesAccessFromUser($login)) > 0) {
            $viewSiteIdsLocal = array();
            $adminSiteIdsLocal = array();
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
                $this->addUserAccess($login, 'view', $viewDiff);
            }
        }
        if (sizeof($adminSiteIds) > 0) {
            $adminDiff = array_diff($adminSiteIds, $adminSiteIdsLocal);
            if (sizeof($adminDiff) > 0) {
                $this->addUserAccess($login, 'admin', $adminDiff);
            }
        }
        if ($isSuperUser) {
            $this->setSuperUserAccess($login, $isSuperUser);
        }
        // Delete the user access for the user who are not allowed to get access.
        // Only delete user access is on.
        if (Config::isDeleteOldUserActive()) {
            if (sizeof($this->getSitesAccessFromUser($login)) == 0) {
                $this->deleteUserOnly($login);
            }
        }
    }

    /**
     * Handles the authentication through the settings set in the Piwik Plugin Settings.
     *
     * @param string Username of the given user, not applicable in Shibboleth as
     *             primary adapter.
     */
    private function handleAuth($username = '')
    {
        $shibbolethAdapter = new ShibbolethAdapter();
        $ldapAdapter = new LdapAdapter();
        $shibbolethUserProperty = $shibbolethAdapter->getUserProperty($username);
        $shibbolethUserInfo = $shibbolethAdapter->getUserInfo($username);
        $ldapUserProperty = $ldapAdapter->getUserProperty($shibbolethUserInfo['username']);
        $ldapUserInfo = $ldapAdapter->getUserInfo($shibbolethUserInfo['username']);
        if ($this->primaryAdapter == 'shibboleth') {
            $this->userInfo = $this->mergeInfo($this->userInfo, $shibbolethUserInfo);
            $this->userProperty = $this->mergeInfo($this->userProperty, $shibbolethUserProperty);
            $this->userProperty = $this->mergeInfo($this->userProperty, $ldapUserProperty);
            $this->userInfo = $this->mergeInfo($this->userInfo, $ldapUserInfo);
        } else {
            $this->userProperty = $this->mergeInfo($this->userProperty, $ldapUserProperty);
            $this->userInfo = $this->mergeInfo($this->userInfo, $ldapUserInfo);
            $this->userInfo = $this->mergeInfo($this->userInfo, $shibbolethUserInfo);
            $this->userProperty = $this->mergeInfo($this->userProperty, $shibbolethUserProperty);
        }
    }

    /**
     * Updates the base array with new data out of the source.
     *
     * @param $base string The key of the value given.
     * @param $result array the array that is being chosen
     *
     * @return new generated base data array.
     */
    private function mergeInfo($base, $source)
    {
        foreach ($base as $k => $v) {
            if (array_key_exists($k, $source)) {
                if ($base[$k] == '' || !$base[$k]) {
                    $base[$k] = $source[$k];
                } elseif (gettype($base[$k]) == 'array') {
                    array_push($base[$k], $source[$k]);
                }
            }
        }
        foreach ($source as $k => $v) {
            if (!array_key_exists($k, $base)) {
                $base[$k] = $v;
            }
        }

        return $base;
    }

    /**
     * generates the random string for passwords and tokens, behind the shibboleth
     * both are useless.
     *
     * @param $length int lenght of the string needed.
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
     * Get the siteId of a given domain.
     *
     * @param $section array of domains and pathes
     *
     * @return int
     */
    public function convertDomainPathToId($sections)
    {
        $result = array();
        foreach ($sections as $s) {
            array_push($result, intval($this->getSiteId($s['domain'].$s['path'])));
        }

        return $result;
    }
}
