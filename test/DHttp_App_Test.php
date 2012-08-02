<?php
/**
 * 应用容器的测试用例.
 *
 * @category
 * @package
 * @version $Id:$
 * @author gaopeng <gaopeng@corp.kaixin001.com>
 *
 * vim: set sw=4 ts=4 et:
 */

require_once('DHttp_TestBase.php');

class MyMiddlewareForAppTest extends DHttp_Middleware
{

    public function call()
    {
        $this->getResponse()->write('foo');
        $this->_callNextMiddleware();
        $this->getResponse()->write('bar');
    }
}

class MyActionForAppTest extends DHttp_Action
{
    public function on_stop(DHttp_KxRequest $req, DHttp_Response $res)
    {
        echo 'stop called';
        $this->_stop();
    }

    public function on_index(DHttp_KxRequest $req, DHttp_Response $res)
    {
        $res->write('INDEX');
        return self::RESULT_SUCCESS;
    }

    public function on_before_forward(DHttp_KxRequest $req, DHttp_Response $res)
    {
        $req->setAttribute('beforeForward', 'gaopeng');
        $res->write('kaixin');

        return $this->getApp()->forward($this->getClassName(), 'forwardto');
    }

    public function on_forwardto(DHttp_KxRequest $req, DHttp_Response $res)
    {
        $req->setAttribute('forwarded', 'funky');
        $res->write('001');

        return self::RESULT_SUCCESS;
    }
}

class DHttp_App_Test extends KxTestCaseBase implements DHttp_Result
{

    /**
     * app_test.html里的内容
     */
    const TEMPLATE_OUTPUT = '[from template]';

    /**
     * @var DHttp_Config
     */
    private $config;

    /**
     * @var DHttp_App
     */
    private $app;

    /**
     *
     * 凡是要调用app->run() or call()，都必须调用本方法来Mock一下，否则会造成SC_METHOD_NOT_ALLOWED
     * 
     * @param array $userSettings
     */
    private function _setupBrowserEnv($userSettings = array())
    {
        $this->app = new DHttp_App($this->config, DHttp_Env::mock($userSettings));
    }

    protected function setUp()
    {
        $this->config = new KHttp_Config_Default('test', 'MyActionForAppTest', 'index');
        $this->config->setMode('debug');
        $this->config->mapResult(self::RESULT_SUCCESS, self::TYPE_SMARTY, 'samples/http/app_test.html');

        $this->app = new DHttp_App($this->config);
    }

    public function testDefaultTimezoneSet()
    {
        $this->assertEquals('Asia/Shanghai', date_default_timezone_get());
    }

    public function testConstruct()
    {
        $this->assertSame($this->config, $this->app->config());
        $this->assertSame(DHttp_ContextUtil::getKxRequest(), $this->app->request());
        $this->assertSame(DHttp_ContextUtil::getResponse(), $this->app->response());

        $this->assertInstanceOf('DHttp_KxRequest', $this->app->request());
        $this->assertInstanceOf('DHttp_Response', $this->app->response());

        $this->assertNull($this->app->action());
    }

    public function testConstructWithEnv()
    {
        $this->_setupBrowserEnv();

        $this->assertEquals('GET', $this->app->request()->getMethod());
        $this->assertEquals('http://www.kaixin001.com/home/?uid=12345',
            $this->app->request()->getReferer());

        $this->assertNull($this->app->action());
    }

    public function testRegisterMiddlewares()
    {
        // by default
        $middlewares = $this->app->getMiddlewares();
        $this->assertEquals(1 + count($this->config->getMiddlewareNames()),
            count($middlewares));

        $this->assertInstanceOf('DHttp_IMiddleware', $middlewares[0]);
        $this->assertSame($this->app, $middlewares[count($middlewares)-1]);

        // register
        $middleware = new MyMiddlewareForAppTest();
        $this->app->register($middleware);

        $middlewares = $this->app->getMiddlewares();
        $this->assertEquals(2 + count($this->config->getMiddlewareNames()),
            count($middlewares));

        $this->assertSame($middleware, $middlewares[0]);  // head
        $this->assertInstanceOf('KHttp_Middleware_Profiler', $middlewares[1]);
        $this->assertSame($this->app, $middlewares[count($middlewares)-1]); // tail
    }

    /**
     * @runInSeparateProcess
     */
    public function testMiddlewareInterceptor()
    {
        $this->_setupBrowserEnv();
        $middleware = new MyMiddlewareForAppTest();
        $this->app->register($middleware);
        $this->app->run();
        $this->assertEquals('fooINDEX' . self::TEMPLATE_OUTPUT . 'bar', $this->app->response()->body());

        $this->assertEquals('MyActionForAppTest', $this->app->action()->getClassName());
        $this->assertSame($this->app, $this->app->action()->getApp());
    }

    public function testConstants()
    {
        $this->assertEquals('kx.before', DHttp_App::HOOK_BEFORE);
        $this->assertEquals('kx.before.dispatch', DHttp_App::HOOK_BEFORE_DISPATCH);
        $this->assertEquals('kx.after.dispatch', DHttp_App::HOOK_AFTER_DISPATCH);
        $this->assertEquals('kx.after', DHttp_App::HOOK_AFTER);
    }

    public function testGetRequestAndResponse()
    {
        $this->assertSame(DHttp_ContextUtil::getKxRequest(), $this->app->request());
        $this->assertSame(DHttp_ContextUtil::getResponse(), $this->app->response());
    }

    /**
     * @expectedException KHttp_Exception_Stop
     */
    public function testStop()
    {
        $this->app->stop();
    }

    public function testHalt()
    {
        $exceptionThrowed = false;
        try
        {
            $this->app->halt(DHttp_Response::SC_FORBIDDEN, 'oh no!');
        }
        catch (KHttp_Exception_Stop $ex)
        {
            $exceptionThrowed = true;
        }

        if (!$exceptionThrowed)
        {
            $this->fail('KHttp_Exception_Stop not thrown');
        }

        $res = $this->app->response();
        $this->assertEquals(DHttp_Response::SC_FORBIDDEN, $res->status());
        $this->assertEquals('oh no!', $res->body());
        $this->assertEquals(array(
            'Content-Type: text/html; charset=UTF-8',
        ), $res->headersArray());

        $this->assertEmpty(ob_get_contents());

        $this->assertNull($this->app->action());
    }

    /**
     * @runInSeparateProcess
     */
    public function testForward()
    {
        $this->_setupBrowserEnv();
        $this->app->config()->setActionMethod('before_forward');
        $this->app->run();

        $this->assertEquals('gaopeng', $this->app->request()->getAttribute('beforeForward'));
        $this->assertEquals('funky', $this->app->request()->getAttribute('forwarded'));

        $this->expectOutputString("HTTP/1.1 200 OK\nContent-Type: text/html; charset=UTF-8\nkaixin001"
            . self::TEMPLATE_OUTPUT);

        // set action attribute
        $this->assertEquals('MyActionForAppTest', $this->app->action()->getClassName());
        $this->assertSame($this->config, $this->app->action()->getConfig());
    }

    public function testRedirect()
    {
        $exceptionThrowed = false;
        try
        {
            $this->app->redirect('http://login.kaixin001.com/');
        }
        catch (KHttp_Exception_Stop $ex)
        {
            $exceptionThrowed = true;
        }

        if (!$exceptionThrowed)
        {
            $this->fail('KHttp_Exception_Stop not thrown');
        }

        $res = $this->app->response();
        $this->assertEquals(302, $res->status());
        $this->assertEquals('', $res->body());

        $headers = $res->headers();
        $this->assertEquals('http://login.kaixin001.com/', $headers['Location']);
        $this->assertEquals('http://login.kaixin001.com/', $headers['location']);
        $this->assertEquals(
            array(
                'Content-Type: text/html; charset=UTF-8',
                'Location: http://login.kaixin001.com/',
            ),
            $res->headersArray()
        );
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testHookInvalidCallable()
    {
        $this->app->hook(DHttp_App::HOOK_BEFORE_DISPATCH, array($this, 'nonExistMethod'));
    }

    public function hookTester()
    {
        echo 'bingo';
    }

    /**
     * @runInSeparateProcess
     */
    public function testHookValidCallableWithoutObStart()
    {
        $this->_setupBrowserEnv();
        $this->app->hook(DHttp_App::HOOK_BEFORE, array($this, 'hookTester'));
        $this->expectOutputString('bingo');
        $this->app->call();
        $this->assertEquals('INDEX' . self::TEMPLATE_OUTPUT, $this->app->response()->body());
    }

    /**
     * @runInSeparateProcess
     */
    public function testHookWithObStart()
    {
        $this->_setupBrowserEnv();
        $this->app->hook(DHttp_App::HOOK_BEFORE_DISPATCH, array($this, 'hookTester'));
        $this->app->call();
        $this->assertEquals('INDEX' . self::TEMPLATE_OUTPUT . 'bingo', $this->app->response()->body());
    }

    /**
     * @runInSeparateProcess
     */
    public function testHelloWorld()
    {
        $this->_setupBrowserEnv();
        $this->app->run();
        $this->assertEquals('INDEX' . self::TEMPLATE_OUTPUT, $this->app->response()->body());
    }

    public function testOutputBufferRelated()
    {
        // test ob_get_level
        while(ob_end_clean())
        {
            // PHPUnit会有一层ob_start，因此要先清除
        }
        $this->assertEquals(0, ob_get_level());

        ob_start(); // add the 1st level
        $this->assertEquals(1, ob_get_level());
        ob_start(); // add the 2nd level
        $this->assertEquals(2, ob_get_level());
        ob_get_clean(); // del the top level
        $this->assertEquals(1, ob_get_level());
        ob_start(); // add a level
        $this->assertEquals(2, ob_get_level());
        ob_end_flush(); // del the
        $this->assertEquals(1, ob_get_level());
        ob_end_clean();
        $this->assertEquals(0, ob_get_level());

        // test ob_clean
        ob_start();
        echo 'foo';
        echo 'bar';
        ob_clean();
        $this->assertEquals('', ob_get_contents());
        $this->assertEquals('', ob_get_clean());
        $this->assertFalse(ob_end_clean());
        $this->assertEquals(0, ob_get_level());

        // test ob_get_clean
        ob_start();
        $this->assertEquals(1, ob_get_level());
        echo 'foo';
        echo 'bar';
        $this->assertEquals('foobar', ob_get_clean());
        $this->assertEquals(0, ob_get_level());

        // test ob_get_contents
        ob_start();
        echo 'foo';
        echo 'bar';
        $this->assertEquals('foobar', ob_get_contents());
        $this->assertEquals('foobar', ob_get_contents());
        $this->assertEquals('foobar', ob_get_clean());
        $this->assertEquals(0, ob_get_level());
        $this->assertEquals('', ob_get_contents());
        $this->assertFalse(ob_end_clean());
        $this->assertEquals('', ob_get_contents());
    }

    /**
     * @runInSeparateProcess
     */
    public function testHeadersSent()
    {
        $this->assertFalse(headers_sent());
        echo 'blah';
        $this->assertFalse(headers_sent());
    }

    public function testActionStop()
    {
        $config = new KHttp_Config_Default('', 'MyActionForAppTest', 'stop');
        $app = new DHttp_App($config, DHttp_Env::mock());
        $app->call();

        list($status, $header, $body) = $app->response()->finalize();

        $this->assertEquals(200, $status);
        $this->assertEquals('stop called', $body);
        $this->assertEquals('text/html; charset=UTF-8', $header['Content-Type']);

        $this->assertEquals('MyActionForAppTest', $app->action()->getClassName());
        $this->assertSame($app, $app->action()->getApp());
    }

    public function testActionGetterAndSetter()
    {
        $action = new MyActionForAppTest($this->app);

        $this->assertNull($this->app->action());

        $this->app->action($action);
        $this->assertSame($action, $this->app->action());
    }

    public function testEnableAnnotationFeature()
    {
        DHttp_App::enableAnnotationFeature();

        $this->assertTrue(true);

        // include only once
        for ($i = 0; $i < 5; $i ++)
        {
            DHttp_App::enableAnnotationFeature();
        }
        $this->assertTrue(true);
    }

}
