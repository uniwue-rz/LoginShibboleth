<?php

/**
 * @group LoginShibboleth
 * @group LdapAdapaterTest
 * @group Plugins
 */

namespace Piwik\Plugins\LoginShibboleth\tests;

use Piwik\Plugins\LoginShibboleth\ShibbolethAdapter;

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
