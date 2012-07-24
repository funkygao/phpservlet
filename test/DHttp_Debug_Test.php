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

$_SERVER['HTTP_HOST'] = 'www.kaixin002.com';
$_GET['gaopeng'] = 1;

require_once('/kx/tests/KxTestCaseBase.php');

class DHttp_Debug_Test extends KxTestCaseBase
{

    public function testIsDebugTurnedOnFalse()
    {
        $_SERVER['HTTP_HOST'] = 'www.kaixin001.com';
        $_GET = array();
        $this->assertFalse(DHttp_Debug::isDebugTurnedOn('gaopeng'));
    }

    public function testIsDebugTurnedOnTrue()
    {
        $this->assertTrue(DHttp_Debug::isDebugTurnedOn('gaopeng'));
    }

}
