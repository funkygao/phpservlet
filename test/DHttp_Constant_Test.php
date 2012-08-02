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

class DHttp_Constant_Test extends KxTestCaseBase
{

    public function testCookieKeys()
    {
        $this->assertEquals('_ref', DHttp_Constant::COOKIE_SESSION_REF);
    }

}
