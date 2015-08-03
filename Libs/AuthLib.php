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

namespace Piwik\Plugins\LoginShibboleth\Lib;

class AuthLib
{
    private $login;
    private $email;
    private $websites;
    private $websites_type;
    private $websites_param;
    private $superuser;
    private $superuser_type;
    private $superuser_param;
    private $access_level;
    private $access_level_type;
    private $access_level_param;

    public function get_login()
    {
        return $this->login;
    }

    public function set_login($login)
    {
        $this->login = $login;
    }

    public function get_email()
    {
        return $this->email;
    }

    public function set_email($email)
    {
        $this->email = $email;
    }

    public function get_websites()
    {
        return $this->get_websites;
    }

    public function set_websites($websites)
    {
        $this->websites = $websites;
    }

    public function get_access_level()
    {
        return $this->access_level;
    }

    public function set_access_level($access_level)
    {
        $this->access_level = $access_level;
    }

    public function get_websites_type()
    {
        return $this->websites_type;
    }

    public function set_websites_type($websites_type)
    {
        $this->websites_type = $websites_type;
    }

    public function get_access_level_type()
    {
        return $this->access_level_type;
    }

    public function set_access_level_type($access_level_type)
    {
        $this->access_level_type = $access_level_type;
    }

    public function get_superuser_type()
    {
        return $this->superuser_type;
    }
    public function set_superuser_type($superuser_type)
    {
        $this->superuser_type = $superuser_type;
    }

    public function get_superuser_param()
    {
        return $this->get_superuser_param;
    }

    public function set_superuser_param($set_superuser_param)
    {
        $this->set_superuser_param = $set_superuser_param;
    }

    public function get_access_level_param()
    {
        return $this->access_level_param;
    }

    public function set_access_level_param($access_level_param)
    {
        $this->access_level_param = $access_level_param;
    }

    public function get_websites_param()
    {
        return $this->website_param;
    }

    public function set_websites_param($websites_param)
    {
        $this->websites_param = $websites_param;
    }
}
