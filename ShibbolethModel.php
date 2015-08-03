<?php


 /**
  * Shibboleth model rewrites the user model with the given data from ldap
  * or Shibboleth.
  *
  * It can be assumed that the data in mysql database without any problem
  * accissible is, as it is the fallback port.
  */
 use Piwik\Plugins\UsersManager\Model;
 use Libs\Shibboleth;
 use Libs\Ldap;

 namespace Piwik\Plugins\LoginShibboleth;


 class ShibbolethModel extends Model
 {


     /**
      * Returns the user on hand the settings done
      * in the config.ini.php
      */
     public function getUser()
     {
       /*
       * Check for the login.
       */

       /*
       * Check for the email.
       */

       /*
       * Check for superuser status.
       */

       /*
       * Check for alias.
       */
     }
 }
