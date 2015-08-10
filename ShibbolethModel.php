<?php


/**
 * Shibboleth model rewrites the user model with the given data from ldap
 * or Shibboleth.
 *
 * It can be assumed that the data in mysql database without any problem
 * accissible is, as it is the fallback port.
 */

namespace Piwik\Plugins\LoginShibboleth;

use Piwik\Date;

class ShibbolethModel extends \Piwik\Plugins\UsersManager\Model
{
    /**
      * Returns the user on hand the settings done
      * in the config.ini.php.
      */
     public function getUser()
     {
         $auth = new Auth();
         $login = $auth->getLogin();
         $alias = $auth->getAlias();
         $websites = $auth->getWebsites();
         $email = $auth->getEmail();
         $token = $auth->getToken();
         $superuser = $auth->getSuperuser();
         if (!$this->userExists($login)) {
             $this->addUser($login,
                            md5($auth->getPassword()),
                            $email,
                            $alias,
                            $token,
                            Date::now()->getDatetime(),
                            0);
             foreach ($websites  as $w) {
                 $this->addUserAccess($login, $w['access'], $w['ids']);
             }
             if ($superuser) {
                 $this->setSuperUserAccess($login, $superuser);
             }
         } else {
             if ($superuser) {
                 $this->setSuperUserAccess($login, $superuser);
             }
             if ($this->getSitesAccessFromUser($login) != 0) {
                 $localSiteIds = array();
                 foreach ($this->getSitesAccessFromUser($login) as $siteAccess) {
                     if (!in_array($siteAccess['site'], $websites[0]['ids']) || !in_array($siteAccess['site'], $websites[1]['ids'])) {
                         $this->deleteUserAccess($login, $siteAccess['site']);
                     } else {
                         array_push($localSiteIds, $siteAccess['site']);
                     }
                 }
                 foreach ($websites[0]['ids'] as $si) {
                     if (!in_array($si, $localSiteIds)) {
                         $this->addUserAccess($login, $websites[0]['access'], $si);
                     }
                 }
                 foreach ($websites[1]['ids'] as $si) {
                     if (!in_array($si, $localSiteIds)) {
                         $this->addUserAccess($login, $websites[1]['access'], $si);
                     }
                 }
             }
         }

         return array(
             'login' => $login,
             'alias' => $alias,
             'email' => $email,
             'token_auth' => $token,
             'superuser_access' => $superuser,
         );
     }
}
