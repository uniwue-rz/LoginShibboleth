<?php

/**
 * Piwik - free/libre analytics platform.
 *
 * @link http://piwik.org
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\LoginShibboleth;

use Piwik\Config as PiwikConfig;

class Config
{
    /**
     * Default configuration for this plugin.
     */
    public static $defaultConfig = array(
      'primary_adapter' => 'shibboleth',
      'ldap_user_name' => '',
      'ldap_password' => '',
      'ldap_dn' => '',
      'ldap_host' => '',
      'ldap_port' => '',
      'ldap_active' => false,
      'ldap_view_filter' => '',
      'ldap_view_attr' => '',
      'ldap_admin_filter' => '',
      'ldap_admin_attr' => '',
      'ldap_superuser_filter' => '',
      'ldap_superuser_attr' => '',
      'ldap_superuser_value' => '',
      'ldap_user_alias' => '',
      'ldap_user_email' => '',
      'ldap_active_data' => '',
      'delete_old_user' => '',
      'shibboleth_user_login' => '',
      'shibboleth_user_alias' => '',
      'shibboleth_user_email' => '',
      'shibboleth_view_group' => '',
      'shibboleth_admin_group' => '',
      'shibboleth_superuser_group' => '',
      'shibboleth_group' => '',
      'shibboleth_restrict_view' => '',
      'shibboleth_restrict_admin' => '',
      'shibboleth_separator' => ';',
    );
    /**
     * Returns an INI option value that is stored in the `[ShibbolethLogin]` config section.
     *
     *
     * @param $optionName string name of the given option
     *
     * @return mixed
     */
    public static function getConfigOption($optionName)
    {
        return self::getConfigOptionFrom(PiwikConfig::getInstance()->LoginShibboleth, $optionName);
    }

    /**
     * Returns the configuration options from the form.
     *
     * @param $config mix option to be set.
     * @param $optionName string the name of option.
     */
    public static function getConfigOptionFrom($config, $optionName)
    {
        if (isset($config[$optionName])) {
            return $config[$optionName];
        }

        return self::getDefaultConfigOptionValue($optionName);
    }

    /**
     * Returns the default value of the given option.
     *
     * @param $optionName string
     *
     * @return mix
     */
    public static function getDefaultConfigOptionValue($optionName)
    {
        return @self::$defaultConfig[$optionName];
    }

    /**
     * Returns the primary Adapter for the login.
     *
     * @return mix
     */
    public static function getPrimaryAdapter()
    {
        return self::getConfigOption('primary_adapter');
    }

    /**
     * Returns the LDAP bind Username.
     *
     * @return string
     */
    public static function getLdapUserName()
    {
        return self::getConfigOption('ldap_user_name');
    }

    /**
     * Returns the LDAP binding password.
     *
     * @return string
     */
    public static function getLdapPassword()
    {
        return self::getConfigOption('ldap_password');
    }

    /**
     * Returns the LDAP binding Host.
     *
     * @return string
     */
    public static function getLdapHost()
    {
        return self::getConfigOption('ldap_host');
    }

    /**
     * Returns the LDAP Bind Port.
     *
     * @return int
     */
    public static function getLdapPort()
    {
        return self::getConfigOption('ldap_port');
    }

    /**
     * Returns the if LDAP is active.
     *
     * @return bool
     */
    public static function isLdapActive()
    {
        return self::getConfigOption('ldap_active');
    }

    /**
     * Returns the sources in which LDAP data will be used.
     *
     * @return array
     */
    public static function getLdapActiveData()
    {
        return self::getConfigOption('ldap_active_data');
    }

    /**
     * Returns the LDAP view filter.
     *
     * @return string
     */
    public static function getLdapViewFilter()
    {
        return self::getConfigOption('ldap_view_filter');
    }

    /**
     * Returns the LDAP view attributes.
     *
     * @return string
     */
    public static function getLdapViewAttrs()
    {
        return self::getConfigOption('ldap_view_filter');
    }

    /**
     * Returns the LDAP admin filter.
     *
     * @return string
     */
    public static function getLdapAdminFilter()
    {
        return self::getConfigOption('ldap_admin_filter');
    }

    /**
     * Returns the LDAP admin attributes.
     *
     * @return string
     */
    public static function getLdapAdminAttrs()
    {
        return self::getConfigOption('ldap_admin_attr');
    }

    /**
     * Returns the LDAP SuperUser filter.
     *
     * @return string
     */
    public static function getLdapSuperUserFilter()
    {
        return self::getConfigOption('ldap_superuser_filter');
    }

    /**
     * Returns the LDAP SuperUser attributes.
     *
     * @return string
     */
    public static function getLdapSuperUserAttrs()
    {
        return self::getConfigOption('ldap_superuser_attr');
    }

    /**
     * Returns the LDAP SuperUser value.
     *
     * @return string
     */
    public static function getLdapSuperUserValue()
    {
        return self::getConfigOption('ldap_superuser_value');
    }

    /**
     * Returns the LDAP key for the user login.
     *
     * @return string
     */
    public static function getLdapUserLogin()
    {
        return self::getConfigOption('ldap_user_login');
    }

    /**
     * Returns the LDAP key for the user alias.
     *
     * @return string
     */
    public static function getLdapUserAlias()
    {
        return self::getConfigOption('ldap_user_alias');
    }

    /**
     * Returns the LDAP key for the user email.
     *
     * @return string
     */
    public static function getLdapUserEmail()
    {
        return self::getConfigOption('ldap_user_email');
    }

    /**
     * If the Login should delete old Users from the website
     * upon login.
     *
     * @return bool
     */
    public static function isDeleteOldUserActive()
    {
        return self::getConfigOption('delete_old_user');
    }

    /**
     * Returns the Shibboleth key for the user login.
     *
     * @return string
     */
    public static function getShibbolethUserLogin()
    {
        return self::getConfigOption('shibboleth_user_login');
    }

    /**
     * Returns the Shibboleth key for the user alias.
     *
     * @return string
     */
    public static function getShibbolethUserAlias()
    {
        return self::getConfigOption('shibboleth_user_alias');
    }

    /**
     * Returns the Shibboleth key for the user email.
     *
     * @return string
     */
    public static function getShibbolethUserEmail()
    {
        return self::getConfigOption('shibboleth_user_email');
    }

    /**
     * Returns the Shibboleth view group key.
     *
     * @return string
     */
    public static function getShibbolethViewGroup()
    {
        return self::getConfigOption('shibboleth_view_group');
    }

    /**
     * Returns the Shibboleth admin group key.
     *
     * @return string
     */
    public static function getShibbolethAdminGroup()
    {
        return self::getConfigOption('shibboleth_admin_group');
    }

    /**
     * Returns the Shibboleth SuperUser group key.
     *
     * @return string
     */
    public static function getShibbolethSuperUserGroup()
    {
        return self::getConfigOption('shibboleth_superuser_group');
    }

    /**
     * Returns the Shibboleth Group key.
     *
     * @return string
     */
    public static function getShibbolethGroup()
    {
        return self::getConfigOption('shibboleth_group');
    }

    /**
     * If the admin user should be restricted.
     *
     * @return bool
     */
    public static function isShibbolethAdminRestricted()
    {
        return self::getConfigOption('shibboleth_restrict_admin');
    }

    /**
     * If the view user should be restricted.
     *
     * @return bool
     */
    public static function isShibbolethViewRestricted()
    {
        return self::getConfigOption('shibboleth_restrict_view');
    }

    /**
     * Returns the seprator for the Shibboleth results.
     *
     * @return string
     */
    public static function getShibbolethSeprator()
    {
        return self::getConfigOption('shibboleth_separator');
    }

    /**
     * Returns the plugins options with values from the default
     * for the values not set.
     *
     * @return array
     */
    public static function getPluginOptionValuesWithDefaults()
    {
        $result = self::$defaultConfig;
        foreach ($result as $name => $ignore) {
            $actualValue = self::getConfigOption($name);
            // special check for useKerberos which can be a string
            if ($name == 'use_webserver_auth'
                && $actualValue === 'false'
            ) {
                $actualValue = 0;
            }
            if (isset($actualValue)) {
                $result[$name] = $actualValue;
            }
        }

        return $result;
    }

    /**
     * Save the plugin options.
     *
     * @param $config
     */
    public static function savePluginOptions($config)
    {
        $loginShibboleth = PiwikConfig::getInstance()->LoginShibboleth;
        foreach (self::$defaultConfig as $name => $value) {
            if (isset($config[$name])) {
                $loginShibboleth[$name] = $config[$name];
            }
        }
        PiwikConfig::getInstance()->LoginShibboleth = $loginShibboleth;
        PiwikConfig::getInstance()->forceSave();
    }
}
