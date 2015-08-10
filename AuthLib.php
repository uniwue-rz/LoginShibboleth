<?php

/*
 * Piwik - Open source web analytics.
 *
 * @link http://piwik.org
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 *
 * @package LoginShibboleth
 **/

namespace Piwik\Plugins\LoginShibboleth;

use Piwik\Db;

class AuthLib
{
    private $login;
    private $email;
    private $alias;
    private $websites;
    private $websites_type;
    private $websites_param;
    private $superuser;
    private $superuser_type;
    private $superuser_param;
    private $access_level;
    private $access_level_type;
    private $access_level_param;

    public function getLogin()
    {
        return $this->login;
    }

    public function setLogin($login)
    {
        $this->login = $login;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function getWebsites($login = false)
    {
        if (!$login) {
            $this->login = $login;
        }

        return $this->get_websites;
    }

    public function setWebsites($websites)
    {
        $this->websites = $websites;
    }

    public function getAccessLevel()
    {
        return $this->access_level;
    }

    public function setAccessLevel($access_level)
    {
        $this->access_level = $access_level;
    }

    public function getWebsitesType()
    {
        return $this->websites_type;
    }

    public function setWebsitesType($websites_type)
    {
        $this->websites_type = $websites_type;
    }

    public function getAccessLevelType()
    {
        return $this->access_level_type;
    }

    public function setAccessLevelType($access_level_type)
    {
        $this->access_level_type = $access_level_type;
    }

    public function getSuperuserType()
    {
        return $this->superuser_type;
    }
    public function setSuperuserType($superuser_type)
    {
        $this->superuser_type = $superuser_type;
    }

    public function getSuperuserParam()
    {
        return $this->get_superuser_param;
    }

    public function setSuperuserParam($set_superuser_param)
    {
        $this->set_superuser_param = $set_superuser_param;
    }

    public function getAccessLevelParam()
    {
        return $this->access_level_param;
    }

    public function setAccessLevelParam($access_level_param)
    {
        $this->access_level_param = $access_level_param;
    }

    public function getWebsitesParam()
    {
        return $this->website_param;
    }

    public function getAlias()
    {
        return $this->alias;
    }

    public function setAlias($alias)
    {
        $this->alias = $alias;
    }

    public function setWebsitesParam($websites_param)
    {
        $this->websites_param = $websites_param;
    }

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

    public function setToken($token)
    {
        $this->token = $token;
    }

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
