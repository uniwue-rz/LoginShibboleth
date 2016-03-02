<?php


/**
 * Piwik - free/libre analytics platform.
 *
 * @link http://piwik.org
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\LoginShibboleth;

use Piwik\Plugins\UsersManager\Model as Model;

class LoginShibbolethUser extends Model
{
    /**
     * Placeholder for the settings.
     *
     * @var
     */
    private $settings;

    /**
     * Placeholder for User Login Id.
     *
     * @var
     */
    private $username;

    /**
     * Placeholder for User's Email.
     *
     * @var
     */
    private $email;

    /**
     * Placeholder for the User's alias.
     *
     * @var
     */
    private $alias;

    /**
     * Placeholder for User's token.
     *
     * @var
     */
    private $token;

    /**
     * Placeholder for User's password.
     *
     * @var
     */
    private $password;

    /**
     * PlaceHolder for UserInfo array.
     *
     * @var
     */
    private $userInfo;

    /**
     * Placeholder for UserProperty array.
     *
     * @var
     */
    private $userProperty;

    public function getUser()
    {
        $this->settings = new Settings();
        $this->userInfo = array('username' => '','email' => '','alias' => '');
        $this->userProperty = array('view' => array(),'admin' => array(),'superuser' => false);
        $this->primaryAdapter = $this->settings->primaryAdapter->getValue();
        $this->ldapActive = $this->settings->ldapActive->getValue();
        $this->activeLdapSource = $this->settings->ldapActiveData->getValue();
        $this->handleAuth();
    }

    /**
     * Handles the authentication through the settings set in the Piwik Plugin Settings.
     *
     * @var string Username of the given user, not applicable in Shibboleth as
     *             primary adapter.
     */
    private function handleAuth($username = '')
    {
        if ($this->primaryAdapter == 'shibboleth') {
            $shibbolethAdapter = new ShibbolethAdapter();
            $shibbolethUserProperty = $shibbolethAdapter->getUserProperty();
            $shibbolethUserInfo = $shibbolethAdapter->getUserInfo();
            $this->userInfo = $this->mergeInfo($this->userInfo, $shibbolethUserInfo);
            $this->userProperty = $this->mergeInfo($this->userProperty, $shibbolethUserProperty);
        } else {
        }
    }

    /**
     * Updates the base array with new data out of the source.
     *
     * @param $base string The key of the value given.
     * @param $result array the array that is being chosen
     *
     * @return new genereated base data array.
     */
    private function mergeInfo($base, $source)
    {
        foreach ($base as $k => $v) {
            if (array_key_exists($k, $source)) {
                if ($base[$k] == '' || !$base[$k]) {
                    $base[$k] = $source[$k];
                } elseif (gettype($base[$k])) {
                    array_push($base[$k], $source[$k]);
                }
            }
        }
        foreach ($source as $k => $v) {
            if (!array_key_exists($k, $base)) {
                $base[$k] = $v;
            }
        }

        return $base;
    }
}
