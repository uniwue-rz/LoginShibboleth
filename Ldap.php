<?php

/**
 * @link http://piwik.org
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 */

namespace Piwik\Plugins\LoginShibboleth;

class Ldap
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
      /*$this->user = $config['ldap']['user'];
      $this->password = $config['ldap']['password'];
      $this->host = $config['ldap']['host'];
      $this->port = $config['ldap']['port'];
      $this->dn = $config['ldap']['dn'];*/
    }

    public function connect()
    {
        $connect = false;
        $ldapconn = ldap_connect($this->host, $this->port)
                        or die('Could not connect to LDAP server.');

        return $ldapconn;
    }

        /**
         *       Uses ldap bind function give bind object back.
         *
         *       @return $ldapbind or false when connection error
         */
        public function bind($connect)
        {
            $bind = false;
            $ldapconn = $connect;
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
         * @param string $user
         *
         * @return array $result
         */
        public function search($user)
        {
            $connect = $this->connect();
            $this->bind($connect);
            $filter = '(manager=cn='.$user.',ou=pers,ou=accounts,o=uni-wuerzburg)';
            $wanted = array('wueAccountWebhostDomain','wueAccountWebhostTarget');
            $sr = ldap_search($connect, $this->dn, $filter, $wanted);
            $sr_res = ldap_get_entries($connect, $sr);
            $result = $sr_res;
            ldap_close($this->connect());

            return $result;
        }
}
