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

class KHttp_Exception_Captcha_Test extends KxTestCaseBase
{

    public function testCaptchaIsWrong()
    {
        $e = KHttp_Exception_Captcha::captchaWrongException();

        $this->assertInstanceOf('KHttp_Exception_Captcha', $e);
        $this->assertTrue($e->isCaptchaWrong());
        $this->assertFalse($e->isCaptchaEmpty());

        $e = KHttp_Exception_Captcha::captchaWrongException('fooBarSpam');
        $this->assertEquals('fooBarSpam', $e->getMessage());
    }

    public function testCaptchIsEmpty()
    {
        $e = KHttp_Exception_Captcha::captchaEmptyException();

        $this->assertInstanceOf('KHttp_Exception_Captcha', $e);
        $this->assertFalse($e->isCaptchaWrong());
        $this->assertTrue($e->isCaptchaEmpty());

        $e = KHttp_Exception_Captcha::captchaEmptyException('fooBarSpam');
        $this->assertEquals('fooBarSpam', $e->getMessage());
    }

}
