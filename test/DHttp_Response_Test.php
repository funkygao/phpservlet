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

class DHttp_Response_Test extends DHttp_TestBase
{

    public function testDeclaredConstants()
    {
        $this->assertEquals('application/json', DHttp_Response::CONTENT_TYPE_JSON);
        $this->assertEquals('text/html', DHttp_Response::CONTENT_TYPE_HTML);
        $this->assertEquals('text/html', DHttp_Response::DEFAULT_CONTENT_TYPE);

        $this->assertEquals('UTF-8', DHttp_Response::DEFAULT_CHARSET);

        $this->assertEquals('Set-Cookie', DHttp_Response::HDR_SET_COOKIE);
        $this->assertEquals('Content-Length', DHttp_Response::HDR_CONTENT_LENGTH);
        $this->assertEquals('Content-Type', DHttp_Response::HDR_CONTENT_TYPE);

        $this->assertEquals(200, DHttp_Response::SC_OK);
        $this->assertEquals(302, DHttp_Response::SC_MOVED_TEMPORARILY);
    }

    public function testConstructorWithoutArgs()
    {
        $r = new DHttp_Response();

        $this->assertEquals('', $r->body());
        $this->assertEquals(200, $r->status());
        $this->assertEquals(0, $r->length());

        $this->assertEquals('text/html; charset=UTF-8', $r['Content-Type']);
    }

    public function testConstructorWithArgs()
    {
        $body = 'Page Not Found';
        $r = new DHttp_Response(
            $body,
            404,
            array(
                'Content-Type' => 'application/json',
                'X-Created-By' => 'KaixinRen'
            )
        );

        $this->assertEquals($body, $r->body());
        $this->assertEquals(404, $r->status());
        $this->assertEquals(strlen($body), $r->length());
        $this->assertEquals('application/json', $r['Content-Type']);
        $this->assertEquals('KaixinRen', $r['X-Created-By']);

        $this->assertEmpty($r['non-exist']);
    }

    public function testSetAndGetStatus()
    {
        $r = new DHttp_Response();
        $r->status(500);
        $this->assertEquals(500, $r->status());

        $r->status(200);
        $this->assertEquals(200, $r->status());

        $this->setExpectedException('InvalidArgumentException', 'Invalid status code');
        $r->status(19899);

        $this->setExpectedException('InvalidArgumentException', 'Invalid status code');
        $r->status(1);
    }

    public function testGetHeadersAndHeadersArray()
    {
        $r = new DHttp_Response();
        $headers = $r->headers();
        $this->assertEquals(1, count($headers));
        $this->assertEquals('text/html; charset=UTF-8', $headers['Content-Type']);

        $r->header('Foo', 'bar');
        $this->assertEquals(2, count($r->headers()));
        $r->header('Location', 'http://login.kaixin001.com');
        $names = $values = '';
        foreach($r->headers() as $k => $v)
        {
            $names .= $k;
            $values .= $v;
        }
        $this->assertEquals('Content-TypeFooLocation', $names);
        $this->assertEquals('text/html; charset=UTF-8barhttp://login.kaixin001.com', $values);

        $this->assertEquals(
            array(
                'Content-Type: text/html; charset=UTF-8',
                'Foo: bar',
                'Location: http://login.kaixin001.com',
            ),
            $r->headersArray()
        );
    }

    public function testGetAndSetHeader()
    {
        $r = new DHttp_Response();
        $r->header('X-Foo', 'Bar');
        $this->assertEquals('Bar', $r->header('X-Foo'));

        // normalized
        $r->header('cOntent-tYpe', 'text/plain');
        $this->assertEquals('text/plain', $r->header('Content-Type'));
    }

    public function testHeaderOverwrite()
    {
        $r = new DHttp_Response();
        $r->header('Location', 'http://login.kaixin001.com');
        $this->assertEquals('http://login.kaixin001.com', $r->header('Location'));

        $r->header('Location', 'http://security.kaixin001.com');
        $this->assertEquals('http://security.kaixin001.com', $r->header('Location'));
    }

    public function testHeaderNotReplace()
    {
        $r = new DHttp_Response();
        $r->header('Cache-Control', 'no-store, no-cache, must-revalidate');
        $r->header('Cache-Control', 'post-check=0, pre-check=0', false);
        $this->assertEquals("no-store, no-cache, must-revalidate\npost-check=0, pre-check=0",
            $r->header('Cache-Control'));

        $r->header('Cache-Control', 'bingo', true);
        $this->assertEquals('bingo', $r->header('Cache-Control'));
    }

    public function testGetAndSetBody()
    {
        $r = new DHttp_Response('Foo');
        $this->assertEquals('Foo', $r->body());

        $r = new DHttp_Response();
        $r->body('Foo');
        $this->assertEquals('Foo', $r->body());
        $r->body('<input id="presence_data" type="hidden" value="9940649,,">');
        $this->assertEquals('<input id="presence_data" type="hidden" value="9940649,,">', $r->body());

        $r = new DHttp_Response('Spam');
        $r->body('Foo'); // overwrite
        $this->assertEquals('Foo', $r->body());
    }

    public function testGetLength()
    {
        $body = '我在北京天安门';
        $r = new DHttp_Response($body);
        $this->assertEquals(strlen($body), $r->length());
    }

    public function testSetLength()
    {
        $r = new DHttp_Response();
        $r->length(3);
        $this->assertEquals(3, $r->length());
        $r->length(0);
        $this->assertEquals(0, $r->length());

        $r->length(-10);
        $this->assertEquals(0, $r->length());
    }

    public function testWriteAppend()
    {
        $r = new DHttp_Response('Foo');
        $r->write('Bar');
        $this->assertEquals('FooBar', $r->body());
        $r->write('');
        $this->assertEquals('FooBar', $r->body());

        $this->setExpectedException('InvalidArgumentException');
        $r->write(5);
    }

    public function testWriteReplace()
    {
        $r = new DHttp_Response('Foo');
        $r->write('Bar', true);
        $this->assertEquals('Bar', $r->body());
        $r->write('append');
        $this->assertEquals('Barappend', $r->body());
        $r->body('hello', true);
        $this->assertEquals('hello', $r->body());
    }

    public function testWriteWithLengthUpdated()
    {
        $r = new DHttp_Response('Foo');
        $this->assertEquals(3, $r->length());

        $r->write('kaixin');
        $this->assertEquals(9, $r->length());

        $r->write('all', true);
        $this->assertEquals(3, $r->length());
    }

    public function testFinalize()
    {
        $r = new DHttp_Response();
        $r->status(404);
        $r['Content-Type'] = 'application/json';
        $r->write('Foo');

        $result = $r->finalize();
        $this->assertEquals(3, count($result));
        $this->assertEquals(404, $result[0]);
        $this->assertEquals('application/json', $result[1]['Content-Type']);

        list($status, $headers, $body) = $r->finalize();
        $this->assertEquals(404, $status);
        $this->assertEquals('Foo', $body);
    }

    public function testFinalizeWithoutContent()
    {
        $r = new DHttp_Response();
        $r->status(DHttp_Response::SC_NO_CONTENT);
        $r['Content-Type'] = 'application/json';
        $r->write('Foo');

        $result = $r->finalize();
        $this->assertEquals(3, count($result));
        $this->assertEquals('', $result[2]);

        list($status, $headers, $body) = $r->finalize();
        $this->assertEquals(204, $status);
        $this->assertEquals('', $body);
        $this->assertNull($headers['Content-Type']);
        $this->assertNull($headers['Content-Length']);
        $this->assertNull($r->contentType());

        $this->assertEquals(3, $r->length());
    }

    public function testRedirect()
    {
        $r = new DHttp_Response();
        $r->redirectFinal('/foo');
        $this->assertEquals(302, $r->status());
        $this->assertEquals('/foo', $r['Location']);
    }

    public function testRedirectWithCustomStatus()
    {
        $r = new DHttp_Response();
        $r->redirectFinal('/foo', 307);
        $this->assertEquals(307, $r->status());
        $this->assertEquals('/foo', $r['Location']);

        $this->assertTrue($r->hasRedirects());
    }

    public function testGetStatusCodes()
    {
        $r = new DHttp_Response();
        $this->assertNotEmpty($r->getStatusCodes());
        $this->assertInternalType('array', $r->getStatusCodes());
        $this->assertGreaterThan(10, count($r->getStatusCodes()));
    }

    public function testHasRedirectsOrNot()
    {
        $r = new DHttp_Response();
        $this->assertFalse($r->hasRedirects());

        $r->redirectFinal('http://www.google.com');
        $this->assertTrue($r->hasRedirects());

        $r->status(200);
        $this->assertTrue($r->hasRedirects());
        unset($r['Location']);
        $this->assertFalse($r->hasRedirects());
    }

    public function testRedirectWithoutDeadLoop()
    {
        DHttp_ContextUtil::getRequest(DHttp_Env::mock(array(
            'SCRIPT_NAME' => '/interface/limittip.php',
        )));

        $r = new DHttp_Response();
        $this->assertFalse($r->hasRedirects());
        $r->redirectFinal('/interface/limittip.php');
        $this->assertFalse($r->hasRedirects());

        $r = new DHttp_Response();
        $this->assertFalse($r->hasRedirects());
        $r->redirectFinal('/interface/limittip.php?foo=bar&spam=99');
        $this->assertFalse($r->hasRedirects());

        $r = new DHttp_Response();
        $this->assertFalse($r->hasRedirects());
        $r->redirectFinal('/p.php?foo=bar&spam=99');
        $this->assertTrue($r->hasRedirects());
    }

    private function _testResponseStatusCode($validStatusCodes, $method)
    {
        foreach ($validStatusCodes as $status)
        {
            $r = new DHttp_Response('', $status);
            $this->assertTrue($r->$method());
        }

        foreach ($r->getStatusCodes() as $status)
        {
            if (!in_array($status, $validStatusCodes))
            {
                $r = new DHttp_Response('', $status);
                $this->assertFalse($r->$method());
            }
        }

    }

    public function testIsEmptyOrNot()
    {
        $this->_testResponseStatusCode(array(201, 204, 304), 'isEmpty');
    }

    public function testIsOkOrNot()
    {
        $this->_testResponseStatusCode(array(200), 'isOk');
    }

    public function testIsForbiddenOrNot()
    {
        $this->_testResponseStatusCode(array(403), 'isForbidden');
    }

    public function testIsNotFoundOrNot()
    {
        $this->_testResponseStatusCode(array(404), 'isNotFound');
    }

    public function testIsClientErrorOrNot()
    {
        $this->_testResponseStatusCode(array(
            400, 401, 402, 403, 404, 405,
            406, 407, 408, 409, 410, 411,
            412, 413, 414, 415, 416, 417,
            422, 423,
        ), 'isClientError');
    }

    public function testIsServerErrorOrNot()
    {
        $this->_testResponseStatusCode(array(
            500, 501, 502, 503, 504, 505
        ), 'isServerError');
    }

    public function testDisableBrowserCache()
    {
        $r = new DHttp_Response();

        $lastModified = gmdate("D, d M Y H:i:s") . ' GMT';
        $r->disableBrowserCache();

        $this->assertEquals('no-cache', $r->header('Pragma'));
        $this->assertEquals('Mon, 26 Jul 1997 05:00:00 GMT', $r['Expires']);
        $this->assertEquals("no-store, no-cache, must-revalidate\npost-check=0, pre-check=0",
            $r->header('Cache-Control'));
        $this->assertEquals($lastModified, $r->header('Last-Modified'));
    }

    public function testOffsetExistsAndGet()
    {
        $r = new DHttp_Response();
        $this->assertFalse(empty($r['Content-Type']));
        $this->assertNotEmpty($r['Content-Type']);
        $this->assertNull($r['foo']);
    }

    public function testIteration()
    {
        $h = new DHttp_Response();
        $output = '';
        foreach ($h as $key => $value)
        {
            $output .= $key . $value;
        }
        $this->assertEquals('Content-Typetext/html; charset=UTF-8', $output);
    }

    public function testCountable()
    {
        $r1 = new DHttp_Response();
        $this->assertEquals(1, count($r1)); //Content-Type

        $r1->header('foo', 'bar');
        $this->assertEquals(2, count($r1));
    }

    public function testSetCookieWithDefaults()
    {
        $r = new DHttp_Response();
        $r->setCookie(new DHttp_Cookie('_user', 'gaopeng'));
        $expected = '_user=gaopeng; domain=' . COMMON_HOST . '; path=/';
        $this->assertEquals($expected, $r[DHttp_Response::HDR_SET_COOKIE]);
        $this->assertEquals($r[DHttp_Response::HDR_SET_COOKIE],
            $r->header(DHttp_Response::HDR_SET_COOKIE));

        // set another cookie
        $r->setCookie(new DHttp_Cookie('_email', 'gaopeng@email.com'));
        $expected = implode("\n",
            array(
                $expected,
                '_email=gaopeng%40email.com; domain=' . COMMON_HOST . '; path=/'
            )
        );
        $this->assertEquals($expected, $r[DHttp_Response::HDR_SET_COOKIE]);

        // set the 3rd cookie
        $r->setCookie(new DHttp_Cookie('_ref', '232323_3334_asdfasdfasdf_il@adfb>9'));
        $expected = implode("\n",
            array(
                $expected,
                '_ref=232323_3334_asdfasdfasdf_il%40adfb%3E9; domain=' . COMMON_HOST . '; path=/'
            )
        );
        $this->assertEquals($expected, $r[DHttp_Response::HDR_SET_COOKIE]);
    }

    public function testOverwriteCookie()
    {
        $r = new DHttp_Response();
        $r->setCookie(new DHttp_Cookie('_user', 'gaopeng'));
        $r->setCookie(new DHttp_Cookie('_user', 'kaixin'));

        $expected = '_user=kaixin; domain=' . COMMON_HOST . '; path=/';
        $this->assertEquals($expected, $r[DHttp_Response::HDR_SET_COOKIE]);
    }

    public function testDeleteCookie()
    {
        $r = new DHttp_Response();
        $r->deleteCookie('_user');
        $expected = '_user=; domain=' . COMMON_HOST . '; path=/';
        $this->assertNotEquals($expected, $r[DHttp_Response::HDR_SET_COOKIE]);
        $this->assertStringStartsWith($expected, $r->header(DHttp_Response::HDR_SET_COOKIE));
    }

    public function testSetCookieWithAdditionalParams()
    {
        $r = new DHttp_Response();
        $c = DHttp_Cookie::create('_user', 'funky.gao@163.com');
        $c->domain('www.kaixin001.com')->path('/home')->httponly(true)->secure(true);
        $r->setCookie($c);

        $expected = '_user=funky.gao%40163.com; domain=www.kaixin001.com; path=/home; secure; httponly';
        $this->assertEquals($expected, $r->header(DHttp_Response::HDR_SET_COOKIE));

        $c = DHttp_Cookie::create('_bingo', '9009');
        $c->domain('kx.cn')->httponly(false)->secure(false);
        $r->setCookie($c);

        $expected = implode("\n",
            array(
                $expected,
                '_bingo=9009; domain=kx.cn; path=/'
            )
        );
        $this->assertEquals($expected, $r[DHttp_Response::HDR_SET_COOKIE]);
    }

    public function testCookieExpires()
    {
        $r = new DHttp_Response();
        $c = DHttp_Cookie::create('_user', 'funky.gao@163.com');
        $c->domain('www.kaixin001.com')->path('/home')->expire(100);
        $r->setCookie($c);

        $this->assertEquals('_user=funky.gao%40163.com; domain=www.kaixin001.com; path=/home; expires=Thu, 01-Jan-1970 00:01:40 UTC',
            $r->header(DHttp_Response::HDR_SET_COOKIE));

        $expireAfter = 100;
        $c->expireAfter($expireAfter);
        $r->setCookie($c);
        $label = gmdate('D, d-M-Y H:i:s e', time() + $expireAfter);
        $this->assertEquals('_user=funky.gao%40163.com; domain=www.kaixin001.com; path=/home; expires=' . $label,
            $r->header(DHttp_Response::HDR_SET_COOKIE));

    }

    public function testGetStatusMessage()
    {
        $r = new DHttp_Response();
        $this->assertEquals('OK', $r->getStatusMessage(200));

        $this->assertEquals('Not Found', $r->getStatusMessage(404));
        $this->assertEquals('Found', $r->getStatusMessage(302));
        $this->assertEquals('Service Unavailable', $r->getStatusMessage(503));
    }

    public function testRenderStatus()
    {
        $r = new DHttp_Response();

        $this->assertEquals('HTTP/1.1 200 OK', $r->renderStatus(PHP_SAPI, 'HTTP/1.1', 200));
        $this->assertEquals('HTTP/1.0 200 OK', $r->renderStatus(PHP_SAPI, 'HTTP/1.0', 200));
        $this->assertEquals('HTTP/1.1 404 Not Found', $r->renderStatus(PHP_SAPI, 'HTTP/1.1', 404));

        $this->assertEquals('HTTP/1.1 405 Method Not Allowed', $r->renderStatus(PHP_SAPI, 'HTTP/1.1',
            DHttp_Response::SC_METHOD_NOT_ALLOWED));

        $this->assertEquals('Status: 200 OK', $r->renderStatus('cgi', 'HTTP/1.1', 200));
    }

    public function testContentType()
    {
        $r = new DHttp_Response();

        $this->assertEquals('text/html; charset=UTF-8', $r->contentType());

        $r->contentType('text/plain');
        $this->assertEquals('text/plain; charset=UTF-8', $r->contentType());

        $r->contentType('application/json', 'GBK');
        $this->assertEquals('application/json; charset=GBK', $r->contentType());
    }

    public function testReset()
    {
        $r = new DHttp_Response();

        $this->assertEquals('text/html; charset=UTF-8', $r->contentType());
        $r->status(404);
        $this->assertEquals(404, $r->status());

        $r->write('blah');
        $this->assertEquals('blah', $r->body());

        $r->reset();

        $this->assertEquals(404, $r->status());
        $this->assertEquals('', $r->body());
        $this->assertNull($r->contentType());
    }

}
