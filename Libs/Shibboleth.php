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

class Shibboleth extends AuthLib
{
    public function __construct()
    {
        $config = parse_ini_file('config.ini.php');
        foreach ($config['shib'] as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * Get user login.
     *
     * @return string
     */
    public function get_login()
    {
        return $_SERVER[$this->login];
    }

    /**
     * Get the user Email.
     *
     * @return string
     */
    public function get_email()
    {
        return $_SERVER[$this->email];
    }

    /**
     * Get the user superuser status from sting data.
     *
     * @return bool
     */
    public function get_superuser_string()
    {
        $groups = explode(trim($_SERVER[$this->superuser]));
        if (in_array($this->superuser_param, $groups)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * get the superuser status from an array.
     *
     * @return bool
     */
    public function get_superuser_array()
    {
    }

    /**
     * get the superuser status from a custom function.
     * use this if you have totally different implimention of
     * shibboleth.
     *
     * @return bool
     */
    public function get_superuser_custom()
    {
    }

    /**
     * get the superuser on hand of the settings done in
     * config.ini file.
     *
     * @return bool
     */
    public function get_superuser()
    {
        switch ($this->superuser_type) {
        case 'string':
          $this->get_superuser_string();
          break;
        case 'array':
          $this->get_superuser_array();
          break;
        case 'custom':
          $this->get_superuser_custom();
          break;
        default:
          break;
      }
    }
}
