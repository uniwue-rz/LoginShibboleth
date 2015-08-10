<?php

namespace Piwik\Plugins\LoginShibboleth;

class Auth
{
    private $config;
    private $shib;
    private $ldap;
    public function __construct()
    {
        $this->config = $this->getConfig();
        $this->shib = new Shibboleth();
        $this->ldap = new Ldap();
    }

    /**
     * Finds the right auth library for the given config.
     *
     * @return Auth (object)
     */
    public function findLib($lib)
    {
        switch ($lib) {
        case 'shib':
          return $this->shib;
        case 'ldap':
          return $this->ldap;
      }
    }

    /**
     * Get the configuration. as a sorted array. The keys will be checked
     * agianst a pre-written key list. They should cover the whole list.
     * The first on the list will be used as a first parameter.
     *
     * @return array
     */
    public function getConfig()
    {
        $result = array();
        $config = parse_ini_file('config.ini.php');
        $datasource = $config['datasource'];
        $config_must_key = array('login',
                              'email',
                              'alias',
                              'superuser',
                              'websites', );
        foreach ($config_must_key as $c) {
            if (!array_key_exists($c, $datasource)) {
                throw new \Exception("The datasource in config does not contain $c");
            }
        }

        foreach ($datasource as $key => $d) {
            $tmp_source = explode(',', trim($d));
            $tmp_array = array($key => $tmp_source);
            $result = array_merge($tmp_array, $result);
        }

        return $result;
    }

    /**
     * Look in the config and find user websites.
     *
     * @return array of numbers.
     */
    public function getEmail()
    {
        $email_config = $this->config['email'];
        foreach ($email_config as $lc) {
            $email_lib = $this->findLib(trim($lc));
            $email = $email_lib->getEmail();
            if ($email) {
                return $email;
            }
        }

        return 'default@uni-wuerzburg.de';
    }

    /**
     * Look in the config, choose the write method
     * to get the user login.
     *
     * @return string
     */
    public function getLogin()
    {
        $login_config = $this->config['login'];
        foreach ($login_config as $lc) {
            $login_lib = $this->findLib(trim($lc));
            $login = $login_lib->getLogin();
            if ($login) {
                return $login;
            }
        }

        return;
    }

    /**
     * Look in the config, choose the write method
     * to get the user alias.
     *
     * @return string
     */
    public function getAlias()
    {
        $alias_config = $this->config['alias'];
        foreach ($alias_config as $lc) {
            $alias_lib = $this->findLib(trim($lc));
            $alias = $alias_lib->getAlias();
            if ($alias) {
                return $alias;
            }
        }

        return '';
    }

    /**
     * Look in the config, choose the write method
     * to get the user superuser status.
     *
     * @return bool
     */
    public function getSuperuser()
    {
        $superuser_config = $this->config['superuser'];
        foreach ($superuser_config as $lc) {
            $superuser_lib = $this->findLib(trim($lc));
            $superuser = $superuser_lib->getSuperuser();
            if ($superuser) {
                return $superuser;
            }
        }

        return false;
    }

    /**
     * Look in the config and find user websites.
     *
     * @return array of numbers.
     */
    public function getWebsites()
    {
        $websites_config = $this->config['websites'];
        foreach ($websites_config as $lc) {
            $websites_lib = $this->findLib(trim($lc));
            $websites = $websites_lib->getWebsites($this->getLogin());
            if ($websites) {
                return $websites;
            }
        }

        return array(
                  array('access' => 'view', 'ids' => array(0)),
                  array('access' => 'admin', 'ids' => array(0)),
                  );
    }

     /**
      * Get the password.
      *
      * @return string 8char
      */
     public function getPassword()
     {
         return $this->shib->getRandomString(8);
     }

    /**
     * Get the token.
     *
     * @return 32char string
     */
    public function getToken()
    {
        return $this->shib->getRandomString(31);
    }
}
