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

require_once('/kx/tests/KxTestCaseBase.php');

class MyActionForActionForwardTest extends DHttp_Action
{

    public function on_forwarded(DHttp_KxRequest $req, DHttp_Response $res)
    {
        $req->setAttribute('china1', 'come');
        $res->write('yes, forwarded got');

        return self::RESULT_SUCCESS;
    }

}

class MyActionForActionTest extends DHttp_Action
{

    public function on_index(DHttp_KxRequest $req, DHttp_Response $res)
    {
        // 可以使用self来引用DHttp_IResult里的常量
        self::RESULT_FAIL;
    }

    public function lastModified($time)
    {
        parent::_lastModified($time);
    }

    public function redirect($url, $status = DHttp_Response::SC_MOVED_TEMPORARILY)
    {
        parent::_redirect($url, $status);
    }

    public function halt($status, $body = '')
    {
        parent::_halt($status, $body);
    }

    public function stop()
    {
        parent::_stop();
    }

    public function expires($time)
    {
        parent::_expires($time);
    }

    public function on_forward(DHttp_KxRequest $req, DHttp_Response $res)
    {
        $req->setAttribute('china', 'go');

        return $this->_forward('MyActionForActionForwardTest', 'forwarded');
    }

}

class DHttp_Action_Test extends KxTestCaseBase implements DHttp_Result
{

    /**
     * @var DHttp_App
     */
    private $app;

    /**
     * @var MyActionForActionTest
     */
    private $action;

    /**
     * @var DHttp_Config
     */
    private $config;

    protected function setUp()
    {
        $this->config = new KHttp_Config_Default('', 'MyActionForActionTest');
        $this->app = new DHttp_App($this->config, DHttp_Env::mock());
        $this->action = new MyActionForActionTest($this->app);
    }

    public function testConstruct()
    {
        $this->assertSame($this->app, $this->action->getApp());
    }

    public function testGetRequest()
    {
        $this->assertSame(DHttp_ContextUtil::getKxRequest(),
            $this->action->getRequest());
    }

    public function testGetResponse()
    {
        $this->assertSame(DHttp_ContextUtil::getResponse(),
            $this->action->getResponse());
    }

    public function testGetLogger()
    {
        $this->assertNotEmpty($this->action->getLogger());
        $this->assertInstanceOf('DLogger_Facade', $this->action->getLogger());
        $this->assertSame(DLogger_Facade::getLogger(), $this->action->getLogger());
    }

    public function testGetConfig()
    {
        $cfg = $this->action->getConfig();
        $this->assertSame($this->config, $cfg);
        $this->assertEquals('MyActionForActionTest', $cfg->getActionClass());
    }

    public function testGetClassName()
    {
        $this->assertEquals('MyActionForActionTest', $this->action->getClassName());
    }

    public function testExpiresValueDifferFromLastModified()
    {
        $time = 1341553866;
        $this->action->expires($time);
        $this->action->lastModified($time);

        $res = $this->action->getResponse();
        $this->assertNotEquals(
            $res->header('Expires'),
            $res->header('Last-Modified')
        );

        $this->assertNotEmpty($res->header('Expires'));
        $this->assertNotEmpty($res->header('Last-Modified'));
    }

    public function testLastModifed()
    {
        $this->action->lastModified('invalidTimeParameter');
        $this->assertEquals('', $this->action->getResponse()->header('Last-Modified'));

        $this->action->lastModified(1341553866);
        $this->assertEquals('Fri, 06 Jul 2012 13:51:06 +0800',
            $this->action->getResponse()->header('Last-Modified'));

        $exThrown = false;
        try
        {
            $this->app = new DHttp_App($this->config, DHttp_Env::mock(
                array(
                    'HTTP_IF_MODIFIED_SINCE' => 'Fri, 06 Jul 2012 13:51:06 +0800'
                )
            ));
            $this->action = new MyActionForActionTest($this->app);
            $this->action->lastModified(1341553866);
        }
        catch (KHttp_Exception_Stop $ex)
        {
            $exThrown = true;
        }

        if (!$exThrown)
        {
            $this->fail('expected KHttp_Exception_Stop not thrown');
        }

        $this->assertEquals(304, $this->action->getResponse()->status());
        $this->assertEquals('', $this->action->getResponse()->body());
    }

    public function testRedirect()
    {
        $exThrown = false;
        try
        {
            $this->action->redirect('http://www.g.cn');
        }
        catch (KHttp_Exception_Stop $ex)
        {
            $exThrown = true;
        }

        if (!$exThrown)
        {
            $this->fail('expected KHttp_Exception_Stop not thrown');
        }


        list($status, $header, $body) = $this->action->getResponse()->finalize();
        $this->assertEquals(302, $status);
        $this->assertEquals('http://www.g.cn', $header['Location']);
        $this->assertEquals('', $body);

        // test case for non-default status code
        $exThrown = false;
        try
        {
            $this->action->redirect('http://www.s.cn/?u=1&b=3&a', DHttp_Response::SC_MOVED_PERMANENTLY);
        }
        catch (KHttp_Exception_Stop $ex)
        {
            $exThrown = true;
        }

        if (!$exThrown)
        {
            $this->fail('expected KHttp_Exception_Stop not thrown');
        }

        list($status, $header, $body) = $this->action->getResponse()->finalize();
        $this->assertEquals(301, $status);
        $this->assertEquals('http://www.s.cn/?u=1&b=3&a', $header['Location']);
        $this->assertEquals('', $body);
    }

    public function testHalt()
    {
        $exThrown = false;
        try
        {
            $this->action->halt(DHttp_Response::SC_FORBIDDEN);
        }
        catch (KHttp_Exception_Stop $ex)
        {
            $exThrown = true;
        }

        if (!$exThrown)
        {
            $this->fail('expected KHttp_Exception_Stop not thrown');
        }

        $this->assertEquals(DHttp_Response::SC_FORBIDDEN, $this->action->getResponse()->status());
        $this->assertEquals('', $this->action->getResponse()->body());

        // test case for not empty body message
        $exThrown = false;
        try
        {
            $this->action->halt(DHttp_Response::SC_BAD_REQUEST, 'shit');
        }
        catch (KHttp_Exception_Stop $ex)
        {
            $exThrown = true;
        }

        if (!$exThrown)
        {
            $this->fail('expected KHttp_Exception_Stop not thrown');
        }

        $this->assertEquals(DHttp_Response::SC_BAD_REQUEST, $this->action->getResponse()->status());
        $this->assertEquals('shit', $this->action->getResponse()->body());
    }

    public function testStop()
    {
        $this->setExpectedException('KHttp_Exception_Stop');

        $this->action->stop();
    }

    public function testExpires()
    {
        $this->action->expires('invalidTimeParameter');
        $this->assertEquals('Thu, 01 Jan 1970 00:00:00 +0000', $this->action->getResponse()->header('Expires'));

        $this->action->expires(1341553866);
        $this->assertEquals('Fri, 06 Jul 2012 05:51:06 +0000',
            $this->action->getResponse()->header('Expires'));
    }

    public function testForward()
    {
        $this->assertSame($this->config, $this->app->config());

        $this->config->setActionMethod('forward');

        // we are here!{{$username}},{{$uid}} in template file
        $this->config->mapResult(self::RESULT_SUCCESS, self::TYPE_SMARTY, 'samples/http/test.html');

        $this->app->call();

        $this->assertEquals('go', $this->app->request()->getAttribute('china'));
        $this->assertEquals('come', $this->app->request()->getAttribute('china1'));

        $res = $this->app->response();
        $this->assertEquals('yes, forwarded gotwe are here!,', $res->body());
        $this->assertEquals(200, $res->status());

    }

}
