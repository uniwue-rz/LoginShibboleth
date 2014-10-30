<?php
namespace Piwik\Plugins\LoginShibboleth;
use Piwik\Settings\SystemSetting;
use Piwik\Settings\UserSetting;
class Settings extends \Piwik\Plugin\Settings
{
	public $ldapUser;
	public $ldapPassword;
	public $ldapHost;
	public $ldapPort;
	public $ldapDn;
	
	protected function init()
	{
		$this->setIntroduction('Set the Ldap Configuration for the Shibboleth Login Plugin');
		$this->createLdapUserSetting();
		$this->createLdapPasswordSetting();
		$this->createLdapHostSetting();
		$this->createLdapPortSetting();
		$this->createLdapDnSetting();
	}

    	private function createLdapUserSetting()
    	{
		$this->ldapUser = new SystemSetting('ldapUser', 'Ldap User');
	        $this->ldapUser->type  = static::TYPE_STRING;
	        $this->ldapUser->uiControlType = static::CONTROL_TEXT;
	        $this->ldapUser->description   = 'Ldap user used to login.';
		$this->ldapUser->inlineHelp = 'Use the complete name with cn="",ou=fa,ou=accounts,o=uni-wuerzburg.';
        	$this->ldapUser->defaultValue  = false;
	        $this->addSetting($this->ldapUser);
	}
     
	private function createLdapPasswordSetting()
        {
                $this->ldapPassword = new SystemSetting('ldapPassword', 'Ldap Password');
		$this->ldapPassword->readableByCurrentUser = True;
                $this->ldapPassword->uiControlType = static::CONTROL_PASSWORD;
                $this->ldapPassword->description   = 'Ldap password for the user used to login.';
		$this->ldapPassword->transform = function ($value) {
			return sha1($value . 'salt');
		};
                $this->addSetting($this->ldapPassword);
        }

	private function createLdapHostSetting()
	{
                $this->ldapHost = new SystemSetting('ldapHost', 'Ldap Host');
                $this->ldapHost->type  = static::TYPE_STRING;
                $this->ldapHost->uiControlType = static::CONTROL_TEXT;
                $this->ldapHost->description   = 'Ldap Host to bind to.';
                $this->ldapHost->inlineHelp = 'For Uni Würzburg it would be the auth.uni-wuerzburg.de';
                $this->ldapHost->defaultValue  = false;
                $this->addSetting($this->ldapHost);
	}

	private function createLdapPortSetting(){
                $this->ldapPort = new SystemSetting('ldapPort', 'Ldap Port');
                $this->ldapPort->type  = static::TYPE_INT;
                $this->ldapPort->uiControlType = static::CONTROL_TEXT;
                $this->ldapPort->description   = 'Set Ldap port for given host';
                $this->ldapPort->inlineHelp = 'For idm.uni-wuerzburg.de it would be 636';
                $this->ldapPort->defaultValue  = false;
                $this->addSetting($this->ldapPort);
	}

	private function createLdapDnSetting(){
                $this->ldapDn = new SystemSetting('ldapDn', 'Ldap Dn');
                $this->ldapDn->type  = static::TYPE_STRING;
                $this->ldapDn->uiControlType = static::CONTROL_TEXT;
                $this->ldapDn->description   = 'Set Ldap port for given host';
                $this->ldapDn->inlineHelp = 'The Tree branch to search on, would be u=fa,ou=accounts,o=uni-wuerzburg';
                $this->ldapDn->defaultValue  = false;
                $this->addSetting($this->ldapDn);
	}

}
