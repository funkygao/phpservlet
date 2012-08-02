<?php
/**
 * DHttp_Request 的单元测试.
 *
 * @category
 * @package
 * @subpackage
 * @version $Id:$
 * @author gaopeng <gaopeng@corp.kaixin001.com>
 *
 * vim: set sw=4 ts=4 et:
 * @todo testGetArray
 */

require_once('DHttp_TestBase.php');

// avoid 'Headers already sent' error
ob_start();

class DHttp_Request_Test extends DHttp_TestBase
{

    const MT_RAND_VALUE = 963932192;

    /**
     * @param array $settings Map
     * @param array $unsets List
     *
     * @return DHttp_Request
     */
    private function _mockRequest(array $settings = array(), $unsets = array())
    {
        // 为了让mt_rand()的值可以预测
        mt_srand(0);

        $env = DHttp_Env::mock($settings);
        foreach ($unsets as $key)
        {
            unset($env[$key]);
        }

        return new DHttp_Request($env);
    }

    public function testParamAsInstanceAttribute()
    {
        $_GET = array(
            'uid'  => '121212',
            'rpid' => '22232323',
            'url'  => 'http://www.baidu.com',
        );

        $r = new DHttp_Request(null, true);
        $this->assertEquals(121212, $r->uid);
        $this->assertEquals(22232323, $r->rpid);
        $this->assertEquals('http://www.baidu.com', $r->url);
        $this->assertEquals('http://www.baidu.com', $r->getStr('url'));
    }

    public function testGetMethod()
    {
        $r = $this->_mockRequest(array('REQUEST_METHOD' => 'GET'));
        $this->assertEquals('GET', $r->getMethod());
        $this->assertEquals('get', $r->getMethod($lowercase = true));
    }

    public function testIsMethodXxx()
    {
        $r = $this->_mockRequest(array('REQUEST_METHOD' => 'GET'));
        $this->assertTrue($r->isGetMethod());
        $this->assertFalse($r->isPostMethod());
        $this->assertFalse($r->isOptionsMethod());
        $this->assertFalse($r->isDeleteMethod());
        $this->assertFalse($r->isPutMethod());
        $this->assertFalse($r->isHeadMethod());

        $r = $this->_mockRequest(array('REQUEST_METHOD' => 'POST'));
        $this->assertTrue($r->isPostMethod());
        $this->assertFalse($r->isGetMethod());

        $r = $this->_mockRequest(array('REQUEST_METHOD' => 'PUT'));
        $this->assertTrue($r->isPutMethod());
        $this->assertFalse($r->isGetMethod());

        $r = $this->_mockRequest(array('REQUEST_METHOD' => 'DELETE'));
        $this->assertTrue($r->isDeleteMethod());
        $this->assertFalse($r->isGetMethod());

        $r = $this->_mockRequest(array('REQUEST_METHOD' => 'HEAD'));
        $this->assertTrue($r->isHeadMethod());
        $this->assertFalse($r->isGetMethod());
    }

    public function testIsAjax()
    {
        $r = $this->_mockRequest(array('HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest'));
        $this->assertTrue($r->isAjax());
        $this->assertTrue($r->isXhr());
        $this->assertTrue($r->isXmlHttpRequest());

        $r = $this->_mockRequest();
        $this->assertFalse($r->isXmlHttpRequest());
        $this->assertFalse($r->isAjax());
        $this->assertFalse($r->isXhr());

        $r = $this->_mockRequest(array('HTTP_X_REQUESTED_WITH' => 'wrong'));
        $this->assertFalse($r->isXmlHttpRequest());
        $this->assertFalse($r->isAjax());
        $this->assertFalse($r->isXhr());

        $_GET = array(
            DHttp_Request::AJAX_REQUEST_KX => '1',
        );
        $r = $this->_mockRequest();
        $this->assertTrue($r->isAjax());

        $_GET = array(
            DHttp_Request::AJAX_REQUEST_KX => '1',
            'foo' => 'bar',
            'spam' => 'baz',
        );
        $r = $this->_mockRequest();
        $this->assertTrue($r->isAjax());
    }

    public function testGetServerAdmin()
    {
        $r = $this->_mockRequest();
        $this->assertNull($r->getServerAdmin());

        $r = $this->_mockRequest(array(
            'SERVER_ADMIN' => 'webmaster@kaixin001.com',
        ));
        $this->assertEquals('webmaster@kaixin001.com', $r->getServerAdmin());
    }

    public function testIsUrlRewritten()
    {
        $r = $this->_mockRequest(array(
            'REQUEST_URI' => '/?bl=34&dd=23',
            'SCRIPT_NAME' => '/index.php',
        ));
        $this->assertFalse($r->isUrlRewritten());

        $r = $this->_mockRequest(array(
            'REQUEST_URI' => '/index.php?bl=34&dd=23',
            'SCRIPT_NAME' => '/index.php',
        ));
        $this->assertFalse($r->isUrlRewritten());

        $r = $this->_mockRequest(array(
            'REQUEST_URI' => '/repaste/123434_3434.html?bl=34&dd=23',
            'SCRIPT_NAME' => '/repaste/detail.php',
        ));
        $this->assertTrue($r->isUrlRewritten());
    }

    public function testIsCli()
    {
        $r = $this->_mockRequest();
        $this->assertTrue($r->isCli());
    }

    public function testIsSslOrIsSecure()
    {
        $r = $this->_mockRequest(array('HTTPS' => 'on'));
        $this->assertTrue($r->isSsl());
        $this->assertTrue($r->isSecure());
    }

    public function testGetSetRemoveAttribute()
    {
        $r = $this->_mockRequest();
        $this->assertEmpty($r->getAttributeNames());

        $r->setAttribute('foo', 'bar');
        $this->assertNotEmpty($r->getAttributeNames());
        $this->assertEquals('bar', $r->getAttribute('foo'));

        $r->removeAttribute('foo');
        $this->assertEmpty($r->getAttributeNames());
        $this->assertNull($r->getAttribute('foo'));

        $r->setAttribute('foo', 'bar');
        $r->setAttribute('spam', 'ham');
        $r->setAttribute('baz', 'eggs');
        $this->assertEquals(3, count($r->getAttributeNames()));
        $this->assertEquals('ham', $r->getAttribute('spam'));

        $this->assertEquals(
            array(
                'foo'  => 'bar',
                'spam' => 'ham',
                'baz'  => 'eggs',
            ),
            $r->getAttributes()
        );

        $r->setAttribute('r', $r);
        $this->assertSame($r, $r->getAttribute('r'));
    }

    public function testValidGetBoolAttribute()
    {
        $r = $this->_mockRequest();
        $r->setAttribute('enabled', true);

        $this->assertTrue($r->getBoolAttribute('enabled'));

        $this->assertFalse($r->getBoolAttribute('non-exist'));
        $this->assertTrue($r->getBoolAttribute('non-exist', true));
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage foo is not bool attribute
     */
    public function testInvalidGetBoolAttribute()
    {
        $r = $this->_mockRequest();
        $r->setAttribute('foo', 'bar');
        $r->getBoolAttribute('foo');
    }

    public function testGetParamAndExists()
    {
        $_GET = array(
            'one' => '1',
            'two' => '2',
            'three' => '3',
            'foo' => 'bar',
        );

        $r = $this->_mockRequest();
        $this->assertTrue($r->exists('one'));
        $this->assertTrue($r->exists('foo'));
        $this->assertFalse($r->exists('bar'));
        $this->assertFalse($r->exists('blah'));

        $this->assertEquals(4, count($r->getParams()));
        $this->assertInternalType('int', $r->getInt('one'));
    }

    public function testGetParamWithBothGetAndPost()
    {
        $_GET = array(
            'a' => '1.2',
            'b' => '12',
            'c' => 'foo',
            'd' => 'true',
            'e' => '1',
            'f' => 'false',
        );

        $_POST = array(
            'a' => '12',
            'z' => 'dd',
        );

        $r = $this->_mockRequest(
            array(
                'REQUEST_METHOD' => 'POST',
                'CONTENT_TYPE'   => 'application/x-www-form-urlencoded',
                'CONTENT_LENGTH' => 15,
            )
        );

        $this->assertEquals(12, $r->getInt('a'));
        $this->assertCount(7, $r->getParams());
        $this->assertEquals('dd', $r->getStr('z'));

    }

    public function testGetWithTypes()
    {
        $_GET = array(
            'a' => '1.2',
            'b' => '12',
            'c' => 'foo',
            'd' => 'true',
            'e' => '1',
            'f' => 'false',
            'foo' => 'asdfasdfasdfasdf'
        );

        $r = $this->_mockRequest();

        $this->assertEquals(1.2, $r->getFloat('a'));
        $this->assertEquals(12, $r->getInt('b'));
        $this->assertEquals('foo', $r->getStr('c'));

        $this->assertEquals('1.2', $r->getStr('a'));

        $this->assertTrue($r->getBool('e'));
        $this->assertTrue($r->getBool('d'));
        $this->assertFalse($r->getBool('f'));

        // getStr with xss clean
        $env = DHttp_Env::mock();
        $r = new DHttp_Request($env, true);
        $this->assertEquals('asdfasdfasdfasdf', $r->getStr('foo', null, true));
        $this->assertInternalType('string', $r->a);
        $this->assertNull($r->non_exist);
    }

    public function testGetWithDefault()
    {
        $_GET = array(
            'a' => '1.2',
            'b' => '12',
            'c' => 'foo',
            'd' => 'true',
            'e' => '1',
            'f' => 'false',
        );
        $r = $this->_mockRequest();
        $this->assertEquals('blah', $r->getStr('non-exist', 'blah'));
        $this->assertNull($r->getInt('non-exist'));
        $this->assertEquals(100, $r->getInt('non-exist', 100));
    }

    public function testGetCookie()
    {
        $_COOKIE['foo'] = 'bar';
        $_COOKIE['spam'] = '中国人顶天立地，美国人无处藏身';

        $r = $this->_mockRequest();

        $this->assertEquals('bar', $r->getCookie('foo'));
        $this->assertEquals('中国人顶天立地，美国人无处藏身', $r->getCookie('spam'));

        // default value
        $this->assertNull($r->getCookie('non-exist'));
        $this->assertEquals('我爱大别山', $r->getCookie('non-exist', '我爱大别山'));
    }

    public function testHeader()
    {
        $r = $this->_mockRequest(array(
            'HTTP_ACCEPT_ENCODING'   => 'gzip',
            'HTTP_ACCEPT_CHARSET'    => 'GBK,utf-8;q=0.7,*;q=0.3',
            'HTTP_IF_MODIFIED_SINCE' => 'Fri, 06 Jul 2012 13:51:06 +0800',
            'HTTP_ACCEPT_LANGUAGE'   => 'zh-TW,zh;q=0.8',
        ));

        $this->assertTrue(is_array($r->headers()));
        $this->assertNotEmpty($r->headers());

        $this->assertEquals('gzip', $r->header('ACCEPT_ENCODING'));
        $this->assertEquals('gzip', $r->header('ACCEPT-ENCODING'));
        $this->assertEquals('gzip', $r->header('HTTP_ACCEPT_ENCODING'));
        $this->assertEquals('gzip', $r->header('HTTP-ACCEPT_ENCODING'));
        $this->assertEquals('gzip', $r->header('HTTP-ACCEPT-ENCODING'));

        $this->assertEquals('gzip', $r->header('aCcept_Encoding'));
        $this->assertEquals('gzip', $r->header('accept-encoding'));

        $this->assertEquals('', $r->header('non-exist'));

        $this->assertEquals('Fri, 06 Jul 2012 13:51:06 +0800', $r->header('If-Modified-Since'));
        $this->assertEquals($r->header('http-If-Modified-Since'),
            $r->header('If-Modified-Since'));
        $this->assertEquals($r->header('htTp_If-Modified-Since'),
            $r->header('If-Modified-SINCE'));

        $this->assertEquals('zh-TW', $r->getLang());
    }

    public function testGetRequestUri()
    {
        $r = $this->_mockRequest(array('REQUEST_URI' => '/home/index.php?uid=1'));
        $this->assertEquals('/home/index.php?uid=1', $r->getRequestUri());
    }

    public function testGetContentType()
    {
        $contentType = 'application/json; charset=ISO-8859-4';
        $r = $this->_mockRequest(array('CONTENT_TYPE' => $contentType));
        $this->assertEquals($contentType, $r->getContentType());

        $r = $this->_mockRequest();
        $this->assertNull($r->getContentType());
    }

    public function testGetContentLength()
    {
        $r = $this->_mockRequest(array(
            'CONTENT_TYPE'   => 'application/x-www-form-urlencoded',
            'CONTENT_LENGTH' => 15,
        ));
        $this->assertEquals(15, $r->getContentLength());

        $r = $this->_mockRequest();
        $this->assertEquals(0, $r->getContentLength());
    }

    public function testGetMediaType()
    {
        $r = $this->_mockRequest(array('CONTENT_TYPE' => 'application/json;charset=utf-8'));
        $this->assertEquals('application/json', $r->getMediaType());

        $r = $this->_mockRequest();
        $this->assertNull($r->getMediaType());

        $r = $this->_mockRequest(array('CONTENT_TYPE' => 'application/json'));
        $this->assertEquals('application/json', $r->getMediaType());
    }

    public function testGetLang()
    {
        $r = $this->_mockRequest(array('HTTP_ACCEPT_LANGUAGE' => 'zh-CN,zh;q=0.8'));
        $this->assertEquals('zh-CN', $r->getLang());

        $r = $this->_mockRequest(array(), array('HTTP_ACCEPT_LANGUAGE'));
        $this->assertEquals('foo', $r->getLang('foo'));
        $this->assertEquals('bar', $r->getLang('bar'));
    }

    public function testGetLangs()
    {
        $r = $this->_mockRequest(array('HTTP_ACCEPT_LANGUAGE' => 'zh-CN,zh;q=0.8'));
        $this->assertEquals(array('zh-CN', 'zh'), $r->getLangs());

        $r = $this->_mockRequest(array(), array('HTTP_ACCEPT_LANGUAGE'));
        $this->assertEquals(array(), $r->getLangs());
    }

    public function testGetQueryString()
    {
        $r = $this->_mockRequest(array(), array('QUERY_STRING'));
        $this->assertEquals('', $r->getQueryString());

        $r = $this->_mockRequest(array('QUERY_STRING' => 't=12&i=4'));
        $this->assertEquals('t=12&i=4', $r->getQueryString());

        $r = $this->_mockRequest(array('QUERY_STRING' => 'abcdefg'));
        $this->assertEquals('abcdefg', $r->getQueryString());
    }

    public function testGetHost()
    {
        $r = $this->_mockRequest(array(
            'SERVER_NAME' => 'kx',
            'HTTP_HOST'   => 'wWw.KAIxin001.com',
        ));
        $this->assertEquals('www.kaixin001.com', $r->getHost());
        $this->assertEquals('www', $r->getSimplifiedHost());
    }

    public function testGetHostAndStripPort()
    {
        $r = $this->_mockRequest(array(
            'SERVER_NAME' => 'kx',
            'HTTP_HOST'   => 'Www.KAIxin001.com:80',
        ));
        $this->assertEquals('www.kaixin001.com', $r->getHost());
        $this->assertEquals('www', $r->getSimplifiedHost());
    }

    public function testGetHostWhenHttpHostHeaderNonExists()
    {
        $r = $this->_mockRequest(array('SERVER_NAME' => 'Kx'), array('HTTP_HOST'));
        $this->assertEquals('kx', $r->getHost());
        $this->assertNotEquals('Kx', $r->getHost());
    }

    public function testGetHostWithPort()
    {
        $r = $this->_mockRequest(array(
            'HTTP_HOST'   => 'www.KAIxin001.com:80',
            'SERVER_NAME' => 'kX',
            'SERVER_PORT' => 80,
            'URL_SCHEME'  => 'http',
        ));
        $this->assertEquals('www.kaixin001.com:80', $r->getHostWithPort());
    }

    public function testGetSimplifiedHost()
    {
        $fixtures = array(
            'sports.sina.com.cn' => 'sports',
            'MusIc.qq.com' => 'music',
            'kaixin001.com' => 'kaixin001',
        );

        foreach ($fixtures as $host => $expected)
        {
            $r = $this->_mockRequest(array(
                'HTTP_HOST'   => $host,
            ));
            $this->assertEquals($expected, $r->getSimplifiedHost());
        }
    }

    public function testGetPort()
    {
        $r = $this->_mockRequest(array('SERVER_PORT' => 80));
        $this->assertEquals(80, $r->getPort());
        $this->assertTrue(is_int($r->getPort()));

        $r = $this->_mockRequest(array('SERVER_PORT' => 8080));
        $this->assertEquals(8080, $r->getPort());
        $this->assertTrue(is_int($r->getPort()));
    }

    public function testGetServerProtocol()
    {
        $r = $this->_mockRequest();
        $this->assertEquals('HTTP/1.1', $r->getServerProcotol(false));
        $this->assertEquals('http/1.1', $r->getServerProcotol(true));

        $r = $this->_mockRequest(array('SERVER_PROTOCOL' => 'HTTP/1.0'));
        $this->assertEquals('HTTP/1.0', $r->getServerProcotol(false));
        $this->assertEquals('http/1.0', $r->getServerProcotol(true));
    }

    public function testGetSchemeIfHttps()
    {
        $r = $this->_mockRequest(array('HTTPS' => 'on'));
        $this->assertEquals('https', $r->getScheme());
    }

    public function testGetSchemeIfHttp()
    {
        $r = $this->_mockRequest(array('URL_SCHEME' => 'http'));
        $this->assertEquals('http', $r->getScheme());
    }

    public function testGetIpUseDefault()
    {
        $r = $this->_mockRequest(array(), array('HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'));

        $this->assertEquals('0.0.0.0', $r->getRemoteIp());
        $this->assertEquals('12.21.21.12', $r->getRemoteIp(false, '12.21.21.12'));
    }

    public function testGetRemoteAddressAndGetIp()
    {
        $r = $this->_mockRequest(array('REMOTE_ADDR' => '127.0.0.1'));
        $this->assertEquals('127.0.0.1', $r->getRemoteAddr());
        $this->assertEquals($r->getRemoteAddr(), $r->getRemoteIp());

        $r = $this->_mockRequest(array(
            'REMOTE_ADDR' => '127.0.0.1',
            'HTTP_CLIENT_IP'   => '127.0.0.2',
        ));
        $this->assertEquals('127.0.0.2', $r->getRemoteIp());
        $this->assertEquals($r->getRemoteAddr(), $r->getRemoteIp());
    }

    public function testGetRemoteAddressWithForwardedFor()
    {
        $r = $this->_mockRequest(array(
            'REMOTE_ADDR'     => '127.0.0.1',
            'HTTP_CLIENT_IP'       => '127.0.0.2',
            'HTTP_X_FORWARDED_FOR' => '127.0.0.3',
        ));
        $this->assertEquals('127.0.0.3', $r->getRemoteIp());
        $this->assertEquals($r->getRemoteAddr(), $r->getRemoteIp());
    }

    public function testGetRemoteAddressWithManyForwardedFor()
    {
        $r = $this->_mockRequest(array(
            'REMOTE_ADDR'     => '127.0.0.1',
            'HTTP_CLIENT_IP'       => '127.0.0.2',
            'HTTP_X_FORWARDED_FOR' => '100.100.100.100',
        ));
        $this->assertEquals('100.100.100.100', $r->getRemoteIp());
        $this->assertEquals('100.100.100.100', $r->getRemoteIp(true));

        $r = $this->_mockRequest(array(
            'REMOTE_ADDR'     => '127.0.0.1',
            'HTTP_CLIENT_IP'       => '127.0.0.2',
            'HTTP_X_FORWARDED_FOR' => '100.100.100.100,    200.200.200.200', // 注意中间的空格
        ));
        $this->assertEquals('100.100.100.100', $r->getRemoteIp());
        $this->assertEquals('200.200.200.200', $r->getRemoteIp(true));

        $this->assertEquals($r->getRemoteAddr(), $r->getRemoteIp());

        $r = $this->_mockRequest(array(
            'REMOTE_ADDR'     => '127.0.0.1',
            'HTTP_CLIENT_IP'       => '127.0.0.2',
            'HTTP_X_FORWARDED_FOR' => '100.100.100.100,    200.200.200.200,200.200.200.201',
        ));
        $this->assertEquals('100.100.100.100', $r->getRemoteIp());
        $this->assertEquals('200.200.200.201', $r->getRemoteIp(true));
    }

    public function testGetReferer()
    {
        $r = $this->_mockRequest(array('HTTP_REFERER' => 'http://foo.com/a?b=c'));
        $this->assertEquals('http://foo.com/a?b=c', $r->getReferer());

        $r = $this->_mockRequest(array(), array('HTTP_REFERER'));
        $this->assertNull($r->getReferer());
    }

    public function testGetRefererHost()
    {
        $r = $this->_mockRequest(array('HTTP_REFERER' => 'http://www.foo.com/a?b=c'));
        $this->assertEquals('www.foo.com', $r->getRefererHost());

        $r = $this->_mockRequest(array('HTTP_REFERER' => 'http://www.news.foo.com/102/bar/detail.php?b=c'));
        $this->assertEquals('www.news.foo.com', $r->getRefererHost());

        $r = $this->_mockRequest(array(), array('HTTP_REFERER'));
        $this->assertEquals('', $r->getRefererHost());
    }

    public function testGetRefererHostWithCaseIgnore()
    {
        $r = $this->_mockRequest(array('HTTP_REFERER' => 'HTTp://wwW.FOo.cOm/a?b=c'));
        $this->assertEquals('www.foo.com', $r->getRefererHost());
    }

    public function testGetScriptName()
    {
        $r = $this->_mockRequest(array('SCRIPT_NAME' => '/home/index.php'));
        $this->assertEquals('/home/index.php', $r->getScriptName());

        $r = $this->_mockRequest(array(), array('SCRIPT_NAME'));
        $this->assertNull($r->getScriptName());
    }

    public function testGetUserAgent()
    {
        $r = $this->_mockRequest(array('HTTP_USER_AGENT' => 'bar;(foo)/spaM'));
        $this->assertEquals('bar;(foo)/spaM', $r->getUserAgent());
        $this->assertEquals('bar;(foo)/spam', $r->getUserAgent(true));
    }

    public function testGetSession()
    {
        $response = DHttp_ContextUtil::getResponse();

        $r = $this->_mockRequest();
        $this->assertNull($r->getSession(false));
        $this->assertNull($response[DHttp_Response::HDR_SET_COOKIE]);

        $session = $r->getSession();
        $this->assertNotNull($session);
        $this->assertInstanceOf('DHttp_Session', $session);
        $this->assertEquals(32, strlen($session->getId()));

        // 确保要把cookie给设置上
        $this->assertStringStartsWith('_kxsess_=', $response[DHttp_Response::HDR_SET_COOKIE]);
        $this->assertStringEndsWith('httponly', $response[DHttp_Response::HDR_SET_COOKIE]);

        $session['a'] = 1;
        $this->assertEquals(1, count($session));
        $this->assertEquals(1, $session['a']);
        $session['a'] = 'kaixin';
        $this->assertEquals('kaixin', $session['a']); // overwrite
        unset($session['a']);
        $this->assertEquals(0, count($session));

        $session['str'] = 'repaste';
        $session['obj'] = $session;
        $session['arr'] = array(
            'a' => 1,
            'b' => array(1, 8, 10)
        );
        $this->assertEquals(3, count($session));
        $this->assertInstanceOf('DHttp_Session', $session['obj']);
        $this->assertEquals('repaste', $session['str']);
        $this->assertArrayHasKey('b', $session['arr']);


        // cleanup
        unset($session['str']);
        unset($session['obj']);
        unset($session['arr']);
    }

    public function testEncodeAndDecode()
    {
        $val = '&';
        $this->assertEquals('&amp;', htmlentities($val));
        $this->assertEquals('%26', urlencode($val));
        $this->assertEquals('%26', rawurlencode($val));

        $val = ' ';
        $this->assertEquals(' ', htmlentities($val));
        $this->assertEquals('+', urlencode($val));
        // urlencode和rawurlencode唯一的区别是对空格的编码方式不同，rawurlencode遵循RFC 1738编码将空格转换为 %20。
        $this->assertEquals('%20', rawurlencode($val));

        // urlencode,除了 -_. 之外的所有非字母数字字符都将被替换成百分号（%）后跟两位十六进制数
        $this->assertEquals('-', urlencode('-'));
        $this->assertEquals('_', urlencode('_'));
        $this->assertEquals('.', urlencode('.'));
    }

}
