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

use Piwik\Config;
use Piwik\Piwik;

require_once PIWIK_INCLUDE_PATH.'/core/Config.php';

/**
 * Login controller.
 */
class Controller extends \Piwik\Plugins\Login\Controller
{

    private $config;

    public function __construct(){
      $config = parse_ini_file('config.ini.php');
      foreach ($config['controller'] as $key => $value) {
          $this->$key = $value;
      }
    }
    /**
     * @param $length
     *
     * @return string
     */
    private function generatePassword($length)
    {
        $chars = '234567890abcdefghijkmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $i = 0;
        $password = '';
        while ($i <= $length) {
            $password .= $chars{mt_rand(0, strlen($chars) - 1)};
            ++$i;
        }

        return $password;
    }

    /**
     * Default function.
     */
    public function index()
    {
        return $this->login();
    }

    public function logout()
    {
        header($this->logout_link);
    }
    /**
     * @param $password
     *
     * @throws \Exception
     */
    protected function checkPasswordHash($password)
    {
        // do not check password (Login uses hashed password, LoginLdap uses real passwords)
    }
}
