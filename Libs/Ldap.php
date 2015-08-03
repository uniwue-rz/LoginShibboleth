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

class Ldap extends AuthLib
{
    private $user;
    private $password;
    private $host;
    private $port;
    private $dn;

    public function __construct()
    {
        $config = parse_ini_file('config.ini.php');
        foreach ($config['ldap'] as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * Connect to the ldap server; creates ldapconnect object.
     */
    public function connect()
    {
        $this->connect = false;
        $ldapconn = ldap_connect($this->host, $this->port)
                      or die('Could not connect to LDAP server.');

        $this->connect = $ldapconn;
    }

    /**
     *       Uses ldap bind function give bind object back.
     *
     *       @return $ldapbind or false when connection error
     */
    public function bind()
    {
        $bind = false;
        $ldapconn = $this->$connect;
        if ($ldapconn) {
            $ldapbind = ldap_bind($ldapconn, $this->user, $this->password);
        }
        if ($ldapbind) {
            $bind = $ldapbind;
        }

        return $bind;
    }

    /**
     * Search for the Domains that a user can manage from the
     * idm Server.
     *
     * @param string $login
     *
     * @return array $result
     */
    public function search($login)
    {
    }
}
