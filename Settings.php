<?php

/**
 * Piwik - free/libre analytics platform.
 *
 * @link http://piwik.org
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\LoginShibboleth;

use Piwik\Settings\SystemSetting;

/**
 * Defines Settings for LoginShibboleth.
 *
 * Usage like this:
 * $settings = new Settings('LoginShibboleth');
 * $settings->ldapUserName->getValue();
 * $settings->ldapPassword->getValue();
 */
class Settings extends \Piwik\Plugin\Settings
{
    /**
     * Placeholder for the ldapUserName Object.
     *
     * @var
     */
    public $ldapUserName;
    /**
     * Placeholder for the ldapPassword Object.
     *
     * @var
     */
    public $ldapPassword;
    /**
     * Placeholder for the ldapDn Object.
     *
     * @var
     */
    public $ldapDn;
    /**
     * Placeholder for the ldapHost Object.
     *
     * @var
     */
    public $ldapHost;
    /**
     * Placeholder for the ldapPort Object.
     *
     * @var
     */
    public $ldapPort;

    /**
     * Placeholder for ldapActive Object.
     *
     * @var
     */
    public $ldapActive;

    /**
     * Placeholder for ldapViewFilter Object.
     *
     * @var
     */
    public $ldapViewFilter;

    /**
     * Placeholder for ldapViewAttrs Object.
     *
     * @var
     */
    public $ldapViewAttrs;

    /**
     * Placeholder for ldapAdminFilter Object.
     *
     * @var
     */
    public $ldapAdminFilter;

    /**
     * Placeholder for ldapAdminAttrs Object.
     *
     * @var
     */
    public $ldapAdminAttrs;

    /**
     * Placeholder for ldapSuperUserFilter Object.
     *
     * @var
     */
    public $ldapSuperUserFilter;

    /**
     * Placeholder for ldapSuperUserAttrs Object.
     *
     * @var
     */
    public $ldapSuperUserAttrs;

    /**
     * Placeholder for ldapSuperUserValues Object.
     *
     * @var
     **/
    public $ldapSuperUserValues;

    /**
     * Placeholder for ldapUserLogin Object.
     *
     * @var
     */
    public $ldapUserLogin;

    /**
     * Placeholder for ldapUserAlias Object.
     *
     * @var
     */
    public $ldapUserAlias;

    /**
     * Placeholder for ldapUserEmail Object.
     *
     * @var
     */
    public $ldapUserEmail;

    /**
     * Placeholder for shibbolethUserName Object.
     *
     * @var
     */
    public $shibbolethLogin;

    /**
     * Placeholder for shibbolethAlias Object.
     *
     * @var
     */
    public $shibbolethAlias;

    /**
     * Placeholder for shibbolethEmail Object.
     *
     * @var
     */
    public $shibbolethEmail;

    /**
     * Placeholder for shibbolethAdminGroup Object.
     *
     * @var
     */
    public $shibbolethAdminGroup;

    /**
     * Placeholder for shibbolethViewGroup Object.
     *
     * @var
     */
    public $shibbolethViewGroup;

    /**
     * Placeholder for shibbolethSuperUserGroup Object.
     *
     * @var
     */
    public $shibbolethSuperUserGroup;

    /**
     * Placeholder for shibolethGroup Object.
     *
     * @var
     */
    public $shibbolethGroup;

    /**
     * Placeholder for deletOldUser Object.
     *
     * @var
     */
    public $deleteOldUser;

    /**
     * Placeholder for primaryAdapter Object.
     *
     * @var
     */
    public $primaryAdapter;

    /**
     * Placeholder for ldapActiveData Object.
     *
     * @var
     */
    public $ldapActiveData;

    /**
     * Placeholder for the ldapAdapter.
     *
     * @var
     */
    public $ldapAdapter;

    /**
     * Placeholder for Shibboleth Seprator.
     *
     * @var
     */
    public $shibbolethSeparator;

    /**
     * Placeholder for Shibboleth restrict View.
     *
     * @var
     */
    public $shibbolethRestictView;

    /**
     * Placeholder for Shibboleth restrict Admin.
     *
     * @var
     */
    public $shibbolethRestictAdmin;

    protected function init()
    {
        $this->setIntroduction('LoginShibboleth Plugin settings can be managed here. Normally there is no need to
        have LDAP set, but for some user there are some extra user information that exist only in LDAP.');
        $this->createShibbolethLogin();
        $this->createShibbolethAlias();
        $this->createShibbolethEmail();
        $this->createShibbolethGroup();
        $this->createShibbolethSeparator();
        $this->createShibbolethRestrictView();
        $this->createShibbolethViewGroup();
        $this->createShibbolethRestrictAdmin();
        $this->createShibbolethAdminGroup();
        $this->createShibbolethSuperUserGroup();
        $this->createDeleteOldUsers();
        $this->createLdapActive();
        $this->createPrimaryAdapter();
        $this->createLdapUserName();
        $this->createLdapPassword();
        $this->createLdapHost();
        $this->createLdapPort();
        $this->createLdapDn();
        $this->createLdapActiveData();
        $this->createLdapViewFilter();
        $this->createLdapViewAttrs();
        $this->createLdapAdminFilter();
        $this->createLdapAdminAttrs();
        $this->createLdapSuperUserFilter();
        $this->createLdapSuperUserAttrs();
        $this->createLdapSuperUserValues();
        $this->createLdapUserAlias();
        $this->createLdapUserEmail();
    }

    /**
     * Creates the Shibboleth Username Setting.
     */
    private function createShibbolethLogin()
    {
        $this->shibbolethLogin = new SystemSetting('shibbolethLogin', 'Shibboleth Username');
        $this->shibbolethLogin->uiControlType = static::CONTROL_TEXT;
        $this->shibbolethLogin->description = 'Shibboleth uid or login string in server header.';
        $this->shibbolethLogin->introduction = 'The following should be set, so the login
        can be retrieved from the Shibboleth header in $_SERVER.';
        $this->shibbolethLogin->inlineHelp = 'Shibboleth group is the array key in
        $_SERVER which represents the username.';
        $this->shibbolethLogin->defaultValue = 'uid';
        $this->addSetting($this->shibbolethLogin);
    }

    /**
     * Creates the Shibboleth Username Setting.
     */
    private function createShibbolethAlias()
    {
        $this->shibbolethAlias = new SystemSetting('shibbolethAlias', 'Shibboleth Alias');
        $this->shibbolethAlias->uiControlType = static::CONTROL_TEXT;
        $this->shibbolethAlias->description = 'Shibboleth alias string in server header.';
        $this->shibbolethAlias->inlineHelp = 'shibbolethAlias is the array key in
        $_SERVER which represents the Full Name.';
        $this->shibbolethAlias->defaultValue = 'fn';
        $this->addSetting($this->shibbolethAlias);
    }

    /**
     * Creates the Shibboleth Username Setting.
     */
    private function createShibbolethEmail()
    {
        $this->shibbolethEmail = new SystemSetting('shibbolethEmail', 'Shibboleth Email');
        $this->shibbolethEmail->uiControlType = static::CONTROL_TEXT;
        $this->shibbolethEmail->description = 'Shibboleth mail string in server header.';
        $this->shibbolethEmail->inlineHelp = 'ShibbolethMail is the array key in
        $_SERVER which represents the Email.';
        $this->shibbolethEmail->defaultValue = 'mail';
        $this->addSetting($this->shibbolethEmail);
    }

    /**
     * Create the Shibboleth Super User Group Setting.
     */
    private function createShibbolethGroup()
    {
        $this->shibbolethGroup = new SystemSetting('shibbolethGroup', 'Shibboleth Group');
        $this->shibbolethGroup->uiControlType = static::CONTROL_TEXT;
        $this->shibbolethGroup->description = 'Shibboleth group string in server header.';
        $this->shibbolethGroup->inlineHelp = 'Shibboleth group is the array key in
          $_SERVER which represents the user groups.';
        $this->shibbolethGroup->defaultValue = 'shibbolethGroup';
        $this->addSetting($this->shibbolethGroup);
    }

    /**
     * Create the Shibboleth Separator Settings.
     */
    private function createShibbolethSeparator()
    {
        $this->shibbolethSeparator = new SystemSetting('shibbolethSeparator', 'Shibboleth Separator');
        $this->shibbolethSeparator->uiControlType = static::CONTROL_TEXT;
        $this->shibbolethSeparator->description = 'Shibboleth separator char for $_SERVER responses.';
        $this->shibbolethSeparator->inlineHelp = 'Shibboleth separator can be ; or , or etc.';
        $this->shibbolethSeparator->defaultValue = ';';
        $this->addSetting($this->shibbolethSeparator);
    }

    /**
     * Create the Shibboleth restrict view Setting.
     */
    private function createShibbolethRestrictView()
    {
        $this->shibbolethRestictView = new SystemSetting('shibbolethRestictView', 'Restrict View with Shibboleth');
        $this->shibbolethRestictView->type = static::TYPE_BOOL;
        $this->shibbolethRestictView->uiControlType = static::CONTROL_CHECKBOX;
        $this->shibbolethRestictView->description = 'Restricts the view access to the group defined next.';
        $this->shibbolethRestictView->inlineHelp = 'Using the first capabilities of the next option with this on,
        the view access will be restricted to the group set there.
        Use this only if you want another level of security.';
        $this->shibbolethRestictView->defaultValue = false;
        $this->addSetting($this->shibbolethRestictView);
    }

    /**
     * Create the Shibboleth view Group Setting.
     */
    private function createShibbolethViewGroup()
    {
        $this->shibbolethViewGroup = new SystemSetting('shibbolethViewGroup', 'Shibboleth View Group');
        $this->shibbolethViewGroup->uiControlType = static::CONTROL_TEXT;
        $this->shibbolethViewGroup->description = 'Comma separated user groups';
        $this->shibbolethViewGroup->inlineHelp = 'To restrict access to Piwik back-end interface, this option
        can be used. There are two capabilities built into this option. First: The general view access of the
        User can be set with this group (USER-PIWIK-VIEW), it gives another layer of security for
        user access management. Setting this option to a existing shibboleth group will result in users only
        within the group and super user to login. It will work if the restrict view access is on (^^).
        Second: Using a reg-ex pattern which contains parentheses like USER-PIWIK-VIEW-(.*).
        With this Piwik will handle the result of the regex-matching as domain and tries
        to give the user access to the domain. This two functions
        can not be combined!';
        $this->shibbolethViewGroup->defaultValue = 'USER-PIWIK-VIEW-(.*)';
        $this->addSetting($this->shibbolethViewGroup);
    }

    /**
     * Create the Shibboleth restrict view Setting.
     */
    private function createShibbolethRestrictAdmin()
    {
        $this->shibbolethRestictAdmin = new SystemSetting('shibbolethRestictAdmin', 'Restrict Admin with Shibboleth');
        $this->shibbolethRestictAdmin->type = static::TYPE_BOOL;
        $this->shibbolethRestictAdmin->uiControlType = static::CONTROL_CHECKBOX;
        $this->shibbolethRestictAdmin->description = 'Restricts the admin access to the group defined next.';
        $this->shibbolethRestictAdmin->inlineHelp = 'Using the first capabilities of the next option with this on,
        the admin access will be restricted to the group set there. Use this only
        if you want another level of security.';
        $this->shibbolethRestictAdmin->defaultValue = false;
        $this->addSetting($this->shibbolethRestictAdmin);
    }

    /**
     * Create the Shibboleth Admin Group Setting.
     */
    private function createShibbolethAdminGroup()
    {
        $this->shibbolethAdminGroup = new SystemSetting('shibbolethAdminGroup', 'Shibboleth Admin Group');
        $this->shibbolethAdminGroup->uiControlType = static::CONTROL_TEXT;
        $this->shibbolethAdminGroup->description = 'Comma separated admin groups';
        $this->shibbolethAdminGroup->inlineHelp = 'To restrict access to Piwik back-end interface, this option
        can be used. There are two capabilities built into this option. First: The general administrator access
        of the User can be set with this group (USER-PIWIK-Admin), it gives another layer of security for
        user access management. Setting this option to a existing shibboleth group will result in
        users only within the group and super user to login (^^). It will work if the restrict admin access is on.
        Second: Using a reg-ex pattern which contains parentheses like USER-PIWIK-ADMIN-(.*).
        With this Piwik will handle the result of the regex-matching as domain and
        tries to give the user access to the domain. This two functions can not be combined!';
        $this->shibbolethAdminGroup->defaultValue = 'USER-PIWIK-ADMIN-(.*)';
        $this->addSetting($this->shibbolethAdminGroup);
    }

    /**
     * Create the Shibboleth Super User Group Setting.
     */
    private function createShibbolethSuperUserGroup()
    {
        $this->shibbolethSuperUserGroup = new SystemSetting('shibbolethSuperUserGroup', 'Shibboleth SuperUser Group');
        $this->shibbolethSuperUserGroup->uiControlType = static::CONTROL_TEXT;
        $this->shibbolethSuperUserGroup->description = 'Comma separated SuperUser groups';
        $this->shibbolethSuperUserGroup->inlineHelp = 'The Shibboleth SuperUser group will be matched and
        the user can login afterwards. As SuperUser can manage everything (equivalent to root in Unix/Linux)
        this group\'s users should be chosen with care. There is no need to have siteId or domain set here and
        The string here will not be worked on to have a domain or siteId found.';
        $this->shibbolethSuperUserGroup->defaultValue = 'USER-PIWIK-SUPER';
        $this->addSetting($this->shibbolethSuperUserGroup);
    }

    /**
     * Create the delete old user activation Setting.
     */
    private function createDeleteOldUsers()
    {
        $this->deleteOldUser = new SystemSetting('deleteOldUser', 'Delete Old Users');
        $this->deleteOldUser->type = static::TYPE_BOOL;
        $this->deleteOldUser->uiControlType = static::CONTROL_CHECKBOX;
        $this->deleteOldUser->description = 'If enabled, delete old user by login.';
        $this->deleteOldUser->inlineHelp = 'This switch is used to clean the user table from not authorized user.
      If set the users that are not authorized, but exist in Piwik will be deleted after they login.';
        $this->deleteOldUser->defaultValue = false;
        $this->addSetting($this->deleteOldUser);
    }

    /**
     * Create the Ldap activation Setting.
     */
    private function createLdapActive()
    {
        $this->ldapActive = new SystemSetting('ldapActive', 'Activate Ldap');
        $this->ldapActive->type = static::TYPE_BOOL;
        $this->ldapActive->uiControlType = static::CONTROL_CHECKBOX;
        $this->ldapActive->introduction = 'The following should be set, if the LDAP is wanted. LDAP options are only
        used when they return a result.';
        $this->ldapActive->description = 'If enabled, make LoginShibboleth to check LDAP too.';
        $this->ldapActive->inlineHelp = 'This switch will make the ShibbolethLogin to also check in LDAP for user\'s
        authorizations. You can have user properties also set from LDAP when Shibboleth brings no result.';
        $this->ldapActive->defaultValue = false;
        $this->addSetting($this->ldapActive);
    }

    /**
     * Creates the primary Adapter Setting.
     */
    private function createPrimaryAdapter()
    {
        $this->primaryAdapter = new SystemSetting('primaryAdapter', 'Primary Adapter');
        $this->primaryAdapter->type = static::TYPE_STRING;
        $this->primaryAdapter->uiControlType = static::CONTROL_SINGLE_SELECT;
        $this->primaryAdapter->availableValues = array('ldap' => 'LDAP Adapter', 'shibboleth' => 'Shibboleth Adapter');
        $this->primaryAdapter->description = 'The value has effect only when LDAP is used.';
        $this->primaryAdapter->inlineHelp = 'Primary Adapter is the the adapter which is chosen to prevail when
        data is both available on LDAP and Shibboleth, per default shibboleth is selected.';
        $this->primaryAdapter->defaultValue = 'shibboleth';
        $this->addSetting($this->primaryAdapter);
    }

    /**
     * Creates the Ldap Username field in the setting.
     */
    private function createLdapUserName()
    {
        $this->ldapUserName = new SystemSetting('ldapUserName', 'Ldap Username');
        $this->ldapUserName->uiControlType = static::CONTROL_TEXT;
        $this->ldapUserName->description = 'Complete LDAP User (cn=....)';
        $this->ldapUserName->inlineHelp = 'LDAP username which will be used to bind to ldap server.
        It should have access to the attribute set afterwards so the login will work properly.';
        $this->ldapUserName->defaultValue = 'username';
        $this->addSetting($this->ldapUserName);
    }
    /**
     * Creates the Ldap Password field in the setting.
     */
    private function createLdapPassword()
    {
        $this->ldapPassword = new SystemSetting('ldapPassword', 'Ldap Password');
        $this->ldapPassword->readableByCurrentUser = true;
        $this->ldapPassword->uiControlType = static::CONTROL_PASSWORD;
        $this->ldapPassword->inlineHelp = 'LDAP password which will be used to bind to LDAP server.';
        $this->ldapPassword->description = 'LDAP Password (will be encrypted with SHA1)';
        $this->ldapPassword->defaultValue = 'password';
        $this->addSetting($this->ldapPassword);
    }

    /**
     * Creates the LDAP Host Settings.
     */
    private function createLdapHost()
    {
        $this->ldapHost = new SystemSetting('ldapHost', 'Ldap Host');
        $this->ldapHost->uiControlType = static::CONTROL_TEXT;
        $this->ldapHost->description = 'LDAP Host Ip or Hostname';
        $this->ldapHost->inlineHelp = 'For ldaps use ldaps:// and for ldap just the hostname.';
        $this->ldapHost->defaultValue = 'ldaphost';
        $this->addSetting($this->ldapHost);
    }

    /**
     * Create the LDAP Port Setting.
     */
    private function createLdapPort()
    {
        $this->ldapPort = new SystemSetting('ldapPort', 'Ldap Port');
        $this->ldapPort->type = static::TYPE_INT;
        $this->ldapPort->uiControlType = static::CONTROL_TEXT;
        $this->ldapPort->description = 'LDAP Port if not set 636';
        $this->ldapPort->inlineHelp = 'For Ldap normally 389, For Ldaps 636';
        $this->ldapPort->defaultValue = '636';
        $this->addSetting($this->ldapPort);
    }

    /**
     * Create the LDAP DN setting.
     */
    private function createLdapDn()
    {
        $this->ldapDn = new SystemSetting('ldapDn', 'Ldap DN');
        $this->ldapDn->uiControlType = static::CONTROL_TEXT;
        $this->ldapDn->description = 'LDAP Distinguished Names';
        $this->ldapDn->inlineHelp = 'DN which contains the attributes for the given user.';
        $this->ldapDn->defaultValue = 'ldapDn';
        $this->addSetting($this->ldapDn);
    }

    /**
     * Create LDAP active data sources Setting.
     */
    private function createLdapActiveData()
    {
        $this->ldapActiveData = new SystemSetting('LdapDataSource', 'Active Ldap Sources');
        $this->ldapActiveData->type = static::TYPE_ARRAY;
        $this->ldapActiveData->uiControlType = static::CONTROL_MULTI_SELECT;
        $this->ldapActiveData->availableValues = array('ldapView' => 'View',
        'ldapAdmin' => 'Admin',
        'ldapSuperUser' => 'SuperUser', );
        $this->ldapActiveData->description = 'The active options only will be searched.';
        $this->ldapActiveData->inlineHelp = 'Select the sources which should be search when ldap is active
        It allows the user to use LDAP partially, for one or two user types. At least one of them should
        be selected.';
        $this->ldapActiveData->defaultValue = array('ldapView', 'ldapAdmin', 'ldapSuperUser');
        $this->ldapActiveData->readableByCurrentUser = true;
        $this->addSetting($this->ldapActiveData);
    }

    /**
     * Create the Ldap View Filter Setting.
     */
    private function createLdapViewFilter()
    {
        $this->ldapViewFilter = new SystemSetting('ldapViewFilter', 'Ldap View Filter');
        $this->ldapViewFilter->uiControlType = static::CONTROL_TEXT;
        $this->ldapViewFilter->description = 'Filter for view user';
        $this->ldapViewFilter->inlineHelp = 'Filter string which should be used to filter LDAP queries for view.
        There is only one variable allowed in filter string which is denoted with ?. It is the username.';
        $this->ldapViewFilter->defaultValue = '(&(manager=cn=?,ou=pers,ou=accounts,o=organization)(webhostdomain=*))';
        $this->addSetting($this->ldapViewFilter);
    }

    /**
     * Create the Ldap view Attrs Setting.
     */
    private function createLdapViewAttrs()
    {
        $this->ldapViewAttrs = new SystemSetting('ldapViewAttrs', 'Ldap View Attrs');
        $this->ldapViewAttrs->uiControlType = static::CONTROL_TEXT;
        $this->ldapViewAttrs->description = 'Attributes for view user, comma separated';
        $this->ldapViewAttrs->inlineHelp = 'The attributes which are needed, to decide if user has view access.
      They should be comma separated and no more than two. The first is the domain, the second is the path.
      If only one given it will be regarded as domain.';
        $this->ldapViewAttrs->defaultValue = 'accountwebhostdomain, accountwebhostpath';
        $this->addSetting($this->ldapViewAttrs);
    }

    /**
     * Create the Ldap Admin Filter Setting.
     */
    private function createLdapAdminFilter()
    {
        $this->ldapAdminFilter = new SystemSetting('ldapAdminFilter', 'Ldap Admin Filter');
        $this->ldapAdminFilter->uiControlType = static::CONTROL_TEXT;
        $this->ldapAdminFilter->description = 'Filter for admin user';
        $this->ldapAdminFilter->inlineHelp = 'Filter string which should be used to filter LDAP queries for admin.
        There is only one variable allowed in filter string which is denoted with ?. It is the username.';
        $this->ldapAdminFilter->defaultValue = '(&(manager=cn=?,ou=pers,ou=accounts,o=organization)(webhostdomain=*))';
        $this->addSetting($this->ldapAdminFilter);
    }

    /**
     * Create the Ldap Admin Attrs Setting.
     */
    private function createLdapAdminAttrs()
    {
        $this->ldapAdminAttrs = new SystemSetting('ldapAdminAttrs', 'Ldap Admin Attrs');
        $this->ldapAdminAttrs->uiControlType = static::CONTROL_TEXT;
        $this->ldapAdminAttrs->description = 'Attributes for admin user, comma separated';
        $this->ldapAdminAttrs->inlineHelp = 'The attributes which are needed, to decide if user has admin access.
      They should be comma separated and no more than two. The first is the domain, the second is the path.
      If only one given it will be regarded as domain.';
        $this->ldapAdminAttrs->defaultValue = 'accountwebhostdomain, accountwebhostpath';
        $this->addSetting($this->ldapAdminAttrs);
    }

    /**
     * Create the Ldap Super User Filter Setting.
     */
    private function createLdapSuperUserFilter()
    {
        $this->ldapSuperUserFilter = new SystemSetting('ldapSuperUserFilter', 'Ldap SuperUser Filter');
        $this->ldapSuperUserFilter->uiControlType = static::CONTROL_TEXT;
        $this->ldapSuperUserFilter->description = 'Filter for Super User';
        $this->ldapSuperUserFilter->inlineHelp = 'Filter string which should be used to filter LDAP queries
        for Super User. There is only one variable allowed in filter string which is
        denoted with ?. It is the username.';
        $this->ldapSuperUserFilter->defaultValue = '(&(manager=cn=?,ou=pers,ou=accounts,o=org)(hostdomain=*))';
        $this->addSetting($this->ldapSuperUserFilter);
    }

    /**
     * Create the Ldap Super User Attrs Setting.
     */
    private function createLdapSuperUserAttrs()
    {
        $this->ldapSuperUserAttrs = new SystemSetting('ldapSuperUserAttrs', 'Ldap SuperUser Attr');
        $this->ldapSuperUserAttrs->uiControlType = static::CONTROL_TEXT;
        $this->ldapSuperUserAttrs->description = 'Attribute for Super User';
        $this->ldapSuperUserAttrs->inlineHelp = 'The attribute which should be compared to the value set next.';
        $this->ldapSuperUserAttrs->defaultValue = 'accountsuperuser';
        $this->addSetting($this->ldapSuperUserAttrs);
    }

    /**
     * Create the Ldap Super User Attrs Setting.
     */
    private function createLdapSuperUserValues()
    {
        $this->ldapSuperUserValues = new SystemSetting('ldapSuperUserValues', 'Ldap SuperUser Values');
        $this->ldapSuperUserValues->uiControlType = static::CONTROL_TEXT;
        $this->ldapSuperUserValues->description = 'Value for the Ldap SuperUser attribute';
        $this->ldapSuperUserValues->inlineHelp = 'The values for each SuperUser attributes must be set here. They
        should be in order as attributes it self.';
        $this->ldapSuperUserValues->defaultValue = 'valueSuperUser;';
        $this->addSetting($this->ldapSuperUserValues);
    }

    /**
     * Creates the Ldap User Alias Setting.
     */
    private function createLdapUserAlias()
    {
        $this->ldapUserAlias = new SystemSetting('ldapUserAlias', 'Ldap User Alias');
        $this->ldapUserAlias->uiControlType = static::CONTROL_TEXT;
        $this->ldapUserAlias->description = 'ldap User Alias (Fullname)';
        $this->ldapUserAlias->inlineHelp = 'ldapUserAlias is the array key in Ldap result which
        represent the user\'s Full Name.';
        $this->ldapUserAlias->defaultValue = 'fn';
        $this->addSetting($this->ldapUserAlias);
    }

    /**
     * Creates the Ldap User Email Setting.
     */
    private function createLdapUserEmail()
    {
        $this->ldapUserEmail = new SystemSetting('ldapUserEmail', 'Ldap User Email');
        $this->ldapUserEmail->uiControlType = static::CONTROL_TEXT;
        $this->ldapUserEmail->description = 'Ldap user mail';
        $this->ldapUserEmail->inlineHelp = 'ldapUserEmail is the key in Ldap result which
        represent the user\'s email.';
        $this->ldapUserEmail->defaultValue = 'mail';
        $this->addSetting($this->ldapUserEmail);
    }
}
