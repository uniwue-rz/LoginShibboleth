<?php

/**
 * @group LoginShibboleth
 * @group LdapAdapaterTest
 * @group Plugins
 */

namespace Piwik\Plugins\LoginShibboleth\tests;

use Piwik\Plugins\LoginShibboleth\LdapAdapter;

/**
 * Test class for the LdapAdpater.
 */
class LdapAdapaterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Setup the adapter first.
     */
    public function setUp()
    {
        $this->la = new LdapAdapter();
    }

    /**
     * Test the connections to the LDAP server.
     *
     * @excpectedException \Exception
     */
    public function testCheckConnections()
    {
        $result = $this->la->checkConnection();
        $this->assertTrue($result);
    }

    /**
     * Test LDAP binding.
     *
     * @excpectedException \Exception
     */
    public function testLdapBind()
    {
        $result = $this->la->checkBind();
        $this->assertTrue($result);
    }
}
