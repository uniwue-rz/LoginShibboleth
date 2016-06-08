<?php

/**
 * @group LoginShibboleth
 * @group LdapAdapaterTest
 * @group Plugins
 */

namespace Piwik\Plugins\LoginShibboleth\tests;

use Piwik\Plugins\LoginShibboleth\LdapAdapter;

function searchLdap($attr, $filter)
{
    return LdapAdapaterTest::searchLdap($attr, $filter);
}

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
        parent::setUp();
        $this->la = new LdapAdapter();
    }

    public function searchLdap($filter, $attrs)
    {
        return array('count' => 1);
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

    /**
     * Test the create View Filter creator.
     *
     * @excpectedException \Exception
     */
    public function testViewFilter()
    {
        $testFilter = $this->la->getAdminFilterAttr(
            'username',
            '(manager=?)',
            'Domain|path',
            '|',
            true,
            array('ldapView')
        );
        $this->assertEquals($testFilter['filter'], '(manager=username)');
        $this->assertEquals($testFilter['attrs'], array('domain', 'path'));
    }

    /*
    * Test the get getManagedUrls with the mocked LDAP.
    */
    public function testGetManagedUrls()
    {
        $manageUrl = $this->la->getManagedUrls('s225274', 'View');
        $this->assertEquals(
            array(
                array('domain' => 'test-piwik.rz.uni-wuerzburg.de', 'path' => ''),
                array('domain' => 'www.rz.uni-wuerzburg.de', 'path' => '/piwik-test'),
            ),
            $manageUrl
        );
    }

    /**
     * Test getSuperUser status from LDAP.
     */
    public function testGetSuperUserStatus()
    {
        $superStatus = $this->la->getUserSuperUserStatus('poa32kc');
        $this->assertTrue($superStatus);
    }

    /**
     * Test get Mail for a given user.
     */
    public function testGetUserEmail()
    {
        $mail = $this->la->getMail('poa32kc');
        $this->assertEquals('pouyan.azari@uni-wuerzburg.de', $mail);
    }

    /**
     * Test get user alias from LDAP.
     */
    public function testGetUserAlias()
    {
        $alias = $this->la->getAlias('poa32kc');
        $this->assertEquals('Pouyan Azari', $alias);
    }
}
