<?php

/**
 * Part of Piwik Shibboleth Login Plug-in. (Test).
 */

namespace Piwik\Plugins\LoginShibboleth\tests;

use Piwik\Plugins\LoginShibboleth\ShibbolethAdapter;

/**
 * Test cases for ShibbolethAdapter class.
 *
 * Write the test cases for ShibbolethAdapter here. When there is new function added to the Shibboleth settings
 * This should be tested here.
 *
 * @author Pouyan Azari <pouyan.azari@uni-wuerzburg.de>
 * @license MIT
 * @copyright 2014-2016 University of Wuerzburg
 * @copyright 2014-2016 Pouyan Azari
 */
class ShibbolethAdapterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Setup the adapter first.
     */
    public function setUp()
    {
        parent::setUp();
        $this->sh = new ShibbolethAdapter();
    }
}
