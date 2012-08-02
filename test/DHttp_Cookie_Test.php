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

class DHttp_Cookie_Test extends KxTestCaseBase
{

    public function testCreate()
    {
        $c1 = new DHttp_Cookie('a', 'b');
        $c2 = DHttp_Cookie::create('a', 'b');
        $this->assertEquals($c1, $c2);

        $c1 = new DHttp_Cookie('d', 'e', 100);
        $c2 = DHttp_Cookie::create('d', 'e', 100);
        $this->assertEquals($c1, $c2);
    }

    public function testConstructWithDefault()
    {
        $cookie = new DHttp_Cookie('_preemail', 'gaopeng@corp.kaixin001.com');

        $this->assertEquals('_preemail', $cookie->getName());
        $this->assertEquals('gaopeng@corp.kaixin001.com', $cookie->getValue());

        $this->assertEquals(COMMON_HOST, $cookie->domain());
        $this->assertEquals('/', $cookie->path());
        $this->assertEquals(0, $cookie->expire());
        $this->assertEquals(false, $cookie->httponly());
        $this->assertEquals(false, $cookie->secure());
    }

    public function testTypeOfNameAndValue()
    {
        $cookie = new DHttp_Cookie('_uid', 122112);
        $this->assertEquals(122112, $cookie->getValue());

        $this->assertInternalType('string', $cookie->getValue());
        $this->assertInternalType('string', $cookie->getName());
    }

    public function testConstructWithCustomArguments()
    {
        $cookie = new DHttp_Cookie('_preemail', 'gaopeng@corp.kaixin001.com', 100);
        $this->assertEquals(100, $cookie->expire());

        $cookie = new DHttp_Cookie('_preemail', 'gaopeng@corp.kaixin001.com', 100,
            '/home/');
        $this->assertEquals('/home/', $cookie->path());

        $cookie = new DHttp_Cookie('_preemail', 'gaopeng@corp.kaixin001.com', 100,
            '/home/', 'www.kaixin001.com');
        $this->assertEquals('www.kaixin001.com', $cookie->domain());

        $cookie = new DHttp_Cookie('_preemail', 'gaopeng@corp.kaixin001.com', 100,
            '/home/', 'www.kaixin001.com', false);
        $this->assertEquals(false, $cookie->secure());

        $cookie = new DHttp_Cookie('_preemail', 'gaopeng@corp.kaixin001.com', 100,
            '/home/', 'www.kaixin001.com', false, false);
        $this->assertEquals(false, $cookie->httponly());

    }

    public function testChainMethod()
    {
        $cookie = new DHttp_Cookie('_preemail', 'gaopeng@corp.kaixin001.com', 100,
            '/home/', 'wwww.kaixin001.com', false);

        $this->assertInstanceOf('DHttp_Cookie', $cookie->httponly(false));
        $this->assertInternalType('bool', $cookie->httponly());

        $this->assertInstanceOf('DHttp_Cookie', $cookie->domain('kaixin001.com'));
        $this->assertInstanceOf('DHttp_Cookie', $cookie->path('/'));
        $this->assertInstanceOf('DHttp_Cookie', $cookie->expire(10));
        $this->assertInstanceOf('DHttp_Cookie', $cookie->expireAfter(12));
        $this->assertInstanceOf('DHttp_Cookie', $cookie->secure(true));

        $this->assertInstanceOf('DHttp_Cookie',
            $cookie->domain('kaixin001.com')->path('/')->secure(false)->expire(33)->httponly(false)
        );

    }

    public function testSetters()
    {
        $cookie = new DHttp_Cookie('_preemail', 'gaopeng@corp.kaixin001.com');

        $cookie->path('/blah');
        $this->assertEquals('/blah', $cookie->path());

        $cookie->domain('pic.kaixin001.com');
        $this->assertEquals('pic.kaixin001.com', $cookie->domain());

        $cookie->expire(10);
        $this->assertEquals(10, $cookie->expire());

        $cookie->secure(false);
        $this->assertFalse($cookie->secure());

        $cookie->httponly(false);
        $this->assertFalse($cookie->httponly());

    }

    public function testExpireAfter()
    {
        $now = time();

        $cookie = new DHttp_Cookie('_preemail', 'gaopeng@corp.kaixin001.com');

        $cookie->expireAfter(10);
        $this->assertGreaterThanOrEqual($now + 10, $cookie->expire());

        $cookie->expireAfter(-100);
        $this->assertGreaterThanOrEqual($now - 100, $cookie->expire());
    }

    public function testRenderHeader()
    {
        $c = new DHttp_Cookie('foo', 'bar', 1339163501);
        $domain = COMMON_HOST;
        $this->assertEquals("foo=bar; domain=$domain; path=/; expires=Fri, 08-Jun-2012 13:51:41 UTC",
            $c->renderHeader());
    }

    public function testRenderHeaderUrlencoded()
    {
        $c = new DHttp_Cookie('user name', '¸ßÅô', 1339163501);
        $domain = COMMON_HOST;
        $this->assertEquals("user+name=%B8%DF%C5%F4; domain=$domain; path=/; expires=Fri, 08-Jun-2012 13:51:41 UTC",
            $c->renderHeader());
    }

}

