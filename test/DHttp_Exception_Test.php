<?php
/**
 *
 *
 * @category
 * @package
 * @version $Id:$
 * @author gaopeng <gaopeng@corp.kaixin001.com>
 *
 * vim: set sw=4 ts=4 et:
 */

require_once('DHttp_TestBase.php');

class DHttp_Exception_Test extends KxTestCaseBase
{

    public function testToString()
    {
        $e = new DHttp_Exception();
        $this->assertEquals("DHttp_Exception [0]: ", $e->toString());

        $e = new DHttp_Exception('foo');
        $this->assertEquals("DHttp_Exception [0]: foo", $e->toString());
        $this->assertEquals(0, $e->getCode());

        $e = new DHttp_Exception('fooBar', 87);
        $this->assertEquals("DHttp_Exception [87]: fooBar", $e->toString());
        $this->assertEquals(87, $e->getCode());
    }

    public function testExceptionType()
    {
        $e = new DHttp_Exception();
        $this->assertInstanceOf('Exception', $e);
    }

}
