<?php

/**
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
    public function getLogin()
    {
        return $_SERVER[$this->login];
    }

    /**
     * Get the user Email.
     *
     * @return string
     */
    public function getEmail()
    {
        return $_SERVER[$this->email];
    }

    /**
     * Get the user superuser status from sting data.
     *
     * @return bool
     */
    public function getSuperuserString()
    {
        $groups = explode(',', $_SERVER[$this->superuser]);
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
    public function getSuperuserArray()
    {
    }

    /**
     * get the superuser status from a custom function.
     * use this if you have totally different implimention of
     * shibboleth.
     *
     * @return bool
     */
    public function getSuperuserCustom()
    {
    }

    /**
     * get the superuser on hand of the settings done in
     * config.ini file.
     *
     * @return bool
     */
    public function getSuperuser()
    {
        switch ($this->superuser_type) {
        case 'string':
          return $this->getSuperuserString();
          break;
        case 'array':
          return $this->getSuperuserArray();
          break;
        case 'custom':
          return $this->getSuperuserCustom();
          break;
        default:
          break;
      }
    }

    public function getWebsites($login)
    {
        return false;
    }

    /**
     * Get the alias.
     *
     * @return string
     */
    public function getAlias()
    {
        return $_SERVER[$this->alias];
    }
}
