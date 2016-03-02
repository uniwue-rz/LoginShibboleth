<?php

/**
 * Piwik - free/libre analytics platform.
 *
 * @link http://piwik.org
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\LoginShibboleth;

use Piwik\Common;
use Piwik\Piwik;
use Exception;

/**
 */
class API extends \Piwik\Plugin\API
{
    /**
     * Constructor.
     */
    public function __construct()
    {
    }
    /**
     * Saves LoginLdap config.
     *
     * @param string $data JSON encoded config array.
     *
     * @return array
     *
     * @throws Exception if user does not have super access, if this is not a POST method or
     *                   if JSON is not supplied.
     */
    public function saveShibbolethConfig($data)
    {
        $this->checkHttpMethodIsPost();
        Piwik::checkUserHasSuperUserAccess();
        $data = json_decode(Common::unsanitizeInputValue($data), true);
        Config::savePluginOptions($data);

        return array('result' => 'success', 'message' => Piwik::translate('General_YourChangesHaveBeenSaved'));
    }

    private function checkHttpMethodIsPost()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            throw new Exception('Invalid HTTP method.');
        }
    }
}
