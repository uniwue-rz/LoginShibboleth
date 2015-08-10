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

use Piwik\DB;

class Ldap extends AuthLib
{
    private $user;
    private $password;
    private $host;
    private $port;
    private $dn;

    /**
     *
     */
    public function __construct()
    {
        $config = parse_ini_file('config.ini.php');
        foreach ($config['ldap'] as $key => $value) {
            $this->$key = $value;
        }
        $this->site_ids = array();
    }

    /**
     * Connect to the ldap server; creates ldapconnect object.
     */
    public function connect()
    {
        $this->conn = false;
        $ldapconn = ldap_connect($this->host, $this->port)
                      or die('Could not connect to LDAP server.');

        $this->conn = $ldapconn;
    }

    /**
     *       Uses ldap bind function give bind object back.
     *
     *       @return $ldapbind or false when connection error
     */
    public function bind($conn)
    {
        $bind = false;
        $ldapconn = $this->conn;
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
     public function searchView()
     {
         $this->connect();
         $bind = $this->bind($this->conn);
         $filter = sprintf($this->to_filter_view, $this->login);
         if ($this->to_get_type_view == 'string') {
             $wanted = array($this->to_get_view);
         }
         if ($this->to_get_type_view == 'array') {
             $to_get_array = explode(',', $this->to_get_view);
         }
         if ($bind) {
             $sr = ldap_search($this->conn, $this->dn, $filter, $wanted);
             $sr_res = ldap_get_entries($this->conn, $sr);
         }
         $result = $sr_res;
         ldap_close($this->connect());

         return $result;
     }

     /**
      * Search for the Domains that a user can manage from the
      * idm Server.
      *
      * @param string $user
      *
      * @return array $result
      */
     public function searchAdmin()
     {
         $this->connect();
         $bind = $this->bind($this->conn);
         $filter = sprintf($this->to_filter_admin, $this->login);
         if ($this->to_get_type_admin == 'string') {
             $wanted = array($this->to_get_admin);
         }
         if ($this->to_get_type_admin == 'array') {
             $to_get_array = explode(',', $this->to_get_admin);
         }
         $sr = ldap_search($this->conn, $this->dn, $filter, $wanted);
         $sr_res = ldap_get_entries($this->conn, $sr);
         $result = $sr_res;
         ldap_close($this->connect());

         return $result;
     }

     /**
      * Get Website with their access.
      *
      * @return array(array("access"=>"","id"=>))
      */
     public function getWebsitesView()
     {
         $res = $this->searchView();
         if ($res) {
             array_push($this->site_ids, array('access' => 'view', 'ids' => array()));
             if ($res['count'] > 0) {
                 foreach ($res as $r) {
                     if (is_array($r)) {
                         array_push($this->site_ids[0]['ids'], $this->getSiteId($r[strtolower($this->to_get_view)][0]));
                     }
                 }
             }
         }
     }

     /**
      * Get Website with their access.
      *
      * @return array(array("access"=>"","id"=>))
      */
     public function getWebsitesAdmin()
     {
         $res = $this->searchAdmin();
         if ($res) {
             array_push($this->site_ids, array('access' => 'admin', 'ids' => array()));
             if ($res['count'] > 0) {
                 foreach ($res as $r) {
                     if (is_array($r)) {
                         array_push($site_ids[1]['ids'], $this->getSiteId($r[strtolower($this->to_get_admin)][0]));
                     }
                 }
             }
         }
     }

     /**
      * Get Website with their access.
      *
      * @return array(array("access"=>"","id"=>))
      */
     public function getWebsites($login)
     {
         $this->login = $login;
         $this->getWebsitesView();
         $this->getWebsitesAdmin();
         return $this->site_ids;
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
         $siteId = Db::fetchOne('SELECT idsite FROM piwik_site WHERE main_url=?',
                     array($domain));
         if (!$siteId) {
             $siteId = Db::fetchOne('SELECT idsite FROM piwik_site_url WHERE url=?',
                     array($domain));
         }

         return $siteId;
     }
}
