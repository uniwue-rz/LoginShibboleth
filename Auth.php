<?php

use Libs\Shibboleth;
use Libs\Ldap;

namespace Piwik\Plugins\LoginShibboleth;

class Auth
{
    private $config;
    public function __construct($login, $email, $alias, $superuser, $websites, $access_level)
    {
        $this->login = $login;
        $this->email = $email;
        $this->alias = $alias;
        $this->superuser = $superuser;
        $this->websites = $websites;
        $this->access_level = $access_level;
        $this->config = $this->get_config();
        $this->shib = new Shibboleth();
        $this->ldap = new Ldap();
    }
    /**
     * Get the configuration. as a sorted array. The keys will be checked
     * agianst a pre-written key list. They should cover the whole list.
     * The first on the list will be used as a first parameter.
     *
     * @return array
     */
    public function get_config()
    {
        $result = array();
        $config = parse_ini_file('config.ini.php');
        $datasource = $config['datasource'];
        $config_must_key = array('login',
                              'email',
                              'alias',
                              'superuser',
                              'access_level',
                              'website', );
        foreach ($config_must_key as $c) {
            if (!array_key_exists($datasource, $c)) {
                throw new \Exception("The datasource in config does not contain $c");
            }
        }

        foreach ($datasource as $key => $d) {
            $tmp_source = explode(',', trim($d));
            array_push($result, $tmp_source);
        }

        return $result;
    }

    /**
     * Look in the config, choose the write method
     * to get the user login.
     *
     * @return string
     */
    public function get_login()
    {
        switch ($this->login) {
          case 'shib':
            return $this->shib->get_login();
            break;
          case 'ldap':
            return $this->ldap->get_login();
            break;
          case 'mysql':
            break;
          default:
            break;
        }
    }

    /**
     * Look in the config, choose the write method
     * to get the user alias.
     *
     * @return string
     */
    public function get_alias()
    {
        switch ($this->login) {
        case 'shib':
          return $this->shib->get_alias();
          break;
        case 'ldap':
          return $this->ldap->get_alias();
          break;
        case 'mysql':
          break;
        default:
          break;
      }
    }

    /**
     * Look in the config, choose the write method
     * to get the user superuser status.
     *
     * @return bool
     */
    public function get_superuser()
    {
        switch ($this->login) {
        case 'shib':
          return $this->shib->get_superuser();
          break;
        case 'ldap':
          return $this->ldap->get_superuser();
          break;
        case 'mysql':
          break;
        default:
          break;
      }
    }

    /*
    *
    * Look in the config and find user websites access.
    *
    */
}
