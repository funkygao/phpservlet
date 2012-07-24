<?php
/**
 * DHttp_Env µ¥Ôª²âÊÔÓÃÀý.
 *
 * @category
 * @package
 * @version $Id:$
 * @author gaopeng <gaopeng@corp.kaixin001.com>
 *
 * vim: set sw=4 ts=4 et:
 */

require_once('/kx/tests/KxTestCaseBase.php');

class DHttp_Env_Test extends KxTestCaseBase
{

    protected function setUp()
    {
        $_SERVER['SERVER_NAME'] = 'www.kaixin001.com';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['SCRIPT_NAME'] = '/home/index.php';
        $_SERVER['REQUEST_URI'] = '/home/index.php?one=1&two=2&three=3';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['QUERY_STRING'] = 'one=1&two=2&three=3';
        $_SERVER['HTTPS'] = '';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        unset($_SERVER['CONTENT_TYPE'], $_SERVER['CONTENT_LENGTH']);
    }

    public function testCountable()
    {
        $env = DHttp_Env::mock();

        $atLeastCount = 10;
        $this->assertGreaterThan($atLeastCount, count($env));

        $env = DHttp_Env::mock(array('blah' => 'foo', 'bar' => 'spam'));
        $atLeastCount += 2;
        $this->assertGreaterThan($atLeastCount, count($env));
    }

    public function testKaixinCustomedProperties()
    {
        $env = DHttp_Env::getInstance(true);

        $this->assertEquals('1.0a', $env['kx.version']);
        $this->assertFalse($env['kx.multithread']);
        $this->assertTrue($env['kx.multiprocess']);
        $this->assertTrue($env['kx.run_once']);
    }

    public function testRequestUri()
    {
        $env = DHttp_Env::getInstance(true);

        $this->assertEquals('/home/index.php?one=1&two=2&three=3', $env['REQUEST_URI']);
        $this->assertEquals($env['REQUEST_URI'], $env->getRequestUri());
    }

    public function testMock()
    {
        $env = DHttp_Env::mock(
            array('REQUEST_METHOD' => 'PUT')
        );

        $env2 = DHttp_Env::getInstance();

        $this->assertSame($env, $env2);
        $this->assertInstanceOf('DHttp_Env', $env);

        $env3 = DHttp_Env::getInstance(true);
        $this->assertNotSame($env, $env3);

        $this->assertEquals('PUT', $env['REQUEST_METHOD']);
        $this->assertEquals($env['REQUEST_METHOD'], $env->getMethod());
        $this->assertEquals(80, $env['SERVER_PORT']);
        $this->assertEquals($env['SERVER_PORT'], $env->getServerPort());

        $this->assertEquals('GET', $env3['REQUEST_METHOD']);

        $this->assertNull($env['NON_EXIST_PROPERTY']);
    }

    public function testScriptName()
    {
        $env = DHttp_Env::getInstance(true);

        $this->assertEquals('/home/index.php', $env['SCRIPT_NAME']);

        $env = DHttp_Env::mock(array(
            'REQUEST_URI' => '/?bl=34&dd=23',
            'SCRIPT_NAME' => '/index.php',
        ));
        $this->assertEquals('/index.php', $env['SCRIPT_NAME']);

        $env = DHttp_Env::mock(array(
            'REQUEST_URI' => '/!farm/index.php?t=3910',
            'SCRIPT_NAME' => '/!farm/index.php',
            'QUERY_STRING' => 'aid=1160&en=farm&url=index.php&t=3910',
        ));
        $this->assertEquals('/!farm/index.php', $env['SCRIPT_NAME']);
        $this->assertEquals($env['SCRIPT_NAME'], $env->getScriptName());
    }

    public function testEmptyQueryString()
    {
        unset($_SERVER['QUERY_STRING']);

        $env = DHttp_Env::getInstance(true);
        $this->assertEquals('', $env['QUERY_STRING']);
        $this->assertEquals('', $env->getQueryString());

        $env = DHttp_Env::mock(array('QUERY_STRING' => 'a=1&b=4'));
        $this->assertNotEmpty($env['QUERY_STRING']);
        $this->assertEquals($env['QUERY_STRING'], $env->getQueryString());
    }

    public function testGetRequestTime()
    {
        $env = DHttp_Env::mock(array('REQUEST_TIME' => 1338862306));

        $this->assertEquals(1338862306, $env->getRequestTime());
    }

    public function testGetServerAdmin()
    {
        $env = DHttp_Env::mock(array('SERVER_ADMIN' => 'webmaster@corp.kaixin001.com'));

        $this->assertEquals('webmaster@corp.kaixin001.com', $env->getServerAdmin());
    }

    public function testIteratorAccess()
    {
        $env = DHttp_Env::getInstance(true);

        $count = 0;
        foreach ($env as $k => $v)
        {
            $this->assertInternalType('string', $k);
            $count ++;
        }

        $this->assertGreaterThan(5, $count);
    }

    public function testServerNameAndPort()
    {
        $env = DHttp_Env::getInstance(true);
        $this->assertEquals('www.kaixin001.com', $env['SERVER_NAME']);
        $this->assertEquals('www.kaixin001.com', $env->getServerName());

        $this->assertEquals(80, $env['SERVER_PORT']);
        $this->assertEquals(80, $env->getServerPort());
    }

    public function testUrlScheme()
    {
        $env = DHttp_Env::getInstance();
        $this->assertEquals('http', $env['URL_SCHEME']);
        $this->assertEquals($env['URL_SCHEME'], $env->getUrlScheme());

        $_SERVER['HTTPS'] = 'on';
        $env = DHttp_Env::getInstance(true);
        $this->assertEquals('https', $env['URL_SCHEME']);
        $this->assertEquals($env['URL_SCHEME'], $env->getUrlScheme());

        $_SERVER['HTTPS'] = 1;
        $env = DHttp_Env::getInstance(true);
        $this->assertEquals('https', $env['URL_SCHEME']);
        $this->assertEquals($env['URL_SCHEME'], $env->getUrlScheme());

        $_SERVER['HTTPS'] = true;
        $env = DHttp_Env::getInstance(true);
        $this->assertEquals('https', $env['URL_SCHEME']);
        $this->assertEquals($env['URL_SCHEME'], $env->getUrlScheme());

        $_SERVER['HTTPS'] = 0;
        $env = DHttp_Env::getInstance(true);
        $this->assertEquals('http', $env['URL_SCHEME']);
        $this->assertEquals($env['URL_SCHEME'], $env->getUrlScheme());

        $_SERVER['HTTPS'] = '';
        $env = DHttp_Env::getInstance(true);
        $this->assertEquals('http', $env['URL_SCHEME']);
        $this->assertEquals($env['URL_SCHEME'], $env->getUrlScheme());

        $_SERVER['HTTPS'] = 'off';
        $env = DHttp_Env::getInstance(true);
        $this->assertEquals('http', $env['URL_SCHEME']);
        $this->assertEquals($env['URL_SCHEME'], $env->getUrlScheme());

        $_SERVER['HTTPS'] = 'foobar';
        $env = DHttp_Env::getInstance(true);
        $this->assertEquals('https', $env['URL_SCHEME']);
        $this->assertEquals($env['URL_SCHEME'], $env->getUrlScheme());
    }

    public function testCustomizedEnv()
    {
        $_SERVER['foo'] = 'bar';

        $env = DHttp_Env::getInstance(true);

        $this->assertNotEquals('bar', $env['foo']);
        $this->assertNull($env['foo']);
    }

    public function testStripHTTPSuffixAndGetHttpHeader()
    {
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XmlHttpRequest';

        $env = DHttp_Env::getInstance(true);

        $this->assertEquals('XmlHttpRequest', $env['HTTP_X_REQUESTED_WITH']);
        $this->assertEquals($env['HTTP_X_REQUESTED_WITH'], $env->getHttpHeader('HTTP_X_REQUESTED_WITH'));
        $this->assertEquals($env['HTTP_X_REQUESTED_WITH'], $env->getHttpHeader('X_REQUESTED_WITH'));
        $this->assertEquals($env['HTTP_X_REQUESTED_WITH'], $env->getHttpHeader('X-REQUESTED_WITH'));
        $this->assertEquals($env['HTTP_X_REQUESTED_WITH'], $env->getHttpHeader('X-REQUESTED-WITH'));
        $this->assertEquals($env['HTTP_X_REQUESTED_WITH'], $env->getHttpHeader('HTTP_X-REQUESTED_WITH'));

        // ignorecase
        $this->assertEquals($env['HTTP_X_REQUESTED_WITH'], $env->getHttpHeader('HtTP_x-REQueSTED_with'));
    }

    public function testSpecialHeaders()
    {
        $env = DHttp_Env::mock(
            array(
                'CONTENT_TYPE'   => 'foo',
                'CONTENT_LENGTH' => 101,
                'PHP_AUTH_USER'  => 'gaopeng',
                'AUTH_TYPE'      => 'basic',
            )
        );

        $this->assertEquals('foo', $env['CONTENT_TYPE']);
        $this->assertEquals('foo', $env->getContentType());
        $this->assertEquals(101, $env['CONTENT_LENGTH']);
        $this->assertEquals(101, $env->getContentLength());

        $this->assertEquals('gaopeng', $env['PHP_AUTH_USER']);
        $this->assertEquals('basic', $env['AUTH_TYPE']);
    }

    public function testIsSet()
    {
        $env = DHttp_Env::getInstance(true);

        $this->assertFalse(isset($env['BLAH_BLAH']));
        $this->assertTrue(isset($env['REQUEST_METHOD']));
    }

    public function testGetHttpHeaders()
    {
        $env = DHttp_Env::mock(
            array(
                'HTTP_ACCEPT_ENCODING'   => 'gzip,deflate,sdch',
                'HTTP_ACCEPT_LANGUAGE'   => 'zh-CN,zh;q=0.8',
                'HTTP_REFERER'           => 'http://www.hao123.com/',
                'HTTP_HOST'              => 'www.kaixin001.com',
            )
        );

        $this->assertEquals('http://www.hao123.com/', $env['HTTP_REFERER']);

        $this->assertEquals($env['HTTP_REFERER'], $env->getHttpHeader('HTTP-REFERER'));
        $this->assertEquals($env['HTTP_REFERER'], $env->getHttpHeader('REFERER'));
        $this->assertEquals($env['HTTP_REFERER'], $env->getHttpHeader('HTTP_REFERER'));
        $this->assertEquals($env['HTTP_REFERER'], $env->getHttpHeader('referer'));
        $this->assertNull($env->getHttpHeader('non-exists'));
        $this->assertEmpty($env->getHttpHeader('non-exists'));

        $this->assertEquals($env['HTTP_ACCEPT_ENCODING'], $env->getHttpHeader('ACCEPT_ENCODING'));
        $this->assertEquals($env['HTTP_ACCEPT_ENCODING'], $env->getHttpHeader('ACCEPT-ENCODING'));

        $this->assertEquals($env['HTTP_HOST'], $env->getHttpHeader('HOST'));
        $this->assertEquals($env['HTTP_ACCEPT_LANGUAGE'], $env->getHttpHeader('ACCEPT_LANGUAGE'));
    }

    public function testGetServerProtocol()
    {
        $env = DHttp_Env::mock();
        $this->assertEquals('HTTP/1.1', $env->getServerProtocol());

        $env = DHttp_Env::mock(array('SERVER_PROTOCOL' => 'HTTP/1.0'));
        $this->assertEquals('HTTP/1.0', $env->getServerProtocol());
    }

    public function testGetPhpSelf()
    {
        $env = DHttp_Env::mock(array('PHP_SELF' => "<script>alert('xss')</script>"));

        $this->assertEquals("&lt;script&gt;alert('xss')&lt;/script&gt;", $env->getPhpSelf());
    }

    public function testIfModifiedSince()
    {
        $env = DHttp_Env::mock(
            array(
                'If-Modified-Since'   => 'Fri, 06 Jul 2012 05:49:33 GMT',
            )
        );
        $this->assertEquals('', $env->getHttpHeader('If-Modified-Since'));
        $this->assertEquals('Fri, 06 Jul 2012 05:49:33 GMT', $env['If-Modified-Since']);
    }

}
