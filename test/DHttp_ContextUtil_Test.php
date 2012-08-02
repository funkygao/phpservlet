<?php
/**
 *
 *
 * @category
 * @package
 * @subpackage
 * @version $Id:$
 * @author gaopeng <gaopeng@corp.kaixin001.com>
 *
 * vim: set sw=4 ts=4 et:
 */

require_once('DHttp_TestBase.php');

class DHttp_ContextUtil_Test extends KxTestCaseBase
{

    public function testSingleton()
    {
        $this->assertSame(
            DHttp_ContextUtil::getKxRequest(),
            DHttp_ContextUtil::getKxRequest()
        );

        $this->assertSame(
            DHttp_ContextUtil::getRequest(),
            DHttp_ContextUtil::getRequest()
        );

        $this->assertSame(
            DHttp_ContextUtil::getResponse(),
            DHttp_ContextUtil::getResponse()
        );
    }

    public function testGetResponseWithRefresh()
    {
        $r0 = DHttp_ContextUtil::getResponse();
        $r1 = DHttp_ContextUtil::getResponse(true);

        $this->assertNotSame($r0, $r1);

        $this->assertNotSame($r1, DHttp_ContextUtil::getResponse(true));
    }

    public function testGetRequestWithEnv()
    {
        $env = DHttp_Env::mock();
        $this->assertNotSame(DHttp_ContextUtil::getKxRequest($env), DHttp_ContextUtil::getKxRequest($env));
        $this->assertNotSame(DHttp_ContextUtil::getRequest($env), DHttp_ContextUtil::getRequest($env));
    }

    public function testNotNull()
    {
        $this->assertNotNull(DHttp_ContextUtil::getResponse());
        $this->assertNotNull(DHttp_ContextUtil::getRequest());
        $this->assertNotNull(DHttp_ContextUtil::getKxRequest());
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf('DHttp_Request', DHttp_ContextUtil::getRequest());
        $this->assertInstanceOf('DHttp_Response', DHttp_ContextUtil::getResponse());
        $this->assertInstanceOf('DHttp_KxRequest', DHttp_ContextUtil::getKxRequest());
    }

    public function testGetMajorAndMinorVersion()
    {
        $this->assertEquals('5', DHttp_ContextUtil::getMajorVersion());
        $this->assertEquals('2.10', DHttp_ContextUtil::getMinorVersion());
    }

    // 只能在192.168.0.142上跑，因为我们假设ip=192.168.0.142
    public function testGetLocalIp()
    {
        $localIp = DHttp_ContextUtil::getLocalIp();

        $ipFile = DATA_PATH . "/localip";
        $this->assertFileExists($ipFile);
        $this->assertEquals('192.168.0.142', $this->getLastLine($ipFile));

        $this->assertInternalType('string', $localIp);
        $this->assertEquals('192.168.0.142', $localIp);
    }

    public function testGetHexLocalIp()
    {
        $this->assertEquals('E800', DHttp_ContextUtil::getHexLocalIp());
    }

    public function testIsUserLoggedIn()
    {
        $this->assertFalse(DHttp_ContextUtil::isUserLoggedIn());
    }

    public function testIsInnerDomain()
    {
        $this->assertTrue(DHttp_ContextUtil::isInnerDomain('.kaixin008.com.cn'));
        $this->assertTrue(DHttp_ContextUtil::isInnerDomain('www.kaixin008.com.cn'));
        $this->assertTrue(DHttp_ContextUtil::isInnerDomain('.vm142.kaixin009.com'));
        $this->assertTrue(DHttp_ContextUtil::isInnerDomain('www.vm142.kaixin009.com'));
        $this->assertTrue(DHttp_ContextUtil::isInnerDomain('login.vm142.kaixin009.com'));
        $this->assertTrue(DHttp_ContextUtil::isInnerDomain('www.news.vm142.kaixin009.com'));

        $this->assertTrue(DHttp_ContextUtil::isInnerDomain('kx001.music.sina.com.cn'));

        $this->assertFalse(DHttp_ContextUtil::isInnerDomain('kaixin008.com.cn'));
        $this->assertFalse(DHttp_ContextUtil::isInnerDomain('kaixin009.com.cn'));
    }

    public function testIsKxImgHost()
    {
        $this->assertTrue(DHttp_ContextUtil::isKxImgHost('.kaixin008.com.cn'));
        $this->assertTrue(DHttp_ContextUtil::isKxImgHost('www.kaixin008.com.cn'));

        $this->assertFalse(DHttp_ContextUtil::isKxImgHost('kaixin008.com.cn'));
        $this->assertFalse(DHttp_ContextUtil::isKxImgHost('.vm142.kaixin009.com'));
    }

    public function testIsKxWebHost()
    {

        $this->assertFalse(DHttp_ContextUtil::isKxWebHost('.kaixin008.com.cn'));
        $this->assertFalse(DHttp_ContextUtil::isKxWebHost('www.kaixin008.com.cn'));

        $this->assertTrue(DHttp_ContextUtil::isKxWebHost('.vm142.kaixin009.com'));
        $this->assertTrue(DHttp_ContextUtil::isKxWebHost('www.vm142.kaixin009.com'));
        $this->assertTrue(DHttp_ContextUtil::isKxWebHost('app.vm142.kaixin009.com'));
        $this->assertTrue(DHttp_ContextUtil::isKxWebHost('login.vm142.kaixin009.com'));
    }

    public function testIsKxPicUri()
    {
        $isPicUrls = array(
            '/pic/a.jpg',
            '/pic/b/c/d/adf/a.jpg',
            '/pic//a.jpg',
            '/pic/',
            '/pic/a.jpg',
            '/pic/a.jpg',
            '/logo/a.gif',
            '/logo/a.jpg',
            '/privacy/a.jpg',
            '/pic/a.jpg',

            '/pic/a.php',
            '/pic/a.htm',
            '/pic/a.html',
            '/logo/bb.txt',
            '/pic//adfadf/asdf/xx90234.php',
            '/pic/blah/a.gif',
            '/pic/a.jpg',
        );

        $isNotPicUrls = array(
            '/pics/a.jpg',
            '//pics/a.jpg',

            '/repaste/a.jpg',
            '//repaste/a.jpg',
            '//repaste//a.jpg',
            '/logo_/a.gif',
            '/logo /a.jpg',
        );

        foreach ($isPicUrls as $uri)
        {
            $this->assertTrue(DHttp_ContextUtil::isKxPicUri($uri), $uri);
        }

        foreach ($isNotPicUrls as $uri)
        {
            $this->assertFalse(DHttp_ContextUtil::isKxPicUri($uri), $uri);
        }

    }

}
